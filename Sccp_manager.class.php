<?php

//namespace FreePBX\modules;
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
// https://github.com/chan-sccp/chan-sccp/wiki/Setup-FreePBX
// http://chan-sccp-b.sourceforge.net/doc/setup_sccp.xhtml
// https://github.com/chan-sccp/chan-sccp/wiki/Conferencing
// https://github.com/chan-sccp/chan-sccp/wiki/Frequently-Asked-Questions
// http://chan-sccp-b.sourceforge.net/doc/_howto.xhtml#nf_adhoc_plar
// https://www.cisco.com/c/en/us/td/docs/voice_ip_comm/cuipph/all_models/xsi/9-1-1/CUIP_BK_P82B3B16_00_phones-services-application-development-notes/CUIP_BK_P82B3B16_00_phones-services-application-development-notes_chapter_011.html
// https://www.cisco.com/c/en/us/td/docs/voice_ip_comm/cuipph/7960g_7940g/sip/4_4/english/administration/guide/ver4_4/sipins44.html
// http://usecallmanager.nz/
/* !TODO!: 
 *  + Cisco Format Mac 
 *  + Model Information 
 *  + Device Right Menu  
  <!-- Dial Templates are not really needed for skinny, skinny get's direct feed back from asterisk per digit -->
  <!-- If your dialplan is finite (completely fixed length (depends on your country dialplan) dialplan, then dial templates are not required) -->
  <!-- As far as i know FreePBX does also attempt to build a finite dialplan -->
  <!-- Having to maintain both an asterisk dialplan and these skinny dial templates is annoying -->

 *  + Dial Templates + Configuration 
 *  + Dial Templates in Global Configuration ( Enabled / Disabled ; default template )
 *  ? Dial Templates - Howto IT Include in XML.Config ???????
 *  - Dial Templates in device Configuration ( Enabled / inheret / Disabled ; template )

 *  - WiFi Config (Bulk Deployment Utility for Cisco 7921, 7925, 7926)?????
 *  + Change internal use Field to _Field (new feature in chan_sccp (added for Sccp_manager))
 *  + Delete phone XML
 *  + Change Installer  ?? (test )
 *  + Installer  Realtime config update
 *  + Installer  Adaptive DB reconfig.
 *  + Add system info page 
 *  + Change Cisco Language data
 *  + Make DB Acces from separate class
 *  + Make System Acces from separate class
 *  + Make Var elements from separate class
 *  + To make creating XML files in a separate class
 *  + Add Switch to select XML schema (display)
 *  + SRST Config
 *  + secondary_dialtone_digits = ""     line config
 *  + secondary_dialtone_tone = 0x22     line config                                              
 *  - deviceSecurityMode http://usecallmanager.nz//itl-file-tlv.html
 *  - transportLayerProtocol http://usecallmanager.nz//itl-file-tlv.html
 *  - Check Time zone .... 
 *  - Failover config
 *  + Auto Addons!
 *  + DND Mode
 *  - support kv-store ?????
 *  + Shared Line 
 *  - bug Soft key set (empty keysets )
 *  - bug Fix ...(K no w bug? no fix)
 *  - restore default Value on page 
 *  - restore default Value on sccp.class
 *  -  'Device SEP ID.[XXXXXXXXXXXX]=MAC'
 *  +  ATA's start with       ATAXXXXXXXXXXXX. 
 *  + Create ATADefault.cnf.xml
 *  - Create Second line Use MAC AABBCCDDEEFF rotation MAC BBCCDDEEFF01 (ATA 187 )
 *  +  Add SEP, ATA, VG prefix.
 *  -  VG248 ports start with VGXXXXXXXXXXXX0. 
 *  * I think this file should be split in 3 parts (as in Model-View-Controller(MVC))
 *    * XML/Database Parts -> Model directory
 *    * Processing parts -> Controller directory
 *    * Ajax Handler Parts -> Controller directory
 *    * Result parts -> View directory
 *  + Support TFTP rewrite :
 *     + dir "settings"
 *     + dir "templates"
 *     + dir "firmware"
 *     + dir "locales"
 *  + Create Simple User Interface 
 *       + sccpsimple.xml
 *  + Add error information on the server information page (critical display error - the system can not work correctly)
 *  - Add Warning Information on Server Info Page 
 *
 */

namespace FreePBX\modules;

class Sccp_manager extends \FreePBX_Helpers implements \BMO {
    /* Field Values for type  seq */

//	const General - sccp.conf            = '0';
//	const General - sccp.conf[general]   = '0';
//	const General - sccp.conf[%keyset%]  = '5';  NAME space 
//	const General - sccp.conf[%keyset%]  = '6';  data
//	const General - default.xml          = '10';
//	const General - templet.xml          = '20';
//	const General - system_path          = '2';
//	const General - don't store          = '99';
//    private $SCCP_LANG_DICTIONARY = 'SCCP-dictionary.xml'; // CISCO LANG file search in /tftp-path 
    private $SCCP_LANG_DICTIONARY = 'be-sccp.jar'; // CISCO LANG file search in /tftp-path 
    private $pagedata = null;
    private $sccp_driver_ver = '11.3';             // Ver fore SCCP.CLASS.PHP 
    public $sccp_manager_ver = '13.0.0.4(M)';
    private $tftpLang = array();
//    private $hint_context = '@ext-local'; /// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! Get it from Config !!!
    private $hint_context = array('default' => '@ext-local'); /// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! Get it from Config !!!
    private $val_null = 'NONE'; /// REPLACE to null Field
    public $sccp_model_list = array();
    public $sccp_metainfo = array();
    private $cnf_wr = null;
    public $sccppath = array();
    public $sccpvalues = array();
    public $sccp_conf_init = array();
    public $xml_data;
    public $class_error; //error construct
    public $info_warning;

    public function __construct($freepbx = null) {
        if ($freepbx == null) {
            throw new Exception("Not given a FreePBX Object");
        }
        $this->class_error = array();
        $this->FreePBX = $freepbx;
        $this->db = $freepbx->Database;
        $this->cnf_wr = \FreePBX::WriteConfig();
        $this->cnf_read = \FreePBX::LoadConfig();
//        $this->v = new \Respect\Validation\Validator();
        $driverNamespace = "\\FreePBX\\Modules\\Sccp_manager";
        if (class_exists($driverNamespace, false)) {
            foreach (glob(__DIR__ . "/Sccp_manager.inc/*.class.php") as $driver) {
                if (preg_match("/\/([a-z1-9]*)\.class\.php$/i", $driver, $matches)) {
                    $name = $matches[1];
                    $class = $driverNamespace . "\\" . $name;
                    if (!class_exists($class, false)) {
                        include($driver);
                    }
                    if (class_exists($class, false)) {
                        $this->$name = new $class($this);
                    } else {
                        throw new \Exception("Invalid Class inside in the include folder" . print_r($freepbx));
                    }
                }
            }
        } else {
            return;
        }

        $this->getSccpSettingFromDB(); // Overwrite Exist 
//        $this->getSccpSetingINI(false); // get from sccep.ini
        $this->init_sccp_path();
        $this->initVarfromDefs();
        $this->initTftpLang();

        if (!empty($this->sccpvalues['SccpDBmodel'])) {
            if ($this->sccpvalues['sccp_compatible']['data'] > $this->sccpvalues['SccpDBmodel']['data']) {
                $this->sccpvalues['sccp_compatible']['data'] = $this->sccpvalues['SccpDBmodel']['data'];
            }
        }
        // Load Advanced Form Constuctor Data 
        if (empty($this->sccpvalues['displayconfig'])) {
            $xml_vars = __DIR__ . '/conf/sccpgeneral.xml.v' . $this->sccpvalues['sccp_compatible']['data'];
        } else {
            $xml_vars = __DIR__ . '/conf/' . $this->sccpvalues['displayconfig']['data'] . '.xml.v' . $this->sccpvalues['sccp_compatible']['data'];
        }
        if (!file_exists($xml_vars)) {
            $xml_vars = __DIR__ . '/conf/sccpgeneral.xml';
        }
        if (file_exists($xml_vars)) {
            $this->xml_data = simplexml_load_file($xml_vars);
            $this->initVarfromXml(); // Overwrite Exist
        }
    }

    /*
     *   Generate Input elements in Html Code from sccpgeneral.xml
     */

    public function ShowGroup($grup_name, $heder_show, $form_prefix = 'sccp', $form_values = null) {
        $htmlret = "";
        if (empty($form_values)) {
            $form_values = $this->sccpvalues;
        }
        if ((array) $this->xml_data) {
            foreach ($this->xml_data->xpath('//page_group[@name="' . $grup_name . '"]') as $item) {
                $htmlret .= load_view(__DIR__ . '/views/formShow.php', array(
                    'itm' => $item, 'h_show' => $heder_show,
                    'form_prefix' => $form_prefix, 'fvalues' => $form_values,
                    'tftp_lang' => $this->getTftpLang(), 'metainfo' => $this->sccp_metainfo)
                );
            }
        } else {
            $htmlret .= load_view(__DIR__ . '/views/formShowError.php');
        }
        return $htmlret;
    }

    /*
     *    Load config vars from base array
     */

    public function initVarfromDefs() {
        foreach ($this->extconfigs->getextConfig('sccpDefaults') as $key => $value) {
            if (empty($this->sccpvalues[$key])) {
                $this->sccpvalues[$key] = array('keyword' => $key, 'data' => $value, 'type' => '0', 'seq' => '0');
            }
        }
    }

    /*
     *    Load config vars from xml
     */

    public function initVarfromXml() {
        if ((array) $this->xml_data) {
            foreach ($this->xml_data->xpath('//page_group') as $item) {
                foreach ($item->children() as $child) {
                    $seq = 0;
                    if (!empty($child['seq'])) {
                        $seq = (string) $child['seq'];
                    }
                    if ($seq < 99) {

                        if ($child['type'] == 'IE') {
                            foreach ($child->xpath('input') as $value) {
                                $tp = 0;
                                if (empty($value->value)) {
                                    $datav = (string) $value->default;
                                } else {
                                    $datav = (string) $value->value;
                                }
                                if (strtolower($value->type) == 'number')
                                    $tp = 1;
                                if (empty($this->sccpvalues[(string) $value->name])) {
                                    $this->sccpvalues[(string) $value->name] = array('keyword' => (string) $value->name, 'data' => $datav, 'type' => $tp, 'seq' => $seq);
//                              $this->sccpvalues[] = array('keyword' => (string)$value->name, 'data' =>(string)$value->default, 'type'=> '0');
                                }
                            }
                        }
                        if ($child['type'] == 'IS' || $child['type'] == 'IED') {
                            if (empty($child->value)) {
                                $datav = (string) $child->default;
                            } else {
                                $datav = (string) $child->value;
                            }
                            if (empty($this->sccpvalues[(string) $child->name])) {
                                $this->sccpvalues[(string) $child->name] = array('keyword' => (string) $child->name, 'data' => $datav, 'type' => '2', 'seq' => $seq);
//                              $this->sccpvalues[] = array('keyword' => (string)$child->name, 'data' =>(string)$child-> default,'type'=>'0');
                            }
                        }
//                        if ($child['type'] == 'SLD' || $child['type'] == 'SLS' || $child['type'] == 'SLT' || $child['type'] == 'SL' || $child['type'] == 'SLM' || $child['type'] == 'SLZ' || $child['type'] == 'SLZN' || $child['type'] == 'SLA') {
                          if (in_array($child['type'], array('SLD','SLS','SLT','SL','SLM','SLZ','SLZN','SLA') )) {
                            if (empty($child->value)) {
                                $datav = (string) $child->default;
                            } else {
                                $datav = (string) $child->value;
                            }
                            if (empty($this->sccpvalues[(string) $child->name])) {
                                $this->sccpvalues[(string) $child->name] = array('keyword' => (string) $child->name, 'data' => $datav, 'type' => '2', 'seq' => $seq);
                            }
                        }
                    }
                }
            }
        }
    }

