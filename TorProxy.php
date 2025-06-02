<?php

/**
 * Tor Proxy Plugin
 *
 * @description A Tor Proxy Server add-on for RaspAP 
 * @author      Bill Zimmerman <billzimmerman@gmail.com>
 * @license     https://github.com/billz/SamplePlugin/blob/master/LICENSE
 * @see         src/RaspAP/Plugins/PluginInterface.php
 * @see         src/RaspAP/UI/Sidebar.php
 */

namespace RaspAP\Plugins\TorProxy;

use RaspAP\Plugins\PluginInterface;
use RaspAP\UI\Sidebar;

class TorProxy implements PluginInterface
{

    private string $pluginPath;
    private string $pluginName;
    private string $templateMain;
    private string $serviceName;
    private string $label;
    private string $icon;
    private string $torConfig;
    private string $netInterface;
    private string $serviceStatus;

    public function __construct(string $pluginPath, string $pluginName)
    {
        $this->pluginPath = $pluginPath;
        $this->pluginName = $pluginName;
        $this->templateMain = 'main';
        $this->label = _('Tor Proxy');
        $this->icon = 'fas fa-lemon'; //'ra-onion';
        $this->serviceName = 'tor@default.service';
        $this->torConfig = '/etc/tor/torrc';
        $this->netInterface = 'eth0';
        $this->serviceStatus = $this->getServiceStatus();

        if ($loaded = self::loadData()) {
            $this->serviceStatus = $loaded->getServiceStatus();
        }
    }

    /**
     * Initialize the TorProxy plugin and create a sidebar item
     *
     * @param Sidebar $sidebar an instance of the Sidebar
     * @see src/RaspAP/UI/Sidebar.php
     * @see https://fontawesome.com/icons
     */
    public function initialize(Sidebar $sidebar): void
    {

        $label = $this->label;
        $icon = $this->icon;
        $action = 'plugin__'.$this->getName();
        $priority = 75;
        $service_name = $this->serviceName;

        $sidebar->addItem($label, $icon, $action, $priority);
    }

    /**
     * Handle page actions by processing inputs and rendering a plugin template
     *
     * @param string $page the current page route
     */
    public function handlePageAction(string $page): bool
    {
        // Verify that this plugin should handle the page
        if (strpos($page, "/plugin__" . $this->getName()) === 0) {

            // Instantiate a StatusMessage object
            $status = new \RaspAP\Messages\StatusMessage;

            if (!RASPI_MONITOR_ENABLED) {
                if (isset($_POST['saveSettings'])) {
                    $return = $this->persistConfig($status, $_POST, $this->torConfig);
                    $status->addMessage('Restart the Tor service for your changes to take effect', 'info');
                } elseif (isset($_POST['startTorService'])) {
                    $status->addMessage('Attempting to start '.$this->serviceName, 'info');
                    exec('sudo /bin/systemctl start '.$this->serviceName, $output, $return);
                    if ($return == 0) {
                        $status->addMessage('Successfully started '.$this->serviceName, 'success');
                        $this->setServiceStatus('up');
                    } else {
                        $status->addMessage('Failed to start '.$this->serviceName, 'danger');
                        $this->setServiceStatus('down');
                    }
                } elseif (isset($_POST['restartTorService'])) {
                    $status->addMessage('Attempting to restart '.$this->serviceName, 'info');
                    exec('sudo /bin/systemctl restart '.$this->serviceName, $output, $return);
                    if ($return == 0) {
                        $status->addMessage('Successfully restarted '.$this->serviceName, 'success');
                        $this->setServiceStatus('up');
                    } else {
                        $status->addMessage('Failed to start '.$this->serviceName, 'danger');
                        $this->setServiceStatus('down');
                    }

                } elseif (isset($_POST['stopTorService'])) {
                    $status->addMessage('Attempting to stop '.$this->serviceName, 'info');
                    exec('sudo /bin/systemctl stop '.$this->serviceName, $output, $return);
                    if ($return == 0) {
                        $status->addMessage('Successfully stopped '.$this->serviceName, 'success');
                        $this->setServiceStatus('down');
                    } else {
                        $status->addMessage('Failed to stop '.$this->serviceName, 'danger');
                    }
                }
            }

            // parse current Tor configuration
            $config = $this->parseConfig($this->torConfig);

            // get default network interface settings
            $ipv4Address = $this->getInterfaceIPv4($this->netInterface) ?? '127.0.0.1';
            $subnet = $this->getInterfaceSubnet($this->netInterface) ?? '192.168.1.0/24';
            $policy = 'accept '.$subnet;

            // override config settings if empty
            $config['SocksPortIP'] = !empty($config['SocksPortIP']) ? $config['SocksPortIP'] : $ipv4Address;
            $config['SocksPolicy'] = !empty($config['SocksPolicy']) ? $config['SocksPolicy'] : $policy;

            // Populate template data
            $__template_data = [
                'title' => _('Tor Proxy'),
                'description' => _('A Tor Proxy Server add-on for RaspAP'),
                'author' => _('Bill Zimmerman'),
                'uri' => 'https://github.com/billz/TorProxy',
                'icon' => $this->icon,
                'serviceStatus' => $this->getServiceStatus(),
                'serviceName' => $this->serviceName,
                'action' => 'plugin__'.$this->getName(),
                'pluginName' => $this->getName(),
                'content' => _('Administer the Tor proxy server with the settings below.'),
                'serviceLog' => $this->getServiceLog(),
                'arrConfig' => $config
            ];

            echo $this->renderTemplate($this->templateMain, compact(
                "status",
                "__template_data"
            ));
            return true;
        }
        return false;
    }

