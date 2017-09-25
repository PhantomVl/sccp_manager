<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$data = 'none;';
foreach ($this->get_DP_list() as $value) {
   $data .= $value['id'].';';
} 
if (strlen($data) >0 ){
    $data = substr ($data,0,-1);
}
 $this->sccpvalues['dial_templet'] = array('keyword' => 'dial_templet', 'data' => $data, 'type' => '10', 'seq' => '90');
?>
<form autocomplete="off" name="frm_device" id="frm_device" class="fpbx-submit" action="" method="post">
    <input type="hidden" name="category" value="deviceform">
    <input type="hidden" name="Submit" value="Submit">
<?php

        echo $this->ShowGroup('sccp_dev_config',1);
        echo $this->ShowGroup('sccp_dev_url',1); 
        echo $this->ShowGroup('sccp_hotline_config',1);        
?>  
</form>