    /* unused */

    public function doConfigPageInit($page) {
        $this->doGeneralPost();
    }

    /* unused */

    public function install() {
        
    }

    /* unused */

    public function uninstall() {
        
    }

    /* unused */

    public function backup() {
        
    }

    /* unused */

    public function restore($backup) {
        
    }

    public function getActionBar($request) {
        $buttons = array();
        switch ($request['display']) {
            case 'sccp_adv':
                if (empty($request['tech_hardware'])) {
                    break;
                }
                $buttons = array(
                    'submit' => array(
                        'name' => 'ajaxsubmit',
                        'id' => 'ajaxsubmit',
                        'value' => _("Save")
                    ),
                    'Save' => array(
                        'name' => 'ajaxsubmit2',
                        'id' => 'ajaxsubmit2',
                        'stayonpage' => 'yes',
                        'value' => _("Save + Continue")
                    ),
                    'cancel' => array(
                        'name' => 'cancel',
                        'id' => 'ajaxcancel',
                        'data-search' => '?display=sccp_adv',
                        'data-hash' => 'sccpdialplan',
                        'value' => _("Cancel")
                    ),
                );
                break;
            case 'sccp_phone':
                if (empty($request['tech_hardware'])) {
                    break;
                }
                $buttons = array(
                    'submit' => array(
                        'name' => 'ajaxsubmit',
                        'id' => 'ajaxsubmit',
                        'value' => _("Save")
                    ),
                    'Save' => array(
                        'name' => 'ajaxsubmit2',
                        'id' => 'ajaxsubmit2',
                        'stayonpage' => 'yes',
                        'value' => _("Save + Continue")
                    ),
                    'cancel' => array(
                        'name' => 'cancel',
                        'id' => 'ajaxcancel',
                        'data-search' => '?display=sccp_phone',
                        'data-hash' => 'sccpdevice',
                        'value' => _("Cancel")
                    ),
                );

                break;
            case 'sccpsettings':
                $buttons = array(
                    'submit' => array(
                        'name' => 'ajaxsubmit',
                        'id' => 'ajaxsubmit',
                        'value' => _("Submit")
                    ),
                    'reset' => array(
                        'name' => 'reset',
                        'id' => 'ajaxcancel',
                        'data-reload' => 'reload',
                        'value' => _("Reset")
                    ),
                );

                break;
        }
        return $buttons;
    }

    /*
     *  Show form information - General
     */

    public function myShowPage() {
        $request = $_REQUEST;
        $action = !empty($request['action']) ? $request['action'] : '';
        if ($this->sccpvalues['sccp_compatible']['data'] >= '433') {
//            $this->sccp_metainfo = $this->srvinterface->getеtestChanSCCP_GlablsInfo('general');
        }

        if (!empty($this->sccpvalues['displayconfig'])) {
            if (!empty($this->sccpvalues['displayconfig']['data']) && ($this->sccpvalues['displayconfig']['data'] == 'sccpsimple')) {
                $this->pagedata = array(
                    "general" => array(
                        "name" => _("General SCCP Settings"),
                        "page" => 'views/server.setting.php'
                    ),
                    "sccpdevice" => array(
                        "name" => _("SCCP Device"),
                        "page" => 'views/server.device.php'
                    ),
                    "sccpinfo" => array(
                        "name" => _("SCCP info"),
                        "page" => 'views/server.info.php'
                    ),
                );
            }
        }

        if (empty($this->pagedata)) {
//			$driver = $this->FreePBX->Config->get_conf_setting('ASTSIPDRIVER');
            $this->pagedata = array(
                "general" => array(
                    "name" => _("General SCCP Settings"),
                    "page" => 'views/server.setting.php'
                ),
                "sccpdevice" => array(
                    "name" => _("SCCP Device"),
                    "page" => 'views/server.device.php'
                ),
                "sccpntp" => array(
                    "name" => _("SCCP Time"),
                    "page" => 'views/server.datetime.php'
                ),
                "sccpcodec" => array(
                    "name" => _("SCCP Codec"),
                    "page" => 'views/server.codec.php'
                ),
                "sccpadv" => array(
                    "name" => _("Advanced SCCP Settings"),
                    "page" => 'views/server.advanced.php'
                ),
                "sccpinfo" => array(
                    "name" => _("SCCP info"),
                    "page" => 'views/server.info.php'
                ),
            );
        }
        if (!empty($this->pagedata)) {
            foreach ($this->pagedata as &$page) {
                ob_start();
                include($page['page']);
                $page['content'] = ob_get_contents();
                ob_end_clean();
            }
        }
        return $this->pagedata;
    }

    public function InfoServerShowPage() {
        $request = $_REQUEST;
        $action = !empty($request['action']) ? $request['action'] : '';
        $this->pagedata = array(
            "general" => array(
                "name" => _("General SCCP Settings"),
                "page" => 'views/server.info.php'
            ),
        );

        foreach ($this->pagedata as &$page) {
            ob_start();
            include($page['page']);
            $page['content'] = ob_get_contents();
            ob_end_clean();
        }

        return $this->pagedata;
    }

    public function AdvServerShowPage() {
        $request = $_REQUEST;
        $action = !empty($request['action']) ? $request['action'] : '';
        $inputform = !empty($request['tech_hardware']) ? $request['tech_hardware'] : '';

//        print_r($inputform);
        if (empty($this->pagedata)) {
            switch ($inputform) {
                case dialplan:
                    $this->pagedata = array(
                        "general" => array(
                            "name" => _("SCCP Dial Plan information"),
                            "page" => 'views/form.dptemplate.php'
                        )
                    );
                    break;
                default:
                    $this->pagedata = array(
                        "general" => array(
                            "name" => _("SCCP Model information"),
                            "page" => 'views/advserver.model.php'
                        ),
                        "sccpkeyset" => array(
                            "name" => _("SCCP Device Keyset"),
                            "page" => 'views/advserver.keyset.php'
                        ),
//                        "sccpdialplan" => array(
//                            "name" => _("SCCP Dial Plan information"),
//                            "page" => 'views/advserver.dialtemplate.php'
//                        )
                    );
                    break;
            }
            foreach ($this->pagedata as &$page) {
                ob_start();
                include($page['page']);
                $page['content'] = ob_get_contents();
                ob_end_clean();
            }
        }

        return $this->pagedata;
    }

    public function PhoneShowPage() {
        $request = $_REQUEST;
        $action = !empty($request['action']) ? $request['action'] : '';
        $inputform = !empty($request['tech_hardware']) ? $request['tech_hardware'] : '';
        if ($this->sccpvalues['sccp_compatible']['data'] >= '433') {
//            $this->sccp_metainfo = $this->srvinterface->getеtestChanSCCP_GlablsInfo('device');
        }

//        print_r($inputform);
        if (empty($this->pagedata)) {
            switch ($inputform) {
                case cisco:
                    $this->pagedata = array(
                        "general" => array(
                            "name" => _("Device configuration"),
                            "page" => 'views/form.adddevice.php'
                        ),
                        "buttons" => array(
                            "name" => _("Device Buttons"),
                            "page" => 'views/form.buttons.php'
                        ));
                    if ($this->sccpvalues['sccp_compatible']['data'] < '433') {
                        $this->pagedata["sccpcodec"] = array(
                                "name" => _("Device SCCP Codec"),
                                "page" => 'views/server.codec.php'
                            );
                    }

                    break;

                case r_user:
                    $this->pagedata = array(
                        "general" => array(
                            "name" => _("Rouming User configuration"),
                            "page" => 'views/form.addruser.php'
                        ),
                        "buttons" => array(
                            "name" => _("Device Buttons"),
                            "page" => 'views/form.buttons.php'
                        ),
                    );

                    break;

                default:
                    $this->pagedata = array(
                        "general" => array(
                            "name" => _("SCCP Extension"),
                            "page" => 'views/hardware.extension.php'
                        ),
                        "sccpdevice" => array(
                            "name" => _("SCCP Phone"),
                            "page" => 'views/hardware.phone.php'
                        )
                    );
                    break;
            }
            foreach ($this->pagedata as &$page) {
                ob_start();
                include($page['page']);
                $page['content'] = ob_get_contents();
                ob_end_clean();
            }
        }

        return $this->pagedata;
    }

    public function FormShowPage() {
        $request = $_REQUEST;
        $action = !empty($request['action']) ? $request['action'] : '';


        if (empty($this->pagedata)) {
//			$driver = $this->FreePBX->Config->get_conf_setting('ASTSIPDRIVER');

            $this->pagedata = array(
                "general" => array(
                    "name" => _("SCCP Extension"),
                    "page" => 'views/extension.page.php'
                )
            );

            $this->pagedata['sccpdevice'] = array(
                "name" => _("SCCP Phone"),
                "page" => 'views/phone.page.php'
            );

            foreach ($this->pagedata as &$page) {
                ob_start();
                include($page['page']);
                $page['content'] = ob_get_contents();
                ob_end_clean();
            }
        }

        return $this->pagedata;
    }

    public function getRightNav($request) {
        if (isset($request['tech_hardware']) && ($request['tech_hardware'] == 'cisco')) {
            return load_view(__DIR__ . "/views/hardware.rnav.php", array('request' => $request));
        }
    }

    public function ajaxRequest($req, &$setting) {
        switch ($req) {
            case 'backupsettings':
            case 'savesettings':
            case 'save_hardware':
            case 'save_ruser':
            case 'save_dialplan_template':
            case 'delete_hardware':
            case 'getPhoneGrid':
            case 'getExtensionGrid':
            case 'getDeviceModel':
            case 'getUserGrid':
            case 'getSoftKey':
            case 'getDialTemplate':
            case 'create_hw_tftp':
            case 'reset_dev':
            case 'reset_token':
            case 'model_enabled':
            case 'model_disabled':
            case 'model_update':
            case 'model_add':
            case 'model_delete':
            case 'update_button_label':
            case 'updateSoftKey':
            case 'deleteSoftKey':
            case 'delete_dialplan':
                return true;
                break;
        }
        return false;
    }