    /**
     * Renders a template from inside a plugin directory
     * @param string $templateName
     * @param array $__data
     */
    public function renderTemplate(string $templateName, array $__data = []): string
    {
        $templateFile = "{$this->pluginPath}/{$this->getName()}/templates/{$templateName}.php";

        if (!file_exists($templateFile)) {
            return "Template file {$templateFile} not found.";
        }
        if (!empty($__data)) {
            extract($__data);
        }

        ob_start();
        include $templateFile;
        return ob_get_clean();
    }

    /**
     * Returns a service status
     * @return string $status
     */
    public function getServiceStatus()
    {
        exec('sudo /bin/systemctl status '.$this->serviceName, $output, $return);
        foreach ($output as $line) {
            if (strpos($line, 'Active: active (running)') !== false) {
                return 'up';
            }
        }
    return 'down';
    }

    /**
     * Returns the current Tor configuration
     *
     * @param string $cfgPath
     * @return array $arrConfig
     */
    public function parseConfig($cfgPath): array
    {
        if (!is_readable($cfgPath)) {
            throw new \RuntimeException("Cannot read Tor config file at: $cfgPath");
        }

        $arrConfig = [];
        $lines = file($cfgPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $trimmed = ltrim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            // strip comments
            $lineSansComment = preg_replace('/\s+#.*/', '', $trimmed);

            // match key-value pairs
            if (preg_match('/^([A-Za-z][A-Za-z0-9]*)\s+(.*)$/', $lineSansComment, $matches)) {
                $key = $matches[1];
                $value = trim($matches[2]);

                // parse SocksPort values
                if ($key === 'SocksPort' && preg_match('/^([\d\.]+):(\d+)$/', $value, $addrMatch)) {
                    $config['SocksPortIP'] = $addrMatch[1];
                    $config['SocksPort'] = (int)$addrMatch[2];
                } else {
                    if (isset($config[$key])) {
                        if (is_array($config[$key])) {
                            $config[$key][] = $value;
                        } else {
                            $config[$key] = [$config[$key], $value];
                        }
                    } else {
                        $config[$key] = $value;
                    }
                }
            }
        }
        return $config;
    }


