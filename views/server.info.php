<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$test_ami = 0;
$test_any = 0;

$driver = $this->FreePBX->Core->getAllDriversInfo();
$core = $this->srvinterface->getSCCPVersion();
$ast_realtime = $this->srvinterface->sccp_realtime_status();

$ast_realm = (empty($ast_realtime['sccp']) ? '' : 'sccp');

// if there are multiple connections, this will only return the first.
foreach ($ast_realtime as $key => $value) {
    if (empty($ast_realm)) {
        if ($value['status'] == 'OK') {
            $ast_realm = $key;
        }
    }
}

$conf_realtime = $this->extconfigs->validate_RealTime($ast_realm);
$db_Schema = $this->dbinterface->validate();
$mysql_info = $this->dbinterface->get_db_sysvalues();
$compatible = $this->srvinterface->get_compatible_sccp();
$info = array();

$info['srvinterface'] = $this->srvinterface->info();
$info['extconfigs'] = $this->extconfigs->info();
$info['dbinterface'] = $this->dbinterface->info();
$info['aminterface'] = $this->aminterface->info();
$info['XML'] = $this->xmlinterface->info();
$info['sccp_class'] = $driver['sccp'];
$info['Core_sccp'] = array('Version' => $core['Version'], 'about' => 'Sccp ver.' . $core['Version'] . ' r' . $core['vCode'] . ' Revision :' . $core['RevisionNum'] . ' Hash :' . $core['RevisionHash']);
$info['Asterisk'] = array('Version' => FreePBX::Config()->get('ASTVERSION'), 'about' => 'Asterisk.');


if (!empty($this->sccpvalues['SccpDBmodel'])) {
    $info['DB Model'] = array('Version' => $this->sccpvalues['SccpDBmodel']['data'], 'about' => 'SCCP DB Configure');
}
if (!empty($this->sccpvalues['tftp_rewrite'])) {
    if ($this->sccpvalues['tftp_rewrite']['data'] == 'pro') {
        $info['Provision_SCCP'] = array('Version' => 'base', 'about' => 'Provision Sccp enabled');
    } else {
        $info['TFTP_Rewrite'] = array('Version' => 'base', 'about' => 'Rewrite Supported');
    }
}
$info['Сompatible'] = array('Version' => $compatible, 'about' => 'Ok');
if (!empty($this->sccpvalues['SccpDBmodel'])) {
    if ($compatible > $this->sccpvalues['SccpDBmodel']['data']) {
        $info['Сompatible']['about'] = '<div class="alert signature alert-danger"> Reinstall SCCP manager required</div>';
    }
}
if ($db_Schema == 0) {
    $info['DB_Schema'] = array('Version' => 'Error', 'about' => '<div class="alert signature alert-danger"> ERROR DB Version </div>');
} else {
    $info['DB_Schema'] = array('Version' => $db_Schema, 'about' => (($compatible == $db_Schema ) ? 'Ok' : 'Incompatible Version'));
}

if (empty($ast_realtime)) {
    $info['RealTime'] = array('Version' => 'Error', 'about' => '<div class="alert signature alert-danger"> No RealTime connections found</div>');
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
                $rt_info .= '<div class="alert signature alert-danger"> Error : ' . $value['message'] . '</div>';
            }
        } elseif ($value['status'] == 'ERROR') {
            $rt_info .= '<div> Found error in realtime sectoin [' . $key . '] : ' . $value['message'] . '</div>';
        }
    }
    $info['RealTime'] = array('Version' => $rt_sccp, 'about' => $rt_info);
}

