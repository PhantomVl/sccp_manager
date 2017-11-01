<?php

if (!defined('FREEPBX_IS_AUTH')) {
    die_freepbx('No direct script access allowed');
}

global $db;
global $amp_conf;
global $astman;
global $version;
global $srvinterface;


$class = "\\FreePBX\\Modules\\Sccp_manager\\srvinterface";
if(!class_exists($class,false)) {
    include(__DIR__."/Sccp_manager.inc/srvinterface.class.php");
}
if(class_exists($class,false)) {
    $srvinterface = new $class();
}

//
// Helper function to retrieve SCCPConfigMetaData via ASTMan
// segment: ["", "general","device","line","softkey"]}
// returns a php variable (array/set)
//
// Move to seperate helper file
function astman_retrieveJSFromMetaData($astman, $segment = "") {
    $params = array();
    if ($segment != "") {
        $params["Segment"] = $segment;
    }
    $response = $astman->send_request('SCCPConfigMetaData', $params);
    if ($response["Response"] == "Success") {
        //outn(_("JSON-content:").$response["JSON"]);
        $decode=json_decode($response["JSON"], true);
        return $decode;
    } else {
        return false;
    }
}

function Get_DB_config($sccp_compatible) {
$db_config_v0 = array(
    'sccpdevmodel' => array(
        'enabled' => array('create' => "INT(2) NULL DEFAULT '0'"),
        'nametemplate' => array('create' => 'VARCHAR(50) NULL DEFAULT NULL'),
        'loadinformationid' => array('create' => "VARCHAR(30) NULL DEFAULT NULL" )
    ),
    'sccpdevice' => array(
        '_hwlang' => array('create' => 'varchar(12) NULL DEFAULT NULL'),
        //'useRedialMenu' => array('create' => "VARCHAR(5) NULL DEFAULT 'no' AFTER `_hwlang`"),
        //'dtmfmode' => array('create' => "VARCHAR(10) default 'outofband'", 'modify' => "VARCHAR(10)", 'def_modify'=> 'outofband'),
        'deny' =>   array('create' => 'VARCHAR(100) NULL DEFAULT NULL','modify' => "VARCHAR(100)"),
        'permit' => array('create' => 'VARCHAR(100) NULL DEFAULT NULL','modify' => "VARCHAR(100)"),
        'backgroundImage' =>   array('create' => 'VARCHAR(255) NULL DEFAULT NULL','modify' => "VARCHAR(255)"),
        'ringtone' => array('create' => 'VARCHAR(255) NULL DEFAULT NULL','modify' => "VARCHAR(255)"),

        'transfer' =>array('def_modify' => "on"),
        'cfwdall' =>array('def_modify' => "on"),
        'cfwdbusy' =>array('def_modify' => "on"),
        'directrtp' =>array('def_modify' => "off"),
        'dndFeature' =>array('def_modify' => "on"),
        'earlyrtp' =>array('def_modify' => "on"),
        'audio_tos'=>array('def_modify' => "0xB8"),
        'audio_cos'=>array('def_modify' => "6"),
        'video_tos'=>array('def_modify' => "0x88"),
        'video_cos'=>array('def_modify' => "5"),
 
        'mwilamp' =>array('def_modify' => "on"),
        'mwioncall' =>array('def_modify' => "on"),
        'private' =>array('def_modify' => "on"),
        'privacy' =>array('def_modify' => "off"),
        'nat' =>array('def_modify' => "auto"),
        'softkeyset' =>array('def_modify' => "softkeyset")
    ),

    'sccpline' => array(
        'namedcallgroup' =>array('create' => "VARCHAR(100) NULL DEFAULT NULL AFTER `setvar`", 'modify' => "VARCHAR(100)"),
        'namedpickupgroup' =>array('create' => "VARCHAR(100) NULL DEFAULT NULL AFTER `namedcallgroup`", 'modify' => "VARCHAR(100)"),
        'adhocNumber' =>array('create' => "VARCHAR(45) NULL DEFAULT NULL AFTER `namedpickupgroup`"),
        'meetme' =>array('create' => "VARCHAR(5) NULL DEFAULT NULL AFTER `adhocNumber`"),
        'meetmenum' =>array('create' => "VARCHAR(45) NULL DEFAULT NULL AFTER `meetme`"),
        'meetmeopts' =>array('create' => "VARCHAR(45) NULL DEFAULT NULL AFTER `meetmenum`"),
        'regexten' =>array('create' => "VARCHAR(45) NULL DEFAULT NULL AFTER `meetmeopts`"),
        'incominglimit' =>array('def_modify' => "2"),
        'transfer' =>array('def_modify' => "on"),
        'vmnum' =>array('def_modify' => "*97"),
        'musicclass' =>array('def_modify' => "default"),
        'echocancel' =>array('def_modify' => "on"),
        'silencesuppression' =>array('def_modify' => "off"),
        'id' =>array('create' => 'VARCHAR( 20 ) NULL DEFAULT NULL', 'modify' => "VARCHAR(20)", 'def_modify' =>"NULL"),
        'dnd' =>array('create' => 'VARCHAR( 12 ) DEFAULT "reject" AFTER `amaflags`', 'modify' => "VARCHAR(12)", 'def_modify' =>"reject")
    )
);
/*  Old  */
$db_config_v5 = array(
    'sccpdevmodel' => array(
        'enabled' => array('create' => "INT(2) NULL DEFAULT '0'"),
        'nametemplate' => array('create' => 'VARCHAR(50) NULL DEFAULT NULL'),
        'loadinformationid' => array('create' => "VARCHAR(30) NULL DEFAULT NULL" )
    ),
    'sccpdevice' => array(
        'pickupexten' =>  array('rename' => "directed_pickup"),
        'directed_pickup' =>  array('create' => "VARCHAR(5) NULL DEFAULT 'yes'"),
        'pickupcontext' =>  array('rename' => "directed_pickup_context"),
        'directed_pickup_context' =>  array('create' => "VARCHAR(100) NULL DEFAULT NULL"),
        'pickupmodeanswer' => array('rename' => "directed_pickup_modeanswer"),
        'directed_pickup_modeanswer' => array('create' => "VARCHAR(5) NULL DEFAULT 'yes'"),
        'hwlang' => array('rename' => "_hwlang"),
        '_hwlang' => array('create' => 'varchar(12) NULL DEFAULT NULL'),
        'useRedialMenu' => array('create' => "VARCHAR(5) NULL DEFAULT 'no' AFTER `_hwlang`"),

        //'dtmfmode' => array('create' => "VARCHAR(10) default 'outofband'", 'modify' => "VARCHAR(10)", 'def_modify'=> 'outofband'),
        'dtmfmode' => array('drop' => "yes"),

        'deny' =>	array('create' => 'VARCHAR(100) NULL DEFAULT NULL','modify' => "VARCHAR(100)"),
        'permit' => array('create' => 'VARCHAR(100) NULL DEFAULT NULL','modify' => "VARCHAR(100)"),
        'backgroundImage' =>	array('create' => 'VARCHAR(255) NULL DEFAULT NULL','modify' => "VARCHAR(255)"),
        'ringtone' => array('create' => 'VARCHAR(255) NULL DEFAULT NULL','modify' => "VARCHAR(255)"),

        'transfer'   =>array('create' => 'VARCHAR(5) DEFAULT "on"','modify' => "VARCHAR(5)",'def_modify' => "on"),
        'cfwdall'    =>array('create' => 'VARCHAR(5) DEFAULT "on"','modify' => "VARCHAR(5)",'def_modify' => "on"),
        'cfwdbusy'   =>array('create' => 'VARCHAR(5) DEFAULT "on"','modify' => "VARCHAR(5)",'def_modify' => "on"),
        'directrtp'  =>array('create' => 'VARCHAR(3) DEFAULT "off"','modify' => "VARCHAR(3)",'def_modify' => "off"),
        'dndFeature' =>array('create' => 'VARCHAR(5) DEFAULT "on"','modify' => "VARCHAR(5)",'def_modify' => "on"),
        'earlyrtp'   =>array('create' => 'VARCHAR(10) DEFAULT "progress"','modify' => "VARCHAR(10)",'def_modify' => "progress"),
        'audio_tos'  =>array('def_modify' => "0xB8"),
        'audio_cos'  =>array('def_modify' => "6"),
        'video_tos'  =>array('def_modify' => "0x88"),
        'video_cos'  =>array('def_modify' => "5"),
        'trustphoneip'=>array('drop' => "yes"),

        'mwilamp'     =>array('create' => 'VARCHAR(5) DEFAULT "on"','modify' => "VARCHAR(5)",'def_modify' => "on"),
        'mwioncall'   =>array('create' => 'VARCHAR(5) DEFAULT "off"','modify' => "VARCHAR(5)",'def_modify' => "off"),
        'private'     =>array('create' => 'VARCHAR(5) DEFAULT "on"','modify' => "VARCHAR(5)",'def_modify' => "on"),
        'privacy'     =>array('create' => 'VARCHAR(100) DEFAULT "full"','modify' => "VARCHAR(5)",'def_modify' => "full"),
        'nat'         =>array('create' => 'VARCHAR(7) DEFAULT "auto"','modify' => "VARCHAR(7)",'def_modify' => "auto"),
        'softkeyset' =>array('def_modify' => "softkeyset")
    ),
    'sccpline' => array(
        'namedcallgroup' =>array('create' => "VARCHAR(100) NULL DEFAULT NULL AFTER `setvar`", 'modify' => "VARCHAR(100)"),
         'namedpickupgroup' =>array('create' => "VARCHAR(100) NULL DEFAULT NULL AFTER `namedcallgroup`", 'modify' => "VARCHAR(100)"),
         'adhocNumber' =>array('create' => "VARCHAR(45) NULL DEFAULT NULL AFTER `namedpickupgroup`"),
         'meetme' =>array('create' => "VARCHAR(5) NULL DEFAULT NULL AFTER `adhocNumber`"),
         'meetmenum' =>array('create' => "VARCHAR(45) NULL DEFAULT NULL AFTER `meetme`"),
         'meetmeopts' =>array('create' => "VARCHAR(45) NULL DEFAULT NULL AFTER `meetmenum`"),
         'regexten' =>array('create' => "VARCHAR(45) NULL DEFAULT NULL AFTER `meetmeopts`"),
         'rtptos' => array('drop' => "yes"),
         'audio_tos' => array('drop' => "yes"),
         'audio_cos' => array('drop' => "yes"),
         'video_tos' => array('drop' => "yes"),
         'video_cos' => array('drop' => "yes"),
         'incominglimit' =>array('def_modify' => "2"),
         'transfer' =>array('def_modify' => "on"),
         'vmnum' =>array('def_modify' => "*97"),
         'musicclass' =>array('def_modify' => "default"),
         'echocancel' =>array('def_modify' => "on"),
         'silencesuppression' =>array('def_modify' => "off"),
         'dnd' =>array('create' => 'VARCHAR( 12 ) DEFAULT "reject" AFTER `amaflags`', 'modify' => "VARCHAR(12)", 'def_modify' =>"reject")
    )
);

$db_config_v3 = array(
    'sccpdevmodel' => array(
        'enabled' => array('create' => "INT(2) NULL DEFAULT '0'"),
        'nametemplate' => array('create' => 'VARCHAR(50) NULL DEFAULT NULL'),
        'loadinformationid' => array('create' => "VARCHAR(30) NULL DEFAULT NULL" )
    ),
    'sccpdevice' => array(
        'pickupexten' =>  array('rename' => "directed_pickup"),
        'directed_pickup' =>  array('create' => "enum('on','off') NOT NULL default 'on'", 'modify' => "enum('on','off')"),
        'pickupcontext' =>  array('rename' => "directed_pickup_context"),
        'directed_pickup_context' =>  array('create' => "VARCHAR(100) NULL DEFAULT NULL"),
        'pickupmodeanswer' => array('rename' => "directed_pickup_modeanswer"),
        'directed_pickup_modeanswer' => array('create' => "enum('on','off') NOT NULL default 'on'", 'modify' => "enum('on','off')"),
        'hwlang' => array('rename' => "_hwlang"),
        '_hwlang' => array('create' => 'varchar(12) NULL DEFAULT NULL'),
        'useRedialMenu' => array('create' => "VARCHAR(5) NULL DEFAULT 'no' AFTER `_hwlang`"),

        //'dtmfmode' => array('create' => "VARCHAR(10) default 'outofband'", 'modify' => "VARCHAR(10)", 'def_modify'=> 'outofband'),
        'dtmfmode' => array('drop' => "yes"),

        'deny' =>	array('create' => 'VARCHAR(100) NULL DEFAULT NULL','modify' => "VARCHAR(100)"),
        'permit' => array('create' => 'VARCHAR(100) NULL DEFAULT NULL','modify' => "VARCHAR(100)"),
        'backgroundImage' =>	array('create' => 'VARCHAR(255) NULL DEFAULT NULL','modify' => "VARCHAR(255)"),
        'ringtone' => array('create' => 'VARCHAR(255) NULL DEFAULT NULL','modify' => "VARCHAR(255)"),

        'transfer'   =>array('create' => "enum('on','off') NOT NULL default 'on'",'modify' => "enum('on','off')"),
        'cfwdall'    =>array('create' => "enum('on','off') NOT NULL default 'on'",'modify' => "enum('on','off')"),
        'cfwdbusy'   =>array('create' => "enum('on','off') NOT NULL default 'on'",'modify' => "enum('on','off')"),
        'directrtp'  =>array('create' => "enum('on','off') NULL default NULL",'modify' => "enum('on','off')"),
        'dndFeature' =>array('create' => "enum('on','off') NULL default NULL",'modify' => "enum('on','off')"),
        'earlyrtp' =>array('create' => "ENUM('immediate','offHook','dialing','ringout','progress','none') NULL default NULL",'modify' => "ENUM('immediate','offHook','dialing','ringout','progress','none')"),
        'audio_tos'=>array('def_modify' => "0xB8"),
        'audio_cos'=>array('def_modify' => "6"),
        'video_tos'=>array('def_modify' => "0x88"),
        'video_cos'=>array('def_modify' => "5"),
        'trustphoneip'=>array('drop' => "yes"),

        'mwilamp'    =>array('create' => "enum('on','off','wink','flash','blink') NULL  default 'on'" ,'modify' => "enum('on','off','wink','flash','blink')"),
        'mwioncall'  =>array('create' => "enum('on','off') NULL default 'on'",'modify' => "enum('on','off')"),
        'private'    =>array('create' => "enum('on','off') NOT NULL default 'off'",'modify' => "enum('on','off')"), // Что-то лишенне 
        'privacy'    =>array('create' => "enum('full','on','off') NOT NULL default 'full'",'modify' => "enum('full','on','off')"), // Что-то лишенне 
        'nat'        =>array('create' => "enum('on','off','auto') NULL default NULL",'modify' => "enum('on','off','auto')"),
        'conf_allow' =>array('create' => "enum('on','off') NOT NULL default 'on'",'modify' => "enum('on','off')"),
        'conf_play_part_announce' =>array('create' => "enum('on','off') NOT NULL default 'on'",'modify' => "enum('on','off')"),
        'conf_mute_on_entry' =>array('create' => "enum('on','off') NOT NULL default 'off'",'modify' => "enum('on','off')"),
        'conf_show_conflist' =>array('create' => "enum('on','off') NOT NULL default 'on'",'modify' => "enum('on','off')"),

        'type'         =>array('create' => 'VARCHAR(15) NULL DEFAULT NULL','modify' => "VARCHAR(15)"),
        'imageversion' =>array('create' => 'VARCHAR(31) NULL DEFAULT NULL','modify' => "VARCHAR(31)"),
        
        'softkeyset' =>array('def_modify' => "softkeyset")
    ),
    'sccpline' => array(
        'namedcallgroup' =>array('create' => "VARCHAR(100) NULL DEFAULT NULL AFTER `setvar`", 'modify' => "VARCHAR(100)"),
         'namedpickupgroup' =>array('create' => "VARCHAR(100) NULL DEFAULT NULL AFTER `namedcallgroup`", 'modify' => "VARCHAR(100)"),
         'adhocNumber' =>array('create' => "VARCHAR(45) NULL DEFAULT NULL AFTER `namedpickupgroup`"),
         'meetme' =>array('create' => "VARCHAR(5) NULL DEFAULT NULL AFTER `adhocNumber`"),
         'meetmenum' =>array('create' => "VARCHAR(45) NULL DEFAULT NULL AFTER `meetme`"),
         'meetmeopts' =>array('create' => "VARCHAR(45) NULL DEFAULT NULL AFTER `meetmenum`"),
         'regexten' =>array('create' => "VARCHAR(45) NULL DEFAULT NULL AFTER `meetmeopts`"),
         'rtptos' => array('drop' => "yes"),
         'audio_tos' => array('drop' => "yes"),
         'audio_cos' => array('drop' => "yes"),
         'video_tos' => array('drop' => "yes"),
         'video_cos' => array('drop' => "yes"),
         'incominglimit' =>array('create' => "INT(11) DEFAULT '6'", 'modify' =>'INT(11)', 'def_modify' => "6"),
         'transfer' =>array('create' => "enum('on','off') NOT NULL default 'on'",'modify' => "enum('on','off')"),
         'vmnum' =>array('def_modify' => "*97"),
         'musicclass' =>array('def_modify' => "default"),
         'id' =>array('create' => 'MEDIUMINT(9) NOT NULL AUTO_INCREMENT, ADD UNIQUE(id);', 'modify' => "MEDIUMINT(9)",'index' =>'id'),
//        'id' =>array('create' => 'VARCHAR( 20 ) NULL DEFAULT NULL', 'modify' => "VARCHAR(20)", 'def_modify' =>"NULL"),
        
         'echocancel' =>array('create' => "enum('on','off') NOT NULL default 'off'",'modify' => "enum('on','off')"),
         'silencesuppression' =>array('create' => "enum('on','off') NOT NULL default 'off'",'modify' => "enum('on','off')"),
         'dnd'        => array('create' => "enum('off','reject','silent','user') NOT NULL default 'reject'", 'modify' => "enum('off','reject','silent','user')", 'def_modify' =>"reject")
    )
);
    if ($sccp_compatible >= 430) {
        return $db_config_v3;
    } else {
        return $db_config_v0;
    }
}

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT" : "AUTO_INCREMENT";

