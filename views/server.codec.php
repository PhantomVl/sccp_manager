<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$def_val = null;
$dev_id = null;
$sccp_codec = $this->getCodecs('audio', true);
if (!empty($_REQUEST['id'])) {
    $dev_id = $_REQUEST['id'];
    $db_res = $this->get_db_SccpTableData('get_sccpdevice_byid', array("id" => $dev_id));
    if (!empty($db_res['allow'])) {
        $i = 1;
        foreach(explode(';', $db_res['allow']) as $c){
            $codec_list[$c] = $i;
            $i ++;
        }
        foreach ($sccp_codec as $c => $v) {
            if (!isset($codec_list[$c])) {
                    $codec_list[$c] = false;
            }
        }
    }
} else {
    $codec_list = $sccp_codec;
}
        
?>

<form autocomplete="off" name="frm_codec" id="frm_codec" class="fpbx-submit" action="" method="post">
    <input type="hidden" name="category" value="codecform">
    <input type="hidden" name="Submit" value="Submit">
    
    <!--SCCP Audio Codecs-->
    <div class="section-title" data-for="sccp_acodecs">
        <h3><i class="fa fa-minus"></i><?php echo _("SCCP Codecs") ?></h3>
    </div>
    <div class="section" data-id="sccp_acodecs">
        <!--Codecs-->
        <div class="element-container">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-3">
                                <label class="control-label" for="codecw"><?php echo _("SCCP Audio Codecs (allow)") ?></label>
                            </div>
                            <div class="col-md-9">
                                <?php echo \show_help(_("This is the default Codec setting for SCCP Device.")) ?>
                                <?php
                                $seq = 1;

                                echo '<ul class="sortable">';
                                foreach ($codec_list as $codec => $codec_state) {
                                    $codec_trans = _($codec);
                                    $codec_checked = $codec_state ? 'checked' : '';
                                    echo '<li><a href="#">'
                                    . '<img src="assets/sipsettings/images/arrow_up_down.png" height="16" width="16" border="0" alt="move" style="float:none; margin-left:-6px; margin-bottom:-3px;cursor:move" /> '
                                    . '<input type="checkbox" '
                                    . ($codec_checked ? 'value="' . $seq++ . '" ' : '')
                                    . 'name="voicecodecs[' . $codec . ']" '
                                    . 'id="' . $codec . '" '
                                    . 'class="audio-codecs" '
                                    . $codec_checked
                                    . ' />'
                                    . '&nbsp;&nbsp;<label for="' . $codec . '"> '
                                    . '<small>' . $codec_trans . '</small>'
                                    . " </label></a></li>\n";
                                }
                                echo '</ul>';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--END Codecs-->


        <!--Codec disallow-->
        <div class="element-container">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-3">
                                <label class="control-label" for="sccp_disallow"><?php echo _("Codec Disallow") ?></label>
                                <i class="fa fa-question-circle fpbx-help-icon" data-for="sccp_disallow"></i>
                            </div>
                            <div class="col-md-9 radioset">
                                <input id="sccp_disallow" type="text" name="sccp_disallow" value="<?php echo $this->sccpDefaults[disallow] ?>">  
                                <label for="sccp_disallow"><?php echo _("default : " . $this->sccpDefaults[disallow]) ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <span id="sccp_disallow-help" class="help-block fpbx-help-block"><?php echo _("Defaut : all. Plz eneter format: alaw,ulaw") ?></span>
                </div>
            </div>
        </div>
        <!--END Codec disallow-->

        <!--END SCCP Audio Codecs-->
    </div>
</form>