    public function ajaxHandler() {
        $request = $_REQUEST;
        $msg = '';
        $cmd_id = $request['command'];
        switch ($cmd_id) {
            case 'savesettings':
                $action = isset($request['sccp_createlangdir']) ? $request['sccp_createlangdir'] : '';
                if ($action == 'yes') {
                    $this->init_tftp_lang_path();
                }
                $this->save_submit($request);
                $this->sccp_db_save_setting();
//                $this->sccp_create_sccp_init();
                $this->sccp_create_tftp_XML();

                $res = $this->srvinterface->sccp_core_commands(array('cmd' => 'sccp_reload'));
//                $res = $this->srvinterface->sccp_core_commands(array('cmd' => 'restart_phone'));
                $msg = 'Config Saved: ' . $res['Response'] . '. Info :' . $res['data'];
//                needreload();
// !TODO!: It is necessary in the future to check, and replace all server responses on correct messages. Use _(msg)                 
                return array('status' => true, 'message' => $msg, 'reload' => true);
                break;
            case 'save_hardware':
                $this->save_hw_phone($request);
//                return array('status' => true, 'href' => 'config.php?display=sccp_phone',  'reload' => true);
                return array('status' => true, 'search' => '?display=sccp_phone', 'hash' => 'sccpdevice');

                return $this->save_hw_phone($request);

                break;
            case 'save_ruser':
//                    $res = $request;
                    $res = $this->save_rouming_users($request);
                    return array('status' => true, 'search' => '?display=sccp_phone', 'hash' => 'general');
                    return array('status' => false, 'message' => print_r($res));
                break;
            /* !TODO!: -TODO-: dialplan templates should be removed (only required for very old devices (like ATA) */
// -------------------------------   Old deviece suport - In the development--- 
            case 'save_dialplan_template':
                $res = $this->save_DialPlant($request);
                if (empty($res)) {
                    return array('status' => true, 'search' => '?display=sccp_adv', 'hash' => 'sccpdialplan');
                } else {
// !TODO!: It is necessary in the future to check, and replace all server responses on correct messages. Use _(msg)                     
                    return array('status' => false, 'message' => print_r($res));
                }
                break;
            case 'delete_dialplan':
                if (!empty($request['dialplan'])) {
                    $get_file = $request['dialplan'];
                    $res = $this->del_DialPlant($get_file);
// !TODO!: It is necessary in the future to check, and replace all server responses on correct messages. Use _(msg) 
                    return array('status' => true, 'message' => 'Dial Template has been deleted ! ', 'table_reload' => true);
                } else {
// !TODO!: It is necessary in the future to check, and replace all server responses on correct messages. Use _(msg) 
                    return array('status' => false, 'message' => print_r($res));
                }
                break;
// -------------------------------   Old deviece suport - In the development--- 
            case 'delete_hardware':
                if (!empty($request['idn'])) {
                    foreach ($request['idn'] as $idv) {
//                        $msg = strpos($idv, 'SEP-');
                        if ($this->strpos_array($idv, array('SEP', 'ATA', 'VG')) !== false) {
                            $this->dbinterface->sccp_save_db('sccpdevice', array('name' => $idv), 'delete', "name");
                            $this->dbinterface->sccp_save_db("sccpbuttons", array(), 'delete', '', $idv);
                            $this->sccp_delete_device_XML($idv); // Концы в вводу !!  
//                            $this->sccp_core_commands(array('cmd' => 'reload_phone', 'name' => $idv));
                            $this->srvinterface->sccp_core_commands(array('cmd' => 'reset_phone', 'name' => $idv));
                        }
                    }
// !TODO!: It is necessary in the future to check, and replace all server responses on correct messages. Use _(msg)                    
                    return array('status' => true, 'table_reload' => true, 'message' => 'HW is Delete ! ');
                }
                break;
            case 'create_hw_tftp':
                $ver_id = ' Test !';
                $this->sccp_delete_device_XML('all'); // Концы в вводу !!  
                $this->sccp_create_tftp_XML();
                $models = $this->dbinterface->get_db_SccpTableData("SccpDevice");
                $ver_id = ' on found active model !';
                //return array('status' => false, 'message' => 'Error :'. print_r($models,1));
                foreach ($models as $data) {
                    $ver_id = $this->sccp_create_device_XML($data['name']);
                };
// !TODO!: -TODO-: Do these returned message strings work with i18n ? 
// !TODO!: It is necessary in the future to check, and replace all server responses on correct messages. Use _(msg) 
                return array('status' => true, 'message' => 'Create new config files (version:' . $ver_id . ')');

                break;
            case 'reset_token':
            case 'reset_dev':
                $msg = '';
                if (!empty($request['name'])) {
                    foreach ($request['name'] as $idv) {
                        $msg = strpos($idv, 'SEP-');
                        if (!(strpos($idv, 'SEP') === false)) {
                            if ($cmd_id == 'reset_token') {
                                $res = $this->srvinterface->sccp_core_commands(array('cmd' => 'reset_token', 'name' => $idv));
                            } else {
                                $res = $this->srvinterface->sccp_core_commands(array('cmd' => 'reset_phone', 'name' => $idv));
                            }
//                            $msg = print_r($this->sccp_core_commands(array('cmd' => 'reset_phone', 'name' => $idv)), 1);
                            $msg = $res['Response'] . ' ' . $res['data'];
                        }
                        if ($idv == 'all') {
                            $dev_list = $this->srvinterface->sccp_get_active_device();
                            foreach ($dev_list as $key => $data) {
                                if ($cmd_id == 'reset_token') {
                                    if (($data['token'] == 'Rej') || ($data['status'] == 'Token ')) {
                                        $res = $this->srvinterface->sccp_core_commands(array('cmd' => 'reset_token', 'name' => $key));
                                        $msg .= 'Send Token reset to :' . $key . ' ';
                                    }
                                } else {
                                    $res = $this->srvinterface->sccp_core_commands(array('cmd' => 'reset_phone', 'name' => $key));
                                    $msg .= $res['Response'] . ' ' . $res['data'] . ' ';
                                }
//                                $msg .= $res['Response'] . ' ' . $res['data'] . ' ';
                            }
                        }
                    }
                }
// !TODO!: It is necessary in the future to check, and replace all server responses on correct messages. Use _(msg)                 
                return array('status' => true, 'message' => 'Reset command send ' . $msg, 'reload' => true);
//                }
                break;
            case 'update_button_label':
                $msg = '';
                $hw_list = array();
//                return array('status' => false, 'message' => 'update_button_label send ' . $msg, 'reload' => false);
                if (!empty($request['name'])) {
                    foreach ($request['name'] as $idv) {
                        if (!(strpos($idv, 'SEP') === false)) {
                            $hw_list[] = array('name' => $idv);
                        }
                        if ($idv == 'all') {
                        }
                    }
                }
                $res = $this->sccp_db_update_butons($hw_list);
                $msg .= $res['Response'] . ' raw: ' . $res['data'] . ' ';
// !TODO!: It is necessary in the future to check, and replace all server responses on correct messages. Use _(msg)                 
                return array('status' => true, 'message' => 'Update Butons Labels Complite ' . $msg, 'reload' => true);
                
            case 'model_add':
                $save_settings = array();
                $key_name = array('model', 'vendor', 'dns', 'buttons', 'loadimage', 'loadinformationid', 'nametemplate');
                $upd_mode = 'replace';
            case 'model_update':
                if ($request['command'] == 'model_update') {
                    $key_name = array('model', 'loadimage', 'nametemplate');
                    $upd_mode = 'update';
                }
                if (!empty($request['model'])) {
                    foreach ($key_name as $key => $value) {
                        if (!empty($request[$value])) {
                            $save_settings[$value] = $request[$value];
                        } else {
                            $save_settings[$value] = $this->val_null; // null
                        }
                    }
                    $this->dbinterface->sccp_save_db('sccpdevmodel', $save_settings, $upd_mode, "model");
                    return array('status' => true, 'table_reload' => true);
                }
                return $save_settings;
                break;
            case 'model_enabled':
                $model_set = '1';
            case 'model_disabled':
                if ($request['command'] == 'model_disabled') {
                    $model_set = '0';
                }
                $msg = '';
                $save_settings = array();
                if (!empty($request['model'])) {
                    foreach ($request['model'] as $idv) {
                        $this->dbinterface->sccp_save_db('sccpdevmodel', array('model' => $idv, 'enabled' => $model_set), 'update', "model");
                    }
                }
                return array('status' => true, 'table_reload' => true);

                break;
            case 'model_delete':
                if (!empty($request['model'])) {
                    $this->dbinterface->sccp_save_db('sccpdevmodel', array('model' => $request['model']), 'delete', "model");
                    return array('status' => true, 'table_reload' => true);
                }
                break;
            case 'getDeviceModel':
                switch ($request['type']) {
                    case 'all':
                    case 'extension':
                    case 'enabled':
                        $devices = $this->getSccp_model_information($request['type'], $validate = TRUE);
                        break;
                }
                if (empty($devices)) {
                    return array();
                }
                return $devices;
                break;

            case 'deleteSoftKey':
                if (!empty($request['softkey'])) {
                    $id_name = $request['softkey'];
                    unset($this->sccp_conf_init[$id_name]);
                    $this->sccp_create_sccp_init();
                    $msg = print_r($this->srvinterface->sccp_core_commands(array('cmd' => 'sccp_reload')), 1);
                    return array('status' => true, 'table_reload' => true);
                }
                break;
            case 'updateSoftKey':
                if (!empty($request['id'])) {
                    $id_name = preg_replace('/[^A-Za-z0-9]/', '', $request['id']);
                    $this->sccp_conf_init[$id_name]['type'] = "softkeyset";
                    foreach ($this->extconfigs->getextConfig('keyset') as $keyl => $vall) {
                        if (!empty($request[$keyl])) {
                            $this->sccp_conf_init[$id_name][$keyl] = $request[$keyl];
                        } else {
//                            $this->sccp_conf_init[$id_name][$keyl] = ''; // ????
                        }
                    }
                    $this->sccp_create_sccp_init();
                    $msg = print_r($this->srvinterface->sccp_core_commands(array('cmd' => 'sccp_reload')), 1);

                    return array('status' => true, 'table_reload' => true);
//                    return $this->sccp_conf_init[$id_name];
                }

//                    sccp_conf_init
                break;
            case 'getSoftKey':
                $result = array();
                $i = 0;
                $keyl = 'default';
                foreach ($this->srvinterface->sccp_list_keysets() as $keyl => $vall) {
                    $result[$i]['softkeys'] = $keyl;
                    if ($keyl == 'default') {
                        foreach ($this->extconfigs->getextConfig('keyset') as $key => $value) {
                            $result[$i][$key] = str_replace(',', '<br>', $value);
                        }
                    } else {
                        foreach ($this->getMyConfig('softkeyset', $keyl) as $key => $value) {
                            $result[$i][$key] = str_replace(',', '<br>', $value);
                        }
                    }

                    $i++;
                }
                return $result;
                break;
            case 'getExtensionGrid':
                $result = $this->dbinterface->get_db_SccpTableData('SccpExtension');
                if (empty($result)) {
                    return array();
                }
/*                $res_info = $this->aminterface->core_list_all_exten('exten');
                if (!empty($res_info)) {
                    foreach ($result as $key => $value) {
                        $tpm_info = $res_info[$value['name']];
                        if (!empty($tpm_info)) {
                            $result[$key]['line_status'] = $tpm_info['status'];
                            $result[$key]['line_statustext'] = $tpm_info['statustext'];
                        } else {
                            $result[$key]['line_status'] = '';
                            $result[$key]['line_statustext'] = '';
                        }
                    }
                }
 * 
 */
                return $result;
                break;
            case 'getPhoneGrid':
                $result = $this->dbinterface->get_db_SccpTableData('SccpDevice');
                $staus = $this->srvinterface->sccp_get_active_device();
                if (empty($result)) {
                    $result = array();
                } else {
//                    $staus = $this->sccp_get_active_device();
                    foreach ($result as &$dev_id) {
                        $id_name = $dev_id['name'];
                        if (!empty($staus[$id_name])) {
                            $dev_id['description'] = $staus[$id_name]['descr'];
                            $dev_id['status'] = $staus[$id_name]['status'];
                            $dev_id['address'] = $staus[$id_name]['address'];
                            $dev_id['new_hw'] = 'N';
                            $staus[$id_name]['news'] = 'N';
                        } else {
                            $dev_id['description'] = '- -';
                            $dev_id['status'] = 'not connected';
                            $dev_id['address'] = '- -';
                        }
                    }
                }
                if (!empty($staus)) {
//                    Array ( [name] => SEP0004F2EDCBFD [mac] => SEP0004F2EDCBFD [type] => 7937 [button] => line,7818,default ) 
                    foreach ($staus as $dev_ids) {
                        $id_name = $dev_ids['name'];
                        if (empty($dev_ids['news'])) {
                            $dev_data = $this->srvinterface->sccp_getdevice_info($id_name);
                            if (!empty($dev_data['SCCP_Vendor']['model_id'])) {
//                            $dev_data = $this->sccp_getdevice_info($id_name);
                                $dev_addon = $dev_data['SCCP_Vendor']['model_addon'];
                                if (empty($dev_addon)) {
                                    $dev_addon = null;
                                }
                                $dev_schema = $this->getSccp_model_information('byciscoid', false, "all", array('model' => $dev_data['SCCP_Vendor']['model_id']));
                                $result[] = array('name' => $id_name, 'mac' => $id_name, 'button' => '---', 'type' => $dev_schema[0]['model'], 'new_hw' => 'Y',
                                    'description' => '*NEW* ' . $dev_ids['descr'], 'status' => '*NEW* ' . $dev_ids['status'], 'address' => $dev_ids['address'],
                                    'addon' => $dev_addon);
                            }
                        }
                    }
                }
                return $result;
                break;
// -------------------------------   Old deviece suport - In the development--- 
            case 'getDialTemplate':
                $result = $this->get_DialPlanList();
                if (empty($result)) {
                    $result = array();
                }
                return $result;
                break;
// -------------------------------   Old deviece suport - In the development---                 
            case 'backupsettings':
                $filename = $this->sccp_create_sccp_backup();
                $file_name = basename($filename);

                header("Content-Type: application/zip");
                header("Content-Disposition: attachment; filename=$file_name");
                header("Content-Length: " . filesize($filename));

                readfile($filename);
                unlink($filename);

//                return array('status' => false, 'message' => $result);
                //              return $result;
                break;
        }
    }