if (empty($conf_realtime)) {
    $info['ConfigsRealTime'] = array('Version' => 'Error', 'about' => '<div class="alert signature alert-danger"> Realtime configuration was not found</div>');
} else {
    $rt_info = '';
    foreach ($conf_realtime as $key => $value) {
        if (($value != 'OK') && ($key != 'extconfigfile')) {
            $rt_info .= '<div> Found error in section ' . $key . ' :' . $value . '</div>';
        }
    }
    if (!empty($rt_info)) {
        $info['ConfigsRealTime'] = array('Version' => 'Error', 'about' => $rt_info);
    }
}
// $mysql_info
if ($mysql_info['Value'] <= '2000') {
    $this->info_warning['MySql'] = array('Increase Mysql Group Concat Max. Length', 'Step 1: Go to mysql path <br> nano /etc/my.cnf',
        'Step 2: And add the following line below [mysqld] as shown below <br> [mysqld] <br>group_concat_max_len = 4096 or more',
        'Step 3: Save and restart <br> systemctl restart mariadb.service<br> Or <br> service mysqld restart');
}


// Check Time Zone comatable
$conf_tz = $this->sccpvalues['ntp_timezone']['data'];
$cisco_tz = $this->extconfigs->getextConfig('sccp_timezone', $conf_tz);
if ($cisco_tz['offset'] == 0) {
    if (!empty($conf_tz)) {
        $tmp_dt = new DateTime(null, new DateTimeZone($conf_tz));
        $tmp_ofset = $tmp_dt->getOffset();
        if (($cisco_tz['offset'] != ($tmp_ofset / 60) )) {
            $this->info_warning['NTP'] = array('The selected NTP time zone is not supported by cisco devices.', 'We will use the Greenwich Time zone');
        }
    }
}

global $amp_conf;


if ($test_any == 1) {
# Output option list, HTML.

    $timezone_identifiers = DateTimeZone::listIdentifiers();
    $timezone_abbreviations = DateTimeZone::listAbbreviations();
    $a = DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC);


    $Ts_set =  $a[200];


// ************************************************************************************
    print_r("<br> Help Info:<br><pre>");
    print_r("<br>");
//print_r(array_column($timezone_abbreviations, 'timezone_id'));
    print_r($Ts_set);
    $tz_tmp = array();

    foreach ($timezone_abbreviations as $subArray) {
        $dddd  = array_search($Ts_set, array_column($subArray, 'timezone_id'));
        if (!empty($dddd)) {
            $tz_tmp[] = $subArray[$dddd];
        }
    }

    if (empty($tz_tmp)) {
        print_r('erroe');
    }
    if (count($tz_tmp)==1) {
        $time_set = $tz_tmp[0];
    } else {
        $tmp_dt = new DateTime(null, new DateTimeZone($Ts_set));
        $tmp_ofset = $tmp_dt->getOffset();
        foreach ($tz_tmp as $subArray) {
            if ($subArray['offset'] == $tmp_ofset) {
                $time_set = $subArray;
            }
        }
    }

    print_r("<br>");
//print_r($time_set);
    print_r($this->sccpvalues['ntp_timezone']);
//print_r($tz_tmp);
    print_r("<br>");
    print_r("<br>");

    print_r("<br>");
//print_r($timezone_abbreviations);
//print_r($timezone_identifiers);
//print_r($timezone);
//print_r($transitions);


    print_r("<br>");
    print_r("</pre>");
// print_r("DIRECT START");
//  print_r($this->sccpvalues['ccm_address']);
//print_r($this->get_php_classes('\\FreePBX\\modules'));
//     print_r(get_declared_classes());
//  $a = $this->aminterface->_config;
//  print_r($a);
// print_r($this->aminterface->info());
//print_r(get_declared_classes());
// print_r($this->aminterface->open());
// $time_start = microtime_float();
// $this->aminterface->open();
// $time_connect = microtime_float();
//  print_r($this->aminterface->send(new \FreePBX\modules\Sccp_manager\aminterface\SCCPShowSoftkeySetsAction()));
//  $a = new \FreePBX\modules\Sccp_manager\aminterface\SCCPShowSoftkeySetsAction();
//    $a = new \FreePBX\modules\Sccp_manager\aminterface\ExtensionStateListAction();
//  $a = new \FreePBX\modules\Sccp_manager\aminterface\SCCPShowDeviceAction('SEP00070E36555C');
//  $a = new \FreePBX\modules\Sccp_manager\aminterface\SCCPDeviceRestartAction('SEP00070E36555C');
//  $a = new \FreePBX\modules\Sccp_manager\aminterface\ReloadAction('chan_sccp');
//$a = new \FreePBX\modules\Sccp_manager\aminterface\CommandAction('core show hints');
/*
  $time_start = microtime_float();
  print_r($this->srvinterface->t_get_ami_data());
  $time_get_dl = microtime_float()-$time_start;
  print_r('<br> Delta :');  print_r($time_get_dl);
  $time_start = microtime_float();
  $tmp_data = $this->aminterface->sccp_get_active_device();   print_r($tmp_data);
  $time_get_dl = microtime_float()-$time_start;
  print_r('<br> Delta :');  print_r($time_get_dl);

  die();

  /*
 */
