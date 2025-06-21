<!-- about tab -->
<div class="tab-pane fade" id="pluginabout">
  <h4 class="mt-3 mb-3"><?php echo _("About ").$__template_data['title'] ;?></h4>
  <div class="col-12 mb-2">
    <code><?php echo $__template_data['pluginName']; ?></code> <?php echo _("was created by ") . $__template_data['author']; ?>.
  </div>
  <div class="col-12 mb-2">
    <?php echo _("Plugin description").": " . $__template_data['description']; ?>.
  </div>
  <div class="col-6 mb-3">
    GitHub <i class="fa-brands fa-github"></i> <a href="<?php echo  $__template_data['uri']; ?>" target="_blank" rel="noopener"><?php echo $__template_data['pluginName']; ?></a>
  </div>
</div><!-- /.tab-pane -->