$table_req = array('sccpdevice', 'sccpline', 'buttonconfig');
$ss = FreePBX::create()->Sccp_manager;
$astman = FreePBX::create()->astman;
$sccp_compatible = 0;
//$db_config = $db_config_v0;
$db_config = '';

function CheckSCCPManagerDBTables($table_req) {
    global $db;
    outn("<li>" . _("Checking for Sccp_manager database tables..") . "</li>");
    foreach ($table_req as $value) {
        $check = $db->getRow("SELECT 1 FROM `$value` LIMIT 0", DB_FETCHMODE_ASSOC);
        if (DB::IsError($check)) {
            //print_r("none, creating table :". $value);
            outn(_("Can't find table: " . $value));
            outn(_("Please goto the chan-sccp/conf directory and create the DB schema manually (See wiki)"));
            die_freepbx("!!!! Installation error: Can not find required ".$value." table !!!!!!\n");
        }
    }
}

function CheckPermissions() {
    outn("<li>" . _("Checking Filesystem Permissions") . "</li>");
    $dst = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/views';
    if (fileowner($_SERVER['DOCUMENT_ROOT']) != fileowner($dst)){
        die_freepbx('Please (re-)check permissions by running "amportal chown. Installation Failed"');
    }
}

function CheckAsteriskVersion() {
    outn("<li>" . _("Checking Asterisk Version : ") . $version . "</li>");
    $version = FreePBX::Config()->get('ASTVERSION');
    if (!empty($version)) {
        // Woo, we have a version
        if (version_compare($version, "12.2.0", ">=")) {
            $ver_compatible = true;
        } else {
            die_freepbx('Asterisk Version is to old, please upgrade to asterisk-12 or higher. Installation Failed');
        }
    } else {
        // Well. I don't know what version of Asterisk I'm running.
        // Assume less than 12.
        $ver_compatible = false;
        die_freepbx('Asterisk Version could not be verified. Installation Failed');
    }
    return $ver_compatible;
}

