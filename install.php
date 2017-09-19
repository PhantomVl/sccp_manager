<?php

if (!defined('FREEPBX_IS_AUTH')) {
    die('No direct script access allowed');
}

global $db;
global $amp_conf;
global $version;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT" : "AUTO_INCREMENT";

$table_req = array('sccpdevice', 'sccpline', 'buttonconfig', 'sccpdeviceconfig');

$sql = <<< END
	CREATE TABLE IF NOT EXISTS `sccpsettings` (
		`keyword` VARCHAR (50) NOT NULL default '',
		`data`    VARCHAR (255) NOT NULL default '',
		`seq`     TINYINT (1),
		`type`    TINYINT (1) NOT NULL default '0',
		PRIMARY KEY (`keyword`,`seq`,`type`)
	)
END;

$ss = FreePBX::create()->Sccp_manager;

outn(_("checking for requery Sccp_manager table.."));
foreach ($table_req as $value) {
    $check = $db->getRow("SELECT 1 FROM `$value` LIMIT 0", DB_FETCHMODE_ASSOC);
    if (DB::IsError($check)) {
//         print_r("none, creating table :". $value);
        out(_("none, Can't fient table: " . $value));
        out(_("none, Plz. Open chai-sccp/conf  directory to create DB scheme"));
        die(_("none, creating table: " . $value));
    }
}


$version = FreePBX::Config()->get('ASTVERSION');
outn(_("checking Version : ").$version);

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

    out(_("none, creating table"));
    $check = $db->query($sql);
