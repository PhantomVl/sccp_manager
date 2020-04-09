<?php /* $Id:$ */
if (!defined('FREEPBX_IS_AUTH')) {
    die('No direct script access allowed');
}
//  License for all code of this FreePBX module can be found in the license file inside the module directory
//  Copyright 2015 Sangoma Technologies.
// vim: set ai ts=4 sw=4 ft=php:

// SccpSettings page. Re-written for usage with chan_sccp
// AGPL v3 Licened

//
$spage = FreePBX::create()->Sccp_manager;
?>

<div class="container-fluid">
    <h1><?php echo _("SCCP Server Settings")?></h1>
    <div class="row">
        <div class="col-sm-12">
            <div class="fpbx-container">
                <div class="display no-border">
                    <div class="nav-container">
                        <div class="scroller scroller-left"><i class="glyphicon glyphicon-chevron-left"></i></div>
                        <div class="scroller scroller-right"><i class="glyphicon glyphicon-chevron-right"></i></div>
                        <div class="wrapper">
                            <ul class="nav nav-tabs list" role="tablist">
                                <?php foreach ($spage->myShowPage() as $key => $page) { ?>
                                    <li data-name="<?php echo $key?>" class="change-tab <?php echo $key == 'general' ? 'active' : ''?>"><a href="#<?php echo $key?>" aria-controls="<?php echo $key?>" role="tab" data-toggle="tab"><?php echo $page['name']?></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <div class="tab-content display">
                        <?php foreach ($spage->myShowPage() as $key => $page) { ?>
                            <div id="<?php echo $key?>" class="tab-pane <?php echo $key == 'general' ? 'active' : ''?>">
                                <?php echo $page['content']?>
                            </div>
                        <?php } ?>
                                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal alerts-->
<div class="modal" id="hwalert" tabindex="-1" role="dialog" aria-labelledby="lhwalert">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- End Modal alerts-->
