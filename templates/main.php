  <?php ob_start() ?>
    <?php if (!RASPI_MONITOR_ENABLED) : ?>
    <input type="submit" class="btn btn-outline btn-primary" name="saveSettings" value="<?php echo _("Save settings"); ?>" />
        <?php if ($__template_data['serviceStatus'] == 'down') : ?>
        <input type="submit" class="btn btn-success" name="startTorService" value="<?php echo _("Start Tor service"); ?>" />
        <?php else : ?>
        <input type="submit" class="btn btn-warning" name="stopTorService" value="<?php echo _("Stop Tor service"); ?>" />
        <input type="submit" class="btn btn-warning" name="restartTorService" value="<?php echo _("Restart Tor service"); ?>" />
        <?php endif; ?>
    <?php endif ?>
  <?php $buttons = ob_get_clean(); ob_end_clean() ?>
 
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col">
              <i class="<?php echo $__template_data['icon']; ?> me-2"></i><?php echo htmlspecialchars($__template_data['title']); ?>
            </div>
            <div class="col">
              <button class="btn btn-light btn-icon-split btn-sm service-status float-end">
                <span class="icon text-gray-600"><i class="fas fa-circle service-status-<?php echo $__template_data['serviceStatus']; ?>"></i></span>
                <span class="text service-status"><?php echo $__template_data['serviceName']; ?></span>
              </button>
            </div>
          </div><!-- /.row -->
        </div><!-- /.card-header -->

        <div class="card-body">
        <?php $status->showMessages(); ?>
          <form role="form" action="<?php echo $__template_data['action']; ?>" method="POST" class="needs-validation" novalidate>
            <?php echo \RaspAP\Tokens\CSRF::hiddenField(); ?>
            <!-- Nav tabs -->
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link active" id="torsettingstab" href="#torsettings" data-bs-toggle="tab"><?php echo _("Settings"); ?></a></li>
                <li class="nav-item"><a class="nav-link" id="torstatustab" href="#torstatus" data-bs-toggle="tab"><?php echo _("Status"); ?></a></li>
                <li class="nav-item"><a class="nav-link" id="pluginabouttab" href="#pluginabout" data-bs-toggle="tab"><?php echo _("About"); ?></a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
              <?php echo renderTemplate("tabs/basic", $__template_data, $__template_data['pluginName']) ?>
              <?php echo renderTemplate("tabs/status", $__template_data, $__template_data['pluginName']) ?>
              <?php echo renderTemplate("tabs/about", $__template_data, $__template_data['pluginName']) ?>
            </div><!-- /.tab-content -->

            <?php echo $buttons ?>
          </form>
        </div><!-- /.card-body -->

      <div class="card-footer"><?php echo _("Information provided by ". $__template_data['serviceName']); ?></div>
    </div><!-- /.card -->
  </div><!-- /.col-lg-12 -->
</div><!-- /.row -->