//    sql($sql);
    if (db::IsError($check)) {
        die_freepbx("Can not create sccpdevmodel table\n");
    }

    $sql = "CREATE TABLE IF NOT EXISTS `sccpdevmodel` (
    `model` varchar(20) NOT NULL DEFAULT '',
    `vendor` varchar(40) DEFAULT '',
    `dns` int(2) DEFAULT '1',
    `buttons` int(2) DEFAULT '0',
    `loadimage` varchar(40) DEFAULT '',
    `loadinformationid` VARCHAR(30) NULL DEFAULT NULL,
    `enabled` INT(2) NULL DEFAULT '0',
    `nametemplet` VARCHAR(50) NULL DEFAULT NULL,
    PRIMARY KEY (`model`),
    KEY `model` (`model`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1";

    $check = $db->query($sql);
    if (db::IsError($check)) {
        die_freepbx("Can not create sccpsettings table\n");
    }
    if ($db->getAll('SHOW COLUMNS FROM sccpdevice WHERE FIELD = "pickupexten"')) {
        out(_("none, modify table from old scheme"));
        $sql = "ALTER TABLE `sccpdevice`
    	    CHANGE COLUMN `pickupexten` `directed_pickup` VARCHAR(5) NULL DEFAULT 'yes',
	    CHANGE COLUMN `pickupcontext` `directed_pickup_context` VARCHAR(100) NULL DEFAULT '' ,
	    CHANGE COLUMN `pickupmodeanswer` `directed_pickup_modeanswer` VARCHAR(5) NULL DEFAULT 'yes'";
        $check = $db->query($sql);
        if (DB::IsError($check)) {
            die_freepbx("Can not add loadinformationid into sccpdevmodel table\n");
        }
    }

    
    if (!$db->getAll('SHOW COLUMNS FROM sccpdevmodel WHERE FIELD = "loadinformationid"')) {
        out(_("none, modify table from old scheme"));
        $sql = "ALTER TABLE `sccpdevmodel` ADD `loadinformationid` varchar(30);";
        $check = $db->query($sql);
        if (DB::IsError($check)) {
            die_freepbx("Can not add loadinformationid into sccpdevmodel table\n");
        }
    }
    if (!$db->getAll('SHOW COLUMNS FROM sccpdevmodel WHERE FIELD = "nametemplet"')) {
        out(_("none, modify table from old scheme"));
        $sql = "ALTER TABLE `sccpdevmodel` ADD COLUMN `enabled` INT(2) NULL DEFAULT '0', ADD COLUMN `nametemplet` VARCHAR(50) NULL DEFAULT NULL,";
        $check = $db->query($sql);
        if (DB::IsError($check)) {
            die_freepbx("Can not add loadinformationid into sccpdevmodel table\n");
        }
    }
    if (!$db->getAll('SHOW COLUMNS FROM sccpdevice WHERE FIELD = "hwlang"')) {
        out(_("none, modify table from old scheme"));
        $sql = "ALTER TABLE `sccpdevice` ADD COLUMN `hwlang` varchar(12) NULL DEFAULT NULL,
                ADD COLUMN `useRedialMenu` VARCHAR(5) NULL DEFAULT 'no' AFTER `hwlang`
                ";
        $check = $db->query($sql);
        if (DB::IsError($check)) {
            die_freepbx("Can not add loadinformationid into sccpdevmodel table\n");
        }
    }
    if (!$db->getAll('SHOW COLUMNS FROM sccpdevice WHERE FIELD = "dtmfmode"')) {
        out(_("none, modify table from old scheme"));
        $sql = "ALTER TABLE `sccpdevice` ADD COLUMN `dtmfmode` varchar(10) default NULL";
        $check = $db->query($sql);
        if (DB::IsError($check)) {
            die_freepbx("Can not add loadinformationid into sccpdevmodel table\n");
        }
    }
    if (!$db->getAll('SHOW COLUMNS FROM sccpline WHERE FIELD = "adhocNumber"')) {
        out(_("none, modify sccpline table from old scheme"));
        $sql = "ALTER TABLE `sccpline`
                ADD COLUMN `namedcallgroup` VARCHAR(45) NULL DEFAULT NULL AFTER `setvar`,
                ADD COLUMN `namedpickupgroup` VARCHAR(45) NULL DEFAULT NULL AFTER `namedcallgroup`,
                ADD COLUMN `adhocNumber` VARCHAR(45) NULL DEFAULT NULL AFTER `namedpickupgroup`,
                ADD COLUMN `meetme` VARCHAR(5) NULL DEFAULT NULL AFTER `adhocNumber`,
                ADD COLUMN `meetmenum` VARCHAR(45) NULL DEFAULT NULL AFTER `meetme`,
                ADD COLUMN `meetmeopts` VARCHAR(45) NULL DEFAULT NULL AFTER `meetmenum`,
                ADD COLUMN `regexten` VARCHAR(45) NULL DEFAULT NULL AFTER `meetmeopts`;";
        $check = $db->query($sql);
        if (DB::IsError($check)) {
            die_freepbx("Can not add loadinformationid into sccpdevmodel table\n");
        }
    }


    out(_("none, Uptade Table Info"));

//$sql = "REPLACE INTO `sccpdevmodel` VALUES ('7925','CISCO',1,1,'',''),('7902','CISCO',1,1,'CP7902080002SCCP060817A','loadInformation30008'),('7905','CISCO',1,1,'CP7905080003SCCP070409A','loadInformation20000'),('7906','CISCO',1,1,'SCCP11.8-3-1S','loadInformation369'),('7910','CISCO',1,1,'P00405000700','loadInformation6'),('7911','CISCO',1,1,'SCCP11.8-3-1S','loadInformation307'),('7912','CISCO',1,1,'CP7912080003SCCP070409A','loadInformation30007'),('7914','CISCO',0,14,'S00105000300','loadInformation124'),('7920','CISCO',1,1,'cmterm_7920.4.0-03-02','loadInformation30002'),('7921','CISCO',1,1,'CP7921G-1.0.3','loadInformation365'),('7931','CISCO',1,1,'SCCP31.8-3-1S','loadInformation348'),('7936','CISCO',1,1,'cmterm_7936.3-3-13-0','loadInformation30019'),('7937','CISCO',1,1,'','loadInformation431'),('7940','CISCO',1,2,'P00308000500','loadInformation8'),('Digital Access+','CISCO',1,1,'D00303010033','loadInformation42'),('7941','CISCO',1,2,'P00308000500','loadInformation115'),('7941G-GE','CISCO',1,2,'P00308000500','loadInformation309'),('7942','CISCO',1,2,'P00308000500','loadInformation434'),('Digital Access','CISCO',1,1,'D001M022','loadInformation40'),('7945','CISCO',1,2,'P00308000500','loadInformation435'),('7960','CISCO',3,6,'P00308000500','loadInformation7'),('7961','CISCO',3,6,'P00308000500','loadInformation30018'),('7961G-GE','CISCO',3,6,'P00308000500','loadInformation308'),('7962','CISCO',3,6,'P00308000500','loadInformation404'),('7965','CISCO',3,6,'P00308000500','loadInformation436'),('7970','CISCO',3,8,'SCCP70.8-3-1S','loadInformation30006'),('7971','CISCO',3,8,'SCCP70.8-3-1S','loadInformation119'),('7975','CISCO',3,8,'SCCP70.8-3-1S','loadInformation437'),('7985','CISCO',3,8,'cmterm_7985.4-1-4-0','loadInformation302'),('ATA 186','CISCO',1,1,'ATA030203SCCP051201A','loadInformation12'),('IP Communicator','CISCO',1,1,'','loadInformation30016'),('12 SP','CISCO',1,1,'','loadInformation3'),('12 SP+','CISCO',1,1,'','loadInformation2'),('30 SP+','CISCO',1,1,'','loadInformation1'),('30 VIP','CISCO',1,1,'','loadInformation5'),('7914,7914','CISCO',0,28,'S00105000300','loadInformation124'),('7915','CISCO',0,14,'',''),('7916','CISCO',0,14,'',''),('7915,7915','CISCO',0,28,'',''),('7916,7916','CISCO',0,28,'',''),('CN622','MOTOROLA',1,1,'','loadInformation335'),('ICC','NOKIA',1,1,'',''),('E-Series','NOKIA',1,1,'',''),('3911','CISCO',1,1,'','loadInformation446'),('3951','CISCO',1,1,'','loadInformation412');";
    $sql = "REPLACE INTO `sccpdevmodel` (`model`, `vendor`, `dns`, `buttons`, `loadimage`, `loadinformationid`, `enabled`, `nametemplet`) VALUES ('12 SP', 'CISCO', 1, 1, '', 'loadInformation3', 0, NULL)," .
            "('12 SP+', 'CISCO', 1, 1, '', 'loadInformation2', 0, NULL), ('30 SP+', 'CISCO', 1, 1, '', 'loadInformation1', 0, NULL), ('30 VIP', 'CISCO', 1, 1, '', 'loadInformation5', 0, NULL), ('3911', 'CISCO', 1, 1, '', 'loadInformation446', 0, NULL), ('3951', 'CISCO', 1, 1, '', 'loadInformation412', 0, ''), ('6901', 'CISCO', 1, 0, 'SCCP6901.9-2-1-a', 'loadInformation547', 0, NULL), ('6911', 'CISCO', 1, 0, 'SCCP6911.9-2-1-a', 'loadInformation548', 0, NULL), ('6921', 'CISCO', 1, 0, 'SCCP69xx.9-2-1-0', 'loadInformation496', 0, NULL), ('6941', 'CISCO', 1, 1, 'SCCP69xx.9-2-1-0', 'loadInformation495', 0, NULL), ('6945', 'CISCO', 1, 0, 'SCCP6945.9-2-1-0', 'loadInformation564', 0, NULL), ('6961', 'CISCO', 1, 0, 'SCCP69xx.9-2-1-0', 'loadInformation497', 0, NULL), ('7902', 'CISCO', 1, 1, 'CP7902080002SCCP060817A', 'loadInformation30008', 0, NULL), " .
            "('7905', 'CISCO', 1, 1, 'CP7905080003SCCP070409A', 'loadInformation20000', 0, NULL), ('7906', 'CISCO', 1, 1, 'SCCP11.9-2-1S', 'loadInformation369', 1, 'SEP0000000000.cnf.xml_791x_template'), ('7910', 'CISCO', 1, 1, 'SCCP11.9-2-1S', 'loadInformation6', 1, 'SEP0000000000.cnf.xml_791x_template'), ('7911', 'CISCO', 1, 1, 'SCCP11.9-2-1S', 'loadInformation307', 1, 'SEP0000000000.cnf.xml_791x_template'), ('7912', 'CISCO', 1, 1, 'CP7912080004SCCP080108A', 'loadInformation30007', 0, NULL), ('7914', 'CISCO', 0, 14, 'S00105000400', 'loadInformation124', 1, NULL),('7914,7914', 'CISCO', 0, 28, 'S00105000400', 'loadInformation124', 1, NULL), ('7915', 'CISCO', 0, 24, 'B015-1-0-4', 'loadInformation227', 1, NULL), ('7915,7915', 'CISCO', 0, 48, 'B015-1-0-4', 'loadInformation228', 1, NULL), ('7916', 'CISCO', 0, 24, 'B015-1-0-4', 'loadInformation229', 1, NULL), " .
            "('7916,7916', 'CISCO', 0, 48, 'B016-1-0-4', 'loadInformation230', 1, NULL), ('7920', 'CISCO', 1, 1, 'cmterm_7920.4.0-03-02', 'loadInformation30002', 0, NULL), ('7921', 'CISCO', 1, 1, 'CP7921G-1.4.1SR1', 'loadInformation365', 0, NULL),('7925', 'CISCO', 1, 6, 'CP7925G-1.4.1SR1', 'loadInformation484', 0, NULL), ('7926', 'CISCO', 1, 1, 'CP7926G-1.4.1SR1', 'loadInformation557', 0, NULL), ('7931', 'CISCO', 1, 34, 'SCCP31.9-2-1S', 'loadInformation348', 0, NULL), ('7935', 'CISCO', 1, 2, 'P00503021900', 'loadInformation9', 0, NULL), ('7936', 'CISCO', 1, 1, 'cmterm_7936.3-3-21-0', 'loadInformation30019', 0, NULL), ('7937', 'CISCO', 1, 1, 'apps37sccp.1-4-4-0', 'loadInformation431', 0, 'SEP0000000000.cnf.xml_7937_template'), ('7940', 'CISCO', 1, 2, 'P0030801SR02', 'loadInformation8', 1, 'SEP0000000000.cnf.xml_796x_template'), " .
            "('7941', 'CISCO', 1, 2, 'SCCP41.9-2-1S', 'loadInformation115', 0, 'SEP0000000000.cnf.xml_796x_template'),('7941G-GE', 'CISCO', 1, 2, 'SCCP41.9-2-1S', 'loadInformation309', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7942', 'CISCO', 1, 2, 'SCCP42.9-2-1S', 'loadInformation434', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7945', 'CISCO', 1, 2, 'SCCP45.9-2-1S', 'loadInformation435', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7960', 'CISCO', 3, 6, 'P0030801SR02', 'loadInformation7', 1, 'SEP0000000000.cnf.xml_796x_template'), ('7961', 'CISCO', 3, 6, 'SCCP41.9-2-1S', 'loadInformation30018', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7961G-GE', 'CISCO', 3, 6, 'SCCP41.9-2-1S', 'loadInformation308', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7962', 'CISCO', 3, 6, 'SCCP42.9-2-1S', 'loadInformation404', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7965', 'CISCO', 3, 6, 'SCCP45.9-2-1S', 'loadInformation436', 0, 'SEP0000000000.cnf.xml_796x_template'), ('7970', 'CISCO', 3, 8, 'SCCP70.9-2-1S', 'loadInformation30006', 0, NULL), ('7971', 'CISCO', 1, 2, 'SCCP75.9-2-1S', 'loadInformation119', 0, NULL), ('7975', 'CISCO', 3, 8, 'SCCP75.9-2-1S', 'loadInformation437', 0, NULL), ('7985', 'CISCO', 3, 8, 'cmterm_7985.4-1-7-0', 'loadInformation302', 0, NULL), ('8941', 'CISCO', 1, 0, 'SCCP894x.9-2-2-0', 'loadInformation586', 0, NULL), ('8945', 'CISCO', 1, 0, 'SCCP894x.9-2-2-0', 'loadInformation585', 0, NULL), ('ATA 186', 'CISCO', 1, 1, 'ATA030204SCCP090202A', 'loadInformation12', 0, NULL), ('ATA 187', 'CISCO', 1, 1, 'ATA187.9-2-3-1', 'loadInformation550', 0, NULL), ('CN622', 'MOTOROLA', 1, 1, '', 'loadInformation335', 0, NULL), ('Digital Access', 'CISCO', 1, 1, 'D001M022', 'loadInformation40', 0, NULL), ('Digital Access+', 'CISCO', 1, 1, 'D00303010033', 'loadInformation42', 0, NULL), ('E-Series', 'NOKIA', 1, 1, '', '', 0, NULL), ('ICC', 'NOKIA', 1, 1, '', '', 0, NULL), " .
            "('IP Communicator', 'CISCO', 1, 1, '', 'loadInformation30016', 0, NULL), ('Nokia E', 'Nokia', 0, 28, '', 'loadInformation275', 0, NULL), ('VGC Phone', 'CISCO', 1, 1, '', 'loadInformation10', 0, NULL), ('VGC Virtual', 'CISCO', 1, 1, '', 'loadInformation11', 0, NULL);";
    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Can not REPLACE defaults into sccpdevmodel table\n");
    }

    $sql = "ALTER TABLE sccpline
	 ALTER COLUMN incominglimit SET DEFAULT '2',
	 ALTER COLUMN transfer SET DEFAULT 'on',
	 ALTER COLUMN vmnum SET DEFAULT '*97',
	 ALTER COLUMN musicclass SET DEFAULT 'default',
	 ALTER COLUMN echocancel SET DEFAULT 'on',
	 ALTER COLUMN silencesuppression SET DEFAULT 'off',
	 CHANGE COLUMN `dnd` `dnd` VARCHAR(12) NULL DEFAULT 'off'
    ";

    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Can not modify sccpline table\n");
    }

    $sql = "ALTER TABLE sccpdevice
	ALTER COLUMN transfer SET DEFAULT 'on',
	ALTER COLUMN cfwdall SET DEFAULT 'on',
	ALTER COLUMN cfwdbusy SET DEFAULT 'on',
	ALTER COLUMN dtmfmode SET DEFAULT 'outofband',
	ALTER COLUMN dndFeature SET DEFAULT 'on',
	ALTER COLUMN directrtp SET DEFAULT 'off',
	ALTER COLUMN earlyrtp SET DEFAULT 'progress',
	ALTER COLUMN mwilamp SET DEFAULT 'on',
	ALTER COLUMN mwioncall SET DEFAULT 'on',
	ALTER COLUMN private SET DEFAULT 'on',
	ALTER COLUMN privacy SET DEFAULT 'off',
	ALTER COLUMN nat SET DEFAULT 'off',
	ALTER COLUMN softkeyset SET DEFAULT 'softkeyset'
    ";
    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Can not modify sccpdevice table\n");
    }
    $sql = "DROP TRIGGER IF EXISTS trg_buttonconfig;
            DELIMITER $$
            CREATE TRIGGER trg_buttonconfig BEFORE INSERT ON buttonconfig
            FOR EACH ROW
            BEGIN
            IF NEW.`type` = 'line' THEN
                IF (SELECT COUNT(*) FROM `sccpline` WHERE `sccpline`.`name` = SUBSTRING_INDEX(NEW.`name`,'!',1)) = 0
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
    $sql = "CREATE OR REPLACE
            ALGORITHM = MERGE
            VIEW sccpdeviceconfig AS
            SELECT GROUP_CONCAT( CONCAT_WS( ',', buttonconfig.type, buttonconfig.name, buttonconfig.options )
            ORDER BY instance ASC
            SEPARATOR ';' ) AS button, sccpdevice.*
            FROM sccpdevice
            LEFT JOIN buttonconfig ON ( buttonconfig.device = sccpdevice.name )
            GROUP BY sccpdevice.name;";
    
    $sql = "CREATE OR REPLACE
            ALGORITHM = MERGE
            VIEW sccpdeviceconfig AS
            SELECT GROUP_CONCAT( CONCAT_WS( ',', buttonconfig.type, buttonconfig.name, buttonconfig.options )
            ORDER BY instance ASC
            SEPARATOR ';' ) AS button,
            `sccpdevice`.`type` AS `type`,`sccpdevice`.`addon` AS `addon`,`sccpdevice`.`description` AS `description`,`sccpdevice`.`tzoffset` AS `tzoffset`,
            `sccpdevice`.`transfer` AS `transfer`,`sccpdevice`.`cfwdall` AS `cfwdall`,`sccpdevice`.`cfwdbusy` AS `cfwdbusy`,`sccpdevice`.`imageversion` AS `imageversion`,
            `sccpdevice`.`deny` AS `deny`,`sccpdevice`.`permit` AS `permit`,`sccpdevice`.`dndFeature` AS `dndFeature`,`sccpdevice`.`directrtp` AS `directrtp`,
            `sccpdevice`.`earlyrtp` AS `earlyrtp`,`sccpdevice`.`mwilamp` AS `mwilamp`,`sccpdevice`.`mwioncall` AS `mwioncall`,`sccpdevice`.`directed_pickup` AS `directed_pickup`,
            `sccpdevice`.`directed_pickup_context` AS `directed_pickup_context`,`sccpdevice`.`directed_pickup_modeanswer` AS `directed_pickup_modeanswer`,
            `sccpdevice`.`private` AS `private`,`sccpdevice`.`privacy` AS `privacy`,`sccpdevice`.`nat` AS `nat`,`sccpdevice`.`softkeyset` AS `softkeyset`,
            `sccpdevice`.`audio_tos` AS `audio_tos`,`sccpdevice`.`audio_cos` AS `audio_cos`,`sccpdevice`.`video_tos` AS `video_tos`,`sccpdevice`.`video_cos` AS `video_cos`,
            `sccpdevice`.`conf_allow` AS `conf_allow`,`sccpdevice`.`conf_play_general_announce` AS `conf_play_general_announce`,
            `sccpdevice`.`conf_play_part_announce` AS `conf_play_part_announce`,`sccpdevice`.`conf_mute_on_entry` AS `conf_mute_on_entry`,
            `sccpdevice`.`conf_music_on_hold_class` AS `conf_music_on_hold_class`,`sccpdevice`.`conf_show_conflist` AS `conf_show_conflist`,
            `sccpdevice`.`setvar` AS `setvar`,`sccpdevice`.`disallow` AS `disallow`,`sccpdevice`.`allow` AS `allow`,`sccpdevice`.`backgroundImage` AS `backgroundImage`,
            `sccpdevice`.`ringtone` AS `ringtone`,`sccpdevice`.`name` AS `name`,`sccpdevice`.`dtmfmode` AS `dtmfmode`,`sccpdevice`.`useRedialMenu` AS `useRedialMenu`
            FROM sccpdevice
            LEFT JOIN buttonconfig ON ( buttonconfig.device = sccpdevice.name )
            GROUP BY sccpdevice.name;";

    $check = $db->query($sql);
    if (DB::IsError($check)) {
        die_freepbx("Can not modify sccpdevice table\n");
    }

    
//    $ss->save_submit($request);
//    $ss->sccp_create_sccp_init();
//    $ss->sccp_db_save_setting();

//}