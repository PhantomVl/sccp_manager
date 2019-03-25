<?php
/* $Id:$ */

/* !TODO!: In an ideal world this should roll back everything the install.php script did, except for what existed before install.php was run */
/* !TODO!: This would require the install.php to make a note of all the actions that were skipped and/or performed */
/* !TODO!: Might be a good idea to create a backup of the database before removing anything */
// !TODO!: -TODO-: I remove only that which is related to the Manager, it is in my opinion not a critical configuration information. This information is partially present in other files.
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

    global $db;
    $version = FreePBX::Config()->get('ASTVERSION');

    out('Remove all SCCP tables');
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
            echo "Deleting key FROM kvstore..";
            sql("DELETE FROM kvstore WHERE module = 'sccpsettings'");
            sql("DELETE FROM kvstore WHERE module = 'Sccp_manager'");
        } 

/* Comment: Maybe save in sccpsettings, if the chan_sccp tables already existed in the database or if they were created by install.php */
/* So that you know if it is save to drop/delete them */

/*      DROP VIEW `sccpdeviceconfig`;
        DROP TABLE `sccpbuttonconfig`;
        DROP TABLE `sccpdevice`;
        DROP TABLE `sccpdevmodel`;
        DROP TABLE `sccpline`;
        DROP TABLE `sccpsettings`;
 */
   }

   echo "done<br>\n";

?>
