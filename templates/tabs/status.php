<!-- status tab -->
<div class="tab-pane fade" id="torstatus">
  <h4 class="mt-3 mb-3"><?php echo _("Tor status") ;?></h4>
  <p><?php echo _("Current <code>tor@default.service</code> status is displayed below."); ?></p>
  <div class="row">
    <div class="mb-3 col-md-8 mt-2">
      <textarea class="logoutput text-secondary" disabled><?php echo htmlspecialchars($serviceLog, ENT_QUOTES); ?></textarea>
    </div>
  </div>
</div><!-- /.tab-pane -->