    public function doGeneralPost() {
//            $this->FreePBX->WriteConfig($config);
        if (!isset($_REQUEST['Submit']))
            return;
    }

    /*
     * 
     * *  Save Hardware Device Information to Db + ???? Create / update XML Profile
     * 
     */
    function get_buttons_phone($get_settings, $ref_id = '', $ref_type = 'sccpdevice') {
//      Get Model Buttons info
        $res = array();

        $lines_list = $this->dbinterface->get_db_SccpTableData('SccpExtension');
        $max_btn = ((!empty($get_settings['buttonscount']) ? $get_settings['buttonscount'] : 100));
        $last_btn = $max_btn;
        for ($it = $max_btn; $it >= 0; $it--) {
            if (!empty($get_settings['button' . $it . '_type'])) {
                $last_btn = $it;
                $btn_t = $get_settings['button' . $it . '_type'];
                if ($btn_t != 'empty') {
                    break;
                }
            }
        }

        for ($it = 0; $it <= $last_btn; $it++) {
            if (!empty($get_settings['button' . $it . '_type'])) {
                $btn_t = $get_settings['button' . $it . '_type'];

                $btn_n = '';
                $btn_opt = '';
                if ($it == 0) {
                    $btn_opt = 'default';
                }
                switch ($btn_t) {
                    case 'feature':
                        $btn_f = $get_settings['button' . $it . '_feature'];
//                        $btn_opt = (empty($get_settings['button' . $it . '_fvalue'])) ? '' : $get_settings['button' . $it . '_fvalue'];
                        $btn_n = (empty($get_settings['button' . $it . '_flabel'])) ? $def_feature[$btn_f]['name'] : $get_settings['button' . $it . '_flabel'];
                        $btn_opt = $btn_f;
                        if (!empty($def_feature[$btn_f]['value'])) {
                            if (empty($get_settings['button' . $it . '_fvalue'])) {
                                $btn_opt .= ',' . $def_feature[$btn_f]['value'];
                            } else {
                                $btn_opt .= ',' . $get_settings['button' . $it . '_fvalue'];
                            }
                        }
                        break;
                    case 'monitor':
                        $btn_t = 'speeddial';
                        $btn_opt = (string) $get_settings['button' . $it . '_line'];
                        $db_res = $this->dbinterface->get_db_SccpTableData('SccpExtension', array('name' => $btn_opt));
                        $btn_n = $db_res[0]['label'];
                        $btn_opt .= ',' . $btn_opt . $this->hint_context['default'];
                        break;
                    case 'speeddial':
                        if (!empty($get_settings['button' . $it . '_input'])) {
                            $btn_n = $get_settings['button' . $it . '_input'];
                        }
                        if (!empty($get_settings['button' . $it . '_phone'])) {
                            $btn_opt = $get_settings['button' . $it . '_phone'];
                            if (empty($btn_n)) {
                                $btn_n = $btn_opt;
                            }
                        }

                        if (!empty($get_settings['button' . $it . '_hint'])) {
                            if ($get_settings['button' . $it . '_hint'] == "hint") {
                                if (empty($btn_n)) {
                                    $btn_t = 'line';
                                    $btn_n = $get_settings['button' . $it . '_hline'] . '!silent';
                                    $btn_opt = '';
                                } else {
//                                    $btn_opt .= ',' . $get_settings['button' . $it . '_hline'] . $this->hint_context['default'];
                                    $btn_opt .= ',' . $get_settings['button' . $it . '_hline'];
                                }
                            }
                        }
                        break;
                    case 'adv.line':
                        $btn_t = 'line';
                        $btn_n = (string) $get_settings['button' . $it . '_line'];
                        $btn_n .= '@' . (string) $get_settings['button' . $it . '_advline'];
                        $btn_opt = (string) $get_settings['button' . $it . '_advopt'];

                        break;
                    case 'line':
                    case 'silent':
                        if (isset($get_settings['button' . $it . '_line'])) {
                            $btn_n = (string) $get_settings['button' . $it . '_line'];
                            if ($it > 0) {
                                if ($btn_t == 'silent') {
                                    $btn_n .= '!silent';
                                    $btn_t = 'line';
                                }
                            }
                        } else {
                            $btn_t = 'empty';
                            $btn_n = '';
                        }
                        break;
                    case 'empty':
                        $btn_t = 'empty';
                        break;
                }
                if (!empty($btn_t)) {
                    $res[] = array('ref' => $ref_id, 'reftype' => $ref_type, 'instance' => (string) ($it + 1), 'type' => $btn_t, 'name' => $btn_n, 'options' => $btn_opt);
                }
            }
        }
        return $res;
        
    }

