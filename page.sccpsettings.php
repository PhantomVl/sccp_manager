<?php /* $Id:$ */
if (!defined('FREEPBX_IS_AUTH')) {
    die('No direct script access allowed');
}
//  License for all code of this FreePBX module can be found in the license file inside the module directory
//  Copyright 2015 Sangoma Technologies.
// vim: set ai ts=4 sw=4 ft=php:

// SccpSettings page. Re-written for usage with chan_sccp
// AGPL v3 Licened

//
$spage = FreePBX::create()->Sccp_manager;

$display_page = $spage->myShowPage();
$display_info = _("SCCP Server Settings");

include('page.html.php');
?>

<!-- End Modal alerts-->