//  $a = new \FreePBX\modules\Sccp_manager\aminterface\CommandAction('realtime mysql status');
//   $a = new \FreePBX\modules\Sccp_manager\aminterface\SCCPConfigMetaDataAction();
//   $response = $this->aminterface->send($a);
//
//  $response = $this->aminterface->getRealTimeStatus();
//  $time_get_a = microtime_float();
//  print_r($response);
//  $tmp_data = $this->aminterface->core_sccp_reload();
//  print_r($tmp_data);
//  print_r($response -> getResult());
//    $events = $response->getEvents();
//  $events = $response->Events2Array();
//
//  print_r($events);
//  print_r('--- RESULT A -----------------');
//  $b = $this->oldinterface->sccp_realtime_status();
//  print_r($b);
//  $b = $this->srvinterface->sccp_realtime_status();
//  print_r($response->getMessage());
//  print_r($a);
//  $events = $response ->getTableNames();
//  $events = $response->getEvents();
//  print_r($events);
//  $b = $response->Table2Array($events[0]);
//  $b = $response->getResult();
//  $b = $response->getResult();
// print_r($b);
// $time_get_ra = microtime_float();


/*
 */
//  $tmp_data = $this->aminterface->sccp_get_active_device();
//  print_r($tmp_data);
}

/* Test Ok
 *
 *
 *
 *
 */