    function save_hw_phone($get_settings, $validateonly = false) {
        $hdr_prefix = 'sccp_hw_';
        $hdr_arprefix = 'sccp_hw-ar_';

        $save_buttons = array();
        $save_settings = array();
        $save_codec = array();
        $def_feature = array('parkinglot' => array('name' => 'P.slot', 'value' => 'default'),
            'devstate' => array('name' => 'Coffee', 'value' => 'coffee'),
            'monitor' => array('name' => 'Record Calls', 'value' => '')
        );
        $name_dev = '';
        $db_field = $this->dbinterface->get_db_SccpTableData("get_colums_sccpdevice");
        $hw_id = (empty($get_settings['sccp_deviceid'])) ? 'new' : $get_settings['sccp_deviceid'];
        $update_hw = ($hw_id == 'new') ? 'update' : 'clear';
        $hw_prefix = 'SEP';
        if (!empty($get_settings[$hdr_prefix . 'type'])) {
            $value = $get_settings[$hdr_prefix . 'type'];
            if (strpos($value, 'ATA') !== false) {
                $hw_prefix = 'ATA';
            }
            if (strpos($value, 'VG') !== false) {
                $hw_prefix = 'VG';
            }
        }
        foreach ($db_field as $data) {
            $key = (string) $data['Field'];
            $value = "";
            switch ($key) {
                case 'name':
                    if (!empty($get_settings[$hdr_prefix . 'mac'])) {
                        $value = $get_settings[$hdr_prefix . 'mac'];
                        $value = strtoupper(str_replace(array('.', '-', ':'), '', $value)); // Delete mac Seporated from string
                        $value = sprintf("%012s", $value);
                        if ($hw_prefix == 'VG') {
                            $value = $hw_prefix . $value . '0';
                        } else {
                            $value = $hw_prefix . $value;
                        }
                        $name_dev = $value;
                    }
                    break;
                case 'disallow':
                    $value = $get_settings['sccp_disallow'];
                    break;

                case 'allow':
                    $i = 0;
                    foreach ($get_settings['voicecodecs'] as $keycodeс => $valcodeс) {
                        $save_codec[$i] = $keycodeс;
                        $i++;
                    };
                    $value = implode(";", $save_codec);
                    break;
                case 'phonecodepage':
                    $value = 'null';
                    if (!empty($get_settings[$hdr_prefix . 'devlang'])) {
                        $lang_data = $this->extconfigs->getextConfig('sccp_lang',$get_settings[$hdr_prefix . 'devlang']);
                        if (!empty($lang_data)) {
                            $value = $lang_data['codepage'];
                        }
                    }
                    break;
                case '_hwlang':
                    if (empty($get_settings[$hdr_prefix . 'netlang']) || empty($get_settings[$hdr_prefix . 'devlang'])) {
                        $value = 'null';
                    } else {
                        $value = $get_settings[$hdr_prefix . 'netlang'] . ':' . $get_settings[$hdr_prefix . 'devlang'];
                    }
                    break;
                default :
                    if (!empty($get_settings[$hdr_prefix . $key])) {
                        $value = $get_settings[$hdr_prefix . $key];
                    }
                    if (!empty($get_settings[$hdr_arprefix . $key])) {
                        $arr_data = '';
                        $arr_clear = FALSE;
                        foreach ($get_settings[$hdr_arprefix . $key] as $vkey => $vval) {
                            $tmp_data = '';
                            foreach ($vval as $vkey => $vval) {
                                switch ($vkey) {
                                    case 'inherit':
                                        if ($vval == 'on') {
                                            $arr_clear = TRUE;
                                            // Злобный ХАК
                                            if ($key == 'permit') {
                                                $save_settings['deny'] = 'NONE';
                                            }
                                        }
                                        break;
                                    case 'internal':
                                        if ($vval == 'on') {
                                            $tmp_data .= 'internal;';
                                        }
                                        break;
                                    default:
                                        $tmp_data .= $vval . '/';
                                        break;
                                }
                            }
                            if (strlen($tmp_data) > 2) {
                                while (substr($tmp_data, -1) == '/') {
                                    $tmp_data = substr($tmp_data, 0, -1);
                                }
                                $arr_data .= $tmp_data . ';';
                            }
                        }
                        while (substr($arr_data, -1) == ';') {
                            $arr_data = substr($arr_data, 0, -1);
                        }
                        if ($arr_clear) {
                            $value = 'NONE';
                        } else {
                            $value = $arr_data;
                        }
                    }
            }
            if (!empty($value)) {
                $save_settings[$key] = $value;
            }
        }
//      Save / Updade Base        
        $this->dbinterface->sccp_save_db("sccpdevice", $save_settings, 'replace');

//      Get Model Buttons info
        $save_buttons = $this-> get_buttons_phone($get_settings,$name_dev, 'sccpdevice');
        
//      Sace Buttons config
        $this->dbinterface->sccp_save_db("sccpbuttons", $save_buttons, $update_hw, '', $name_dev);

//      Create Device XML 
        $this->sccp_create_device_XML($name_dev);

        if ($hw_id == 'new') {
            $this->srvinterface->sccp_core_commands(array('cmd' => 'reset_phone', 'name' => $name_dev));
        } else {
            $this->srvinterface->sccp_core_commands(array('cmd' => 'restart_phone', 'name' => $name_dev));
        }

        return $save_settings;
    }

    function save_submit($get_settings, $validateonly = false) {
        $hdr_prefix = 'sccp_';
        $hdr_arprefix = 'sccp-ar_';
        $save_settings = array();
        $save_codec = array();
        $integer_msg = _("%s must be a non-negative integer");
        $errors = array();
        $i = 0;
        foreach ($get_settings as $key => $value) {
            $pos = strpos($key, $hdr_prefix);
            if ($pos !== false) {
                $key1 = substr_replace($key, '', 0, strlen($hdr_prefix));
                if (!empty($this->sccpvalues[$key1])) {
                    if (!($this->sccpvalues[$key1]['data'] == $value)) {
                        $save_settings[] = array('keyword' => $this->sccpvalues[$key1]['keyword'], 'data' => $value,
                            'seq' => $this->sccpvalues[$key1]['seq'], 'type' => $this->sccpvalues[$key1]['type']);
                    }
                }
            }
            $pos = strpos($key, $hdr_arprefix);
            if ($pos !== false) {
                $key1 = substr_replace($key, '', 0, strlen($hdr_arprefix));
                $arr_data = '';
                if (!empty($this->sccpvalues[$key1])) {
                    foreach ($value as $vkey => $vval) {
                        $tmp_data = '';
                        foreach ($vval as $vkey => $vval) {
                            switch ($vkey) {
                                case 'inherit':
                                case 'internal':
                                    if ($vval == 'on') {
                                        $tmp_data .= 'internal;';
                                    }
                                    break;
                                default:
                                    $tmp_data .= $vval . '/';
                                    break;
                            }
                        }
                        if (strlen($tmp_data) > 2) {
                            while (substr($tmp_data, -1) == '/') {
                                $tmp_data = substr($tmp_data, 0, -1);
                            }
                            $arr_data .= $tmp_data . ';';
                        }
                    }
                    while (substr($arr_data, -1) == ';') {
                        $arr_data = substr($arr_data, 0, -1);
                    }
                    if (!($this->sccpvalues[$key1]['data'] == $arr_data)) {
                        $save_settings[] = array('keyword' => $this->sccpvalues[$key1]['keyword'], 'data' => $arr_data,
                            'seq' => $this->sccpvalues[$key1]['seq'], 'type' => $this->sccpvalues[$key1]['type']);
                    }
                }
            }
            switch ($key) {
                case 'voicecodecs':
                case 'vcodec':
                    foreach ($value as $keycodeс => $valcodeс) {
                        $save_codec[$i] = $keycodeс;
                        $i++;
                    };
                    $tmpv = implode(";", $save_codec);
                    if ($tmpv !== $this->sccpvalues['allow']['data']) {
                        $save_settings[] = array('keyword' => 'allow', 'data' => $tmpv,
                            'seq' => $this->sccpvalues['allow']['seq'],
                            'type' => $this->sccpvalues['allow']['type']);
                    }
                    break;

                case 'sccp_ntp_timezone':
                    $tz_id = $value;
                    $TZdata = $this->extconfigs->getextConfig('sccp_timezone', $tz_id);
                    if (!empty($TZdata)) {
                        $save_settings[] = array('keyword' => 'tzoffset', 'data' => ($TZdata['offset'] / 60),
                            'seq' => '98',
                            'type' => '2');
                    }
                    break;
            }
        }
        if (!empty($save_settings)) {
            $this->sccp_db_save_setting($save_settings);
            $this->getSccpSettingFromDB();
//            $this->sccp_create_sccp_init();
        }
        $this->sccp_create_sccp_init(); // Rewrite Config.
        $save_settings[] = array('status' => true);
        return $save_settings;
    }

    
    
        function save_rouming_users($get_settings, $validateonly = false) {
        $hdr_prefix = 'sccp_ru_';
        $hdr_arprefix = 'sccp_ru-ar_';

        $save_buttons = array();
        $save_settings = array();
/*
        $def_feature = array('parkinglot' => array('name' => 'P.slot', 'value' => 'default'),
            'devstate' => array('name' => 'Coffee', 'value' => 'coffee'),
            'monitor' => array('name' => 'Record Calls', 'value' => '')
        );
 * 
 */
        $name_dev = '';
        $db_field = $this->dbinterface->get_db_SccpTableData("get_colums_sccpuser");
//        $hw_id = (empty($get_settings['sccp_deviceid'])) ? 'new' : $get_settings['sccp_deviceid'];
//        $update_hw = ($hw_id == 'new') ? 'update' : 'clear';
        $hw_prefix = 'SEP';
        $name_dev = $get_settings[$hdr_prefix . 'id'];
        
        foreach ($db_field as $data) {
            $key = (string) $data['Field'];
            $value = "";
            switch ($key) {
                case 'name':
                    $value = $name_dev;
                    break;
                case '_hwlang':
                    if (empty($get_settings[$hdr_prefix . 'netlang']) || empty($get_settings[$hdr_prefix . 'devlang'])) {
                        $value = 'null';
                    } else {
                        $value = $get_settings[$hdr_prefix . 'netlang'] . ':' . $get_settings[$hdr_prefix . 'devlang'];
                    }
                    break;
                default :
                    if (!empty($get_settings[$hdr_prefix . $key])) {
                        $value = $get_settings[$hdr_prefix . $key];
                    }
                    if (!empty($get_settings[$hdr_arprefix . $key])) {
                        $arr_data = '';
                        $arr_clear = FALSE;
                        foreach ($get_settings[$hdr_arprefix . $key] as $vkey => $vval) {
                            $tmp_data = '';
                            foreach ($vval as $vkey => $vval) {
                                switch ($vkey) {
                                    case 'inherit':
                                        if ($vval == 'on') {
                                            $arr_clear = TRUE;
                                            // Злобный ХАК
                                            if ($key == 'permit') {
                                                $save_settings['deny'] = 'NONE';
                                            }
                                        }
                                        break;
                                    case 'internal':
                                        if ($vval == 'on') {
                                            $tmp_data .= 'internal;';
                                        }
                                        break;
                                    default:
                                        $tmp_data .= $vval . '/';
                                        break;
                                }
                            }
                            if (strlen($tmp_data) > 2) {
                                while (substr($tmp_data, -1) == '/') {
                                    $tmp_data = substr($tmp_data, 0, -1);
                                }
                                $arr_data .= $tmp_data . ';';
                            }
                        }
                        while (substr($arr_data, -1) == ';') {
                            $arr_data = substr($arr_data, 0, -1);
                        }
                        if ($arr_clear) {
                            $value = 'NONE';
                        } else {
                            $value = $arr_data;
                        }
                    }
            }
            if (!empty($value)) {
                $save_settings[$key] = $value;
            }
        }
//      Save / Updade Base      
//        return $save_settings;
        // $update_hw = ($hw_id == 'new') ? 'update' : 'clear';
        
        $this->dbinterface->sccp_save_db("sccpuser", $save_settings, 'replace', 'name');

        $save_buttons = $this-> get_buttons_phone($get_settings,$name_dev, 'sccpline');
//      Sace Buttons config
        $this->dbinterface->sccp_save_db("sccpbuttons", $save_buttons,'clear', '', $name_dev);
        return $save_buttons;

        return $save_settings;
    }

    
    
    
    
    public function getSccpSettingFromDB() {
        $raw_data = $this->dbinterface->get_db_SccpSetting();
        foreach ($raw_data as $var) {
            $this->sccpvalues[$var['keyword']] = array('keyword' => $var['keyword'], 'data' => $var['data'], 'seq' => $var['seq'], 'type' => $var['type']);
        }
        return;
    }

