# Tor Proxy Plugin

<img width="356" alt="torproxy" src="https://github.com/user-attachments/assets/3f9bf8e8-315a-499b-baf0-fdb44ffa5c14" />

This plugin adds Tor proxy server support to RaspAP.

## Contents

 - [Installation](#installation)
 - [Configuration](#configuration)
 - [Restarting Tor Proxy](#restarting-tor-proxy)
 - [Client setup](#client-setup)
 - [Verifying your connection](#verifying-your-connection)
 - [Monitoring traffic](#monitoring-traffic)

## Installation
Install the Tor proxy plugin from RaspAP's **System > Plugins** tab. Choose **Details** corresponding to the plugin, then **Install now** from the pop-up dialog.

RaspAP's plugin manager automates installating dependencies, creating a baseline Tor configuration, setting permissions and so on. When these steps are done, the installer will refresh the UI. The Tor Proxy plugin will then appear in the sidebar and be immediately available to configure.

## Configuration
Baseline Tor settings that will work for most devices are preconfigured by the plugin. The IPv4 address of your device's default network interface is added to the "Network Interface" field. Likewise, the subnet for this interface is used to define the "Socks Policy". 

<img width="350" alt="tor-plugin" src="https://github.com/user-attachments/assets/cbe89dab-e87c-40e2-8142-c076511a3a09" />

If you wish to change these values, ensure that they correspond to the default route configured on your device. You can determine this by executing `ip route`:

```
$ ip route
default via 192.168.1.254 dev eth0 proto dhcp src 192.168.1.49 metric 100
```

In this example, `eth0` is the interface associated with the default route and `192.168.1.49` is the IPv4 address assigned to it by DHCP.

If you have services listening on Tor's default Socks port (`9050`) or Control port (`9051`) you may change these values to alternate ports. The "Daemon Mode", "Authentication method" and "Data directory" settings are read-only. 

## Restarting Tor Proxy
If you've modified any of the default settings, you must choose **Restart Tor service** for the changes to take effect. After a moment, check the **Status** tab. Example output of a working `tor@default.service` is shown below:

```
● tor@default.service - Anonymizing overlay network for TCP
     Loaded: loaded (/lib/systemd/system/tor@default.service; enabled-runtime; preset: enabled)
     Active: active (running) since Tue 2025-06-03 03:40:32 PDT; 152ms ago
    Process: 80143 ExecStartPre=/usr/bin/install -Z -m 02755 -o debian-tor -g debian-tor -d /run/tor (code=exited, status=0/SUCCESS)
    Process: 80145 ExecStartPre=/usr/bin/tor --defaults-torrc /usr/share/tor/tor-service-defaults-torrc -f /etc/tor/torrc --RunAsDaemon 0 --verify-config (code=exited, status=0/SUCCESS)
   Main PID: 80146 (tor)
      Tasks: 1 (limit: 768)
        CPU: 4.948s
     CGroup: /system.slice/system-tor.slice/tor@default.service
             └─80146 /usr/bin/tor --defaults-torrc /usr/share/tor/tor-service-defaults-torrc -f /etc/tor/torrc --RunAsDaemon 0

Jun 03 03:40:28 rpitest Tor[80146]: Opened Control listener connection (ready) on 127.0.0.1:9051
Jun 03 03:40:28 rpitest Tor[80146]: Parsing GEOIP IPv4 file /usr/share/tor/geoip.
Jun 03 03:40:28 rpitest Tor[80146]: Parsing GEOIP IPv6 file /usr/share/tor/geoip6.
Jun 03 03:40:29 rpitest Tor[80146]: Bootstrapped 0% (starting): Starting
Jun 03 03:40:32 rpitest Tor[80146]: Starting with guard context "default"
Jun 03 03:40:32 rpitest Tor[80146]: Signaled readiness to systemd
Jun 03 03:40:32 rpitest systemd[1]: Started tor@default.service - Anonymizing overlay network for TCP.
Jun 03 03:40:32 rpitest Tor[80146]: Bootstrapped 5% (conn): Connecting to a relay
Jun 03 03:40:33 rpitest Tor[80146]: Bootstrapped 10% (conn_done): Connected to a relay
Jun 03 03:40:33 rpitest Tor[80146]: Bootstrapped 14% (handshake): Handshaking with a relay
...
Jun 03 03:40:34 rpitest Tor[80146]: Bootstrapped 90% (ap_handshake_done): Handshake finished with a relay to build circuits
Jun 03 03:40:34 rpitest Tor[80146]: Bootstrapped 95% (circuit_create): Establishing a Tor circuit
Jun 03 03:40:35 rpitest Tor[80146]: Bootstrapped 100% (done): Done
```

If the service is unable to bootstrap the Tor circuit, check your configuration and try again.

## Client setup
With the Tor proxy server running, you can configure a browser to use it as a proxy. Firefox has this ability built-in and is generally the easiest to use. However, there are several [Chrome plugins](https://chrome.google.com/webstore/detail/proxy-switcher-and-manage/onnfghpihccifgojkpnnncpagjcdbjod?hl=en) that allow you to use a proxy.

Using Firefox, choose **Settings > Network Settings** and select the **Manual proxy configuration** option.

<img width="540" alt="firefox" src="https://github.com/user-attachments/assets/deb60fd3-be57-4986-8cec-c4e6dbcd9dc3" />

Add your Tor Proxy server's IPv4 address in the "SOCKS Host" field and its corresponding port. Ensure that the "SOCKS v5" option is checked.

## Verifying your connection
To verify that your internet traffic is being routed through the Tor network, visit [https://check.torproject.org/](https://check.torproject.org/).

<img width="540" alt="tor-firefox" src="https://github.com/user-attachments/assets/48c4ff2c-e870-439e-8cae-656bff849f94" />

If instead you see "Sorry. You are not using Tor" check that your browser configuration matches your Tor Proxy server settings.

## Monitoring traffic
You can also monitor Tor proxy traffic on your device by executing `nyx`. The Tor Proxy plugin installs this package for you. First, verify that permissions on the Tor cookie allow Nyx to run without elevated privileges:

```
sudo chmod 755 /run/tor/control.authcookie
```

You can then simply execute it with `nyx` (no sudo):


<img width="540" alt="tor-nyx" src="https://github.com/user-attachments/assets/d50b9cdb-37c6-49b0-9ab5-9b933dcd2234" />

Use the arrow keys to toggle through the displays. The `m` key opens a menu. Press `q` to quit Nyx.