if ($test_ami == 1) {
    $time_ami = 0;
    $time_old = 0;
    $test_info = array();
    $tmp_test_name = 'get_version';
    print_r('<br>-------------- OLD: ' . $tmp_test_name . '---------------------------<br>');
    $time_get_start = microtime_float();
    $tmp_data = $this->oldinterface->get_compatible_sccp();
    print_r($tmp_data);
    $tmp_data = $this->oldinterface->getSCCPVersion();
    print_r($tmp_data);
    $tmp_data = $this->oldinterface->getChanSCCPVersion();
    print_r($tmp_data);
    $tmp_data = $this->oldinterface->sccp_realtime_status();
    print_r($tmp_data);
    $time_get_dl = microtime_float();

    $test_info[$tmp_test_name]['old'] = $time_get_dl - $time_get_start;
    print_r('<br> Delta :');
    print_r($time_get_dl - $time_get_start);
    $time_get_start = $time_get_dl;

    print_r('<br>-------------- AMI: ' . $tmp_test_name . ' ---------------------------<br>');
    $time_get_start = microtime_float();
    $tmp_data = $this->srvinterface->get_compatible_sccp();
    print_r($tmp_data);
    print_r('<br>Not Use<br>');
    $tmp_data = $this->srvinterface->getChanSCCPVersion();
    print_r($tmp_data);
    $tmp_data = $this->srvinterface->sccp_realtime_status();
    print_r($tmp_data);
    $time_get_dl = microtime_float();
    $test_info[$tmp_test_name]['ami'] = $time_get_dl - $time_get_start;
    print_r('<br> Delta :');
    print_r($time_get_dl - $time_get_start);


    $tmp_test_name = 'getdevice_info';
    print_r('<br>-------------- OLD: ' . $tmp_test_name . '---------------------------<br>');
    $time_get_start = microtime_float();
    $tmp_data = $this->oldinterface->sccp_getdevice_info('SEP00070E36555C');
    print_r($tmp_data);
    $time_get_dl = microtime_float();
    $test_info[$tmp_test_name]['old'] = $time_get_dl - $time_get_start;
    print_r('<br> Delta :');
    print_r($time_get_dl - $time_get_start);

    print_r('<br>-------------- AMI: ' . $tmp_test_name . ' ---------------------------<br>');
    $time_get_start = microtime_float();
    $tmp_data = $this->srvinterface->sccp_getdevice_info('SEP00070E36555C');
    print_r($tmp_data);
    $time_get_dl = microtime_float();
    $test_info[$tmp_test_name]['ami'] = $time_get_dl - $time_get_start;
    print_r('<br> Delta :');
    print_r($time_get_dl - $time_get_start);

    $tmp_test_name = 'get_active_device';
    print_r('<br>-------------- OLD: ' . $tmp_test_name . '---------------------------<br>');
    $time_get_start = microtime_float();
    $tmp_data = $this->oldinterface->sccp_get_active_device();
    print_r($tmp_data);
    $time_get_dl = microtime_float();
    $test_info[$tmp_test_name]['old'] = $time_get_dl - $time_get_start;
    print_r('<br> Delta :');
    print_r($time_get_dl - $time_get_start);

    print_r('<br>-------------- AMI: ' . $tmp_test_name . ' ---------------------------<br>');
    $time_get_start = microtime_float();
    $tmp_data = $this->aminterface->sccp_get_active_device();
    print_r($tmp_data);
    $time_get_dl = microtime_float();
    $test_info[$tmp_test_name]['ami'] = $time_get_dl - $time_get_start;
    print_r('<br> Delta :');
    print_r($time_get_dl - $time_get_start);

    $tmp_test_name = 'sccp_list_keysets';
    print_r('<br>-------------- OLD: ' . $tmp_test_name . '---------------------------<br>');
    $time_get_start = microtime_float();
    $tmp_data = $this->oldinterface->sccp_list_keysets();
    print_r($tmp_data);
    $time_get_dl = microtime_float();
    $test_info[$tmp_test_name]['old'] = $time_get_dl - $time_get_start;
    print_r('<br> Delta :');
    print_r($time_get_dl - $time_get_start);
    print_r('<br>-------------- AMI: ' . $tmp_test_name . ' ---------------------------<br>');
    $time_get_start = microtime_float();
    $tmp_data = $this->aminterface->sccp_list_keysets();
    print_r($tmp_data);
    $time_get_dl = microtime_float();
    $test_info[$tmp_test_name]['ami'] = $time_get_dl - $time_get_start;
    print_r('<br> Delta :');
    print_r($time_get_dl - $time_get_start);

    $tmp_test_name = 'list_all_hints';
    print_r('<br>-------------- OLD: ' . $tmp_test_name . '---------------------------<br>');
    $time_get_start = microtime_float();
    $tmp_data = $this->oldinterface->sccp_list_all_hints();
    print_r($tmp_data);
    $time_get_dl = microtime_float();
    $test_info[$tmp_test_name]['old'] = $time_get_dl - $time_get_start;
    print_r('<br> Delta :');
    print_r($time_get_dl - $time_get_start);
    print_r('<br>-------------- AMI: ' . $tmp_test_name . ' ---------------------------<br>');
    $time_get_start = microtime_float();
    $tmp_data = $this->aminterface->core_list_all_hints();
    print_r($tmp_data);
    $time_get_dl = microtime_float();
    $test_info[$tmp_test_name]['ami'] = $time_get_dl - $time_get_start;
    print_r('<br> Delta :');
    print_r($time_get_dl - $time_get_start);

    $tmp_test_name = 'sccp_list_hints';
    print_r('<br>-------------- OLD: ' . $tmp_test_name . '---------------------------<br>');
    $time_get_start = microtime_float();
    $tmp_data = $this->oldinterface->sccp_list_hints();
    print_r($tmp_data);
    $time_get_dl = microtime_float();
    $test_info[$tmp_test_name]['old'] = $time_get_dl - $time_get_start;
    print_r('<br> Delta :');
    print_r($time_get_dl - $time_get_start);
    print_r('<br>-------------- AMI: ' . $tmp_test_name . ' ---------------------------<br>');
    $time_get_start = microtime_float();
    $tmp_data = $this->aminterface->core_list_hints();
    print_r($tmp_data);
    $time_get_dl = microtime_float();
    $test_info[$tmp_test_name]['ami'] = $time_get_dl - $time_get_start;
    print_r('<br> Delta :');
    print_r($time_get_dl - $time_get_start);

    print_r('<br>--- Stat  -----------------<br>');

    print_r('<div class="fpbx-container container-fluid"><div class="row"><div class="container"> <div class="table-responsive"><table class="table"><thead><tr><th>Function</th><th>Old Time</th><th> Ami Time</th></tr></thead><tbody>');
    $time_ami = 0;
    $time_old = 0;
    foreach ($test_info as $key => $value) {
        print_r('<tr><td>' . $key . '</td><td>' . $value['old'] . '</td><td>' . $value['ami'] . '</td></tr>');
        $time_ami += $value['ami'];
        $time_old += $value['old'];
    }
    print_r('</tbody></table></div></div></div></div>');
    print_r('<br>Ami Response :');
    print_r($time_ami);
    print_r('<br>PBX Response :');
    print_r($time_old);
    print_r('<br>--- Stat  -----------------<br>');
}
/*
 */

