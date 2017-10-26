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
/*
 * ToDo: 
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
 *  + SRST Config
 *  - Failover config
 *  + Auto Addons!
 *  + DND Mode
 *  + secondary_dialtone_digits = ""     line config
 *  + secondary_dialtone_tone = 0x22     line config                                              
 *  - support kv-store ?????
 *  + Shared Line 
 *  - bug Fix ...(K no w bug? no fix)
 *  - restore default Value on page 
 *  - restore default Value on sccp.class
 *  -  'Device SEP ID.[XXXXXXXXXXXX]=MAC'
 *  -  ATA's start with       ATAXXXXXXXXXXXX.
 *  -  VG248 ports start with VGXXXXXXXXXXXX0. 
 *  * I think this file should be split in 3 parts (as in Model-View-Controller(MVC))
 *    * XML/Database Parts -> Model directory
 *    * Processing parts -> Controller directory
 *    * Ajax Handler Parts -> Controller directory
 *    * Result parts -> View directory
 */

namespace FreePBX\modules;

class Sccp_manager extends \FreePBX_Helpers implements \BMO {
    /* Field Values for type  seq */

//	const General - sccp.conf            = '0';
//	const General - sccp.conf[general]   = '0';
//	const General - sccp.conf[%keyset%]  = '5';  NAME space 
//	const General - sccp.conf[%keyset%]  = '6';  data
//	const General - default.xml          = '10';
//	const General - teplet.xml           = '20';
//	const General - system_path          = '2';
//	const General - don't store          = '99';

//    private $SCCP_LANG_DICTIONARY = 'SCCP-dictionary.xml'; // CISCO LANG file search in /tftp-path 
    private $SCCP_LANG_DICTIONARY = 'be-sccp.jar'; // CISCO LANG file search in /tftp-path 
    private $pagedata = null;
    private $sccp_driver_ver = '11.2';
    private $tftpLang = array();
    private $hint_context = '@ext-local'; /// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! Get it from Config !!!
    private $val_null = 'NONE'; /// REPLACE to null Field
    
    public $sccp_model_list = array();
    private $cnf_wr = null;
    public $sccppath = array();
    public $sccpvalues = array();
    public $sccp_conf_init = array();
    public $xml_data;
    
