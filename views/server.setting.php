<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
// vim: set ai ts=4 sw=4 ft=phtml:

// $var_hw_config = $this->get_db_SccpTableData("get_sccpdevice_byid", array('id' => 'SEPB8BEBF224790'));
        global $astman;
    $ast_out = $astman->Command("sccp show version");
    if (preg_match("/Release.*\(/", $ast_out['data'] , $matches)) {
        $ast_out = explode(' ', substr($matches[0],9,-1));
        $res = 0;
        if ($ast_out[0] >= '4.3.0'){
            $res = 1;
        }
        if (!empty($ast_out[1]) && $ast_out[1] == 'develop'){           
            $res = 10;
            if (!empty($ast_out[3]) && $ast_out[3] >= '702487a'){           
                $res += 1;
            }
        } else {
           $res = 0;
        }
    }

print_r(base_convert('702487a', 16, 10));
print_r('<br>');
print_r($version);
print_r('<br>');
?>

<form autocomplete="off" name="frm_general" id="frm_general" class="fpbx-submit" action="" method="post">
    <input type="hidden" name="category" value="generalform">
    <input type="hidden" name="Submit" value="Submit">
    <?php 
//    print_r($this-> sccp_conf_init);
//    print_r(@parse_ini_file($this->sccppath["sccp_conf"], true));
//          print_r(\FreePBX::LoadConfig()->getConfig('sccp.conf'));
//          print_r($this->FreePBX->LoadConfig('sccp.conf'));
//        $this->sccp_create_tftp_XML();
//        print_r($this->get_db_SccpTableData('SccpDevice'));
//        print_r('<br>');
//        print_r($this->sccp_get_active_devise());
//        print_r(music_list());
//        print_r($this->sccppath["sccp_conf"]);
//        print_r($this-> getMyConfig('softkeyset'));
/*
        $dev_tech ="sccp";
        $drivers = FreePBX::Core()->getAllDrivers();
        if(isset($drivers[$dev_tech])) {
                $devopts = $drivers[$dev_tech]->getDevice('1234');
        } else {
              $devopts = array();
        }
        print_r($devopts);
*/
    
        echo $this->ShowGroup('sccp_general',1);
        echo $this->ShowGroup('sccp_net',1);
        echo $this->ShowGroup('sccp_lang',1);
        echo $this->ShowGroup('sccp_qos_config',1);
        echo $this->ShowGroup('sccp_extpath_config',1);
//        $this->sccp_db_save_setting();
//        $this->sccp_create_sccp_init();
        
    ?>    

</form>
