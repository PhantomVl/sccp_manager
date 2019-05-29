<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$driver = $this->FreePBX->Core->getAllDriversInfo();
$core = $this->srvinterface->getSCCPVersion();
$ast_realtime = $this->srvinterface->sccp_realtime_status();

$ast_realm = (empty($ast_realtime['sccp']) ? '':'sccp');

foreach ($ast_realtime as $key => $value) {
    if (empty($ast_realm)) {
       if ($value['status'] == 'OK') {
           $ast_realm = $key;
       }
    }
}

$conf_realtime    = $this->extconfigs->validate_RealTime($ast_realm);
$db_Schema        = $this->dbinterface->validate();
$mysql_info       = $this->dbinterface->get_db_sysvalues();
$compatable       = $this->srvinterface->get_compatible_sccp();
$info             = array();
                  
$info['srvinterface'] = $this->srvinterface->info();
$info['extconfigs']   = $this->extconfigs->info();
$info['dbinterface']  = $this->dbinterface->info();
$info['aminterface']  = $this->aminterface->info();
$info['XML']          = $this->xmlinterface->info();
$info['sccp_class']   = $driver['sccp'];
$info['Core_sccp']    = array('Version' => $core['Version'],  'about'=> 'Sccp ver.'. $core['Version'].' r'.$core['vCode']. ' Revision :'. $core['RevisionNum']. ' Hash :'. $core['RevisionHash']);
$info['Asterisk']     = array('Version' => FreePBX::Config()->get('ASTVERSION'),  'about'=> 'Asterisk.');


if (!empty($this->sccpvalues['SccpDBmodel'])) {
    $info['DB Model'] = array('Version' => $this->sccpvalues['SccpDBmodel']['data'],  'about'=> 'SCCP DB Configure');
}
if (!empty($this->sccpvalues['tftp_rewrite'])) {
    if ($this->sccpvalues['tftp_rewrite']['data'] == 'pro') {
        $info['Provision_SCCP'] = array('Version' => 'base',  'about'=> 'Provision Sccp enabled');
    } else {
        $info['TFTP_Rewrite'] = array('Version' => 'base',  'about'=> 'Rewrite Supported');
    }
}
$info['Сompatible'] = array('Version' => $compatable,  'about'=> 'Ok');
if (!empty($this->sccpvalues['SccpDBmodel'])) {
    if ($compatable > $this->sccpvalues['SccpDBmodel']['data']){
        $info['Сompatible']['about'] = '<div class="alert signature alert-danger"> Reinstall SCCP manager required</div>';
    }
}
if ($db_Schema == 0) {
    $info['DB_Schema'] = array('Version' => 'Error',  'about'=> '<div class="alert signature alert-danger"> ERROR DB Version </div>');
} else {
    $info['DB_Schema'] = array('Version' => $db_Schema,  'about'=> (($compatable == $db_Schema ) ? 'Ok' : 'Incompatable Version'));
}

if (empty($ast_realtime)) {
    $info['RealTime'] = array('Version' => 'Error',  'about'=> '<div class="alert signature alert-danger"> No found Real Time connections</div>');
} else {
    $rt_info = '';
    $rt_sccp = 'Failed';
    foreach ($ast_realtime as $key => $value) {
        if ($key == $ast_realm) {
            if ($value['status'] == 'OK') {
                $rt_sccp = 'TEST OK'; 
                $rt_info .= 'SCCP Connections found';
            } else {
                $rt_sccp = 'SCCP ERROR';
                $rt_info .= '<div class="alert signature alert-danger"> Error : '. $value['message']. '</div>';
            }
        } else if ($value['status'] == 'ERROR') {
            $rt_info .= '<div> Found error in realtime sectoin ['.$key.'] : '.  $value['message']. '</div>';
        }
    }
    $info['RealTime'] = array('Version' => $rt_sccp,  'about'=> $rt_info);
}