//  $events = $response->getEvents();
//  print_r($events);
//$b = $response->Table2Array($events[0]);
//$b = $response->getResult();
//  print_r('--- RESULT 2 -----------------<br>');
//print_r($events);
//  print_r($b);
//  $ser = serialize($response);
//  print_r($ser);
//  $result2 = unserialize($ser);
//  print_r($result2);
/* $events = $result2->getEvents();
  $this->assertEquals($result2->getMessage(), 'Channels will follow');
  $this->assertEquals($events[0]->getName(), 'CoreShowChannelsComplete');
  $this->assertEquals($events[0]->getListItems(), 0);
 */
//  print_r('--- RESULT 3 -----------------');
//  print_r($a);
//  print_r('--- С RESULT -----------------');
//  print_r($this->aminterface::SCCPShowDevicesAction());
//
//  print_r($this->aminterface->close());
//
//
//print_r($this->dbinterface->HWextension_db_SccpTableData('SccpExtension'));
//  print_r($this->srvinterface->getеtestChanSCC());
//  $test_data = $this->srvinterface-> astman_GetRaw('ExtensionStateList');
//  print_r($test_data);
//  print_r($this->srvinterface-> core_list_all_exten());
//  print_r($this->getHintInformation());
//  print_r($this->aminterface->open());
//  print_r($this->aminterface-> core_list_all_exten('exten'));
//  print_r($this->aminterface->Sok_param['total']);
//  print_r($this->srvinterface->t_get_meta_data());
//  print_r($this->sccp_metainfo);
print(" ");
/* */
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
                            echo '<h3>' . $key . '</h3>';
                            if (is_array($value)) {
                                echo '<li>' . _(implode('</li><li>', $value)) . '</li>';
                            } else {
                                echo '<li>' . _($value) . '</li>';
                            }
                            echo '<br>';
                        }
                        ?>
                    </pre>
                    <br><h4 style="border:2px solid Tomato;color:Green;" > Check these problems before continuing to work.</h4> <br>
                </div>
            </div>
        </div>
    </div>
    <br>
    <?php
}

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
<?php } ?>
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
<?php echo $this->showGroup('sccp_info', 0); ?>
