<div class="tab-pane active" id="torsettings">
  <h4 class="mt-3"><?php echo _("Basic settings"); ?></h4>
  <div class="row">
    <div class="mb-3 col-12 mt-2">
      <div class="row">
        <div class="col-12">
          <?php echo htmlspecialchars($content); ?>
        </div>
      </div>

      <div class="row mt-3">
        <div class="mb-3 col-md-4">
          <label for="txtinternal"><?php echo _("Network interface") ;?></label>
            <i class="fas fa-question-circle text-muted" data-bs-toggle="tooltip" data-bs-placement="auto" title="<?php echo _("The network interface Tor will use to accept SOCKS proxy connections from client applications."); ?>"></i>
            <input type="text" class="form-control ip_address" name="txtinternal" value="<?php echo $arrConfig['SocksPortIP']; ?>">
        </div>

        <div class="mb-3 col-md-2">
          <label for="txtport"><?php echo _("Port") ;?></label>
            <i class="fas fa-question-circle text-muted" data-bs-toggle="tooltip" data-bs-placement="auto" title="<?php echo _("The port that the service is running on."); ?>"></i>
            <input type="text" class="form-control" name="txtport" value="<?php echo $arrConfig['SocksPort']; ?>">
        </div>
      </div>
      <div class="row">
        <div class="mb-3 col-md-6">
          <label for="txtpolicy"><?php echo _("Socks Policy") ;?></label>
            <i class="fas fa-question-circle text-muted" data-bs-toggle="tooltip" data-bs-placement="auto" title="<?php echo _("Access control rules that determine which IP addresses or networks are permitted to connect to Tor's SOCKS proxy port"); ?>"></i>
            <input type="text" class="form-control" name="txtpolicy" value="<?php echo $arrConfig['SocksPolicy']; ?>">
        </div>
      </div>

      <div class="row">
        <div class="mb-3 col-md-6">
          <label for="chxdaemon"><?php echo _("Daemon mode") ;?></label>
          <div class="form-check form-switch mt-1">
            <input class="form-check-input" id="chxdaemon" name="daemonmode" type="checkbox" value="1" <?php if ($arrConfig['RunAsDaemon']) echo "checked"; ?> disabled>
            <label class="form-check-label" for="chxdaemon">Set <code>RunAsDaemon</code> to run Tor as a background process.</label>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="mb-3 col-md-6">
          <label for="txtclientip"><?php echo _("Authentication method") ;?></label>
            <i class="fas fa-question-circle text-muted" data-bs-toggle="tooltip" data-bs-placement="auto" title="<?php echo _("Enables authentication for Tor's control port using a randomly generated cookie file instead of a password."); ?>"></i>
            <div class="form-check form-switch mt-1">
            <input class="form-check-input" id="chxcookieauth" name="authmethod" type="checkbox" value="1" <?php if ($arrConfig['CookieAuthentication']) echo "checked"; ?> disabled>
            <label class="form-check-label" for="chxcookieauth">Use <code>CookieAuthentication</code> to secure Tor's contorl port.</label>
          </div>  
        </div>
      </div>

      <div class="row">
        <div class="mb-3 col-md-6">
          <label for="txtsocksmethod"><?php echo _("Data directory") ;?></label>
            <i class="fas fa-question-circle text-muted" data-bs-toggle="tooltip" data-bs-placement="auto" title="<?php echo _("The directory where Tor stores its persistent data files, including cached network information, keys, and state files needed for operation."); ?>"></i>
            <input type="text" class="form-control" name="txtdatadirectory" disabled value="<?php echo $arrConfig['DataDirectory']; ?>">
        </div>
      </div>

      <div class="row">
        <div class="mb-3 col-md-6">
          <label for="txtcontrolport"><?php echo _("Control port") ;?></label>
            <i class="fas fa-question-circle text-muted" data-bs-toggle="tooltip" data-bs-placement="auto" title="<?php echo _("Specifies which port Tor will use to accept control connections, allowing external applications to monitor and control the Tor process through its protocol."); ?>"></i>
            <input type="text" class="form-control" name="txtcontrolport" value="<?php echo $arrConfig['ControlPort']; ?>">
        </div>
      </div>

    </div>
  </div>
</div><!-- /.tab-pane | basic tab -->