if (empty($conf_realtime)) {
    $info['ConfigsRealTime'] = array('Version' => 'Error',  'about'=> '<div class="alert signature alert-danger"> No found Real Time Configs</div>');
} else {
    $rt_info = '';
    foreach ($conf_realtime as $key => $value) {
        if (($value != 'OK') && ($key != 'extconfigfile')) {
            $rt_info .= '<div> Found error in section '.$key.' :'.  $value. '</div>';
        }
    } 
    if (!empty($rt_info)) {
        $info['ConfigsRealTime'] = array('Version' => 'Error',  'about'=> $rt_info);
    }
}
// $mysql_info 
if ($mysql_info['Value'] <= '2000') {
    $this->info_warning['MySql'] = Array('Increase Mysql Group Concat Max. Length','Step 1: Go to mysql path <br> nano /etc/my.cnf',
                                          'Step 2: And add the following line below [mysqld] as shown below <br> [mysql] <br>group_concat_max_len = 4096 or more',
                                          'Step 3: Save and restart <br> systemctl restart mariadb.service<br> Or <br> service mysqld restart');
}
 //global $amp_conf;
// ************************************************************************************
/*
print_r("<br> Help Info:<br><pre>");
 print_r("<br>");
 //print_r($buton_list);
 print_r("<br>");
 print_r($user_list);
 print_r("<br>");
// print_r("DIRECT START");
//  print_r($this->sccpvalues['ccm_address']);
 //print_r($this->get_php_classes('\\FreePBX\\modules\\'));
 //print_r($this->dbinterface->get_db_SccpTableData('SccpExtension'));
//  print_r($this->srvinterface->getеtestChanSCCP_GlablsInfo());
//  $test_data = $this->srvinterface-> astman_GetRaw('ExtensionStateList');
//  print_r($test_data);
//  print_r($this->srvinterface-> core_list_all_exten());
//  print_r($this->get_hint_info());
//  print_r($this->aminterface-> core_list_all_exten('exten'));
//  print_r($this->aminterface->Sok_param['total']);
//  print_r($this->srvinterface->t_get_meta_data());
//  print_r($this->sccp_metainfo);
 print("</pre>");
*/
// ************************************************************************************

//   $lang_arr =  $this->extconfigs->getextConfig('sccp_lang','sk_SK');    
//   print_r('<br>');
//   print_r(timezone_identifiers_list());
//   print_r('<br>');

//print_r($this->dbinterface->info());

 if (!empty($this->info_warning)) {
    ?>    
    <div class="fpbx-container container-fluid">
        <div class="row">
            <div class="container">
                <h2 style="border:2px solid Tomato;color:Tomato;" >Sccp Manager Warning</h2>
                <div class="table-responsive">          
                    <br> There are Warning in the SCCP Module:<br><pre>
                        <?php
                            foreach ($this->info_warning as $key => $value) {
                                echo '<h3>'.$key.'</h3>';
                                if (is_array($value)) {
                                    echo '<li>'._(implode('</li><li>', $value)).'</li>';
                                } else {
                                    echo '<li>'. _($value).'</li>';
                                }
                            }
                        ?>
                    </pre>
                    <br><h4 style="border:2px solid Tomato;color:Green;" > Check these problems before continuing to work.</h4> <br>
                </div>
            </div>
        </div>
    </div>
<br>
<?php  }     

if (!empty($this->class_error)) {
    ?>    
    <div class="fpbx-container container-fluid">
        <div class="row">
            <div class="container">
                <h2 style="border:2px solid Tomato;color:Tomato;" >Diagnostic information about SCCP Manager errors</h2>
                <div class="table-responsive">          
                    <br> There is an error in the :<br><pre>
                        <?php print_r($this->class_error); ?>
                    </pre>
                    <br> Correct these problems before continuing to work. <br>
                    <br><h3 style="border:2px solid Tomato;color:Green;" > Open 'SCCP Connectivity' -> Server Config' to change global settings</h3> <br>
                </div>
            </div>
        </div>
    </div>
<br>
<?php  }  ?>
<div class="fpbx-container container-fluid">
    <div class="row">
        <div class="container">
            <h2>Sccp Manager V.<?php print_r($this->sccp_manager_ver); ?> Info </h2>
            <div class="table-responsive">          
                <table class="table">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Version</th>
                            <th>Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($info as $key => $value) {
                            echo '<tr><td>' . $key . '</td><td>' . $value['Version'] . '</td><td>' . $value['about'] . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <a class="btn btn-default" href="ajax.php?module=sccp_manager&command=backupsettings"><i class="fa fa-plane">&nbsp;</i><?php echo _("BackUp Config") ?></a>
        </div>

    </div>
</div>
<?php  echo $this->ShowGroup('sccp_info',0); ?>

