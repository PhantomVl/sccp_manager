<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$forminfo =array(
                array("name"=>"dev_buttons",label=>"Buttons Configuration"),
                array("name"=>"button",label=>"Buttons ",help=>"help.")
    );
//$buttons_type=  array("empty","line","service","feature","speeddial");
//   "feature","service" -- Add leter !
$buttons_type=  array("empty","line","silent","monitor","speeddial","feature","adv.line");
$feature_list=  array('parkinglot'=>'Park Slots','monitor'=> "Record Calls",'devstate'=> "Change Status");

$lines_list = $this->dbinterface->get_db_SccpTableData('SccpExtension');
//$hint_list  = $this->dbinterface->get_db_SccpTableData('SccpExtension');
$data_sort = Array();
$hint_list1  = $this->get_hint_info();
foreach ($hint_list1 as $key => $value) {
    $data_sort[$value['name']] = $key;
}
ksort($data_sort);
//print_r(count($data_sort));
//print_r(count($hint_list1));
foreach ($data_sort as $key => $value) {
    $hint_list[$value] = $hint_list1[$value];
}
//print_r($hint_list);
//ksort($hint_list);
//natsort($hint_list);
$line_id =0;
$max_buttons =56;
$show_buttons =1;
//print_r($hint_list);
if (!empty($_REQUEST['id'])) {
    $dev_id = $_REQUEST['id'];
    $db_buttons = $this->dbinterface->get_db_SccpTableData('get_sccpdevice_buttons', array("id" => $dev_id));
    $db_device = $this->dbinterface->get_db_SccpTableData('get_sccpdevice_byid', array("id" => $dev_id));
    $max_buttons = $db_device['buttons'];
    if (!empty($db_device['addon_buttons'])){
        $max_buttons += $db_device['addon_buttons'];
    }
    $show_buttons = $max_buttons;
}
if (!empty($_REQUEST['new_id'])) {
    $val = $_REQUEST['type'];
    $dev_schema =  $this-> getSccp_model_information('byid', false, "all", array('model' =>$val));
//   $db_device = $this->dbinterface->get_db_SccpTableData('get_sccpdevice_byid', array("id" => $val));
    $max_buttons = $dev_schema[0]['buttons'];
    if (!empty($_REQUEST['addon'])) {
        $val = $_REQUEST['addon'];
        $dev_schema =  $this-> getSccp_model_information('byid', false, "all", array('model' =>$val));
        $max_buttons += $dev_schema[0]['buttons'];
    }
    $show_buttons = $max_buttons;   
}

?>