    public function __construct($freepbx = null) {
        if ($freepbx == null) {
            throw new Exception("Not given a FreePBX Object");
        }
        $this->errors = array();
        $this->FreePBX = $freepbx;
        $this->db = $freepbx->Database;
        $this->cnf_wr = \FreePBX::WriteConfig();
        $this->cnf_read = \FreePBX::LoadConfig();
        $this->v = new \Respect\Validation\Validator();

        $driverNamespace = "\\FreePBX\\Modules\\Sccp_manager";
        if(class_exists($driverNamespace,false)) {
            foreach(glob(__DIR__."/Sccp_manager.inc/*.class.php") as $driver) {
                if(preg_match("/\/([a-z1-9]*)\.class\.php$/i",$driver,$matches)) {
                    $name = $matches[1];
                    $class = $driverNamespace . "\\" . $name;
                    if(!class_exists($class,false)) {
                        include($driver);
                    }
                    if(class_exists($class,false)) {
                        $this->$name = new $class();
                    } else {
                        throw new \Exception("Invalid Class inside in the include folder".print_r($freepbx));
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


        // Load Advanced Form Constuctor Data 
        $xml_vars = __DIR__ . '/conf/sccpgeneral.xml.v'.$this->sccpvalues['sccp_compatible']['data'];
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
                    'tftp_lang' => $this->getTftpLang())
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
                        if ($child['type'] == 'SLD' || $child['type'] == 'SLS' || $child['type'] == 'SLT' || $child['type'] == 'SL' || $child['type'] == 'SLM' || $child['type'] == 'SLZ' || $child['type'] == 'SLZN' || $child['type'] == 'SLA') {
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

    public function doConfigPageInit($page) {
        $this->doGeneralPost();
    }

    public function install() {
        
    }

    public function uninstall() {
        
    }

    public function backup() {
        
    }

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
                        ),
                        "sccpcodec" => array(
                            "name" => _("Device SCCP Codec"),
                            "page" => 'views/server.codec.php'
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
            case 'savesettings':
            case "save_hardware":
            case "save_dp_template":
            case "delete_hardware":
            case "getPhoneGrid":
            case "getExtensionGrid":
            case "getDeviceModel":
            case "getUserGrid":
            case "getSoftKey":
            case "getDialTemplete":
            case "create_hw_tftp":
            case "reset_dev":
            case 'reset_token':
            case "model_enabled":
            case "model_disabled":
            case "model_update":
            case "model_add":
            case "model_delete":
            case "updateSoftKey":
            case "deleteSoftKey":
            case "delete_dialplan":
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
                
                $res = $this->srvinterface->sccp_core_comands(array('cmd' => 'sccp_reload'));
                $msg = 'Config Saved: ' . $res['Response'] . '. Info :' . $res['data'];
//                needreload();
                return array('status' => true, 'message' => $msg, 'reload' => true);
                break;
            case 'save_hardware':
                $this->save_hw_phone($request);
//                return array('status' => true, 'href' => 'config.php?display=sccp_phone',  'reload' => true);
                return array('status' => true, 'search' => '?display=sccp_phone', 'hash' => 'sccpdevice');

                return $this->save_hw_phone($request);

                break;
            case "save_dp_template":
                $res = $this->save_DialPlant($request);
                if (empty($res)) {
                    return array('status' => true, 'search' => '?display=sccp_adv', 'hash' => 'sccpdialplan');
                } else {
                    return array('status' => false, 'message' => print_r($res));
                }
                break;
            case "delete_dialplan":
                if (!empty($request['dialplan'])) {
                    $get_file = $request['dialplan'];
                    $res = $this->del_DialPlant($get_file);
                    return array('status' => true, 'message' => 'Dial Templet is Delete ! ', 'table_reload'=>true);
                } else {
                    return array('status' => false, 'message' => print_r($res));
                }
                break;
            case 'delete_hardware':
                if (!empty($request['idn'])) {
                    foreach ($request['idn'] as $idv) {
                        $msg = strpos($idv, 'SEP-');
                        if (!(strpos($idv, 'SEP') === false)) {
                            $this->dbinterface->sccp_save_db('sccpdevice', array('name' => $idv), 'delete', "name");
                            $this->dbinterface->sccp_save_db("sccpbuttons", array(), 'delete', '', $idv);
                            $this->sccp_delete_device_XML($idv); // Концы в вводу !!  
//                            $this->sccp_core_comands(array('cmd' => 'reload_phone', 'name' => $idv));
                            $this->srvinterface->sccp_core_comands(array('cmd' => 'reset_phone', 'name' => $idv));
                        }
                    }
                    return array('status' => true, 'table_reload' => true, 'message' => 'HW is Delete ! ');
                }
                break;
            case 'create_hw_tftp':
                $this->sccp_delete_device_XML('all'); // Концы в вводу !!  
                $this->sccp_create_tftp_XML();
                $models = $this->dbinterface->get_db_SccpTableData("SccpDevice");
                $ver_id = ' on found active model !';
                foreach ($models as $data) {
                    $ver_id = $this->sccp_create_device_XML($data['name']);
                };
                return array('status' => true, 'message' => 'Create new CNF files Ver :' . $ver_id);

                break;
            case 'reset_token':
            case 'reset_dev':
                $msg = '';
                if (!empty($request['name'])) {
                    foreach ($request['name'] as $idv) {
                        $msg = strpos($idv, 'SEP-');
                        if (!(strpos($idv, 'SEP') === false)) {
                            if ($cmd_id == 'reset_token') {
                                $res = $this->srvinterface->sccp_core_comands(array('cmd' => 'reset_token', 'name' => $idv));
                            } else {
                                $res = $this->srvinterface->sccp_core_comands(array('cmd' => 'reset_phone', 'name' => $idv));
                            }
//                            $msg = print_r($this->sccp_core_comands(array('cmd' => 'reset_phone', 'name' => $idv)), 1);
                            $msg = $res['Response'] . ' ' . $res['data'];
                        }
                        if ($idv == 'all') {
                            $dev_list = $this->srvinterface->sccp_get_active_devise();
                            foreach ($dev_list as $key => $data) {
                                if ($cmd_id == 'reset_token') {
                                    if (($data['token'] == 'Rej') || ($data['status'] == 'Token ') ) {
                                        $res = $this->srvinterface->sccp_core_comands(array('cmd' => 'reset_token', 'name' => $key));
                                        $msg .= 'Send Token reset to :'. $key .' ';
                                    }
                                } else {
                                    $res = $this->srvinterface->sccp_core_comands(array('cmd' => 'reset_phone', 'name' => $key));
                                    $msg .= $res['Response'] . ' ' . $res['data'] . ' ';
                                }    
//                                $msg .= $res['Response'] . ' ' . $res['data'] . ' ';
                            }
                        }
                    }
                }
                return array('status' => true, 'message' => 'Reset comand send ' . $msg, 'reload' => true);
//                }
                break;
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
                            $save_settings[$value] = $val_null; // null
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
                        $this->dbinterface ->sccp_save_db('sccpdevmodel', array('model' => $idv, 'enabled' => $model_set), 'update', "model");
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

            case "deleteSoftKey":
                if (!empty($request['softkey'])) {
                    $id_name = $request['softkey'];
                    unset($this->sccp_conf_init[$id_name]);
                    $this->sccp_create_sccp_init();
                    $msg = print_r($this->srvinterface->sccp_core_comands(array('cmd' => 'sccp_reload')), 1);
                    return array('status' => true, 'table_reload' => true);
                }
                break;
            case "updateSoftKey":
                if (!empty($request['id'])) {
                    $id_name = $request['id'];
                    $this->sccp_conf_init[$id_name]['type'] = "softkeyset";
                    foreach ($this->extconfigs->getextConfig('keyset') as $keyl => $vall) {
                        if (!empty($request[$keyl])) {
                            $this->sccp_conf_init[$id_name][$keyl] = $request[$keyl];
                        }
                    }
                    $this->sccp_create_sccp_init();
                    $msg = print_r($this->srvinterface->sccp_core_comands(array('cmd' => 'sccp_reload')), 1);

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
            case "getExtensionGrid":
                $result = $this->dbinterface->get_db_SccpTableData('SccpExtension');
                if (empty($result)) {
                    $result = array();
                }
                return $result;
                break;
            case "getPhoneGrid":
                $result = $this->dbinterface->get_db_SccpTableData('SccpDevice');
                $staus = $this->srvinterface->sccp_get_active_devise();
                if (empty($result)) {
                    $result = array();
                } else {
//                    $staus = $this->sccp_get_active_devise();
                    foreach ($result as &$dev_id) {
                        $id_name = $dev_id['name'];
                        if (!empty($staus[$id_name])) {
                            $dev_id['description'] = $staus[$id_name]['descr'];
                            $dev_id['status'] = $staus[$id_name]['status'];
                            $dev_id['address'] = $staus[$id_name]['address'];
                            $dev_id['new_hw'] = 'N';
                            $staus[$id_name]['news'] ='N';
                        } else {
                            $dev_id['description'] = '- -';
                            $dev_id['status'] = 'no connect';
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
//                            $dev_data = $this->sccp_getdevice_info($id_name);
                            $dev_addon= $dev_data['SCCP_Vendor']['model_addon'];
                            if (empty($dev_addon)) {
                                $dev_addon = null;
                            }
                            $dev_schema =  $this-> getSccp_model_information('byciscoid', false, "all", array('model' =>$dev_data['SCCP_Vendor']['model_id']));
                            $result[] = array('name' => $id_name, 'mac' => $id_name, 'button' => '---', 'type' => $dev_schema[0]['model'],  'new_hw' => 'Y',
                                'description' => '*NEW* '.$dev_ids['descr'], 'status' => '*NEW* '.$dev_ids['status'], 'address' => $dev_ids['address'], 
                                'addon' => $dev_addon);
                        }
                    }

                }
                return $result;
                break;
                
            case "getDialTemplete":
                $result = $this->get_DP_list();
                if (empty($result)) {
                    $result = array();
                }
                return $result;
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
        foreach ($db_field as $data) {
            $key = (string) $data['Field'];
            $value = "";
            switch ($key) {
                case 'name':
                    if (!empty($get_settings[$hdr_prefix . 'mac'])) {
                        $value = $get_settings[$hdr_prefix . 'mac'];
                        $value = 'SEP' . strtoupper(str_replace(array('.', '-', ':'), '', $value)); // Delete mac Seporated from string
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
                        foreach ($get_settings[$hdr_arprefix. $key] as $vkey => $vval) {
                            $tmp_data = '';
                            foreach ($vval as $vkey => $vval) {
                                    $tmp_data .= $vval . '/';
                            }
                            if (strlen($tmp_data) > 2) {
                                $arr_data .= substr($tmp_data, 0, -1) . ';';
                            }
                        }
                        $arr_data = substr($arr_data, 0, -1);
                        $value = $arr_data;
                    }
                    
            }
            if (!empty($value)) {
                $save_settings[$key] = $value;
            }
        }
//      Save / Updade Base        
        $this->dbinterface->sccp_save_db("sccpdevice", $save_settings, 'replace');

//      Get Model Butons info
        $lines_list = $this->dbinterface->get_db_SccpTableData('SccpExtension');
        $max_btn = ((!empty($get_settings['butonscount']) ? $get_settings['butonscount'] : 100));
        $last_btn = $max_btn;
        for ($it = $max_btn; $it >0; $it--) {
            if (!empty($get_settings['button' . $it . '_type'])) {
                $last_btn = $it;
                $btn_t = $get_settings['button' . $it . '_type'];
                if ($btn_t != 'empty'){
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
                        $db_res = $this-> dbinterface->get_db_SccpTableData('SccpExtension', array('id' => $btn_opt));
                        $btn_n = $db_res[0]['label'];
                        $btn_opt .= ',' . $btn_opt . $this->hint_context;
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
                                    $btn_opt .= ',' . $get_settings['button' . $it . '_hline'] . $this->hint_context;
                                }
                            }
                        }
                        break;
                    case 'adv.line':
                        $btn_t = 'line';
                        $btn_n = (string) $get_settings['button' . $it . '_line'];
                        $btn_n .= '@'.(string)$get_settings['button' . $it . '_advline'];
                        $btn_opt = (string) $get_settings['button' . $it . '_advopt'];
                        
                        break;
                    case 'line':
                    case 'silent':
                        $btn_n = (string) $get_settings['button' . $it . '_line'];
                        if ($it > 0) {
                            if ($btn_t == 'silent') {
                                $btn_n .= '!silent';
                                $btn_t = 'line';
                            }
                        }
                        break;
                    case 'empty':
                        $btn_t = 'empty';
                        break;
                }
                if (!empty($btn_t)) {
                    $save_buttons[] = array('device' => $name_dev, 'instance' => (string) ($it + 1), 'type' => $btn_t, 'name' => $btn_n, 'options' => $btn_opt);
                }
            }
        }

//      Sace Buttons config
        $this->dbinterface ->sccp_save_db("sccpbuttons", $save_buttons, $update_hw, '', $name_dev);

//      Create Device XML 
        $this->sccp_create_device_XML($name_dev);

        if ($hw_id == 'new') {
            $this->srvinterface->sccp_core_comands(array('cmd' => 'reset_phone', 'name' => $name_dev));
        } else {
            $this->srvinterface->sccp_core_comands(array('cmd' => 'reload_phone', 'name' => $name_dev));
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
                            $tmp_data .= $vval . '/';
                        }
                        if (strlen($tmp_data) > 2) {
                            $arr_data .= substr($tmp_data, 0, -1) . ';';
                        }
                    }
                    $arr_data = substr($arr_data, 0, -1);
                    if (!($this->sccpvalues[$key1]['data'] == $arr_data)) {
                        $save_settings[] = array('keyword' => $this->sccpvalues[$key1]['keyword'], 'data' => $arr_data,
                            'seq' => $this->sccpvalues[$key1]['seq'], 'type' => $this->sccpvalues[$key1]['type']);
                    }
                }
            }
            switch ($key) {
                case 'voicecodecs':
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
                        $TZdata = $this-> extconfigs->getextConfig('sccp_timezone',$tz_id);
                        if (!empty($TZdata)){
                            $save_settings[] = array('keyword' => 'tzoffset', 'data' => ($TZdata['offset']/60),
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
    
    public function getSccpSettingFromDB() {
        $raw_data = $this->dbinterface->get_db_SccpSetting();
        foreach ($raw_data as $var) {
            $this->sccpvalues[$var['keyword']] = array('keyword' => $var['keyword'], 'data' => $var['data'], 'seq' => $var['seq'], 'type' => $var['type']);
        }
        return;
        
    }    
    
    function sccp_get_keysetdata($name) {

        if ($name == 'default') {
            $keysetData = sccp_get_confData('softkeyset');
            $keysetData['name'] = 'default';
        } else {
            $keysetData = sccp_get_confData($name);
        }
        $keysetData['name'] = ($keysetData['name'] ? $keysetData['name'] : $name);
        return $keysetData;
    }

    function sccp_edit_keyset($keysetData) {
        global $amp_conf;
        $key_name = array('onhook', 'connected', 'onhold', 'ringin', 'offhook', 'conntrans', 'digitsfoll', 'connconf', 'ringout', 'offhookfeat', 'onhint', 'onstealable');

        $keysetImplode['name'] = $keysetData['name'];
        $keysetImplode['type'] = $keysetData['type'];
        $keysetImplode['file_context'] = $keysetData['file_context'];
        foreach ($key_name as $i) {
            if (isset($keysetData[$i])) {
                $keysetImplode[$i] = implode(',', $keysetData[$i]);
            }
        }
//
// Write config file context section.
//
        $file_context = $keysetData['name'];
        if ($file_context != 'default') {
            $confDir = $amp_conf["ASTETCDIR"];
            if (strlen($confDir) < 1) {
                $confDir = "/etc/asterisk";
            }
            $inputfile = "$confDir/sccp.conf";
            if (!file_exists($inputfile)) {
                $sccpfile = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/sccp.conf');
                file_put_contents($inputfile, $sccpfile);
            }
            $handle = fopen($inputfile, "r");
            $file_context = "[$file_context]";
            $sccpfile = '';

            $conext_data = "$file_context	; Managed by sccp_manager\n";
            $conext_data .= "type=softkeyset\n";
            foreach ($keysetImplode as $field => $value) {
                echo($field);
                if ($field != 'type' && $field != 'name' && $field != 'file_context') {
                    echo($value);
                    if (strlen($value) > 1) {
                        $conext_data .= $field . "=" . $value . "\n";
                    }
                }
            }
            $conext_data .= "\n";


            $new_context = "1";
            echo($file_context);
            if ($handle) {
                while (($input = fgets($handle)) != false) {
                    if (trim($input) != $file_context) {
//		    echo($input);
                        $sccpfile .= $input;
                    } else {
                        $new_context = "0";
                        $sccpfile .= $conext_data;
//
//	We don't include the 'name=' directive in sccp.conf contexts.
//		    $sccpfile .= "name=".$keysetImplode['name']."\n";
//
                        $trimmer = true;
                        while ($trimmer) {
                            $trimmer = ($input = fgets($handle));
                            if (substr($input, 0, 1) == '[') {
                                $trimmer = false;
                                $sccpfile .= $input;
                            }
                        }
                    }
                }
                if ($new_context != "0") {
                    $sccpfile .= $conext_data;
                }
            }
        }
        file_put_contents($inputfile, $sccpfile);
        return $sccpfile;
    }

    function sccp_display_keyset($keysetData, $softkey, $option) {
        if ($keysetData['name'] == 'default') {
            $output = "<font size='+1'>";
            if (strpos(' ' . $keysetData[$softkey], $option)) {
                $output .= '&#x2611;';
            } else {
                $output .= '&#x2610;';
            }
            $output .= "</font>&nbsp;$option<br>";
        } else {
            $output = "<input type='checkbox' name='keysetData[$softkey][]' value='$option'";
            if (strpos(' ' . $keysetData[$softkey], $option)) {
                $output .= ' checked';
            }
            $output .= "> $option<br>";
        }
        return $output;
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

        switch ($type) {
            case 'audio':
                $codecs = $this->getMyConfig('voicecodecs');
                break;
            case 'video':
                $codecs = $this->getConfig('videocodecs');
                break;
            case 'text':
                $codecs = $this->getConfig('textcodecs');
                break;
            case 'image':
                $codecs = $this->getConfig('imagecodecs');
                break;
            default:
                throw new Exception(_('Unknown Type'));
                break;
        }

        if (empty($codecs) || !is_array($codecs)) {
            switch ($type) {
                case 'audio':
                    $codecs = $this->FreePBX->Codecs->getAudio(true);
                    break;
                case 'video':
                    $codecs = $this->FreePBX->Codecs->getVideo(true);
                    break;
                case 'text':
                    $codecs = $this->FreePBX->Codecs->getText(true);
                    break;
                case 'image':
                    $codecs = $this->FreePBX->Codecs->getImage(true);
                    break;
            }
        }

        if ($showDefaults) {
            switch ($type) {
                case 'audio':
                    $allCodecs = $this->FreePBX->Codecs->getAudio();
                    break;
                case 'video':
                    $allCodecs = $this->FreePBX->Codecs->getVideo();
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
        $dir = $this->sccppath["tftp_path"];
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
     *    Chek file enverovments ( xml)
     */

    private function init_tftp_lang_path() {
        $dir = $this->sccppath["tftp_path"];
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
     *    Chek file enverovments ( xml)
     */

    function init_sccp_path() {
        global $db;
        global $amp_conf;        
        
        $confDir = $amp_conf["ASTETCDIR"];
        if (empty($this->sccppath["asterisk"])) {
            if (strlen($confDir) < 1) {
                $this->sccppath["asterisk"] = "/etc/asterisk";
            } else {
                $this->sccppath["asterisk"] = $confDir;
            }
        }

        if (empty($this->sccppath["sccp_conf"])) {
            $this->sccppath["sccp_conf"] = $this->sccppath["asterisk"] . "/sccp.conf";
        }

        if (empty($this->sccppath["tftp_path"])) {
            if (!empty($sccpvalues["tftp_path"])) {
                if (file_exists($this->$sccpvalues["tftp_path"]["data"])) {
                    $this->sccppath["tftp_path"] = $this->$sccpvalues["tftp_path"]["data"];
                }
            }
            if (empty($this->sccppath["tftp_path"])) {
                if (file_exists($this->extconfigs->getextConfig('sccpDefaults',"tftp_path"))) {
                    $this->sccppath["tftp_path"] = $this->extconfigs->getextConfig('sccpDefaults',"tftp_path");
                }
            }
        }
        if (!empty($this->sccppath["tftp_path"])) {
            $this->sccppath["tftp_templates"] = $this->sccppath["tftp_path"] . '/templates';
            if (!file_exists($this->sccppath["tftp_templates"])) {
                if (!mkdir($this->sccppath["tftp_templates"], 0777, true)) {
                    die('Error create template dir');
                }
            }
        }
        if (!empty($this->sccppath["tftp_path"])) {
            $this->sccppath["tftp_DP"] = $this->sccppath["tftp_path"] . '/Dialplan';
            if (!file_exists($this->sccppath["tftp_DP"])) {
                if (!mkdir($this->sccppath["tftp_DP"], 0777, true)) {
                    die('Error create DialPlan template dir');
                }
            }
        }

        if (empty($_SERVER['DOCUMENT_ROOT'])) {
            return;
        }

        if (!file_exists($this->sccppath["tftp_templates"] . '/XMLDefault.cnf.xml_template')) {
            $src_path = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/conf/';
            $dst_path = $this->sccppath["tftp_templates"] . '/';
            foreach (glob($src_path . '*.*_template') as $filename) {
                copy($filename, $dst_path . basename($filename));
            }
        }

        $this->sccpvalues['sccp_compatible'] = array('keyword' => 'compatible', 'data' => $this->srvinterface->get_compatible_sccp(), 'type' => '1', 'seq' => '99');                
//        $this->sccpvalues['sccp_compatible'] = '11';

        $driver = $this->FreePBX->Core->getAllDriversInfo();
        $driver_replace = '';
        if (empty($driver['sccp'])) {
            $driver_replace = 'yes';
        } else {
            if (empty($driver['sccp']['sccp_driver_ver'])) {
                $driver_replace = 'yes';                
            } else {
                if ($driver['sccp']['sccp_driver_ver'] != $this->sccp_driver_ver){
                    $driver_replace = 'yes';                
                }
            }
        }

        $dst = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/core/functions.inc/drivers/Sccp.class.php';
        if (!file_exists($dst) || $driver_replace =='yes') {
            $src_path = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/conf/' . basename($dst).'.v'.$this->sccpvalues['sccp_compatible']['data'];
            if (file_exists($src_path)) {
                copy($src_path, $dst);
            } else {
                $src_path = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/conf/' . basename($dst);
                copy($src_path, $dst);
            }
        } else {
              $driver = $this->FreePBX->Core->getAllDriversInfo();
              

        }

        if (!file_exists($this->sccppath["sccp_conf"])) { // System re Config 
            $sccpfile = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/conf/sccp.conf');
            file_put_contents($this->sccppath["sccp_conf"], $sccpfile);
        }

        $this->sccp_conf_init = $this->cnf_read->getConfig('sccp.conf');

//        $this->sccp_conf_init = @parse_ini_file($this->sccppath["sccp_conf"], true);
    }

    /*
     *      
     *      
     */

    
    function get_DP_list() {
        $dir = $this->sccppath["tftp_DP"].'/*.xml';
        $base_len = strlen($this->sccppath["tftp_DP"])+ 1;
        $res =  glob($dir);
        $dp_list = array();
        foreach ($res as $key => $value) {
            $res[$key] = array('id'=> substr($value,$base_len,-4), 'file' => substr($value,$base_len));
        }
        
        return $res;
    }

    function get_DialPlant($get_file) {
        $file = $this->sccppath["tftp_DP"].'/'.$get_file.'.xml';
        if (file_exists($file)) {
//            $load_xml_data = simplexml_load_file($file);

            $fileContents= file_get_contents($file);
            $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
            $fileContents = trim(str_replace('"', "'", $fileContents));
            $fileContents = strtolower($fileContents);
            $res  = (array) simplexml_load_string($fileContents);     
        }
        return $res;
    }

    function del_DialPlant($get_file) {
        if (!empty($get_file)) {
            $file = $this->sccppath["tftp_DP"].'/'.$get_file.'.xml';
            if (file_exists($file)) {
                $res = unlink($file);
            }
        }
        return $res;
    }

    
    function save_DialPlant($get_settings) {
        $xmlstr = "<DIALTEMPLATE>\n";
        $dialFelds = array('match','timeout','rewrite','tone'); //str -to lo ! 

        $hdr_prefix = 'sccp_dial_';
        $hdr_arprefix = 'sccp_dial-ar_';
        $save_data = array();
        $integer_msg = _("%s must be a non-negative integer");
        $errors = array();
        foreach ($get_settings[$hdr_arprefix.'dialtemplatee'] as $key => $value) {
            $xmlstr .=  '<TEMPLATE';
            if (!empty($value['match'])) {
                foreach ($dialFelds as $fld){
                    if (isset($value[$fld]) ) {
                        if ($value[$fld] == 'empty' || $value[$fld] == '')  {
//                            
                        } else {
                            $xmlstr .=  ' '.$fld.'="'.(string)$value[$fld].'"';
                        }
                    }
                }
            } else {
                $errors = array('Fields "match" is requered !!');
                
            }
            $xmlstr .= "/>\n";
        }
        $xmlstr .= '</DIALTEMPLATE>';
        if (!empty($get_settings['idtemplate'])) {
            if ($get_settings['idtemplate'] == '*new*') {
                if (!empty($get_settings[$hdr_prefix.'dialtemplatee_name'])) {
                    $put_file = (string)$get_settings[$hdr_prefix.'dialtemplatee_name'];
                } else { $errors = array('Fields Dial Plan Name is requered !!'); }
            } else $put_file = (string)$get_settings['idtemplate'];
        } else { $errors = array('Fields Dial Plan Name is requered !!'); }

        if (empty($errors)) {
//            $put_file = 'test';
            $put_file = str_replace(array("\n", "\r", "\t","/","\\",".",","), '', $put_file);            
            $file = $this->sccppath["tftp_DP"].'/'.$put_file.'.xml';
            file_put_contents($file, $xmlstr);
        }
        
        return $errors;
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
        $def_xml_fields = array('authenticationURL', 'informationURL', 'messagesURL', 'servicesURL', 'directoryURL', 'proxyServerURL', 'idleTimeout', 'idleURL');
        $def_xml_locale = array('userLocale', 'networkLocaleInfo', 'networkLocale');
        $xml_name = $this->sccppath["tftp_path"] . '/XMLDefault.cnf.xml';
        $xml_template = $this->sccppath["tftp_templates"] . '/XMLDefault.cnf.xml_template';

        if (file_exists($xml_template)) {
            $xml_work = simplexml_load_file($xml_template);


            $xnode = &$xml_work->callManagerGroup->members;
            if ($this->sccpvalues['bindaddr']['data'] == '0.0.0.0') {
                $ifc = 0;
                foreach ($this->getIP_information() as $value) {
                    if (!empty($value[0])) {
                        if (!in_array($value[0], array('0.0.0.0', '127.0.0.1'), true)) {
                            $xnode_obj = clone $xnode->member;
                            $xnode_obj['priority'] = $ifc;
                            //$xnode_obj =  &$xnode -> member -> callManager;
                            $xnode_obj->callManager->name = $this->sccpvalues['servername']['data'];
                            $xnode_obj->callManager->ports->ethernetPhonePort = $this->sccpvalues['port']['data'];
                            $xnode_obj->callManager->processNodeName = $value[0];
                            if ($ifc === 0) {
                                $this->replaceSimpleXmlNode($xnode->member, $xnode_obj);
                            } else {
                                $this->appendSimpleXmlNode($xnode->member, $xnode_obj);
                            }
                            $ifc ++;
                        }
                    }
                }
            } else {
                $xnode->member['priority'] = '0';
                $xnode_obj = &$xnode->member->callManager;
                $xnode_obj->name = $this->sccpvalues['servername']['data'];
                $xnode_obj->ports->ethernetPhonePort = $this->sccpvalues['port']['data'];
                $xnode_obj->processNodeName = $this->sccpvalues['bindaddr']['data'];
            }
            $this->replaceSimpleXmlNode($xml_work->callManagerGroup->members, $xnode);

            foreach ($def_xml_fields as $value) {
                if (!empty($this->sccpvalues['dev_' . $value])) {
                    $xml_work->$value = trim($this->sccpvalues['dev_' . $value]['data']);
                }
            }
            foreach ($def_xml_locale as $key) {
                if (!empty($xml_work->$key)) {
                    $xnode = &$xml_work->$key;
                    switch ($key) {
                        case 'userLocale':
                        case 'networkLocaleInfo':
                            if ($key == 'networkLocaleInfo') {
                                $lang = $this->sccpvalues['netlang']['data'];
                            } else {
                                $lang = $this->sccpvalues['devlang']['data'];
                            }
//                            configs->getConfig('sccp_lang')
                            $lang_arr =  $this->extconfigs->getextConfig('sccp_lang',$lang);
                            $xnode->name = $lang_arr['locale'];
                            $xnode->langCode = $$lang_arr['code'];
//                            $this -> replaceSimpleXmlNode($xml_work->$key,$xnode); 
                            break;
                        case 'networkLocale':
                            $lang_arr =  $this->extconfigs->getextConfig('sccp_lang',$this->sccpvalues['netlang']['data']);                            
                            $xnode = $lang_arr['language'];
                            break;
                    }
                    //$this-> replaceSimpleXmlNode($xml_work->$value, $xnode );                     
                }
            }

            $msro = $this->getSccp_model_information($get = "enabled", $validate = false); // Get Active
            if (empty($msro))
                $msro = $this->getSccp_model_information($get = "all", $validate = false); // Get All
            foreach ($msro as $var) {
                if (!empty($var['loadinformationid'])) {
                    $node = $xml_work->addChild($var['loadinformationid'], $var['loadimage']);
                    $node->addAttribute('model', $var['vendor'] . ' ' . $var['model']);
                }
            }
            $xml_work->asXml($xml_name);  // Save  XMLDefault1.cnf.xml
            //
            //
            //
//            die(print_r($xml_work));            
        }
    }

    /*
     *          Create  (SEP) dev_ID.cnf.xml
     */

    function sccp_create_device_XML($dev_id = '') {
        $var_xml_general_fields = array('authenticationURL' => 'dev_authenticationURL', 'informationURL' => 'dev_informationURL', 'messagesURL' => 'dev_messagesURL',
            'servicesURL' => 'dev_servicesURL', 'directoryURL' => 'dev_directoryURL', 'proxyServerURL' => 'dev_proxyServerURL', 'idleTimeout' => 'dev_idleTimeout',
            'idleURL' => 'dev_idleURL', 'sshUserId' => 'dev_sshUserId', 'sshPassword' => 'dev_sshPassword', 'deviceProtocol' => 'dev_deviceProtocol'
            );
        $var_xml_general_vars = array('capfAuthMode' => 'null', 'capfList'=> 'null', 'mobility' => 'null', 
                                      'phoneServices' =>'null', 'certHash' =>'null',
                                      'deviceSecurityMode' => '1');
        
        if (empty($dev_id)) {
            return false;
        }
        $var_hw_config = $this->dbinterface->get_db_SccpTableData("get_sccpdevice_byid", array('id' => $dev_id));

        if (empty($var_hw_config)) {
            return false;
        }

        if (!empty($var_hw_config['nametemplate'])) {
            $xml_template = $this->sccppath["tftp_templates"] . '/' . $var_hw_config['nametemplate'];
        } else {
            $xml_template = $this->sccppath["tftp_templates"] . '/SEP0000000000.cnf.xml_79df_template';
        }
        $xml_name = $this->sccppath["tftp_path"] . '/' . $dev_id . '.cnf.xml';
        if (file_exists($xml_template)) {
            $xml_work = simplexml_load_file($xml_template);

            foreach ($var_xml_general_vars as $key => $data) {
                if (isset($xml_work->$key)) {
                    if ($data != 'null') {
                        $xml_work->$key = $data;
                    } else {
                        $node = $xml_work->$key;
                        unset($node[0][0]);
                    }
                }
            }
            
            foreach ($xml_work as $key => $data) {
//              Set System global Values
                if (!empty($var_xml_general_fields[$key])) {
                    $xml_work->$key = $this->sccpvalues[$var_xml_general_fields[$key]]['data'];
                } 
//              Set section Values
                $xml_node = $xml_work->$key;
                switch ($key) {
                    case 'devicePool':
                        $xml_node = $xml_work->$key;
                        foreach ($xml_work->$key->children() as $dkey => $ddata) {
                            switch ($dkey) {
                                case 'dateTimeSetting':
                                    $xnode = &$xml_node->$dkey;
                                    $tz_id = $this->sccpvalues['ntp_timezone']['data'];
                                    $TZdata = $this-> extconfigs->getextConfig('sccp_timezone',$tz_id);
                                    if (empty($TZdata)){
                                        $TZdata = array('offset' => '0', 'daylight' => '');
                                    }
                                    $xnode->name = $tz_id;
                                    $xnode->dateTemplate = $this->sccpvalues['dateformat']['data'];
                                    $xnode->timeZone = $tz_id.((empty($TZdata['daylight']))? '': '/'.$TZdata['daylight']);

                                    if ($this->sccpvalues['ntp_config_enabled']['data'] == 'yes') {
                                        $xnode->ntps->ntp->name = $this->sccpvalues['ntp_server']['data'];
                                        $xnode->ntps->ntp->ntpMode = $this->sccpvalues['ntp_server_mode']['data'];
                                    } else {
                                        $xnode->ntps = '';
                                    }
                                    // Ntp Config
                                    break;
                                case 'srstInfo':
                                    if ($this->sccpvalues['srst_Option']['data'] == 'user') {
                                        break;
                                    }
                                    $xnode = &$xml_node->$dkey;
                                    $xnode -> name = $this->sccpvalues['srst_Name']['data'];
                                    $xnode -> srstOption = $this->sccpvalues['srst_Option']['data'];
                                    $xnode -> userModifiable = $this->sccpvalues['srst_userModifiable']['data'];
                                    $xnode -> isSecure = $this->sccpvalues['srst_isSecure']['data'];

                                    $srst_fld = array('srst_ip' => array('ipAddr','port') );
//                                    $srst_fld = array('srst_ip' => array('ipAddr','port') , 'srst_sip' => array('sipIpAddr','sipPort') );
                                    foreach ($srst_fld as $srst_pro => $srs_put){
                                        $srst_data = explode(';', $this->sccpvalues[$srst_pro]['data']);
                                        $si = 1;
//                                        $xnode['test'] = $srst_data[0];
                                        foreach ($srst_data as $value) {
                                            $srs_val =  explode('/',$value);
                                            $nod = $srs_put[0].$si;
                                            $xnode -> $nod = $srs_val[0];
                                            $nod = $srs_put[1].$si;
                                            $xnode -> $nod = $srs_val[1];
                                            $si ++;
                                        }
                                       while ($si < 4) {
                                            $nod = $srs_put[0].$si;
                                            $xnode -> $nod = '';
                                            $nod = $srs_put[1].$si;
                                            $xnode -> $nod = '';
                                            $si ++;
                                        }
                                    }
                                    break;
                                case 'connectionMonitorDuration':
                                    $xml_node->$dkey =  strval(intval(intval($this->sccpvalues['keepalive']['data'])* 0.75));
                                    break;
                                case 'callManagerGroup':
                                    $xnode = &$xml_node->$dkey->members;
                                    if ($this->sccpvalues['bindaddr']['data'] == '0.0.0.0') {
                                        $ifc = 0;
                                        foreach ($this->getIP_information() as $value) {
                                            if (!empty($value[0])) {
                                                if (!in_array($value[0], array('0.0.0.0', '127.0.0.1'), true)) {
                                                    $xnode_obj = clone $xnode->member;
//                                                $xnode_obj = $xnode -> member;
//                                                $xnode_obj = $xnode -> addChild($xnode->member);
                                                    $xnode_obj['priority'] = $ifc;
                                                    //$xnode_obj =  &$xnode -> member -> callManager;
                                                    $xnode_obj->callManager->name = $this->sccpvalues['servername']['data'];
                                                    $xnode_obj->callManager->ports->ethernetPhonePort = $this->sccpvalues['port']['data'];
                                                    $xnode_obj->callManager->processNodeName = $value[0];
                                                    if ($ifc === 0) {
                                                        $this->replaceSimpleXmlNode($xnode->member, $xnode_obj);
                                                    } else {
                                                        $this->appendSimpleXmlNode($xnode->member, $xnode_obj);
                                                    }
                                                    $ifc ++;
                                                }
                                            }
                                        }
                                    } else {
                                        $xnode->member['priority'] = '0';
                                        $xnode_obj = &$xnode->member->callManager;
                                        $xnode_obj->name = $this->sccpvalues['servername']['data'];
                                        $xnode_obj->ports->ethernetPhonePort = $this->sccpvalues['port']['data'];
                                        $xnode_obj->processNodeName = $this->sccpvalues['bindaddr']['data'];
                                    }
                                    break;
                            }
                        }
                        $this->replaceSimpleXmlNode($xml_work->$key, $xml_node);
                        break;
                    case 'versionStamp':
                        $xml_work->$key = time();
                        break;
                    case 'loadInformation':
                        $xml_work->$key = $var_hw_config["loadimage"];
                        if (!empty($var_hw_config['addon'])) {
                            $hw_addon = explode(',', $var_hw_config['addon']);
                            $xnode = $xml_work->addChild('addOnModules');
                            $ti = 1;
                            foreach ($hw_addon as $key) {
                                $hw_inf = $this->getSccp_model_information('byid', false, "all", array('model' => $key));
                                $xnode_obj = $xnode->addChild('addOnModule');
//                                if $hw_inf['loadimage']
                                $xnode_obj->addAttribute('idx', $ti);
                                $xnode_obj->addChild('loadInformation', $hw_inf[0]['loadimage']);
                                $ti ++;
                            }
//                            $this->appendSimpleXmlNode($xml_work , $xnode_obj);
                        }
                        break;
                    case 'userLocale':
                    case 'networkLocaleInfo':
                    case 'networkLocale':
                        $hwlang = '';
                        $lang = '';
                        if (!empty($var_hw_config["_hwlang"])) {
                            $hwlang = explode(':', $var_hw_config["_hwlang"]);
                        }
                        if (($key == 'networkLocaleInfo') || ($key == 'networkLocale')) {
//                            $lang=$this->sccpvalues['netlang']['data'];
                            $lang = (empty($hwlang[0])) ? $this->sccpvalues['netlang']['data'] : $hwlang[0];
                        } else {
                            $lang = (empty($hwlang[1])) ? $this->sccpvalues['devlang']['data'] : $hwlang[1];
//                            $lang=$this->sccpvalues['devlang']['data'];
                        }
                        if (($lang  !='null') && (!empty(trim($lang)))) {
                            if ($key == 'networkLocale') {
                                $xml_work->$key = $lang;
                            } else {
                                $lang_arr =  $this->extconfigs->getextConfig('sccp_lang',$lang);
                                if (!empty($lang_arr)) {
                                    $xml_node->name = $lang_arr['locale'];
                                    $xml_node->langCode = $lang_arr['code'];
                                    $this->replaceSimpleXmlNode($xml_work->$key, $xml_node);
                                }
                            }
                        } else {    
                            $xml_work->$key ='';
                        }
                        break;
// Move all set to $var_xml_general_vars
//                    case 'mobility':
//                    case 'capfList':
//                       break;
//                    case 'phoneServices':
//                        break;
//                        $xml_work->$key = '';
                    default:
                        break;
                }
            }

//            print_r($xml_work);
            $xml_work->asXml($xml_name);  // Save  
        } else {
            die('Error Hardware templatee :' . $xml_template . ' not found');
        }
        return time();
    }

    function sccp_delete_device_XML($dev_id = '') {
        if (empty($dev_id)) {
            return false;
        }
        if ($dev_id =='all') {
            $xml_name = $this->sccppath["tftp_path"] . '/SEP*.cnf.xml';
            array_map("unlink", glob($xml_name));
        } else {
            if  (!strpos($dev_id,'SEP')) {
                return false;
            }
            $xml_name = $this->sccppath["tftp_path"] . '/' . $dev_id . '.cnf.xml';
            if (file_exists($xml_name)) {
                unlink($xml_name);
            }
        }
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
                    case "netlang": // Remove Key 
                    case "devlang":
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
        $file_ext = array('.loads', '.LOADS', '.sbn', '.SBN', '.bin', '.BIN');
        $dir = $this->sccppath["tftp_path"];
        $dir_tepl = $this->sccppath["tftp_templates"];

        $raw_settings = $this-> dbinterface -> getDb_model_info($get,  $format_list, $filter) ;
        
        if ($validate) {
            for ($i = 0; $i < count($raw_settings); $i++) {
                $raw_settings[$i]['validate'] = '-;-';
                if (!empty($raw_settings[$i]['loadimage'])) {
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
    

    function getIP_information() {
        $interfaces['auto'] = array('0.0.0.0', 'All', '0');

        exec("/sbin/ip -o addr", $result, $ret);
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
}
