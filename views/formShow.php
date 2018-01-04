<?php
/*
 *                          IE - Text Input 
 *                         IED - Text Input Dynamic
 *                         ITED- Input Dynamic Table
 *                          IS - Radio box
 *                          SL - Select element 
 *                         SLA - Select element (from - data )
 *    Input element Select SLD - Date format 
 *                         SLZ - Time Zone 
 *                         SLZN - Time Zone List
 *                         SLT - TFTP Lang
 *                         SLM - Music on hold 
 *                         SLK - System KeySet
 *  * Input element Select SLS - System Language 
 *    Input element Select SDM - Model List 
 *                         SDE - Extension List 
 *    Help elemen          HLP - Help Element
 */

$npref = $form_prefix.'_';
$napref = $form_prefix.'-ar_';
if (empty($form_prefix)){
    $npref = "sccp_";
    $napref ="sccp-ar_";
}
$day_format = array("D.M.Y", "D.M.YA", "Y.M.D", "YA.M.D", "M-D-Y", "M-D-YA", "D-M-Y", "D-M-YA", "Y-M-D", "YA-M-D", "M/D/Y", "M/D/YA",
        "D/M/Y", "D/M/YA", "Y/M/D", "YA/M/D", "M/D/Y", "M/D/YA");
$mysql_table = array("sccpdevice","sccpdeviceconfig");
//$time_zone_name = timezone_identifiers_list();
$time_zone = array('-12' => 'GTM -12', '-11' => 'GTM -11', '-10' => 'GTM -10', '-09' => 'GTM -9',
                   '-08' => 'GTM -8',  '-07' => 'GTM -7',  '-06' => 'GTM -6', '-05' => 'GTM -5',
                   '-04' => 'GTM -4',  '-03' => 'GTM -3',  '-02' => 'GTM -2', '-01' => 'GTM -1',
                   '00'  => 'GTM time', '01' => 'GTM +1',  '02'  => 'GTM +2', '03'  => 'GTM +3',
                   '04'  => 'GTM +4',   '05' => 'GTM +5',  '06'  => 'GTM +6', '07'  => 'GTM +7',
                   '08'  => 'GTM +8',   '09' => 'GTM +9',  '10'  => 'GTM +10', '11'=> 'GTM +11', '12' => 'GTM +12');

$time_zone_name = \FreePBX::Sccp_manager()-> extconfigs-> getextConfig('cisco_timezone');
//$time_zone = \FreePBX::Sccp_manager()-> extconfigs-> getextConfig('cisco_time');
//$system_time_zone = \FreePBX::Sccp_manager()->getSysnemTimeZone();

if (\FreePBX::Modules()->checkStatus("soundlang")) {
    $syslangs = \FreePBX::Soundlang()->getLanguages();
    if (!is_array($syslangs)) {
        $syslangs = array();
    }
}
if (function_exists('music_list')){
    $moh_list = music_list();
//    $cur = (isset($mohsilence) && $mohsilence != "" ? $mohsilence : 'default');
}
if (!is_array($moh_list)){
    $moh_list = array('default');
}
$sofkey_list = \FreePBX::Sccp_manager()-> srvinterface -> sccp_list_keysets();
$model_list = \FreePBX::Sccp_manager()->dbinterface->get_db_SccpTableData("HWDevice");
$extension_list = \FreePBX::Sccp_manager()->dbinterface->get_db_SccpTableData("HWextension");

$extension_list[]=array(model=>'NONE', vendor=>'CISCO', dns=>'0');

$items = $itm -> children();

