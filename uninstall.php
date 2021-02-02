<?php
/* $Id:$ */

/* !TODO!: In an ideal world this should roll back everything the install.php script did, except for what existed before install.php was run */
/* !TODO!: This would require the install.php to make a note of all the actions that were skipped and/or performed */
/* !TODO!: Might be a good idea to create a backup of the database before removing anything */
// !TODO!: -TODO-: I remove only that which is related to the Manager, it is in my opinion not a critical configuration information. This information is partially present in other files.
/*
function CreateBackUpConfig() {
    global $amp_conf;
    outn("<li>" . _("Create Config BackUp") . "</li>");
    $cnf_int = \FreePBX::Config();
    $backup_files = array('extconfig','extconfig','res_mysql', 'res_config_mysql','sccp');
    $backup_ext = array('_custom.conf', '.conf');
    $dir = $cnf_int->get('ASTETCDIR');
    $zip = new \ZipArchive();
    $filename = $dir . "/sccp_uninstall_backup" . date("Ymd"). ".zip";
    if ($zip->open($filename, \ZIPARCHIVE::CREATE)) {
        foreach ($backup_files as $file) {
            foreach ($backup_ext as $b_ext) {
                if (file_exists($dir . '/'.$file . $b_ext)) {
                    $zip->addFile($dir . '/'.$file . $b_ext);
                }
            }
        }
        $zip->close();
    } else {
        outn("<li>" . _("Error Create BackUp: ") . $filename ."</li>");
    }
    outn("<li>" . _("Create Config BackUp: ") . $filename ."</li>");
}
*/
if (!defined('FREEPBX_IS_AUTH')) {
    die('No direct script access allowed');
}

    global $db;
    $version = FreePBX::Config()->get('ASTVERSION');

    out('Removing all Sccp_manager tables');
    $tables = array('sccpdevmodel', 'sccpsettings');
foreach ($tables as $table) {
    $sql = "DROP TABLE IF EXISTS {$table}";
    $result = $db->query($sql);
    if (DB::IsError($result)) {
        die_freepbx($result->getDebugInfo());
    }
    unset($result);
}
if (!empty($version)) {
 // Woo, we have a version
    $check = $db->getRow("SELECT 1 FROM `kvstore` LIMIT 0", DB_FETCHMODE_ASSOC);
    if (!(DB::IsError($check))) {
        //print_r("none, creating table :". $value);
        echo "Deleting keys FROM kvstore..";
        sql("DELETE FROM kvstore WHERE module = 'sccpsettings'");
        sql("DELETE FROM kvstore WHERE module = 'Sccp_manager'");
    }

/* Comment: Maybe save in sccpsettings, if the chan_sccp tables already existed in the database or if they were created by install.php */
/* So that you know if it is safe to drop/delete them */

/*      DROP VIEW  IF EXISTS`sccpdeviceconfig`;
    DROP TABLE IF EXISTS `sccpbuttonconfig`;
    DROP TABLE IF EXISTS `sccpdevice`;
    DROP TABLE IF EXISTS `sccpdevmodel`;
    DROP TABLE IF EXISTS `sccpline`;
    DROP TABLE IF EXISTS `sccpsettings`;
    DROP TABLE IF EXISTS `sccpuser`;
 *
 */
}

   echo "done<br>\n";