function CheckChanSCCPСomatable() {
    global $srvinterface,$astman;
    if (!$astman) {
        ie_freepbx('No asterisk manager connection provided!. Installation Failed');
    }
    $sccp_compatible = $srvinterface->get_compatible_sccp();
    outn("<li>" . _("Sccp model Compatable code : ") . $sccp_compatible . "</li>");
    return $sccp_compatible;
}
          
    
function InstallDB_sccpsettings() {
    global $db;
    outn("<li>" . _("Creating sccpsettings table...") . "</li>");
    $sql = "CREATE TABLE IF NOT EXISTS `sccpsettings` (
            `keyword` VARCHAR (50) NOT NULL default '',
            `data`    VARCHAR (255) NOT NULL default '',
            `seq`     TINYINT (1),
            `type`    TINYINT (1) NOT NULL default '0',
            PRIMARY KEY (`keyword`,`seq`,`type`)
    );";
    $check = $db->query($sql);
    if (db::IsError($check)) {
        die_freepbx("Can not create sccpsettings table, error:$check\n");
    }
    return true;
}

function InstallDB_sccpdevmodel() {
    global $db;
    outn("<li>" . _("Creating sccpdevmodel table...") . "</li>");
    $sql = "CREATE TABLE IF NOT EXISTS `sccpdevmodel` (
        `model` varchar(20) NOT NULL DEFAULT '',
        `vendor` varchar(40) DEFAULT '',
        `dns` int(2) DEFAULT '1',
        `buttons` int(2) DEFAULT '0',
        `loadimage` varchar(40) DEFAULT '',
        `loadinformationid` VARCHAR(30) NULL DEFAULT NULL,
        `enabled` INT(2) NULL DEFAULT '0',
        `nametemplate` VARCHAR(50) NULL DEFAULT NULL,
        PRIMARY KEY (`model`),
        KEY `model` (`model`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
    $check = $db->query($sql);
    if (db::IsError($check)) {
        die_freepbx("Can not create sccpdevmodel table, error:$check\n");
    }
    return true;
}

/*
function InstallDB_updateEnums($db_config) {
    global $db;
    if (!$db_config) {
        die_freepbx("No db_config provided");
    }
    $count_modify = 0;
    outn("<li>" . _("Update Database Enums") . "</li>");
    foreach ($db_config as $tabl_name => &$tab_modify) {
        $sql = "";
        $sql_r = array();
        foreach ($tab_modify as $row_fld => $row_data){
            if (!empty($row_data['crate'])) {                    
                if (strpos($row_data['crate'], 'enum') !== false) {
                    $sql_r[] = $row_fld;
                }
            }
        }
        $sql = "SELECT " . implode(',', $sql_r) . " from ".$tabl_name;
        $db_result= $db->getAll($sql);
        if (DB::IsError($db_result)) {
            die_freepbx("Can not add get informations from ".$tabl_name." table\n");
        }
        $sql_table = '';
        $enum_data = array('true'=>'on','false'=>'off','yes'=>'on','no'=>'off');
        foreach ($db_result as $tabl_data){
            $sql = "";
            foreach( $tabl_data as $row => $col_val){
                $val= strtolower($col_val);
                if (isset($enum_data[$val])) {
                    $sql .= $row.'="'.$enum_data[$val].'",';
                }
            }
            if (!empty($sql)){
            }
        }
        
    }
    
}

*/

function InstallDB_updateSchema($db_config) {
    global $db;
    if (!$db_config) {
        die_freepbx("No db_config provided");
    }
    $count_modify = 0;
    outn("<li>" . _("Modify Database schema") . "</li>");
    foreach ($db_config as $tabl_name => &$tab_modify) {
        // 0 - name 1-type  4- default
        $sql = "DESCRIBE ".$tabl_name."";
        $db_result= $db->getAll($sql);
        if (DB::IsError($db_result)) {
            die_freepbx("Can not add get informations from ".$tabl_name." table\n");
        }
        foreach ($db_result as $tabl_data){
            $fld_id = $tabl_data[0];
            if (!empty($tab_modify[$fld_id])) {
                $db_config[$tabl_name][$fld_id]['status']  = 'yes';
                if (!empty($tab_modify[$fld_id]['def_modify'])) {
                    if (strtoupper($tab_modify[$fld_id]['def_modify']) ==  strtoupper($tabl_data[4])) {
                        $db_config[$tabl_name][$fld_id]['def_mod_stat']  = 'no';
                    }
                }
                if (!empty($tab_modify[$fld_id]['modify'])) {
                    if ( strtoupper ($tab_modify[$fld_id]['modify']) ==  strtoupper($tabl_data[1])) {
                        $db_config[$tabl_name][$fld_id]['mod_stat']  = 'no';
                    }
                }
                if (!empty($tab_modify[$fld_id]['rename'])) {
                    $fld_id_source = $tab_modify[$fld_id]['rename'];
                    $db_config[$tabl_name][$fld_id_source]['status']  = 'yes';
                    $db_config[$tabl_name][$fld_id]['create']  = $db_config[$tabl_name][$fld_id_source]['create'];
                }
            }
        }
        $sql_create =''; 
        $sql_modify =''; 
        $sql_update =''; 
        
        foreach ($tab_modify as $row_fld => $row_data){
            if (empty($row_data['status'])) {
                if (!empty($row_data['create'])) {
                    $sql_create .='ADD COLUMN `'.$row_fld.'` '. $row_data['create'].', ';
                    $count_modify ++;
                }
            } else {
                if (!empty($row_data['rename'])) {                    
                    $sql_modify .= 'CHANGE COLUMN `'.$row_fld.'` `'. $row_data['rename'].'` '.$row_data['create'].', ';
                    $count_modify ++;
                }
                if (!empty($row_data['modify'])) {
                    if (empty($row_data['mod_stat'])) {
                        if (!empty($row_data['create'])) {
//                            $sql_modify .=  "CHANGE COLUMN  `".$row_fld."` `".$row_fld."` ".$row_data['create'].", ";
                            $sql_modify .=  "MODIFY COLUMN  `".$row_fld."` ".$row_data['create'].", ";
                        } else {
//                            $sql_modify .=  "CHANGE COLUMN  `".$row_fld."` `".$row_fld."` ".$row_data['modify']." DEFAULT '".$row_data['def_modify']."', ";
                            $sql_modify .=  "MODIFY COLUMN  `".$row_fld."` ".$row_data['modify']." DEFAULT '".$row_data['def_modify']."', ";
                        }
                        if (strpos($row_data['modify'],'enum') !== false ) {
                            $sql_update .= "UPDATE ".$tabl_name." set `".$row_fld."`=case when lower(`".$row_fld."`) in ('yes','true','1') then 'on' when lower(`".$row_fld."`) in ('no', 'false', '0') then 'off' else `".$row_fld."` end; ";
                        }
                        $row_data['def_mod_stat'] = 'no';
                        $count_modify ++;
                    }
                }
                if (!empty($row_data['def_modify'])) {
                    if (empty($row_data['def_mod_stat'])) {
                        $sql_modify .=  "ALTER COLUMN `".$row_fld."` SET DEFAULT '".$row_data['def_modify']."', ";
                        $count_modify ++;
                    }
                }
                if (!empty($row_data['drop'])) {                    
                    $sql_create .='DROP COLUMN `'.$row_fld.'`, ';
                    $count_modify ++;
                }
                
            }
        }
        if (!empty($sql_update)) {
            $sql_update = 'BEGIN; '.$sql_update.' COMMIT;';
            sql($sql_update);
            $affected_rows = $db->affectedRows();
//            $check = $db->query($sql_update);
//            $db->closeCursor();
            outn("<li>" . _("Update table row :").$affected_rows. "</li>");
//            if (db::IsError($check)) {
//                die_freepbx("Can not update  ".$tabl_name." table sql: ".$sql_update."n");
//                die_freepbx("Can not update  ".$tabl_name." table\n");
//            }
        }

        if (!empty($sql_create)) {
            outn("<li>" . _("Create New table"). "</li>");
            $sql_create = "ALTER TABLE `".$tabl_name."` ". substr($sql_create,0,-2); 
            $check = $db->query($sql_create);
            if (db::IsError($check)) {
                die_freepbx("Can not create ".$tabl_name." table sql: ".$sql_create."n");
            }
        }
        if (!empty($sql_modify)) {
            outn("<li>" . _("Modify table"). "</li>");

            $sql_modify = "ALTER TABLE `".$tabl_name."` ". substr($sql_modify,0,-2).';'; 
            $check = $db->query($sql_modify);
            if (db::IsError($check)) {
                out("<li>" . print_r($check,1). "</li>");
                die("Can not modify ".$tabl_name." table sql: ".$sql_modify."n");
                die_freepbx("Can not modify ".$tabl_name." table sql: ".$sql_modify."n");
            }
        }
    }
    outn("<li>" . _("Total modify count :") .$count_modify. "</li>");
    return true;
}

function InstallDB_fillsccpdevmodel() {
    global $db;
    outn("<li>" . _("Fill sccpdevmodel") . "</li>");
    $sql = "REPLACE INTO `sccpdevmodel` (`model`, `vendor`, `dns`, `buttons`, `loadimage`, `loadinformationid`, `enabled`, `nametemplate`) VALUES ('12 SP', 'CISCO', 1, 1, '', 'loadInformation3', 0, NULL)," .
                "('12 SP+', 'CISCO', 1, 1, '', 'loadInformation2', 0, NULL), ('30 SP+', 'CISCO', 1, 1, '', 'loadInformation1', 0, NULL), ('30 VIP', 'CISCO', 1, 1, '', 'loadInformation5', 0, NULL), ('3911', 'CISCO', 1, 1, '', 'loadInformation446', 0, NULL), ('3951', 'CISCO', 1, 1, '', 'loadInformation412', 0, ''), ('6901', 'CISCO', 1, 0, 'SCCP6901.9-2-1-a', 'loadInformation547', 0, NULL), ('6911', 'CISCO', 1, 0, 'SCCP6911.9-2-1-a', 'loadInformation548', 0, NULL), ('6921', 'CISCO', 1, 0, 'SCCP69xx.9-2-1-0', 'loadInformation496', 0, NULL), ('6941', 'CISCO', 1, 1, 'SCCP69xx.9-2-1-0', 'loadInformation495', 0, NULL), ('6945', 'CISCO', 1, 0, 'SCCP6945.9-2-1-0', 'loadInformation564', 0, NULL), ('6961', 'CISCO', 1, 0, 'SCCP69xx.9-2-1-0', 'loadInformation497', 0, NULL), ('7902', 'CISCO', 1, 1, 'CP7902080002SCCP060817A', 'loadInformation30008', 0, NULL), " .
                "('7905', 'CISCO', 1, 1, 'CP7905080003SCCP070409A', 'loadInformation20000', 0, NULL), ('7906', 'CISCO', 1, 1, 'SCCP11.9-2-1S', 'loadInformation369', 1, 'SEP0000000000.cnf.xml_791x_template'), ('7910', 'CISCO', 1, 1, 'SCCP11.9-2-1S', 'loadInformation6', 1, 'SEP0000000000.cnf.xml_791x_template'), ('7911', 'CISCO', 1, 1, 'SCCP11.9-2-1S', 'loadInformation307', 1, 'SEP0000000000.cnf.xml_791x_template'), ('7912', 'CISCO', 1, 1, 'CP7912080004SCCP080108A', 'loadInformation30007', 0, NULL), ('7914', 'CISCO', 0, 14, 'S00105000400', 'loadInformation124', 1, NULL),('7914,7914', 'CISCO', 0, 28, 'S00105000400', 'loadInformation124', 1, NULL), ('7915', 'CISCO', 0, 24, 'B015-1-0-4', 'loadInformation227', 1, NULL), ('7915,7915', 'CISCO', 0, 48, 'B015-1-0-4', 'loadInformation228', 1, NULL), ('7916', 'CISCO', 0, 24, 'B015-1-0-4', 'loadInformation229', 1, NULL), " .
                "('7916,7916', 'CISCO', 0, 48, 'B016-1-0-4', 'loadInformation230', 1, NULL), ('7920', 'CISCO', 1, 1, 'cmterm_7920.4.0-03-02', 'loadInformation30002', 0, NULL), ('7921', 'CISCO', 1, 1, 'CP7921G-1.4.1SR1', 'loadInformation365', 0, NULL),('7925', 'CISCO', 1, 6, 'CP7925G-1.4.1SR1', 'loadInformation484', 0, NULL), ('7926', 'CISCO', 1, 1, 'CP7926G-1.4.1SR1', 'loadInformation557', 0, NULL), ('7931', 'CISCO', 1, 34, 'SCCP31.9-2-1S', 'loadInformation348', 0, NULL), ('7935', 'CISCO', 1, 2, 'P00503021900', 'loadInformation9', 0, NULL), ('7936', 'CISCO', 1, 1, 'cmterm_7936.3-3-21-0', 'loadInformation30019', 0, NULL), ('7937', 'CISCO', 1, 1, 'apps37sccp.1-4-4-0', 'loadInformation431', 0, 'SEP0000000000.cnf.xml_7937_template'), ('7940', 'CISCO', 1, 2, 'P0030801SR02', 'loadInformation8', 1, 'SEP0000000000.cnf.xml_796x_template'), " .
                "('7941', 'CISCO', 1, 2, 'SCCP41.9-2-1S', 'loadInformation115', 0, 'SEP0000000000.cnf.xml_796x_template'),('7941G-GE', 'CISCO', 1, 2, 'SCCP41.9-2-1S', 'loadInformation309', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7942', 'CISCO', 1, 2, 'SCCP42.9-2-1S', 'loadInformation434', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7945', 'CISCO', 1, 2, 'SCCP45.9-2-1S', 'loadInformation435', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7960', 'CISCO', 3, 6, 'P0030801SR02', 'loadInformation7', 1, 'SEP0000000000.cnf.xml_796x_template'), ('7961', 'CISCO', 3, 6, 'SCCP41.9-2-1S', 'loadInformation30018', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7961G-GE', 'CISCO', 3, 6, 'SCCP41.9-2-1S', 'loadInformation308', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7962', 'CISCO', 3, 6, 'SCCP42.9-2-1S', 'loadInformation404', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7965', 'CISCO', 3, 6, 'SCCP45.9-2-1S', 'loadInformation436', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7970', 'CISCO', 3, 8, 'SCCP70.9-2-1S', 'loadInformation30006', 0, NULL), ('7971', 'CISCO', 1, 2, 'SCCP75.9-2-1S', 'loadInformation119', 0, NULL), ('7975', 'CISCO', 3, 8, 'SCCP75.9-2-1S', 'loadInformation437', 0, NULL), ('7985', 'CISCO', 3, 8, 'cmterm_7985.4-1-7-0', 'loadInformation302', 0, NULL), ('8941', 'CISCO', 1, 0, 'SCCP894x.9-2-2-0', 'loadInformation586', 0, NULL), ('8945', 'CISCO', 1, 0, 'SCCP894x.9-2-2-0', 'loadInformation585', 0, NULL), ('ATA 186', 'CISCO', 1, 1, 'ATA030204SCCP090202A', 'loadInformation12', 0, NULL), ('ATA 187', 'CISCO', 1, 1, 'ATA187.9-2-3-1', 'loadInformation550', 0, NULL), ('CN622', 'MOTOROLA', 1, 1, '', 'loadInformation335', 0, NULL), ('Digital Access', 'CISCO', 1, 1, 'D001M022', 'loadInformation40', 0, NULL), ('Digital Access+', 'CISCO', 1, 1, 'D00303010033', 'loadInformation42', 0, NULL), ('E-Series', 'NOKIA', 1, 1, '', '', 0, NULL), ('ICC', 'NOKIA', 1, 1, '', '', 0, NULL), " .
                "('IP Communicator', 'CISCO', 1, 1, '', 'loadInformation30016', 0, NULL), ('Nokia E', 'Nokia', 0, 28, '', 'loadInformation275', 0, NULL), ('VGC Phone', 'CISCO', 1, 1, '', 'loadInformation10', 0, NULL), ('VGC Virtual', 'CISCO', 1, 1, '', 'loadInformation11', 0, NULL);";
    $check = $db->query($sql);
    if (db::IsError($check)) {
        die_freepbx("Can not create sccpdevmodel table, error:$check\n");
    }
    return true;
}

function InstallDB_updateSccpDevice() {
    global $db;
    outn("<li>" . _("Update sccpdevice") . "</li>");
    $sql = "UPDATE `sccpdevice` set audio_tos='0xB8',audio_cos='6',video_tos='0x88',video_cos='5' where audio_tos=NULL or audio_tos='';";
    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Can not REPLACE defaults into sccpdevice table\n");
    }
}