    public function getMyConfig($var = null, $id = "noid") {
//    $final = false;
        switch ($var) {
            case "voicecodecs":
                $val = explode(";", $this->sccpvalues['allow']['data']);
                $final = array();
                $i = 1;
                foreach ($val as $value) {
                    $final[$value] = $i;
                    $i++;
                }
                break;
            case "softkeyset":
                $final = array();
                $i = 0;
                if ($id == "noid") {
                    foreach ($this->sccp_conf_init as $key => $value) {
                        if ($this->sccp_conf_init[$key]['type'] == 'softkeyset') {
                            $final[$i] = $value;
                            $i++;
                        }
                    }
                } else {
                    if (!empty($this->sccp_conf_init[$id])) {
                        if ($this->sccp_conf_init[$id]['type'] == 'softkeyset') {
                            $final = $this->sccp_conf_init[$id];
                        }
                    }
                }

                break;
        }
        return $final;
    }

    public function getCodecs($type, $showDefaults = false) {
        $codecs_res = array();
        switch ($type) {
            case 'audio':
                $lcodecs = $this->getMyConfig('voicecodecs');
                $allCodecs = $this->FreePBX->Codecs->getAudio();
                break;
            case 'video':
                $lcodecs = $this->getMyConfig('voicecodecs');
                $allCodecs = array('h264'=>'1','h263'=>'','h265'=>'','h261'=>'');
//                $allCodecs = $this->FreePBX->Codecs->getVideo();
                break;
            case 'text':
                $lcodecs = $this->getConfig('textcodecs');
                break;
            case 'image':
                $lcodecs = $this->getConfig('imagecodecs');
                break;
            default:
                throw new Exception(_('Unknown Type'));
                break;
        }

        if (empty($lcodecs) || !is_array($lcodecs)) {
            switch ($type) {
                case 'audio':
                    $codecs = $this->FreePBX->Codecs->getAudio(true);
                    break;
                case 'video':
//                    $codecs = $this->FreePBX->Codecs->getVideo(true);
                    $codecs = array('h264'=>'1','h263'=>'','h265'=>'','h261'=>'');
                    break;
                case 'text':
                    $codecs = $this->FreePBX->Codecs->getText(true);
                    break;
                case 'image':
                    $codecs = $this->FreePBX->Codecs->getImage(true);
                    break;
            }
        } else {
            foreach ($lcodecs as $c => $v) {
                if (isset($allCodecs[$c])) {
                    $codecs[$c] = true;
                }
            }
        }

        if ($showDefaults) {
            switch ($type) {
                case 'audio':
                    $allCodecs = $this->FreePBX->Codecs->getAudio();
                    break;
                case 'video':
//                    $allCodecs = $this->FreePBX->Codecs->getVideo();
                    $allCodecs = array('h264'=>'1','h263'=>'','h265'=>'','h261'=>'');
                    break;
                case 'text':
                    $allCodecs = $this->FreePBX->Codecs->getText();
                    break;
                case 'image':
                    $allCodecs = $this->FreePBX->Codecs->getImage();
                    break;
            }
            // Update the $codecs array by adding un-selected codecs to the end of it.

            foreach ($allCodecs as $c => $v) {
                if (!isset($codecs[$c])) {
                    $codecs[$c] = false;
                }
            }

            return $codecs;
        } else {
            //Remove all non digits
            $final = array();
            foreach ($codecs as $codec => $order) {
                $order = trim($order);
                if (ctype_digit($order)) {
                    $final[$codec] = $order;
                }
            }
            asort($final);
            return $final;
        }
    }

    /**
     * Update or Set Codecs
     * @param {string} $type           Codec Type
     * @param {array} $codecs=array() The codecs with order, if blank set defaults
     */
    public function setCodecs($type, $codecs = array()) {
        $default = empty($codecs) ? true : false;
        switch ($type) {
            case 'audio':
                $codecs = $default ? $this->FreePBX->Codecs->getAudio(true) : $codecs;
                $this->setConfig("voicecodecs", $codecs);
                break;
            case 'video':
                $codecs = $default ? $this->FreePBX->Codecs->getVideo(true) : $codecs;
                $this->setConfig("videocodecs", $codecs);
                break;
            case 'text':
                $codecs = $default ? $this->FreePBX->Codecs->getText(true) : $codecs;
                $this->setConfig("textcodecs", $codecs);
                break;
            case 'image':
                $codecs = $default ? $this->FreePBX->Codecs->getImage(true) : $codecs;
                $this->setConfig("imagecodecs", $codecs);
                break;
            default:
                throw new Exception(_('Unknown Type'));
                break;
        }
        return true;
    }

    function Sccp_manager_hookGet_config($engine) {
        $this->debugdata($engine);
    }

    function Sccp_manager_get_config($engine) {
        $this->debugdata($engine);
    }

    /*
      function soundlang_hookGet_config($engine) {

      global $core_conf;
      $this->debugdata($engine);

      switch ($engine) {
      case "asterisk":
      //                if (isset($core_conf) && is_a($core_conf, "core_conf")) {
      //                    $language = FreePBX::Soundlang()->getLanguage();
      //                    if ($language != "") {
      //                        $core_conf->addSipGeneral('language', $language);
      //                        $core_conf->addIaxGeneral('language', $language);
      //                    }
      //                }
      break;
      }
      }
     */

    /**
     * Retrieve Active Codecs
     * return fiends Lag pack
     * 
     */
    public function getTftpLang() {
        return $this->tftpLang;
    }

    private function initTftpLang() {
        $result = array();
        if (empty($this->sccppath["tftp_path"]) || empty($this->sccppath["tftp_lang_path"])) {
            return;
        }
        $dir = $this->sccppath["tftp_lang_path"];

        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $filename = $dir . DIRECTORY_SEPARATOR . $value . DIRECTORY_SEPARATOR . $this->SCCP_LANG_DICTIONARY;
                    if (file_exists($filename)) {
                        $lang_ar = $this->extconfigs->getextConfig('sccp_lang');
                        foreach ($lang_ar as $lang_key => $lang_value) {
                            if ($lang_value['locale'] == $value) {
                                $result[$lang_key] = $value;
                            }
                        }
//                        $result[] = $value;
                    }
                }
            }
        }
        $this->tftpLang = $result;
    }

    /*
     *    Check tftp/xml file path and permissions
     */

    private function init_tftp_lang_path() {
        $dir = $this->sccppath["tftp_lang_path"];
        foreach ($this->extconfigs->getextConfig('sccp_lang') as $lang_key => $lang_value) {
            $filename = $dir . DIRECTORY_SEPARATOR . $lang_value['locale'];
            if (!file_exists($filename)) {
                if (!mkdir($filename, 0777, true)) {
                    die('Error create lang dir');
                }
            }
        }
    }

    /*
     *    Check file paths and permissions
     */

