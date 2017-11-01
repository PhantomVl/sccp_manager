<?php
/* $Id:$ */
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

    global $db;
    $version = FreePBX::Config()->get('ASTVERSION');

    echo "dropping table sccpdevmodel..";
    sql("DROP TABLE IF EXISTS `sccpdevmodel`");
    echo "dropping table sccpsettings..";
    sql("DROP TABLE IF EXISTS `sccpsettings`");
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
        DROP TABLE `buttonconfig`;
        DROP TABLE `sccpdevice`;
        DROP TABLE `sccpdevmodel`;
        DROP TABLE `sccpline`;
        DROP TABLE `sccpsettings`;
 */
   }

   echo "done<br>\n";

?>