if ($h_show==1) {
 $sec_class ='';   
 if (!empty($items ->class)){
    $sec_class = (string)$items ->class;
 }
 ?>

 <div class="section-title" data-for="<?php echo $npref.$itm['name'];?>">
    <h3><i class="fa fa-minus"></i><?php echo _($items ->label) ?></h3>
 </div>
 <div class="section <?php echo $sec_class;?>" data-id="<?php echo $npref.$itm['name'];?>">

<?php
}
foreach ($items as $child) {
    if (empty($child->help)) {
        $child->help = 'Help is not available.';
    }

    if ($child['type'] == 'IE') {
        $res_input = '';
        $res_name = '';
        $res_id = $npref.$child->input[0]->name;
        if (empty($child->nameseparator)) {
            $child->nameseparator = ' / ';
        }
        $i = 0;
 
        echo '<!-- Begin '.$child->label.' -->';
                
        ?>
        <div class="element-container">
            <div class="row"> <div class="form-group"> 
                    <div class="col-md-3">
                        <label class="control-label" for="<?php echo $res_id; ?>"><?php echo _($child->label);?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="<?php echo $res_id; ?>"></i>
                    </div>
                    <div class="col-md-9">
                        <?php
                        
                        foreach ($child->xpath('input') as $value) {
                                $res_n =  (string)$value->name;
                                $res_name = $npref . $res_n;
                                if (empty($res_id)) {
                                  $res_id = $res_name;
                                }

                                if (!empty($fvalues[$res_n])) {
                                    if (!empty($fvalues[$res_n]['data'])) {
                                        $value->value = $fvalues[$res_n]['data'];
                                    }
                                }

                                if (empty($value->value)) {
                                    $value->value = $value->default;
                                }
                                if (empty($value->type)) {
                                    $value->type = 'text';
                                }
                                if (empty($value->class)) {
                                    $value->class = 'form-control';
                                }
                                if ($i > 0) echo $child->nameseparator;
//
                            echo '<input type="' . $value->type . '" class="' . $value->class . '" id="' . $res_id . '" name="' . $res_name . '" value="' . $value->value.'"';
                            if (isset($value->options)){
                                foreach ($value->options ->attributes() as $optkey =>$optval){
                                    echo  ' '.$optkey.'="'.$optval.'"';
                                }
                            }
                            if (!empty($value->min)) echo  ' min="'.$value->min.'"';
                            if (!empty($value->max)) echo  ' max="'.$value->max.'"';
                            echo  '>';
                            $i ++;

                        }
                        ?>
                    </div>
                </div></div>
            <div class="row"><div class="col-md-12">
                    <span id="<?php echo $res_id;?>-help" class="help-block fpbx-help-block"><?php echo _($child->help);?></span>
                </div></div>
        </div>

        <?php
        echo '<!-- END '.$child->label.' -->';
        
    }
    if ($child['type'] == 'IED') {
        $res_input = '';
        $res_name = '';
        $res_value = '';
        $res_n =  (string)$child->name;

//        $res_value
        $lnhtm = '';
        $res_id = $napref.$child->name;
        $i = 0;
        $max_row = 255;
        if (!empty($child->max_row)) {
            $max_row = $child->max_row;
        }

        if (!empty($fvalues[$res_n])) {
            if (!empty($fvalues[$res_n]['data'])) {
                $res_value = explode(';', $fvalues[$res_n]['data']);
            }
        }
        if (empty($res_value)) {
            $res_value = array((string) $child->default);
//            $res_value = explode('/', (string) $child->default);
        }
        
        echo '<!-- Begin '.$child->label.' -->';
        ?>
	<div class="element-container">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-3">
                                <label class="control-label" for="<?php echo $res_id; ?>"><?php echo _($child->label);?></label>
                                <i class="fa fa-question-circle fpbx-help-icon" data-for="<?php echo $res_id; ?>"></i>
                            </div>
                            
                            <div class="col-md-9">
                            <?php 
                            if (!empty($child->cbutton)) {
                                echo '<div class="form-group form-inline">';
                                foreach ($child->xpath('cbutton') as $value) {
                                    $res_n = $res_id.'[0]['.$value['field'].']';
                                    $res_vf = '';
                                    if ($value['value']=='NONE' && empty($res_value)){
                                        $res_vf = 'active';
                                    } 
                                    $ch_key = array_search($value['value'],$res_value);
                                    if ($ch_key !== false) {
                                        unset($res_value[$ch_key]);
                                        $res_vf = 'active';
                                        $res_value = explode(';', implode(';', $res_value));
                                    }
                                    $opt_hide ='';
                                    $opt_class="button-checkbox";
                                    if (!empty($value->option_hide)) { 
                                        $opt_class .= " sccp_button_hide";
                                        $opt_hide = ' data-vhide="'.$value->option_hide.'" data-btn="checkbox" data-clhide="'.$value->option_hide['class'].'" ';
                                    }                          
                                    if (!empty($value->option_disabled)) { 
                                        $opt_class .= " sccp_button_disabled";
                                        $opt_hide = ' data-vhide="'.$value->option_disabled.'" data-btn="checkbox" data-clhide="'.$value->option_disabled['class'].'" ';
                                    }                          

                                    if (!empty($value->class)) { 
                                        $opt_class .= " ".(string)$value->class;
                                    }
                                    
                                    echo '<span class="'.$opt_class.'"'.$opt_hide.'><button type="button" class="btn '.$res_vf.'" data-color="primary">';
                                    echo '<i class="state-icon '. (($res_vf == 'active')?'glyphicon glyphicon-check"':'glyphicon glyphicon-uncheck'). '"></i> ';
                                    echo $value.'</button><input type="checkbox" name="'. $res_n.'" class="hidden" '. (($res_vf == 'active')?'checked="checked"':'') .'/></span>';
                                }
                                echo '</div>';
                            }
                            $opt_class = "col-sm-7 ".$res_id."-gr";
                            if (!empty($child->class)) { 
                                $opt_class .= " ".(string)$child->class;
                            }
                            echo '<div class = "'.$opt_class.'">';
                                    
                            foreach ($res_value as $dat_v) {
                            ?>
                                <div class = "<?php echo $res_id;?> form-group form-inline" data-nextid=<?php echo $i+1;?> > 
                            <?php
                            $res_vf = explode('/', $dat_v);
                            $i2 = 0;
                            foreach ($child->xpath('input') as $value) {
                                $res_n = $res_id.'['.$i.']['.$value['field'].']';
                                $fields_id = (string)$value['field'];
                                $opt_at[$fields_id]['nameseparator']=(string)$value['nameseparator'];
                                if (!empty($value->class)) {
                                    $opt_at[$fields_id]['class']='form-control ' .(string)$value->class;
                                }
                                $opt_at[$fields_id]['nameseparator']=(string)$value['nameseparator'];
                                
                                echo '<input type="text" name="'. $res_n.'" class="'.$opt_at[$fields_id]['class'].'" value="'.$res_vf[$i2].'"';
                                if (isset($value->options)){
                                    foreach ($value->options ->attributes() as $optkey =>$optval){
                                        $opt_at[$fields_id]['options'][$optkey]=(string)$optval;
                                        echo  ' '.$optkey.'="'.$optval.'"';
                                    }
                                }
                                echo '> '.(string)$value['nameseparator'].' ';
                                $i2 ++;

                            }
                            if (!empty($child->add_pluss)) {
                                echo '<button type="button" class="btn btn-primary btn-lg input-js-add" id="'.$res_id.'-btn" data-id="'.$res_id.'" data-for="'.$res_id.'" data-max="'.$max_row.'"data-json="'.bin2hex(json_encode($opt_at)).'"><i class="fa fa-plus pull-right"></i></button>';
                            }
                            echo '</div>';
                            $i++;
                            }
                            ?>
                                    
                                </div>
                            <?php 
                                if (!empty($child->addbutton)) {
                                    echo '<div class = "col-sm-5 '.$res_id.'-gr">';
                                    echo '<input type="button" id="'.$res_id.'-btn" data-id="'.$res_id.'" data-for="'.$res_id.'" data-max="'.$max_row.'"data-json="'.bin2hex(json_encode($opt_at)).'" class="input-js-add" value="'._($child->addbutton).'" />';
                                    echo '</div>';
                                }
                            ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row"><div class="col-md-12">
                <span id="<?php echo $res_id;?>-help" class="help-block fpbx-help-block"><?php echo _($child->help);?></span>
            </div></div>
	</div>
        <?php        
        echo '<!-- END '.$child->label.' -->';
        
    }    
    
    if ($child['type'] == 'IS') {
        $res_n =  (string)$child->name;
        $res_id = $npref.$child->name;
            
            echo '<!-- Begin '.$child->label.' -->';
        ?>
        <div class="element-container">
            <div class="row"><div class="form-group"> 
                    <div class="col-md-3 radioset">
                        <label class="control-label" for="<?php echo $res_id; ?>"><?php echo _($child->label);?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="<?php echo $res_id; ?>"></i>
                    </div>
                    <div class="col-md-9 radioset " data-hide="on">
                        <?php
                          $i = 0;
//                          $res_v = 'no';
                          $opt_hide = '';
                          if (empty($child->default)) {
                              $res_v = 'no';
                          } else {
                              $res_v = (string)$child->default;
                          }
                          if (!empty($child->value)) { 
                               $res_v = (string)$child->value;
                          }
                          if (!empty($fvalues[$res_n])) {
                            if (!empty($fvalues[$res_n]['data'])) {
                                $res_v = (string)$fvalues[$res_n]['data'];
                            }
                          }
                          if (!empty($child->option_hide)) { 
                           $opt_hide = ' class="sccp_button_hide" data-vhide="'.$child->option_hide.'" data-clhide="'.$child->option_hide['class'].'" ';
                          }                          
                          foreach ($child->xpath('button') as $value) {
                            $val_check = (string)$value[@value];
                            if ($val_check == '' || $val_check == 'NONE' || $val_check == 'none' ) {
                                $val_check = (((string)$value[@value] == $res_v) ? " checked" : "");
                            } else {
                                $val_check = (strtolower((string)$value[@value]) == strtolower($res_v) ? " checked" : "");
                            }
                            echo '<input type="radio" name="' . $res_id . '" id="' . $res_id. '_' . $i .'" value="' . $value[@value] . '"' . $val_check . $opt_hide.'>';
                            echo '<label for="' . $res_id. '_' . $i . '">' . _($value) . '</label>';
                            $i++;
                          }
                        ?>                        
                        </div>
                </div></div>
            <div class="row"><div class="col-md-12">
                    <span id="<?php echo $res_id;?>-help" class="help-block fpbx-help-block"><?php echo _($child->help);?></span>
            </div></div>
        </div>

        <?php
        echo '<!-- END '.$child->label.' -->';

    }
    
/*
 *    Input element Select SLD - Date format 
 *                         SLZ - Time Zone 
 *                        
 *                         SLM - Music on hold 
 *                         SLK - System KeySet
 */

    if ($child['type'] == 'SLD'  || $child['type'] == 'SLM'|| $child['type'] == 'SLK' ) {
//        $value = $child -> select;
        $res_n =  (string)$child ->name;       
        $res_id = $npref.$res_n;
        if (empty($child->class)) {
           $child->class = 'form-control';
        }
        
        if ($child['type'] == 'SLD') {
            $select_opt= $day_format;
        }

        if ($child['type'] == 'SLM') {
            $select_opt= $moh_list;
        }
        if ($child['type'] == 'SLK') {
            $select_opt= $sofkey_list;
        }
//        if ($child['type'] == 'SLZ') {
//            $select_opt= $time_zone;
//        }

        echo '<!-- Begin '.$child->label.' -->';

        ?>
        <div class="element-container">
           <div class="row"> <div class="form-group"> 
 
                   <div class="col-md-3">
                        <label class="control-label" for="<?php echo $res_id; ?>"><?php echo _($child->label);?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="<?php echo $res_id; ?>"></i>
                    </div>
                    <div class="col-md-9"><div class = "lnet form-group form-inline" data-nextid=1> <?php
                            echo  '<select name="'.$res_id.'" class="'. $child->class . '" id="' . $res_id . '">';
                            if (!empty($fvalues[$res_n])) {
                                if (!empty($fvalues[$res_n]['data'])) {
                                    $child->value = $fvalues[$res_n]['data'];
                                }
                            }
                            foreach ($select_opt as $key) {
                                echo '<option value="' . $key . '"';
                                if ($key == $child->value) {
                                    echo ' selected="selected"';
                                }
                                echo '>' . $key . '</option>';
                            }
                            ?> </select>
                    </div></div>
            </div></div>
            <div class="row"><div class="col-md-12">
                <span id="<?php echo $res_id;?>-help" class="help-block fpbx-help-block"><?php echo _($child->help);?></span>
            </div></div>
        </div>
        <?php
        echo '<!-- END '.$child->label.' -->';
        
    }
/*
 *    Input element Select SLS - System Language 
 */
    
    if ($child['type'] == 'SLS' || $child['type'] == 'SLT' || $child['type'] == 'SLA' || $child['type'] == 'SLZ' || $child['type'] == 'SLZN') {
//        $value = $child -> select;
        $res_n =  (string)$child ->name;       
        $res_id = $npref.$res_n;
        $child->value ='';


        if ($child['type'] == 'SLS') {
            $select_opt= $syslangs;
        }
        if ($child['type'] == 'SLT') {
            $select_opt= $tftp_lang;
        }
        if ($child['type'] == 'SLZN') {
            $select_opt= $time_zone_name;
        }
        if ($child['type'] == 'SLZ') {
            $select_opt= $time_zone;
//            $child->value = ($system_time_zone[offset]/60);
        }

        if ($child['type'] == 'SLA') {
            $select_opt ='';
            if (!empty($fvalues[$res_n])) {
                if (!empty($fvalues[$res_n]['data'])) {
                    $res_value = explode(';', $fvalues[$res_n]['data']);
                }
                if (empty($res_value)) {
                    $res_value = array((string) $child->default);
                }
                foreach ($res_value as $key) {
                    $select_opt[$key]= $key;
                }
            }
        }
        
        if (empty($child->class)) {
           $child->class = 'form-control';
        }
        
        if (!empty($fvalues[$res_n])) {
            if (!empty($fvalues[$res_n]['data'])) {
                $child->value = $fvalues[$res_n]['data'];
            }
        }
        
        if (empty($child->value)){
            if (!empty($child->default)){
                $child->value = $child->default;
            }
        }
        
        echo '<!-- Begin '.$child->label.' -->';
        ?>
        <div class="element-container">
           <div class="row"> <div class="form-group"> 
 
                   <div class="col-md-3">
                        <label class="control-label" for="<?php echo $res_id; ?>"><?php echo _($child->label);?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="<?php echo $res_id; ?>"></i>
                    </div>
                    <div class="col-md-9"> <!-- <div class = "lnet form-group form-inline" data-nextid=1> --> <?php
                            echo  '<select name="'.$res_id.'" class="'. $child->class . '" id="' . $res_id . '">';
                            foreach ($select_opt as $key => $val) {
                                if (is_array($val)) {
                                    $opt_key = (isset($val['id'])) ? $val['id'] : $key;
                                    $opt_val = (isset($val['val'])) ? $val['val'] : $val;
                                } else {
                                    $opt_key = $key;
                                    $opt_val = $val;
                                }
                                echo '<option value="' . $opt_key . '"';
                                if ($opt_key == $child->value) {
                                    echo ' selected="selected"';
                                }
                                echo '>' . $opt_val. '</option>';
                            }
                            ?> </select>
                    <!-- </div> --> </div>
            </div></div>
            <div class="row"><div class="col-md-12">
                <span id="<?php echo $res_id;?>-help" class="help-block fpbx-help-block"><?php echo _($child->help);?></span>
            </div></div>
        </div>
        <!--END System Language-->
        <?php
        echo '<!-- END '.$child->label.' -->';
        
    }
/*
 *    Input element Select 
 */

    if ($child['type'] == 'SL') {
        $res_n =  (string)$child->name;
        $res_id = $npref.$child->name;
        
        if (empty($child ->class)) {
           $child->class = 'form-control';
        }
        echo '<!-- Begin '.$child->label.' -->';

        ?>
        <div class="element-container">
           <div class="row"> <div class="form-group"> 
 
                   <div class="col-md-3">
                        <label class="control-label" for="<?php echo $res_id; ?>"><?php echo _($child->label);?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="<?php echo $res_id; ?>"></i>
                    </div>
                    <div class="col-md-9"> <div class = "lnet form-group form-inline" data-nextid=1> <?php
                        echo  '<select name="'.$res_id.'" class="'. $child->class . '" id="' . $res_id . '">';
                        if (!empty($fvalues[$res_n])) {
                            if (!empty($fvalues[$res_n]['data'])) {
                                $child->value = $fvalues[$res_n]['data'];
                            }
                        }
                         foreach ($child->xpath('select') as $value) {
                            if (!empty($value[@value])) {
                                $key = $value[@value];
                            } else { 
                                $key =  (string)$value; 
                            }
                            echo '<option value="' . $key . '"';
                            if (strtolower((string)$key) == strtolower((string)$child->value)) {
                                echo ' selected="selected"';
                            }
                            echo '>' . (string)$value. '</option>';
                        }
                        ?> </select>
                            
                    </div> </div>
            </div></div>
            <div class="row"><div class="col-md-12">
                <span id="<?php echo $res_id;?>-help" class="help-block fpbx-help-block"><?php echo _($child->help);?></span>
            </div></div>
        </div>
        <?php
        echo '<!-- END '.$child->label.' -->';
    }

 /*
 *    Input element Select SDM - Model List 
 *                         SDE - Extension List 
 */

    if ($child['type'] == 'SDM' || $child['type'] == 'SDE' ) {
//        $value = $child -> select;
        $res_n =  (string)$child ->name;       
        $res_id = $npref.$res_n;
        if (empty($child->class)) {
           $child->class = 'form-control';
        }
        if ($child['type'] == 'SDM') {
            $select_opt= $model_list;            
        }
        if ($child['type'] == 'SDE') {
            $select_opt= $extension_list;            
        }

        echo '<!-- Begin '.$child->label.' -->';

        ?>
        <div class="element-container">
           <div class="row"> <div class="form-group"> 
 
                   <div class="col-md-3">
                        <label class="control-label" for="<?php echo $res_id; ?>"><?php echo _($child->label);?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="<?php echo $res_id; ?>"></i>
                    </div>
                    <div class="col-md-9"><div class = "lnet form-group form-inline" data-nextid=1> <?php
                            echo  '<select name="'.$res_id.'" class="'. $child->class . '" id="' . $res_id . '"';
                            if (isset($child->options)){
                                foreach ($child->options->attributes() as $optkey =>$optval){
                                    echo  ' '.$optkey.'="'.$optval.'"';
                                }
                            }
                            echo  '>';

                            $fld = (string)$child->select['name'];
                            $flv = (string)$child->select;
                            $flk = (string)$child->select['dataid'];
                            $flkv= (string)$child->select['dataval'];
                            $key = (string)$child->default;
                            if (!empty($fvalues[$res_n])) {
                                if (!empty($fvalues[$res_n]['data'])) {
                                    $child->value = $fvalues[$res_n]['data'];
                                    $key = $fvalues[$res_n]['data'];
                                }
                            }
                            
                            foreach ($select_opt as $data) {
                                echo '<option value="' . $data[$fld] . '"';
                                if ($key == $data[$fld]) {
                                    echo ' selected="selected"';
                                }
                                if (!empty($flk)){
                                    echo ' data-id="'.$data[$flk].'"';
                                }
                                if (!empty($flkv)){
                                    echo ' data-val="'.$data[$flkv].'"';
                                }
                                echo '>' . $data[$flv] . '</option>';
                            }

                            ?> </select>
                    </div></div>
            </div></div>
            <div class="row"><div class="col-md-12">
                <span id="<?php echo $res_id;?>-help" class="help-block fpbx-help-block"><?php echo _($child->help);?></span>
            </div></div>
        </div>
        <?php
        echo '<!-- END '.$child->label.' -->';
        
    }
        if ($child['type'] == 'ITED') {
        $res_input = '';
        $res_name = '';
        $res_na =  (string)$child->name;

//        $res_value
        $lnhtm = '';
        $res_id = $napref.$child->name;
        $i = 0;

        if (!empty($fvalues[$res_na])) {
            if (!empty($fvalues[$res_na]['data'])) {
                $res_value = explode(';', $fvalues[$res_na]['data']);
            }
        }
        if (empty($res_value)) {
            $res_value = array((string) $child->default);
//            $res_value = explode('/', (string) $child->default);
        }
        
        echo '<!-- Begin '.$res_id.' -->';
        ?>
        <table class="table table-striped" id="dp-table-<?php echo $res_id;?>">

        <?php
        foreach ($res_value as $dat_v) {
            echo '<tr data-nextid="'.($i+1).'" class="'.$res_id.'" id="'.$res_id.'-row-'.($i).'"> ';
            if (!empty($child->label)) {
                echo '<td class=""> <div class="input-group">'.$child->label.'</div></td>';
            }

            $res_vf = explode('/', $dat_v);
            $i2 = 0;
                            
            foreach ($child->xpath('element') as $value) {
                $fields_id = (string)strtolower($value['field']);
                $res_n  = $res_id.'['.$i.']['.$fields_id.']';
                $res_ni = $res_id.'_'.$i.'_'.$fields_id;

                $opt_at[$fields_id]['display_prefix']=(string)$value['display_prefix'];
                $opt_at[$fields_id]['display_sufix']=(string)$value['display_sufix'];
                                
                if (empty($value->options->class)) {
                    $opt_at[$fields_id]['options']['class']='form-control';
                } 
                $opt_at[$fields_id]['type']=(string)$value['type'];
                $res_opt['addon'] ='';
                if (isset($value->options)){
                    foreach ($value->options ->attributes() as $optkey =>$optval){
                        $opt_at[$fields_id]['options'][$optkey]=(string)$optval;
                        $res_opt['addon'] .=' '.$optkey.'="'.$optval.'"';
                    }
                }
                                
                echo '<td class="">';
                $res_opt['inp_st'] = '<div class="input-group"> <span class="input-group-addon" id="basep_'.$res_n.'">'.$opt_at[$fields_id]['display_prefix'].'</span>';
                $res_opt['inp_end'] = '<span class="input-group-addon" id="bases_'.$res_n.'">'.$opt_at[$fields_id]['display_sufix'].'</span></div>';
                switch ($value['type']){
                    case 'date':
                        echo $res_opt['inp_st'].'<input type="date" name="'. $res_n.'" value="'.$res_vf[$i2].'"'.$res_opt['addon']. '>'.$res_opt['inp_end'];
                        break;
                    case 'number':
                        echo $res_opt['inp_st'].'<input type="number" name="'. $res_n.'" value="'.$res_vf[$i2].'"'.$res_opt['addon']. '>'.$res_opt['inp_end'];
                        break;
                    case 'input':
                        echo $res_opt['inp_st'].'<input type="text" name="'. $res_n.'" value="'.$res_vf[$i2].'"'.$res_opt['addon']. '>'.$res_opt['inp_end'];
                        break;
                    case 'title':
                        if ($i > 0 ) {
                            break;
                        }
                    case 'label':
                        $opt_at[$fields_id]['data'] = (string)$value;
                        echo '<label '.$res_opt['addon'].' >'.(string)$value.'</label>';
                        break;
                    case 'select':
                        echo  $res_opt['inp_st'].'<select name="'.$res_n.'" id="' . $res_n . '"'. $res_opt['addon'].'>';
                        $opt_at[$fields_id]['data']='';
                        foreach ($value->xpath('data') as $optselect ){
                            $opt_at[$fields_id]['data'].= (string)$optselect.';';
                            echo '<option value="' . $optselect. '"';
                            if (strtolower((string)$optselect) == strtolower((string)$res_vf[$i2])) {
                                echo ' selected="selected"';
                            }
                            echo '>' . (string)$optselect. '</option>';
                        }
                        echo  '</select>'.$res_opt['inp_end'];
                        break;
                }
                echo '</td>';                                
                $i2 ++;

            }
            echo '<td><input type="button" id="'.$res_id.'-btn" data-id="'.($i).'" data-for="'.$res_id.'" data-json="'.bin2hex(json_encode($opt_at)).'" class="table-js-add" value="+" />';
            if ($i > 0 ) {
                echo '<input type="button" id="'.$res_id.'-btndel" data-id="'.($i).'" data-for="'.$res_id.'" class="table-js-del" value="-" />';
            }
                            
            echo '</td></tr>';
            $i++;
                            }
            echo '</table>';
            echo '<!-- END '.$res_id.' -->';
        
    }    
    
    if ($child['type'] == 'HLP' ) {
        $res_n =  (string)$child ->name;
        $res_id = $npref.$res_n;
        if (empty($child->class)) {
           $child->class = 'form-control';
        }
        echo '<!-- Begin '.$child->label.' -->';

        ?>
            
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo _($child->label);?>
                <a data-toggle="collapse" href="<?php echo '#'.$res_id;?>"><i class="fa fa-plus pull-right"></i></a>
            </div>
            <div class="panel-body collapse" id="<?php echo $res_id;?>">
        <?php
                foreach ($child->xpath('element') as $value) {
                    switch ($value['type']){
                        case 'p':
                        case 'h1':
                        case 'h2':
                        case 'h3':
                        case 'h4':
                            echo '<'.$value['type'].'>'._((string)$value).'</'.$value['type'].'>';
                            break;
                        case 'table':
                            echo '<'.$value['type'].' class="table" >';
                            foreach ($value->xpath('row') as $trow) {
                                echo '<tr>';
                                foreach ($trow->xpath('col') as $tcol) {
                                    echo '<td>'._((string)$tcol).'</td>';
                                }
                                echo '</tr>';
                            }
                            echo '</'.$value['type'].'>';
                            break;
                    }
                }
        ?>

            </div>
        </div>
        <?php
        echo '<!-- END '.$child->label.' -->';
        
    }

    
}
?>
<?php
    if ($h_show==1) {
       echo '</div>';
    }
 ?>