// !TODO!: -TODO-: This function is getting a little big. Might be possible to sperate tftp work into it's own file/class. Initially, you need to remove the not working section and commented out section

    function init_sccp_path() {
        global $db;
        global $amp_conf;
        $driver_revision = array('0' => '', '430' => '.v431', '431' => '.v432', '432' => '.v432', '433' => '.v433');


        $confDir = $amp_conf["ASTETCDIR"];
        if (empty($this->sccppath["asterisk"])) {
            if (strlen($confDir) < 1) {
                $this->sccppath["asterisk"] = "/etc/asterisk";
            } else {
                $this->sccppath["asterisk"] = $confDir;
            }
        }
        $ver_id = $this->srvinterface->get_compatible_sccp();
        if (!empty($this->sccpvalues['SccpDBmodel'])) {
            $ver_id = $this->sccpvalues['SccpDBmodel']['data'];
        }

        $driver = $this->FreePBX->Core->getAllDriversInfo();
        $sccp_driver_replace = '';
        if (empty($driver['sccp'])) {
            $sccp_driver_replace = 'yes';
        } else {
            if (empty($driver['sccp']['Version'])) {
                $sccp_driver_replace = 'yes';
            } else {
                if ($driver['sccp']['Version'] != $this->sccp_driver_ver . $driver_revision[$ver_id]) {
                    $sccp_driver_replace = 'yes';
                }
            }
        }

//        $this->sccpvalues['sccp_compatible'] = '11';
        $this->sccpvalues['sccp_compatible'] = array('keyword' => 'compatible', 'data' => $ver_id, 'type' => '1', 'seq' => '99');

        $this->sccppath = $this->extconfigs->validate_init_path($confDir, $this->sccpvalues, $sccp_driver_replace);

        $driver = $this->FreePBX->Core->getAllDriversInfo(); // ??????

        $read_config = $this->cnf_read->getConfig('sccp.conf');
        $this->sccp_conf_init['general'] = $read_config['general'];
        foreach ($read_config as $key => $value) {
            if (isset($read_config[$key]['type'])) { // copy soft key
                if ($read_config[$key]['type'] == 'softkeyset') {
                    $this->sccp_conf_init[$key] = $read_config[$key];
                }
            }
        }

        $hint = $this->srvinterface->sccp_list_hints();
        foreach ($hint as $key => $value) {
            if ($this->hint_context['default'] != $value) {
                $this->hint_context[$key] = $value;
            }
        }
    }

    /*
     *      DialPlan 
     *      
     */

    function get_DialPlanList() {
        $dir = $this->sccppath["tftp_DP"] . '/*.xml';
        $base_len = strlen($this->sccppath["tftp_DP"]) + 1;
        $res = glob($dir);
        $dp_list = array();
        foreach ($res as $key => $value) {
            $res[$key] = array('id' => substr($value, $base_len, -4), 'file' => substr($value, $base_len));
        }

        return $res;
    }

    function get_DialPlan($get_file) {
        $file = $this->sccppath["tftp_DP"] . '/' . $get_file . '.xml';
        if (file_exists($file)) {
//            $load_xml_data = simplexml_load_file($file);

            $fileContents = file_get_contents($file);
            $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
            $fileContents = trim(str_replace('"', "'", $fileContents));
            $fileContents = strtolower($fileContents);
            $res = (array) simplexml_load_string($fileContents);
        }
        return $res;
    }

    function del_DialPlan($get_file) {
        if (!empty($get_file)) {
            $file = $this->sccppath["tftp_DP"] . '/' . $get_file . '.xml';
            if (file_exists($file)) {
                $res = unlink($file);
            }
        }
        return $res;
    }

    function save_DialPlan($get_settings) {
        $xmlstr = "<DIALTEMPLATE>\n";
        $dialFelds = array('match', 'timeout', 'rewrite', 'tone'); //str -to lo ! 

        $hdr_prefix = 'sccp_dial_';
        $hdr_arprefix = 'sccp_dial-ar_';
        $save_data = array();
        $integer_msg = _("%s must be a non-negative integer");
        $errors = array();
        foreach ($get_settings[$hdr_arprefix . 'dialtemplate'] as $key => $value) {
            $xmlstr .= '<TEMPLATE';
            if (!empty($value['match'])) {
                foreach ($dialFelds as $fld) {
                    if (isset($value[$fld])) {
                        if ($value[$fld] == 'empty' || $value[$fld] == '') {
//                            
                        } else {
                            $xmlstr .= ' ' . $fld . '="' . (string) $value[$fld] . '"';
                        }
                    }
                }
            } else {
                $errors = array('Fields need to match !!');
            }
            $xmlstr .= "/>\n";
        }
        $xmlstr .= '</DIALTEMPLATE>';
        if (!empty($get_settings['idtemplate'])) {
            if ($get_settings['idtemplate'] == '*new*') {
                if (!empty($get_settings[$hdr_prefix . 'dialtemplate_name'])) {
                    $put_file = (string) $get_settings[$hdr_prefix . 'dialtemplate_name'];
                } else {
                    $errors = array('Fields Dial Plan Name is requered !!');
                }
            } else
                $put_file = (string) $get_settings['idtemplate'];
        } else {
            $errors = array('Fields Dial Plan Name is requered !!');
        }

        if (empty($errors)) {
//            $put_file = 'test';
            $put_file = str_replace(array("\n", "\r", "\t", "/", "\\", ".", ","), '', $put_file);
            $file = $this->sccppath["tftp_DP"] . '/' . $put_file . '.xml';
            file_put_contents($file, $xmlstr);
        }

        return $errors;
    }

    /*
     *      Update Butons Labels on mysql DB
     *      
     */
    private function sccp_db_update_butons($hw_list = array()) {
        
        $save_buttons = array();
        if (!empty($hw_list)) {
            $buton_list = array();
            foreach ($hw_list as $value) {
                $buton_tmp =$this->dbinterface->get_db_SccpTableData("get_sccpdevice_buttons", array('buttontype'=>'speeddial','id'=>$value['name']));
//                die(print_r($buton_tmp,1));
                if (!empty($buton_tmp)) {
                    $buton_list = array_merge($buton_list, $buton_tmp);
                }
            }
        } else {
            $buton_list = $this->dbinterface->get_db_SccpTableData("get_sccpdevice_buttons", array('buttontype'=>'speeddial'));
        }
//        die(print_r($buton_list,1));
        if (empty($buton_list)) {
            return array('Response'=> ' Found 0 device ', 'data'=> '');
        }
        $copy_fld = array('ref','reftype','instance','buttontype');
        $user_list = $user_list = $this->dbinterface->get_db_SccpTableByID("SccpExtension",Array(),'name');
        foreach ($buton_list as $value) {
            $btn_opt = explode(',',$value['options']);
            $btn_id = $btn_opt[0];
            if (!empty($user_list[$btn_id])){
                if ($user_list[$btn_id]['label'] != $value['name']) {
                    $btn_data['name'] = $user_list[$btn_id]['label'];
                    foreach ($copy_fld as $ckey) {
                       $btn_data[$ckey] = $value[$ckey];
                    }
                    $save_buttons[] = $btn_data;
                }
                
            }
        }
        if (empty($save_buttons)) {
            return array('Response'=> 'No update required', 'data'=> ' 0 - records ');
        }
        $res = $this->dbinterface->sccp_save_db("sccpbuttons",  $save_buttons, 'replace', '','');
        return array('Response'=> 'Update records :'.count($save_buttons),'data'=>$res);
    }
    
    /*
     *      Save Config Value to mysql DB
     *      sccp_db_save_setting(empty) - Save All settings from $sccpvalues
     */

    private function sccp_db_save_setting($save_value = array()) {
        global $db;
        global $amp_conf;

        $save_settings = array();

        if (empty($save_value)) {
            foreach ($this->sccpvalues as $key => $val) {
                if (!trim($val['data']) == '') {
                    $save_settings[] = array($key, $db->escapeSimple($val['data']), $val['seq'], $val['type']);
                }
            }
            $this->dbinterface->sccp_save_db('sccpsettings', $save_settings, 'clear');
        } else {
            $this->dbinterface->sccp_save_db('sccpsettings', $save_value, 'update');
            return true;
        }
        return true;
    }

    /*
     *          Create XMLDefault.cnf.xml
     */

    function sccp_create_tftp_XML() {

        foreach ($this->sccpvalues as $key => $value) {
            $data_value[$key] = $value['data'];
        }
        $data_value['server_if_list'] = $this->getIP_information2('ip4');
        $model_information = $this->getSccp_model_information($get = "enabled", $validate = false); // Get Active

        if (empty($model_information))
            $model_information = $this->getSccp_model_information($get = "all", $validate = false); // Get All

        $lang_data = $this->extconfigs->getextConfig('sccp_lang');
        $data_value['tftp_path'] = $this->sccppath["tftp_path"];

        $this->xmlinterface->create_default_XML($this->sccppath["tftp_path_store"], $data_value, $model_information, $lang_data);
    }

    /*
     *          Create  (SEP) dev_ID.cnf.xml
     */

    function sccp_create_device_XML($dev_id = '') {

        if (empty($dev_id)) {
            return false;
        }

        $dev_config = $this->dbinterface->get_db_SccpTableData("get_sccpdevice_byid", array('id' => $dev_id));

        foreach ($this->sccpvalues as $key => $value) {
            $data_value[$key] = $value['data'];
        }
        $data_value['ntp_timezone_id'] = $this->extconfigs->getextConfig('sccp_timezone', $data_value['ntp_timezone']);
        $data_value['server_if_list'] = $this->getIP_information2('ip4');
        $dev_config['tftp_path'] = $this->sccppath["tftp_path"];
        $dev_config['tftp_firmware'] = '';
        /*        if (!empty($this->sccpvalues['tftp_rewrite'])) {
          if ( $this->sccpvalues['tftp_rewrite']['data'] == 'internal' ) {
          $dir_list = $this->find_all_files($dev_config['tftp_path'], $dev_config['loadimage']);
          foreach ($dir_list as $filek){
          if (!empty($filek)) {
          $fnd_path= '';
          $fnd_path = str_replace($dev_config['tftp_path'],'',$filek);
          $fnd_path = substr($fnd_path,1,strpos($fnd_path, $dev_config['loadimage'])-1);
          if (!empty($fnd_path)) {
          $dev_config['tftp_firmware'] = $fnd_path;
          }
          break;
          }
          }
          }
          }
         */
        $dev_config['addon_info'] = array();
        if (!empty($dev_config['addon'])) {
            $hw_addon = explode(',', $dev_config['addon']);
            foreach ($hw_addon as $key) {
                $hw_data = $this->getSccp_model_information('byid', false, "all", array('model' => $key));
                $dev_config['addon_info'][$key] = $hw_data[0]['loadimage'];
            }
        }
        $lang_data = $this->extconfigs->getextConfig('sccp_lang');
//        return $this->sccppath["tftp_path_store"];

        return $this->xmlinterface->create_SEP_XML($this->sccppath["tftp_path_store"], $data_value, $dev_config, $dev_id, $lang_data);
    }

    function sccp_delete_device_XML($dev_id = '') {
        if (empty($dev_id)) {
            return false;
        }
        if ($dev_id == 'all') {
            $xml_name = $this->sccppath["tftp_path_store"] . '/SEP*.cnf.xml';
            array_map("unlink", glob($xml_name));
            $xml_name = $this->sccppath["tftp_path_store"] . '/ATA*.cnf.xml';
            array_map("unlink", glob($xml_name));
            $xml_name = $this->sccppath["tftp_path_store"] . '/VG*.cnf.xml';
            array_map("unlink", glob($xml_name));
        } else {
            if (!strpos($dev_id, 'SEP')) {
                return false;
            }
            $xml_name = $this->sccppath["tftp_path_store"] . '/' . $dev_id . '.cnf.xml';
            if (file_exists($xml_name)) {
                unlink($xml_name);
            }
        }
    }

    private function sccp_create_sccp_backup() {
        global $amp_conf;
        $dir_info = array();
        $backup_files = array($amp_conf['ASTETCDIR'] . '/sccp', $amp_conf['ASTETCDIR'] . '/extensions', $amp_conf['ASTETCDIR'] . '/extconfig',
            $amp_conf['ASTETCDIR'] . '/res_config_mysql', $amp_conf['ASTETCDIR'] . '/res_mysql');
        $backup_ext = array('.conf', '_additional.conf', '_custom.conf');
        $backup_info = $this->sccppath["tftp_path"] . '/sccp_dir.info';

        $result = $this->dbinterface->dump_sccp_tables($this->sccppath["tftp_path"], $amp_conf['AMPDBNAME'], $amp_conf['AMPDBUSER'], $amp_conf['AMPDBPASS']);
        $dir_info['asterisk'] = $this->find_all_files($amp_conf['ASTETCDIR']);
        $dir_info['tftpdir'] = $this->find_all_files($this->sccppath["tftp_path"]);
        $dir_info['driver'] = $this->FreePBX->Core->getAllDriversInfo();
        $dir_info['core'] = $this->srvinterface->getSCCPVersion();
        $dir_info['realtime'] = $this->srvinterface->sccp_realtime_status();
        $dir_info['srvinterface'] = $this->srvinterface->info();
        $dir_info['extconfigs'] = $this->extconfigs->info();
        $dir_info['dbinterface'] = $this->dbinterface->info();
        $dir_info['XML'] = $this->xmlinterface->info();


        $fh = fopen($backup_info, 'w');
        $dir_str = "Begin JSON data ------------\r\n";
        fwrite($fh, $dir_str);
        fwrite($fh, json_encode($dir_info));
        $dir_str = "\r\n\r\nBegin TEXT data ------------\r\n";
        foreach ($dir_info['asterisk'] as $data) {
            $dir_str .= $data . "\r\n";
        }
        foreach ($dir_info['tftpdir'] as $data) {
            $dir_str .= $data . "\r\n";
        }
        fputs($fh, $dir_str);
        fclose($fh);

        $zip = new \ZipArchive();
        $filename = $result . "." . gethostname() .  ".zip";
        if ($zip->open($filename, \ZIPARCHIVE::CREATE)) {
            $zip->addFile($result);
            $zip->addFile($backup_info);
            foreach ($backup_files as $file) {
                foreach ($backup_ext as $b_ext) {
                    if (file_exists($file . $b_ext)) {
                        $zip->addFile($file . $b_ext);
                    }
                }
            }
            $zip->close();
        }
        unlink($backup_info);
        unlink($result);
        return $filename;
    }

    function sccp_create_sccp_init() {
//     Make sccp.conf data        
//     [general]
        foreach ($this->sccpvalues as $key => $value) {
            if ($value['seq'] == 0) {
                switch ($key) {
                    case "allow":
                    case "disallow":
                    case "deny":
                    case "localnet":
                    case "permit":
                        $this->sccp_conf_init['general'][$key] = explode(';', $value['data']);
                        break;
                    case "devlang": 
                        $lang_data = $this->extconfigs->getextConfig('sccp_lang',$value['data']);
                        if (!empty($lang_data)) {
                            $this->sccp_conf_init['general']['phonecodepage'] = $lang_data['codepage'];
                        }
                        break;
                    case "netlang": // Remove Key 
                    case "tftp_path":
                    case "sccp_compatible":
                        break;
                    default:
                        $this->sccp_conf_init['general'][$key] = $value['data'];
                }
            }
        }
//     [Namesoftkeyset]
//      type=softkeyset        

        $this->cnf_wr->writeConfig('sccp.conf', $this->sccp_conf_init);
//        return $this-> sccp_conf_init;        
    }

    function getSccp_model_information($get = "all", $validate = false, $format_list = "all", $filter = array()) {
//        $file_ext = array('.loads', '.LOADS', '.sbn', '.SBN', '.bin', '.BIN','.zup','.ZUP');
        $file_ext = array('.loads', '.sbn', '.bin', '.zup');
//        $dir = $this->sccppath["tftp_path"];
        $dir = $this->sccppath["tftp_firmware_path"];
        $dir_tepl = $this->sccppath["tftp_templates"];

        $search_mode = '';
        if (!empty($this->sccpvalues['tftp_rewrite'])) {
            $search_mode = $this->sccpvalues['tftp_rewrite']['data'];
            if ($search_mode == 'pro') {
                $dir_list = $this->find_all_files($dir, $file_ext, 'fileonly');
            } else {
                $dir_list = $this->find_all_files($dir, $file_ext);
            }
        } else {
            $dir_list = $this->find_all_files($dir, $file_ext, 'fileonly');
        }

        $raw_settings = $this->dbinterface->getDb_model_info($get, $format_list, $filter);

        if ($validate) {
            for ($i = 0; $i < count($raw_settings); $i++) {
                $raw_settings[$i]['validate'] = '-;-';
                if (!empty($raw_settings[$i]['loadimage'])) {
                    $raw_settings[$i]['validate'] = 'no;';
                    if ((strtolower($raw_settings[$i]['vendor'] == 'cisco')) || !empty($dir_list)) {
                        foreach ($dir_list as $filek) {
                            switch ($search_mode) {
                                case 'pro':
                                case 'on':
                                case 'internal':
                                    if (strpos(strtolower($filek), strtolower($raw_settings[$i]['loadimage'])) !== false) {
                                        $raw_settings[$i]['validate'] = 'yes;';
                                    }
                                    break;
                                case 'internal2':

                                    break;
                                case 'off':
                                default: // Place in root TFTP dir
//                                    $raw_settings[$i]['buttons'] = $dir.'/'.$raw_settings[$i]['loadimage'];
                                    if (strpos(strtolower($filek), strtolower($dir . '/' . $raw_settings[$i]['loadimage'])) !== false) {
//                                    if (strpos(strtolower($filek), strtolower($raw_settings[$i]['loadimage'])) !== false) {
                                        $raw_settings[$i]['validate'] = 'yes;';
                                    }
                                    break;
                            }
                        }
                    }
                    /* OLD search                   
                      $file = $dir . '/' . $raw_settings[$i]['loadimage'];
                      if (is_dir($file)) {
                      $file .= '/' . $raw_settings[$i]['loadimage'];
                      }
                      $raw_settings[$i]['validate'] = 'no;';
                      if (strtolower($raw_settings[$i]['vendor']) == 'cisco') {
                      foreach ($file_ext as $value) {
                      if (file_exists($file . $value)) {
                      $raw_settings[$i]['validate'] = 'yes;';
                      break;
                      }
                      }
                      } else {
                      if (file_exists($file)) {
                      $raw_settings[$i]['validate'] = 'yes;';
                      }
                      }
                     * 
                     */
                } else {
                    $raw_settings[$i]['validate'] = '-;';
                }
                if (!empty($raw_settings[$i]['nametemplate'])) {
                    $file = $dir_tepl . '/' . $raw_settings[$i]['nametemplate'];
                    if (file_exists($file)) {
                        $raw_settings[$i]['validate'] .= 'yes';
                    } else {
                        $raw_settings[$i]['validate'] .= 'no';
                    }
                } else {
                    $raw_settings[$i]['validate'] .= '-';
                }
            }
        }
        return $raw_settings;
    }

    function get_hint_info($sort = true, $filter = array()) {
        $res = array();
        $default_hint = '@ext-local';

// get all extension
        $res = $this->aminterface->core_list_all_exten('hint', $filter);
        if (empty($res)) {
//       Old Req get all hints   
            $tmp_data = $this->srvinterface->sccp_list_all_hints();
            foreach ($tmp_data as $value) {
                $res[$value] = array('key' => $value, 'exten' => before('@', $value), 'label' => $value);
            }
        }

// Update info from sccp_db
        $tmp_data = $this->dbinterface->get_db_SccpTableData('SccpExtension');
        foreach ($tmp_data as $value) {
            $name_l = $value['name'];
            if (!empty($res[$name_l . $default_hint])) {
                $res[$name_l . $default_hint]['exten'] = $name_l;
                $res[$name_l . $default_hint]['label'] = $value['label'];
            } else { // if not exist in system hints ..... ???????
                $res[$name_l . $default_hint] = array('key' => $name_l . $default_hint, 'exten' => $name_l, 'label' => $value['label']);
            }
        }
        if (!$sort) {
            return $res;
        }

        foreach ($res as $key => $value) {
            $data_sort[$value['exten']] = $key;
        }
        ksort($data_sort);
        foreach ($data_sort as $key => $value) {
            $res_sort[$value] = $res[$value];
        }

// Update info from sip DB 
        /* !TODO!: Update Hint info from sip DB ??? */

        return $res_sort;
    }

    function getIP_information2($type = '') {
        $interfaces = array();
        switch ($type) {
            case 'ip4':
                exec("/sbin/ip -4 -o addr", $result, $ret);
                break;
            case 'ip6':
                exec("/sbin/ip -6 -o addr", $result, $ret);
                break;

            default:
                exec("/sbin/ip -o addr", $result, $ret);
                break;
        }
        foreach ($result as $line) {
            $vals = preg_split("/\s+/", $line);
            if ($vals[3] == "mtu")
                continue;
            if ($vals[2] != "inet" && $vals[2] != "inet6")
                continue;
            if (preg_match("/(.+?)(?:@.+)?:$/", $vals[1], $res)) {
                continue;
            }
            $ret = preg_match("/(\d*+.\d*+.\d*+.\d*+)[\/(\d*+)]*/", $vals[3], $ip);

            $interfaces[$vals[1] . ':' . $vals[2]] = Array('name' => $vals[1], 'type' => $vals[2], 'ip' => ((empty($ip[1]) ? '' : $ip[1])));
        }
        return $interfaces;
    }

    function getIP_information_old() {
        $interfaces['auto'] = array('0.0.0.0', 'All', '0');

        exec("/sbin/ip -4 -o addr", $result, $ret);
        foreach ($result as $line) {
            $vals = preg_split("/\s+/", $line);

            // We only care about ipv4 (inet) lines, or definition lines
            if ($vals[2] != "inet" && $vals[3] != "mtu")
                continue;

            if (preg_match("/(.+?)(?:@.+)?:$/", $vals[1], $res)) { // Matches vlans, which are eth0.100@eth0
                // It's a network definition.
                // This won't clobber an exsiting one, as it always comes
                // before the IP addresses.
                $interfaces[$res[1]] = array();
                continue;
            }
            if ($vals[4] == "scope" && $vals[5] == "host") {
                $int = 6;
            } else {
                $int = 8;
            }

            // Strip netmask off the end of the IP address
            $ret = preg_match("/(\d*+.\d*+.\d*+.\d*+)[\/(\d*+)]*/", $vals[3], $ip);
            $interfaces[$vals[$int]] = array($ip[1], $vals[$int], ((empty($ip[2]) ? '' : $ip[2])));
        }
//        $int = 0;
//        foreach ($interfaces as $value) {
//            $this->sccpvalues['interfaces_'.$int] = array('keyword' => 'interfaces_'.$value[1], 'data' => $value[0], 'type' => '1', 'seq' => '99');                
//            $int ++;
//        }
        return $interfaces;
    }

    private function replaceSimpleXmlNode($xml, $element = SimpleXMLElement) {
        $dom = dom_import_simplexml($xml);
        $import = $dom->ownerDocument->importNode(
                dom_import_simplexml($element), TRUE
        );
        $dom->parentNode->replaceChild($import, $dom);
    }

    private function appendSimpleXmlNode($xml, $element = SimpleXMLElement) {

        $dom = dom_import_simplexml($xml);
        $import = $dom->ownerDocument->importNode(
                dom_import_simplexml($element), TRUE
        );
//        $dom->parentNode->appendChild($import, $dom);        
        $dom->parentNode->appendChild($import->cloneNode(true));
    }

