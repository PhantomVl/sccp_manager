<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$def_val = null;
$dev_id = null;
$dev_new = null;
$device_warning= null;
// Default value from Server setings

$def_val['netlang'] =  array("keyword" => 'netlang', "data" => $this->sccpvalues['netlang']['data'], "seq" => "99");
$def_val['devlang'] =  array("keyword" => 'devlang', "data" => $this->sccpvalues['devlang']['data'], "seq" => "99");
$def_val['directed_pickup_context'] =  array("keyword" => 'directed_pickup_context', "data" => $this->sccpvalues['directed_pickup_context']['data'], "seq" => "99");

if (!empty($_REQUEST['new_id'])) {
    $dev_id = $_REQUEST['new_id'];
    $val = str_replace(array('SEP','ATA','VG'), '', $dev_id);
    $val = implode('.', sscanf($val, '%4s%4s%4s')); // Convert to Cisco display Format
    $def_val['mac'] = array("keyword" => 'mac', "data" => $val, "seq" => "99");
    $val = $_REQUEST['type'];
    $def_val['type'] = array("keyword" => 'type', "data" => $val, "seq" => "99");
    if (!empty($_REQUEST['addon'])) {
        $def_val['addon'] = array("keyword" => 'type', "data" => $_REQUEST['addon'], "seq" => "99");
    }
}

if (!empty($_REQUEST['id'])) {
    $dev_id = $_REQUEST['id'];
    $dev_new = $dev_id;
    $db_res = $this->dbinterface->get_db_SccpTableData('get_sccpdevice_byid', array("id" => $dev_id));
    foreach ($db_res as $key => $val) {
        if (!empty($val)) {
            switch ($key) {
                case 'type':
                    $tmp_raw = $this->getSccpModelInformation('byid', true, 'all', array('model'=>$val));
                    if (!empty($tmp_raw[0])) {
                        $tmp_raw = $tmp_raw[0];
                    }
                    if (!empty($tmp_raw['validate'])) {
                        $tmpar =  explode(";", $tmp_raw['validate']);
                        if ($tmpar[0] != 'yes') {
                            $device_warning['Image'] = array('Device firmware not found : '.$tmp_raw['loadimage']);
                        }
                        if ($tmpar[1] != 'yes') {
                            $device_warning['Template'] = array('Missing device configuration template : '. $tmp_raw['nametemplate']);
                        }
                    }
                    break;
                case 'name':
                    $key = 'mac';
                    $val = str_replace(array('SEP','ATA','VG'), '', $val);
                    $val = implode('.', sscanf($val, '%4s%4s%4s')); // Convert to Cisco display Format
                    break;
                case '_hwlang':
                    $tmpar =  explode(":", $val);
                    $def_val['netlang'] =  array("keyword" => 'netlang', "data" => $tmpar[0], "seq" => "99");
                    $def_val['devlang'] =  array("keyword" => 'devlang', "data" => $tmpar[1], "seq" => "99");
                    break;
//                case 'permit':
//                case 'deny':
//                    $def_val[$key . '_net'] = array("keyword" => $key, "data" => before('/', $val), "seq" => "99");
//                    $key = $key . '_mask';
//                    $val = after('/', $val);
//                    break;
            }
            $def_val[$key] = array("keyword" => $key, "data" => $val, "seq" => "99");
        }
    }
}
//print_r($db_res);
 
if (!empty($device_warning)) {
    ?>    
    <div class="fpbx-container container-fluid">
        <div class="row">
            <div class="container">
                <h2 style="border:2px solid Tomato;color:Tomato;" >Warning in the SCCP Device</h2>
                <div class="table-responsive">          
                        <?php
                        foreach ($device_warning as $key => $value) {
                            echo '<h3>'.$key.'</h3>';
                            if (is_array($value)) {
                                echo '<li>'._(implode('</li><li>', $value)).'</li>';
                            } else {
                                echo '<li>'. _($value).'</li>';
                            }
                        }
                        ?>
                    </pre>
                </div>
            </div>
        </div>
    </div>
<br>

<?php } ?>

<form autocomplete="off" name="frm_adddevice" id="frm_adddevice" class="fpbx-submit" action="" method="post" data-id="hw_edit">
    <input type="hidden" name="category" value="adddevice_form">
    <input type="hidden" name="Submit" value="Submit">

    <?php
    if (empty($dev_new)) {
        echo '<input type="hidden" name="sccp_deviceid" value="new">';
    } else {
        echo '<input type="hidden" name="sccp_deviceid" value="'.$dev_id.'">';
    }
    if (empty($dev_id)) {
        echo $this->showGroup('sccp_hw_dev', 1, 'sccp_hw', $def_val);
    } else {
        echo $this->showGroup('sccp_hw_dev_edit', 1, 'sccp_hw', $def_val);
    }
    echo $this->showGroup('sccp_hw_dev2', 1, 'sccp_hw', $def_val);
    echo $this->showGroup('sccp_hw_dev_advance', 1, 'sccp_hw', $def_val);
    echo $this->showGroup('sccp_hw_dev_softkey', 1, 'sccp_hw', $def_val);
    echo $this->showGroup('sccp_hw_dev_pickup', 1, 'sccp_hw', $def_val);
    echo $this->showGroup('sccp_hw_dev_conference', 1, 'sccp_hw', $def_val);
    echo $this->showGroup('sccp_hw_dev_network', 1, 'sccp_hw', $def_val);
    ?>    
</form>