function InstallDB_createButtonConfigTrigger() {
    global $db;
    outn("<li>" . _("(Re)Create buttonconfig trigger") . "</li>");
    $sql = "
    DROP TRIGGER IF EXISTS trg_buttonconfig;
    DELIMITER $$
    CREATE TRIGGER trg_buttonconfig BEFORE INSERT ON buttonconfig
    FOR EACH ROW
    BEGIN
    IF NEW.`type` = 'line' THEN
        SET @line_x = SUBSTRING_INDEX(NEW.`name`,'!',1);
        SET @line_x = SUBSTRING_INDEX(@line_x,'@',1);            
        IF (SELECT COUNT(*) FROM `sccpline` WHERE `sccpline`.`name` = @line_x ) = 0
        THEN
                UPDATE `Foreign key contraint violated: line does not exist in sccpline` SET x=1;
        END IF;
    END IF;
    END $$
    DELIMITER ;";
    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Can not modify sccpdevice table\n");
    }
    return true;
}

function InstallDB_CreateSccpDeviceConfigView($sccp_compatible) {
    global $db;
    outn("<li>" . _("(Re)Create sccpdeviceconfig view") . "</li>");
    $sql = "";
    if ($sccp_compatible < 431)  {
        $sql= "
        CREATE OR REPLACE
            ALGORITHM = MERGE
            VIEW sccpdeviceconfig AS

        SELECT GROUP_CONCAT( CONCAT_WS( ',', buttonconfig.type, buttonconfig.name, buttonconfig.options )
        ORDER BY instance ASC
        SEPARATOR ';' ) AS button,
        `sccpdevice`.`type` AS `type`,`sccpdevice`.`addon` AS `addon`,`sccpdevice`.`description` AS `description`,`sccpdevice`.`tzoffset` AS `tzoffset`,
        `sccpdevice`.`transfer` AS `transfer`,`sccpdevice`.`cfwdall` AS `cfwdall`,`sccpdevice`.`cfwdbusy` AS `cfwdbusy`,`sccpdevice`.`imageversion` AS `imageversion`,
        `sccpdevice`.`deny` AS `deny`,`sccpdevice`.`permit` AS `permit`,`sccpdevice`.`dndFeature` AS `dndFeature`,`sccpdevice`.`directrtp` AS `directrtp`,
        `sccpdevice`.`earlyrtp` AS `earlyrtp`,`sccpdevice`.`mwilamp` AS `mwilamp`,`sccpdevice`.`mwioncall` AS `mwioncall`,`sccpdevice`.`pickupexten` AS `pickupexten`,
        `sccpdevice`.`pickupcontext` AS `pickupcontext`,`sccpdevice`.`pickupmodeanswer` AS `pickupmodeanswer`,`sccpdevice`.`private` AS `private`,
        `sccpdevice`.`privacy` AS `privacy`,`sccpdevice`.`nat` AS `nat`,`sccpdevice`.`softkeyset` AS `softkeyset`,`sccpdevice`.`audio_tos` AS `audio_tos`,
        `sccpdevice`.`audio_cos` AS `audio_cos`,`sccpdevice`.`video_tos` AS `video_tos`,`sccpdevice`.`video_cos` AS `video_cos`,`sccpdevice`.`conf_allow` AS `conf_allow`,
        `sccpdevice`.`conf_play_general_announce` AS `conf_play_general_announce`,`sccpdevice`.`conf_play_part_announce` AS `conf_play_part_announce`,
        `sccpdevice`.`conf_mute_on_entry` AS `conf_mute_on_entry`,`sccpdevice`.`conf_music_on_hold_class` AS `conf_music_on_hold_class`,
        `sccpdevice`.`conf_show_conflist` AS `conf_show_conflist`,`sccpdevice`.`setvar` AS `setvar`,`sccpdevice`.`disallow` AS `disallow`,
        `sccpdevice`.`allow` AS `allow`,`sccpdevice`.`backgroundImage` AS `backgroundImage`,`sccpdevice`.`ringtone` AS `ringtone`,`sccpdevice`.`name` AS `name`
        FROM sccpdevice
        LEFT JOIN buttonconfig ON ( buttonconfig.device = sccpdevice.name )
        GROUP BY sccpdevice.name;";
    } else {
        $sql = "
        CREATE OR REPLACE 
            ALGORITHM = MERGE
            VIEW sccpdeviceconfig AS
        SELECT GROUP_CONCAT( CONCAT_WS( ',', buttonconfig.type, buttonconfig.name, buttonconfig.options )
        ORDER BY instance ASC
        SEPARATOR ';' ) AS button, sccpdevice.*
        FROM sccpdevice
        LEFT JOIN buttonconfig ON ( buttonconfig.device = sccpdevice.name )
        GROUP BY sccpdevice.name;";
    }
    $results = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx(sprintf(_("Error updating sccpdeviceconfig view. Command was: %s; error was: %s "), $sql, $results->getMessage()));
    }
    return true;
}    

