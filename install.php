<?php

if (!defined('FREEPBX_IS_AUTH')) {
    die('No direct script access allowed');
}

global $db;
global $amp_conf;
global $astman;
global $version;

$db_config_v0 = array('sccpdevmodel' => array('enabled' => array('create' => "INT(2) NULL DEFAULT '0'"),
                                           'nametemplate' => array('create' => 'VARCHAR(50) NULL DEFAULT NULL'),
                                           'loadinformationid' => array('create' => "VARCHAR(30) NULL DEFAULT NULL" )
                                           ),
                     'sccpdevice' => array(
                                           '_hwlang' => array('create' => 'varchar(12) NULL DEFAULT NULL'),
//                                           'useRedialMenu' => array('create' => "VARCHAR(5) NULL DEFAULT 'no' AFTER `_hwlang`"),
//                                           'dtmfmode' => array('create' => "VARCHAR(10) default 'outofband'", 'modify' => "VARCHAR(10)", 'def_modify'=> 'outofband'),
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
    
                       'sccpline' => array('namedcallgroup' =>array('create' => "VARCHAR(100) NULL DEFAULT NULL AFTER `setvar`", 'modify' => "VARCHAR(100)"),
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
                                           'dnd' =>array('create' => 'VARCHAR( 12 ) DEFAULT "reject" AFTER `amaflags`', 'modify' => "VARCHAR(12)", 'def_modify' =>"reject")
                           )
        );


$db_config_v3 = array('sccpdevmodel' => array('enabled' => array('create' => "INT(2) NULL DEFAULT '0'"),
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
                         
//                                           'dtmfmode' => array('create' => "VARCHAR(10) default 'outofband'", 'modify' => "VARCHAR(10)", 'def_modify'=> 'outofband'),
                                           'dtmfmode' => array('drop' => "yes"),
                         
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
                                           'trustphoneip'=>array('drop' => "yes"),
                         
                                           'mwilamp' =>array('def_modify' => "on"),
                                           'mwioncall' =>array('def_modify' => "on"),
                                           'private' =>array('def_modify' => "on"),
                                           'privacy' =>array('def_modify' => "off"),
                                           'nat' =>array('def_modify' => "auto"),
                                           'softkeyset' =>array('def_modify' => "softkeyset")
                                            ),
                       'sccpline' => array('namedcallgroup' =>array('create' => "VARCHAR(100) NULL DEFAULT NULL AFTER `setvar`", 'modify' => "VARCHAR(100)"),
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

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT" : "AUTO_INCREMENT";

$table_req = array('sccpdevice', 'sccpline', 'buttonconfig');

$ss = FreePBX::create()->Sccp_manager;

outn(_("checking for Sccp_manager database tables.."));
foreach ($table_req as $value) {
    $check = $db->getRow("SELECT 1 FROM `$value` LIMIT 0", DB_FETCHMODE_ASSOC);
    if (DB::IsError($check)) {
//         print_r("none, creating table :". $value);
        out(_("none, Can't find table: " . $value));
        out(_("none, Please goto the chan-sccp/conf directory and create the DB schema (See wiki)"));
        die("!!!! Installation error: Can not find required ".$value." table !!!!!!\n");
//        die_freepbx("!!!! Instalation error. Can not find required ".$value." table !!!!!!\n");
    }
}


$version = FreePBX::Config()->get('ASTVERSION');
outn(_("checking Version : ").$version);

$dst = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/views';
if (fileowner($_SERVER['DOCUMENT_ROOT']) != fileowner($dst)){
    die('Please (re-)check permissions by running "amportal chown"');
}

if (!empty($version)) {
    // Woo, we have a version
    if (version_compare($version, "12.2.0", ">=")) {
        $ver_compatable = true;
    }
} else {
    // Well. I don't know what version of Asterisk I'm running.
    // Assume less than 12.

    $ver_compatable = false;
    die('Versin is not comapable');
}

// Should be replaced by a function to check the AMI:SCCPConfigMetaData
// JSON: {"Name":"Chan-sccp-b","Branch":"issues/394_1","Version":"4.3.0","Revision":"8d9d74fM","ConfigRevision":"0","ConfigureEnabled": ["park","pickup","realtime","video","conferenence","dirtrfr","feature_monitor","functions","manager_events","devstate_feature","dynamic_speeddial","dynamic_speeddial_cid","experimental","debug"],"Segments":["general","device","line","softkey"]}
$ast_out = $astman->Command("sccp show version");
$db_config = $db_config_v0;
if (preg_match("/Release.*\(/", $ast_out['data'] , $matches)) {
    $ast_out = explode(' ', substr($matches[0],9,-1));
    $sccp_ver = 0;
    if ($ast_out[0] >= '4.3.0'){
        $sccp_ver = 1;
    }
    if (!empty($ast_out[1]) && $ast_out[1] == 'develop'){           
        $sccp_ver = 10;
        if (!empty($ast_out[3])) {
            if (base_convert($ast_out[3],16,10) >= base_convert('702487a',16,10)){           
                $sccp_ver += 1;
                $db_config = $db_config_v3;
            }
        }
    } else {
       $sccp_ver = 0;
    }
}



// test
//    $sccp_ver = 0;
//    $db_config = $db_config_v0;
// test
    outn(_(" Sccp Version : ").$sccp_ver);

$sql = <<< END
    CREATE TABLE IF NOT EXISTS `sccpsettings` (
            `keyword` VARCHAR (50) NOT NULL default '',
            `data`    VARCHAR (255) NOT NULL default '',
            `seq`     TINYINT (1),
            `type`    TINYINT (1) NOT NULL default '0',
            PRIMARY KEY (`keyword`,`seq`,`type`)
    )
END;

    out(_("No sccpsettings table, creating table"));
    $check = $db->query($sql);
    if (db::IsError($check)) {
        die_freepbx("Can not create sccpsettings table, error:$check\n");
    }
    
$sql = <<< END
    CREATE TABLE IF NOT EXISTS `sccpdevmodel` (
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
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1
END;

$check = $db->query($sql);
    if (db::IsError($check)) {
        die_freepbx("Can not create sccpdevmodel table, error:$check\n");
    }
    out(_("none, Modify DB schama"));
    
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
        
        foreach ($tab_modify as $row_fld => $row_data){
            if (empty($row_data['status'])) {
                if (!empty($row_data['create'])) {
                    $sql_create .='ADD COLUMN `'.$row_fld.'` '. $row_data['create'].', ';
                }
            } else {
                if (!empty($row_data['rename'])) {                    
                    $sql_modify .= 'CHANGE COLUMN `'.$row_fld.'` `'. $row_data['rename'].'` '.$row_data['create'].', ';
                    
                }
                if (!empty($row_data['modify'])) {
                    if (empty($row_data['mod_stat'])) {
                        if (!empty($row_data['create'])) {
                            $sql_modify .=  "CHANGE COLUMN  `".$row_fld."` `".$row_fld."` ".$row_data['create'].", ";
                        } else {
                            $sql_modify .=  "CHANGE COLUMN  `".$row_fld."` `".$row_fld."` ".$row_data['modify']." DEFAULT '".$row_data['def_modify']."', ";
                        }
                        $row_data['def_mod_stat'] = 'no';
                    }
                }
                if (!empty($row_data['def_modify'])) {
                    if (empty($row_data['def_mod_stat'])) {
                        $sql_modify .=  "ALTER COLUMN `".$row_fld."` SET DEFAULT '".$row_data['def_modify']."', ";
                    }
                }
                if (!empty($row_data['drop'])) {                    
                    $sql_create .='DROP COLUMN `'.$row_fld.'`, ';
                }
                
            }
        }
        if (!empty($sql_create)) {
            $sql_create = "ALTER TABLE `".$tabl_name."` ". substr($sql_create,0,-2); 
            $check = $db->query($sql_create);
            if (db::IsError($check)) {
                die_freepbx("Can not create ".$tabl_name." table sql: ".$sql_create."n");
                die_freepbx("Can not create ".$tabl_name." table\n");
        }
            
        }
        if (!empty($sql_modify)) {
            $sql_modify = "ALTER TABLE `".$tabl_name."` ". substr($sql_modify,0,-2); 
            $check = $db->query($sql_modify);
            if (db::IsError($check)) {
                die_freepbx("Can not modify ".$tabl_name." table sql: ".$sql_modify."n");
                die_freepbx("Can not modify ".$tabl_name." table\n");
            }
        }
    }

    out(_("none, Uptade Table Info"));

//$sql = "REPLACE INTO `sccpdevmodel` VALUES ('7925','CISCO',1,1,'',''),('7902','CISCO',1,1,'CP7902080002SCCP060817A','loadInformation30008'),('7905','CISCO',1,1,'CP7905080003SCCP070409A','loadInformation20000'),('7906','CISCO',1,1,'SCCP11.8-3-1S','loadInformation369'),('7910','CISCO',1,1,'P00405000700','loadInformation6'),('7911','CISCO',1,1,'SCCP11.8-3-1S','loadInformation307'),('7912','CISCO',1,1,'CP7912080003SCCP070409A','loadInformation30007'),('7914','CISCO',0,14,'S00105000300','loadInformation124'),('7920','CISCO',1,1,'cmterm_7920.4.0-03-02','loadInformation30002'),('7921','CISCO',1,1,'CP7921G-1.0.3','loadInformation365'),('7931','CISCO',1,1,'SCCP31.8-3-1S','loadInformation348'),('7936','CISCO',1,1,'cmterm_7936.3-3-13-0','loadInformation30019'),('7937','CISCO',1,1,'','loadInformation431'),('7940','CISCO',1,2,'P00308000500','loadInformation8'),('Digital Access+','CISCO',1,1,'D00303010033','loadInformation42'),('7941','CISCO',1,2,'P00308000500','loadInformation115'),('7941G-GE','CISCO',1,2,'P00308000500','loadInformation309'),('7942','CISCO',1,2,'P00308000500','loadInformation434'),('Digital Access','CISCO',1,1,'D001M022','loadInformation40'),('7945','CISCO',1,2,'P00308000500','loadInformation435'),('7960','CISCO',3,6,'P00308000500','loadInformation7'),('7961','CISCO',3,6,'P00308000500','loadInformation30018'),('7961G-GE','CISCO',3,6,'P00308000500','loadInformation308'),('7962','CISCO',3,6,'P00308000500','loadInformation404'),('7965','CISCO',3,6,'P00308000500','loadInformation436'),('7970','CISCO',3,8,'SCCP70.8-3-1S','loadInformation30006'),('7971','CISCO',3,8,'SCCP70.8-3-1S','loadInformation119'),('7975','CISCO',3,8,'SCCP70.8-3-1S','loadInformation437'),('7985','CISCO',3,8,'cmterm_7985.4-1-4-0','loadInformation302'),('ATA 186','CISCO',1,1,'ATA030203SCCP051201A','loadInformation12'),('IP Communicator','CISCO',1,1,'','loadInformation30016'),('12 SP','CISCO',1,1,'','loadInformation3'),('12 SP+','CISCO',1,1,'','loadInformation2'),('30 SP+','CISCO',1,1,'','loadInformation1'),('30 VIP','CISCO',1,1,'','loadInformation5'),('7914,7914','CISCO',0,28,'S00105000300','loadInformation124'),('7915','CISCO',0,14,'',''),('7916','CISCO',0,14,'',''),('7915,7915','CISCO',0,28,'',''),('7916,7916','CISCO',0,28,'',''),('CN622','MOTOROLA',1,1,'','loadInformation335'),('ICC','NOKIA',1,1,'',''),('E-Series','NOKIA',1,1,'',''),('3911','CISCO',1,1,'','loadInformation446'),('3951','CISCO',1,1,'','loadInformation412');";
    $sql = "REPLACE INTO `sccpdevmodel` (`model`, `vendor`, `dns`, `buttons`, `loadimage`, `loadinformationid`, `enabled`, `nametemplate`) VALUES ('12 SP', 'CISCO', 1, 1, '', 'loadInformation3', 0, NULL)," .
            "('12 SP+', 'CISCO', 1, 1, '', 'loadInformation2', 0, NULL), ('30 SP+', 'CISCO', 1, 1, '', 'loadInformation1', 0, NULL), ('30 VIP', 'CISCO', 1, 1, '', 'loadInformation5', 0, NULL), ('3911', 'CISCO', 1, 1, '', 'loadInformation446', 0, NULL), ('3951', 'CISCO', 1, 1, '', 'loadInformation412', 0, ''), ('6901', 'CISCO', 1, 0, 'SCCP6901.9-2-1-a', 'loadInformation547', 0, NULL), ('6911', 'CISCO', 1, 0, 'SCCP6911.9-2-1-a', 'loadInformation548', 0, NULL), ('6921', 'CISCO', 1, 0, 'SCCP69xx.9-2-1-0', 'loadInformation496', 0, NULL), ('6941', 'CISCO', 1, 1, 'SCCP69xx.9-2-1-0', 'loadInformation495', 0, NULL), ('6945', 'CISCO', 1, 0, 'SCCP6945.9-2-1-0', 'loadInformation564', 0, NULL), ('6961', 'CISCO', 1, 0, 'SCCP69xx.9-2-1-0', 'loadInformation497', 0, NULL), ('7902', 'CISCO', 1, 1, 'CP7902080002SCCP060817A', 'loadInformation30008', 0, NULL), " .
            "('7905', 'CISCO', 1, 1, 'CP7905080003SCCP070409A', 'loadInformation20000', 0, NULL), ('7906', 'CISCO', 1, 1, 'SCCP11.9-2-1S', 'loadInformation369', 1, 'SEP0000000000.cnf.xml_791x_template'), ('7910', 'CISCO', 1, 1, 'SCCP11.9-2-1S', 'loadInformation6', 1, 'SEP0000000000.cnf.xml_791x_template'), ('7911', 'CISCO', 1, 1, 'SCCP11.9-2-1S', 'loadInformation307', 1, 'SEP0000000000.cnf.xml_791x_template'), ('7912', 'CISCO', 1, 1, 'CP7912080004SCCP080108A', 'loadInformation30007', 0, NULL), ('7914', 'CISCO', 0, 14, 'S00105000400', 'loadInformation124', 1, NULL),('7914,7914', 'CISCO', 0, 28, 'S00105000400', 'loadInformation124', 1, NULL), ('7915', 'CISCO', 0, 24, 'B015-1-0-4', 'loadInformation227', 1, NULL), ('7915,7915', 'CISCO', 0, 48, 'B015-1-0-4', 'loadInformation228', 1, NULL), ('7916', 'CISCO', 0, 24, 'B015-1-0-4', 'loadInformation229', 1, NULL), " .
            "('7916,7916', 'CISCO', 0, 48, 'B016-1-0-4', 'loadInformation230', 1, NULL), ('7920', 'CISCO', 1, 1, 'cmterm_7920.4.0-03-02', 'loadInformation30002', 0, NULL), ('7921', 'CISCO', 1, 1, 'CP7921G-1.4.1SR1', 'loadInformation365', 0, NULL),('7925', 'CISCO', 1, 6, 'CP7925G-1.4.1SR1', 'loadInformation484', 0, NULL), ('7926', 'CISCO', 1, 1, 'CP7926G-1.4.1SR1', 'loadInformation557', 0, NULL), ('7931', 'CISCO', 1, 34, 'SCCP31.9-2-1S', 'loadInformation348', 0, NULL), ('7935', 'CISCO', 1, 2, 'P00503021900', 'loadInformation9', 0, NULL), ('7936', 'CISCO', 1, 1, 'cmterm_7936.3-3-21-0', 'loadInformation30019', 0, NULL), ('7937', 'CISCO', 1, 1, 'apps37sccp.1-4-4-0', 'loadInformation431', 0, 'SEP0000000000.cnf.xml_7937_template'), ('7940', 'CISCO', 1, 2, 'P0030801SR02', 'loadInformation8', 1, 'SEP0000000000.cnf.xml_796x_template'), " .
            "('7941', 'CISCO', 1, 2, 'SCCP41.9-2-1S', 'loadInformation115', 0, 'SEP0000000000.cnf.xml_796x_template'),('7941G-GE', 'CISCO', 1, 2, 'SCCP41.9-2-1S', 'loadInformation309', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7942', 'CISCO', 1, 2, 'SCCP42.9-2-1S', 'loadInformation434', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7945', 'CISCO', 1, 2, 'SCCP45.9-2-1S', 'loadInformation435', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7960', 'CISCO', 3, 6, 'P0030801SR02', 'loadInformation7', 1, 'SEP0000000000.cnf.xml_796x_template'), ('7961', 'CISCO', 3, 6, 'SCCP41.9-2-1S', 'loadInformation30018', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7961G-GE', 'CISCO', 3, 6, 'SCCP41.9-2-1S', 'loadInformation308', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7962', 'CISCO', 3, 6, 'SCCP42.9-2-1S', 'loadInformation404', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7965', 'CISCO', 3, 6, 'SCCP45.9-2-1S', 'loadInformation436', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7970', 'CISCO', 3, 8, 'SCCP70.9-2-1S', 'loadInformation30006', 0, NULL), ('7971', 'CISCO', 1, 2, 'SCCP75.9-2-1S', 'loadInformation119', 0, NULL), ('7975', 'CISCO', 3, 8, 'SCCP75.9-2-1S', 'loadInformation437', 0, NULL), ('7985', 'CISCO', 3, 8, 'cmterm_7985.4-1-7-0', 'loadInformation302', 0, NULL), ('8941', 'CISCO', 1, 0, 'SCCP894x.9-2-2-0', 'loadInformation586', 0, NULL), ('8945', 'CISCO', 1, 0, 'SCCP894x.9-2-2-0', 'loadInformation585', 0, NULL), ('ATA 186', 'CISCO', 1, 1, 'ATA030204SCCP090202A', 'loadInformation12', 0, NULL), ('ATA 187', 'CISCO', 1, 1, 'ATA187.9-2-3-1', 'loadInformation550', 0, NULL), ('CN622', 'MOTOROLA', 1, 1, '', 'loadInformation335', 0, NULL), ('Digital Access', 'CISCO', 1, 1, 'D001M022', 'loadInformation40', 0, NULL), ('Digital Access+', 'CISCO', 1, 1, 'D00303010033', 'loadInformation42', 0, NULL), ('E-Series', 'NOKIA', 1, 1, '', '', 0, NULL), ('ICC', 'NOKIA', 1, 1, '', '', 0, NULL), " .
            "('IP Communicator', 'CISCO', 1, 1, '', 'loadInformation30016', 0, NULL), ('Nokia E', 'Nokia', 0, 28, '', 'loadInformation275', 0, NULL), ('VGC Phone', 'CISCO', 1, 1, '', 'loadInformation10', 0, NULL), ('VGC Virtual', 'CISCO', 1, 1, '', 'loadInformation11', 0, NULL);";
    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Can not REPLACE defaults into sccpdevmodel table\n");
    }
    
    $sql = 'UPDATE `sccpdevice` set audio_tos="0xB8",audio_cos="6",video_tos="0x88",video_cos="5" where audio_tos=NULL or audio_tos="";';
    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Can not REPLACE defaults into sccpdevice table\n");
    }
    
    $sql = "DROP TRIGGER IF EXISTS trg_buttonconfig;
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
            END$$
            DELIMITER ;";
    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Can not modify sccpdevice table\n");
    }
    $sql_v3 = "CREATE OR REPLACE
            ALGORITHM = MERGE
            VIEW sccpdeviceconfig AS
            SELECT GROUP_CONCAT( CONCAT_WS( ',', buttonconfig.type, buttonconfig.name, buttonconfig.options )
            ORDER BY instance ASC
            SEPARATOR ';' ) AS button, sccpdevice.*
            FROM sccpdevice
            LEFT JOIN buttonconfig ON ( buttonconfig.device = sccpdevice.name )
            GROUP BY sccpdevice.name;";
    
    $sql_v0 = "CREATE OR REPLACE
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

    if ($sccp_ver == 11)  {
        $check = $db->query($sql_v3);
    } else {
        $check = $db->query($sql_v0);
    }
    if (DB::IsError($check)) {
        die_freepbx("Can not modify sccpdevice table\n");
    }
    
//    $ss->save_submit($request);
//    $ss->sccp_create_sccp_init();
//    $ss->sccp_db_save_setting();

//}