    /**
     * Safely updates the Tor config, preserving comments
     *
     * @param object $status
     * @param array $post
     * @return object
     */
    public function persistConfig($status, $post, $torrcPath)
    {
        $status->addMessage('Attempting to save Tor Proxy settings', 'info');
        $directives = [
            'SocksPort' => "{$post['txtinternal']}:{$post['txtport']}",
            'SocksPolicy' => "{$post['txtpolicy']}",
            'RunAsDaemon' => '1',
            'CookieAuthentication' => '1',
            'DataDirectory' => '/var/lib/tor',
            'ControlPort' => "{$post['txtcontrolport']}"
        ];

        $existing = file_exists($torrcPath) ? file($torrcPath, FILE_IGNORE_NEW_LINES) : [];
        $output = [];
        $seen = array_fill_keys(array_keys($directives), false);

        foreach ($existing as $line) {
            $trimmed = trim($line);
            $matched = false;

            foreach ($directives as $key => $value) {
                if (preg_match("/^$key\b/i", $trimmed)) {
                    $output[] = "$key $value";
                    $seen[$key] = true;
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                $output[] = $line;
            }
        }

        // append directives if missing
        foreach ($directives as $key => $value) {
            if (!$seen[$key]) {
                $output[] = "$key $value";
            }
        }

        try {
            file_put_contents('/tmp/torrc', implode(PHP_EOL, $output) . PHP_EOL);
            system('sudo cp /tmp/torrc ' . escapeshellarg($torrcPath), $result);

            if ($result === 0) {
                $status->addMessage("Tor configuration saved successfully to {$torrcPath}", 'success');
            } else {
                $status->addMessage("Failed to save Tor configuration to {$torrcPath}", 'error');
            }
        } catch (\Exception $e) {
            $status->addMessage("Failed to write Tor configuration: " . $e->getMessage(), 'error');
        }
        return $status;
    }

    /**
     * Retrieves the subnet for a given network interface
     *
     * @param string $interface
     * @return string|null if not found
     */
    public function getInterfaceSubnet($interface): ?string
    {
        $output = [];
        $cmd = "ip -o -f inet addr show ". escapeshellarg($interface) ." 2>/dev/null";
        exec($cmd, $output);

        if (empty($output)) {
            return null;
        }

        foreach ($output as $line) {
            if (preg_match('/inet (\d+\.\d+\.\d+\.\d+)\/(\d+)/', $line, $matches)) {
                $ip = $matches[1];
                $prefix = (int)$matches[2];
                $subnet = $this->ipToSubnet($ip, $prefix);
                return "$subnet/$prefix";
            }
        }
        return null;

    }

    private function ipToSubnet(string $ip, int $prefix): string
    {
        $ipLong = ip2long($ip);
        $netmask = -1 << (32 - $prefix);
        $netmask = $netmask & 0xFFFFFFFF; // ensure unsigned
        $network = $ipLong & $netmask;
        return long2ip($network);
    }

    /**
     * Retrieves the IPv4 address for a given network interface
     *
     * @param string $interface
     * @return string|null Returns the IP address or null if not found
     */
    public function getInterfaceIPv4(string $interface): ?string
    {
        $output = [];
        $cmd = "ip -o -f inet addr show " . escapeshellarg($interface) . " 2>/dev/null";
        exec($cmd, $output);

        foreach ($output as $line) {
            if (preg_match('/inet (\d+\.\d+\.\d+\.\d+)\//', $line, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Returns the current tor@default.service status
     * @return string $serviceLog
     */
    public function getServiceLog()
    {
        exec('sudo /bin/systemctl status '.$this->serviceName, $output);
        $serviceLog = implode("\n", $output);
        return $serviceLog;
    }

    // Setter for service status
    public function setServiceStatus($status)
    {
        $this->serviceStatus = $status;
    }

    // Static method to load persisted data
    public static function loadData(): ?self
    {
        $filePath = "/tmp/plugin__".self::getName() .".data";
        if (file_exists($filePath)) {
            $data = file_get_contents($filePath);
            return unserialize($data);
        }
        return null;
    }

    // Returns an abbreviated class name
    public static function getName(): string
    {
        return basename(str_replace('\\', '/', static::class));
    }

}

