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
                    if (isset($_POST['interface'])) {
                        $return = $this->persistConfig($status, $_POST);
                        $status->addMessage('Restarting '.$this->serviceName, 'info');
                    }

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

            // Parse the current Tor configuration
            $config = $this->parseConfig();

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
     * @return array $arrConfig
     */
    public function parseConfig()
    {
        $arrConfig = [];
        exec('cat ' . escapeshellarg($this->danteConfig), $cfg);

        $blockKey = null;
        $blockLines = []; // accumulate block lines

        foreach ($cfg as $line) {
            // skip empty lines or comments
            $line = trim($line);
            if (strlen($line) === 0 || $line[0] === '#') {
                continue;
            }

            // block start
            if (preg_match('/^(client|socks)\s+pass\s*\{$/', $line, $matches)) {
                $blockKey = $matches[1] . '_pass';
                $blockLines = [];
                continue;
            }

            // block end
            if ($blockKey && $line === '}') {
                $arrConfig[$blockKey] = $blockLines;
                $blockKey = null;
                $blockLines = [];
                continue;
            }

            // accumulate block lines
            if (preg_match('/^([^:]+):(.*)$/', $line, $matches)) {
            $key = trim($matches[1]);
            $value = trim($matches[2]);

            // split internal address and port
            if ($key === 'internal' && preg_match('/^([\d\.]+)\s+port=(\d+)$/', $value, $internalMatches)) {
                    $arrConfig['internal_addr'] = $internalMatches[1];
                    $arrConfig['internal_port'] = $internalMatches[2];
                } else {
                    $arrConfig[$key] = $value;
                }
            } elseif (preg_match('/^([^=]+)=(.*)$/', $line, $matches)) {
                $key = trim($matches[1]);
                $value = trim($matches[2]);
                $arrConfig[$key] = $value;
            }
        }
        return $arrConfig;
    }

    /* Persists the Tor configuration
     *
     * @param object $status
     * @param object $post
     */
    public function persistConfig($status, $post)
    {
        $status->addMessage('Saving Tor Proxy settings', 'info');

        $content = <<<CONFIG
        logoutput: syslog
        user.privileged: {$post['txtuserprivileged']}
        user.unprivileged: {$post['txtuserunprivileged']}

        # The listening network interface or address.
        internal: {$post['txtinternal']} port={$post['txtport']}

        # The proxying network interface or address.
        external: {$post['interface']}

        # socks-rules determine what is proxied through the external interface.
        socksmethod: {$post['txtsocksmethod']}

        # client-rules determine who can connect to the internal interface.
        clientmethod: {$post['txtclientmethod']}

        client pass {
            from: {$post['txtclientip']}
        }
        socks pass {
            from: 0.0.0.0/0 to: 0.0.0.0/0
        }

        CONFIG;

        try {
            file_put_contents('/tmp/torrc', $content);
            system('sudo cp /tmp/torrc ' .$this->torConfig, $result);
            if ($result == 0) {
                $status->addMessage('Tor configuration saved successfully to '.$this->torConfig, 'success');
            } else {
                $status->addMessage('Failed to save Tor configuration to '.$this->torConfig, 'error');
            }
        } catch (\Exception $e) {
            $status->addMessage('Failed to save Tor configuration: ' .$e->getMessage(), 'error');
        }

        return $status;
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