<form autocomplete="off" name="frm_editbuttons" id="frm_editbuttons" class="fpbx-submit" action="" method="post" data-id="hw_edit">
    <input type="hidden" name="category" value="frm_editbuttons">
    <input type="hidden" name="Submit" value="Submit">
    <input type="hidden" name="buttonscount" value="<?php echo $max_buttons?>">
    <div class="section-title" data-for="<?php echo $forminfo[0]['name'];?>">
        <h3><i class="fa fa-minus"></i><?php echo _($forminfo[0]['label']) ?></h3>
    </div>
    <div class="section" data-id="<?php echo $forminfo[0]['name'];?>">
    <div class="row"> <div class="form-group"> 
            <div class="col-sm-2">
                <label class="control-label">Help</label>
                <i class="fa fa-question-circle fpbx-help-icon" data-for="frmbuttons"></i>
            </div>
            <div class="col-sm-10">
                <span id="frmbuttons-help" class="help-block fpbx-help-block"><?php echo _("buttons come in the following flavours: <br>
                    <ul>
                    <li>empty: Empty button (no options)</li>
                    <li>line: Registers the line with identifier specified as [name]</li>
                    <li>silent:   buttons equal 'Line' with out ring</li>
                    <li>monitor:  buttons mode speeddial + show status</li>
                    <li>speeddial: Adds a speeddial with label [name] and [option1] as number Optionally, [option2] can be used to specify a hint by extension@context as usual.</li>
                    <li>service (not implemented): Adds a service url Feature buttons have an on/off status represented on the device with a tick-box and can be used to set the device in a particular state. Currently Possible [option1],[option2] combinations:</li>
                    <ul>
                    <li>privacy,callpresent = Make a private call, number is suppressed</li><li>privacy,hint = Make a private call, hint is suppressed</li><li>cfwdall,number = Forward all calls </li><li>cfwbusy,number = Forward on busy</li><li>
                    cfwnoaswer,number = Forward on no-answer (not implemented yet)<br> DND,busy = Do-not-disturb, return Busy signal to Caller <br> DND,silent = Do-not-disturb, return nothing to caller <br>
                    monitor = Record Calls using AutoMon (asterisk 1.6.x only)</li><li>devstate,custom_devstate = Device State Feature Button (asterisk 1.6.1 and up). custom_devstate is the name of the custom devicestate to be toggled (How to use devicestate)
                    hold = To be implemented</li><li>transfer = To be implemented</li><li>multiblink = To be implemented</li><li>mobility = To be implemented</li><li>conference = To be implemented</li>
                    </ui></ui>");?></span>
            </div>
         
    </div></div>        
    <?php
        for ($line_id = 0; $line_id <=$max_buttons; $line_id ++){
//          print_r($db_buttons[$line_id]);
            $show_form_mode = '';
            $defaul_tv = (empty($db_buttons[$line_id])) ?  "empty": $db_buttons[$line_id]['type'];
            $defaul_btn = (empty($db_buttons[$line_id])) ?  "": $db_buttons[$line_id]['name'];
            $defaul_opt = (empty($db_buttons[$line_id])) ?  array(''): explode(',',$db_buttons[$line_id]['options']);

            $show_form_mode = $defaul_tv; 
            $def_hint = '';       // Hint check Box
            $def_hint_btn = '';   // Hint Combo Box
            $def_silent = '';
            $defaul_advline = '';
            if (strpos($defaul_btn,'@') >0) {
                $defaul_tv = 'adv.line';
                $show_form_mode = 'adv.line';
                $defaul_btn = strtok($defaul_btn,'@');
                $defaul_advline = strtok('@');
            }
            if ($line_id == 0) {
                $show_form_mode = 'line';
            }
            if (stripos($defaul_btn,'!') >0) {
                $defaul_btn = strtok($defaul_btn,'!');
                $defaul_tv = 'silent';
                $def_silent = 'checked';
            }
            if ($defaul_tv == "feature") {
                $defaul_ftr = $defaul_opt[0];                
                $defaul_fcod = (empty($defaul_opt[1])) ?  '': $defaul_opt[1];
//                print_r($defaul_fcod);
            }
            foreach ($defaul_opt as $data_i) {
                if (strpos($data_i,'@')>0) {
                    $test_btn = strtok($data_i,'@');
                    $def_hint = 'checked';
                    $defaul_btn = $data_i;
                    $def_hint_btn = $data_i;
                    if ($test_btn == $defaul_opt[0]) {
                        foreach ($lines_list as $data){
                           if ($data['name']==$test_btn) {
                                $show_form_mode = 'line';
                                $defaul_tv = 'monitor';
                                $defaul_btn = $test_btn;
                                break;
                           }
                        }
                    }                        
                }
            }                                
//            print_r($defaul_btn);
//            print_r($defaul_opt);
            
            echo '<!-- Begin button :'.$line_id.' -->';
            echo '<div class="line_button element-container" '.(($line_id < $show_buttons)?"":"hidden ").'data-id="'.$line_id.'">';
//            echo 'Mode : '.$show_form_mode. ' opt: ';
//            print_r( $defaul_opt);

    ?>    
            <div class="row"> <div class="form-group"> 
                    <div class="col-sm-2">
                        <label class="control-label" for="<?php echo $forminfo[1]['name'].$line_id; ?> "><?php echo _($forminfo[1]['label'].$line_id).(($line_id =="0")?' Default ':''); ?></label>
                    </div>
                    <div class="col-sm-5">
                        <div class="col-xs-3">
<!--  Line Type Select                        -->
                        <select class="form-control lineselect" data-id="<?php echo $line_id;?>" name="<?php echo $forminfo[1]['name'].$line_id.'_type';?>" >
                        <?php 
                            if ($line_id == 0) {
                                echo '<option value="line" selected >DEF LINE</option>';                                
                            }else {
                                foreach ($buttons_type as $data){
                                $select = (($data == $defaul_tv)?"selected":"");
                                echo '<option value="'.$data.'" '.$select.' >'.$data.'</option>';
                                }
                            }
                            ?>
                        </select>
                        </div>
<!--  if Line Type = feature Show Futures -->
                        <div class="col-xs-7">
                        <select class ="form-control linefeature_<?php echo $line_id.(($show_form_mode=='feature')?'':' hidden');?>" name="<?php echo $forminfo[1]['name'].$line_id.'_feature';?>" >
                        <?php 
                            foreach ($feature_list as $fkey => $fval) {
                                $select = (($fkey == $defaul_ftr)?"selected":"");
                                echo '<option value="'.$fkey.'" '.$select.' >'.$fval.'</option>';
                            }
                            ?>
                        </select>
<!--  if Line Type = line Show SCCP Num -->
                        <select class ="form-control lineselect_<?php echo $line_id.(($show_form_mode=='line' || $show_form_mode=='adv.line')?'':' hidden');?>" name="<?php echo $forminfo[1]['name'].$line_id.'_line';?>" >
                        <?php 
                            foreach ($lines_list as $data){
                              $select = (($data['name']==$defaul_btn)?"selected":"");
                              echo '<option value="'.$data['name'].'" '.$select.' >'.$data['name'].' / '.$data['label'].'</option>';
                            }
                            ?>
                        </select>
<!--  if Line Type = Othe Show    Input -->
                        <div class="linevalue_<?php echo $line_id.(($show_form_mode=='speeddial')? '':' hidden');?>" >
                            <?php 
                                echo '<input class="form-control" type="text" id="'.$forminfo[1]['name'].$line_id.'_input"  name="'.$forminfo[1]['name'].$line_id.'_input" placeholder="Name" value="'.$db_buttons[$line_id]['name'].'" >';
                            ?>
                        </div>
                        </div>

                    </div>
                    <div class="col-md-5">
<!--  if Line Type = speeddial Show  Hint line -->
                        <div class="linespeed_<?php echo $line_id.(($show_form_mode=='speeddial')? '':' hidden');?>" name="<?php echo $forminfo[1]['name'].$line_id.'_hint';?>">
                            <?php 
                                echo '<div class="col-xs-5">';
                                echo '<input class="form-control" type="text" id="'.$forminfo[1]['name'].$line_id.'_phone"  name="'.$forminfo[1]['name'].$line_id.'_phone" placeholder="Phone" value="'.$defaul_opt[0].'">';
                                echo '</div><div class="col-xs-2 radioset" data-toggle="buttons">';
                                echo '<input class="form-control" type="checkbox" name="'.$forminfo[1]['name'].$line_id.'_hint" id="'.$forminfo[1]['name'].$line_id.'_hint" '.$def_hint.' value= "hint">';
                                echo '<label for="'.$forminfo[1]['name'].$line_id.'_hint">hints</label>';
                                echo '</div><div class="col-xs-5">';

                                echo '<select  class="form-control" name="'.$forminfo[1]['name'].$line_id.'_hline" >';
                                
                                foreach ($hint_list as $data){
                                  $select = (($data['hint']==$def_hint_btn)?"selected":"");
                                  echo '<option value="'.$data['hint'].'" '.$select.' >'.$data['name'].' / '.$data['label'].'</option>';
                                }
                                echo '</select>';
                                echo '</div>';
                            ?>
                        </div>
                        <div class="linefeature_<?php echo $line_id.(($show_form_mode=='feature')? '':' hidden');?>" name="<?php echo $forminfo[1]['name'].$line_id.'_hint';?>">
                            <div class="col-xs-5">
                            <?php 
                                echo '<input class="form-control" type="text" id="'.$forminfo[1]['name'].$line_id.'_flabel"  name="'.$forminfo[1]['name'].$line_id.'_flabel" placeholder="Display Label" value="'.$db_buttons[$line_id]['name'].'" >';
                            ?>
                            </div>
                            <div class="col-xs-5">
                            <?php 
                                echo '<input class="form-control" type="text" id="'.$forminfo[1]['name'].$line_id.'_fvalue"  name="'.$forminfo[1]['name'].$line_id.'_fvalue" placeholder="code" value="'.$defaul_fcod.'" >';
                            ?>
                            </div>
                        </div>
<!--  if Line Type = Advanced Show  Hint line -->

                        <div class="lineadv_<?php echo $line_id.(($show_form_mode=='adv.line')? '':' hidden');?>" name="<?php echo $forminfo[1]['name'].$line_id.'_hint';?>">
                            <div class="col-xs-5">
                            <?php 
                                echo '<input class="form-control" type="text" id="'.$forminfo[1]['name'].$line_id.'_advline"  name="'.$forminfo[1]['name'].$line_id.'_advline" placeholder="[+=][01]:[cidname]" value="'.$defaul_advline.'" >';
                            ?>
                            </div>
                            <div class="col-xs-5">
                            <?php 
                                echo '<input class="form-control" type="text" id="'.$forminfo[1]['name'].$line_id.'_advopt"  name="'.$forminfo[1]['name'].$line_id.'_advopt" placeholder="ButtonLabel,Options" value="'.$db_buttons[$line_id]['options'].'" >';
                            ?>
                            </div>
                        </div>

                    </div>

                </div></div>
        </div>
    <?php
        echo '<!-- End button :'.$line_id.' -->';
        }
        
    ?>    
        
                        
        
    </div>    
</form>