//    private function removeSimpleXmlNode($node) {
//        $dom = dom_import_simplexml($node);
//        $dom->parentNode->removeChild($dom);
//    }
    private function strpos_array($haystack, $needles) {
        if (is_array($needles)) {
            foreach ($needles as $str) {
                if (is_array($str)) {
                    $pos = strpos_array($haystack, $str);
                } else {
                    $pos = strpos($haystack, $str);
                }
                if ($pos !== FALSE) {
                    return $pos;
                }
            }
        } else {
            return strpos($haystack, $needles);
        }
        return FALSE;
    }

    private function find_all_files($dir, $file_mask = null, $mode = 'full') {

        $result = NULL;
        if (empty($dir) || (!file_exists($dir))) {
            return $result;
        }

        $root = scandir($dir);
        foreach ($root as $value) {
            if ($value === '.' || $value === '..') {
                continue;
            }
            if (is_file("$dir/$value")) {
                $filter = false;
                if (!empty($file_mask)) {
                    if (is_array($file_mask)) {
                        foreach ($file_mask as $k) {
                            if (strpos(strtolower($value), strtolower($k)) !== false) {
                                $filter = true;
                            }
                        }
                    } else {
                        if (strpos(strtolower($value), strtolower($file_mask)) !== false) {
                            $filter = true;
                        }
                    }
                } else {
                    $filter = true;
                }
                if ($filter) {
                    if ($mode == 'fileonly') {
                        $result[] = "$value";
                    } else {
                        $result[] = "$dir/$value";
                    }
                } else {
                    $result[] = null;
                }
                continue;
            }
            $sub_fiend = $this->find_all_files("$dir/$value", $file_mask, $mode);
            if (!empty($sub_fiend)) {
                foreach ($sub_fiend as $sub_value) {
                    if (!empty($sub_value)) {
                        $result[] = $sub_value;
                    }
                }
            }
        }
        return $result;
    }
}