function Setup_RealTime() {
    global $amp_conf;
    outn("<li>" . _("Pre config RealTime") . "</li>");
    $cnf_int = \FreePBX::Config();
    $cnf_wr = \FreePBX::WriteConfig();
    $cnf_read = \FreePBX::LoadConfig();
    $def_config =array('sccpdevice' => 'mysql,sccp,sccpdeviceconfig','sccpline' => ' mysql,sccp,sccpline');
    $def_bd_config = array('dbhost' => $amp_conf['AMPDBHOST'], 'dbname' => $amp_conf['AMPDBNAME'], 
                           'dbuser' => $amp_conf['AMPDBUSER'], 'dbpass' => $amp_conf['AMPDBPASS'],
                           'dbport' => '3306', 'dbsock' => '/var/lib/mysql/mysql.sock');
    $def_bd_sec = 'sccp';

    $dir = $cnf_int->get('ASTETCDIR');
    
    $res_conf_old = '';
    $res_conf = '';
    $ext_conf = '';
    if (file_exists($dir. '/extconfig.conf')) {
        $ext_conf = $cnf_read->getConfig('extconfig.conf');
    }

    if (!empty($ext_conf)) {        
        $tmp = array();
        if (!empty($ext_conf['settings']['sccpdevice'])) {
            $tmp = explode(',', $ext_conf['settings']['sccpdevice']);
            $def_config['sccpdevice']=$ext_conf['settings']['sccpdevice'];
        }  
        if (!empty($ext_conf['settings']['sccpline'])) {
            if (empty($tmp)){
                $tmp = explode(',', $ext_conf['settings']['sccpline']);
                $tmp[2] ='sccpdevice';
                $def_config['sccpdevice']= implode(',', $tmp);
            } 
            $def_config['sccpline']=$ext_conf['settings']['sccpline'];
        }
        if (!empty($tmp)){ 
            $def_bd_sec = $tmp[1];
        }
    }
    $ext_conf['settings']['sccpdevice'] = $def_config['sccpdevice'];
    $ext_conf['settings']['sccpline'] = $def_config['sccpline'];

    if (file_exists($dir. '/res_mysql.conf')) {
        $res_conf = $cnf_read->getConfig('res_mysql.conf');
        if (empty($res_conf[$def_bd_sec])) {
            $res_conf[$def_bd_sec] = $def_bd_config; 
        }
        $cnf_wr -> writeConfig('res_mysql.conf', $res_conf,false);
    }
    if (file_exists($dir. '/res_config_mysql.conf')) {
        $res_conf = $cnf_read->getConfig('res_config_mysql.conf');
        if (empty($res_conf_old[$def_bd_sec])) {
            $res_conf[$def_bd_sec] = $def_bd_config; 
        }
        $cnf_wr -> writeConfig('res_config_mysql.conf', $res_conf,false);
    }
    if (empty($res_conf)) {
        $res_conf[$def_bd_sec] = $def_bd_config; 
        $res_conf['general']['dbsock'] = $res_conf[$def_bd_sec]['dbsock'];
        $cnf_wr -> writeConfig('res_config_mysql.conf', $res_conf,false);
    }
    $cnf_wr -> writeConfig('extconfig.conf', $ext_conf,false);
    
}

CheckSCCPManagerDBTables($table_req);
#CheckPermissions();
CheckAsteriskVersion();
$sccp_compatible = CheckChanSCCPСomatable();
$db_config = Get_DB_config($sccp_compatible);
InstallDB_sccpsettings();
InstallDB_sccpdevmodel();
InstallDB_updateSchema($db_config);
InstallDB_fillsccpdevmodel();
InstallDB_updateSccpDevice();
InstallDB_createButtonConfigTrigger();
InstallDB_CreateSccpDeviceConfigView($sccp_compatible);
Setup_RealTime();
outn("<br>");

//    $ss->save_submit($request);
//    $ss->sccp_create_sccp_init();
//    $ss->sccp_db_save_setting();
//
//}