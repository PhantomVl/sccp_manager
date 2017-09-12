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
      if (version_compare($version, "14.0.0", "<=")) {
        echo "Deleting key FROM kvstore..";
        sql("DELETE FROM kvstore WHERE module = 'sccpsettings'");
        sql("DELETE FROM kvstore WHERE module = 'Sccp_manager'");
      }
/*      DROP VIEW `sccpdeviceconfig`;
        DROP TABLE `buttonconfig`;
        DROP TABLE `sccpdevice`;
        DROP TABLE `sccpdevmodel`;
        DROP TABLE `sccpline`;
        DROP TABLE `sccpsettings`;
 * 
 */
   }

   echo "done<br>\n";

?>
