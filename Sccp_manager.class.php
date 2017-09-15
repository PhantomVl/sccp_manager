<?php

//namespace FreePBX\modules;
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
// https://github.com/chan-sccp/chan-sccp/wiki/Setup-FreePBX
// http://chan-sccp-b.sourceforge.net/doc/setup_sccp.xhtml
// https://github.com/chan-sccp/chan-sccp/wiki/Conferencing
// https://github.com/chan-sccp/chan-sccp/wiki/Frequently-Asked-Questions
// http://chan-sccp-b.sourceforge.net/doc/_howto.xhtml#nf_adhoc_plar

namespace FreePBX\modules;

class Sccp_manager extends \FreePBX_Helpers implements \BMO {
    /* Field Values for type  seq */

//	const General - sccp.conf            = '0';
//	const General - sccp.conf[general]   = '0';
//	const General - sccp.conf[%keyset%]  = '5';  NAME space 
//	const General - sccp.conf[%keyset%]  = '6';  data
//	const General - default.xml          = '10';
//	const General - system_path          = '2';
//	const General - don't store          = '99';
 
    private $SCCP_LANG_DICTIONARY = 'SCCP-dictionary.xml'; // CISCO LANG file search in /tftp-path 
    private $pagedata = null;
    private $tftpLang = array();
    private $hint_context = '@ext-local'; /// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! Get it from Config !!!
    public $sccp_model_list = array();
    private $cnf_wr = null;
    public $sccppath = array();
    public $sccpvalues = array();
    public $sccp_conf_init = array();
    public $sccpDefaults = array(
        "servername" => 'VPBXSCCP',
        "bindaddr" => '0.0.0.0', "port" => '2000',
        "deny" => '0.0.0.0/0.0.0.0',
        "permit" => '0.0.0.0/0.0.0.0',
        "dateformat" => 'D.M.Y',
        "disallow" => 'all', "allow" => 'alaw;ulaw',
        "devicetable" => 'sccpdevice',
        "linetable" => 'sccpline',
        "tftp_path" => '/tftpboot'
    );
    public $xml_data;
    public $keysetdefault = array('onhook' => 'redial,newcall,cfwdall,dnd,pickup,gpickup,private',
        'connected' => 'hold,endcall,park,vidmode,select,cfwdall,cfwdbusy,idivert',
        'onhold' => 'resume,newcall,endcall,transfer,conflist,select,dirtrfr,idivert,meetme',
        'ringin' => 'answer,endcall,transvm,idivert',
        'offhook' => 'redial,endcall,private,cfwdall,cfwdbusy,pickup,gpickup,meetme,barge',
        'conntrans' => 'hold,endcall,transfer,conf,park,select,dirtrfr,vidmode,meetme,cfwdall,cfwdbusy',
        'digitsfoll' => 'back,endcall,dial',
        'connconf' => 'conflist,newcall,endcall,hold,vidmode',
        'ringout' => 'empty,endcall,transfer,cfwdall,idivert',
        'offhookfeat' => 'redial,endcall',
        'onhint' => 'redial,newcall,pickup,gpickup,barge',
        'onstealable' => 'redial,newcall,cfwdall,pickup,gpickup,dnd,intrcpt',
        'holdconf' => 'resume,newcall,endcall,join',
        'uriaction' => 'default');
//   Cisco  Language Code / Directory  
    public $sccp_lang = array('ar_SA' => array('code' => 'ar', 'language' => 'Arabic', 'locale' => 'Arabic_Saudi_Arabia'),
        'bg_BG' => array('code' => 'bg', 'language' => 'Bulgarian', 'locale' => 'Bulgarian_Bulgaria'),
        'cz_CZ' => array('code' => 'cz', 'language' => 'Czech', 'locale' => 'Czech_Czech_Republic'),
        'da_DK' => array('code' => 'da', 'language' => 'Danish', 'locale' => 'Danish_Denmark'),
        'de_DE' => array('code' => 'de', 'language' => 'German', 'locale' => 'German_Germany'),
        'el_GR' => array('code' => 'el', 'language' => 'Greek', 'locale' => 'Greek_Greece'),
        'en_AU' => array('code' => 'en', 'language' => 'English', 'locale' => 'AU_English_United_States'),
        'en_GB' => array('code' => 'en', 'language' => 'English', 'locale' => 'English_United_Kingdom'),
        'en_US' => array('code' => 'en', 'language' => 'English', 'locale' => 'English_United_States'),
        'es_ES' => array('code' => 'es', 'language' => 'Spanish', 'locale' => 'Spanish_Spain'),
        'et_EE' => array('code' => 'et', 'language' => 'Estonian', 'locale' => 'Estonian_Estonia'),
        'fi_FI' => array('code' => 'fi', 'language' => 'Finnish', 'locale' => 'Finnish_Finland'),
        'fr_CA' => array('code' => 'fr', 'language' => 'French', 'locale' => 'French_Canada'),
        'fr_FR' => array('code' => 'fr', 'language' => 'French', 'locale' => 'French_France'),
        'he_IL' => array('code' => 'he', 'language' => 'Hebrew', 'locale' => 'Hebrew_Israel'),
        'hr_HR' => array('code' => 'hr', 'language' => 'Croatian', 'locale' => 'Croatian_Croatia'),
        'hu_HU' => array('code' => 'hu', 'language' => 'Hungarian', 'locale' => 'Hungarian_Hungary'),
        'it_IT' => array('code' => 'it', 'language' => 'Italian', 'locale' => 'Italian_Italy'),
        'ja_JP' => array('code' => 'ja', 'language' => 'Japanese', 'locale' => 'Japanese_Japan'),
        'ko_KO' => array('code' => 'ko', 'language' => 'Korean', 'locale' => 'Korean_Korea_Republic'),
        'lt_LT' => array('code' => 'lt', 'language' => 'Lithuanian', 'locale' => 'Lithuanian_Lithuania'),
        'lv_LV' => array('code' => 'lv', 'language' => 'Latvian', 'locale' => 'Latvian_Latvia'),
        'nl_NL' => array('code' => 'nl', 'language' => 'Dutch', 'locale' => 'Dutch_Netherlands'),
        'no_NO' => array('code' => 'no', 'language' => 'Norwegian', 'locale' => 'Norwegian_Norway'),
        'pl_PL' => array('code' => 'pl', 'language' => 'Polish', 'locale' => 'Polish_Poland'),
        'pt_BR' => array('code' => 'pt', 'language' => 'Portuguese', 'locale' => 'Portuguese_Brazil'),
        'pt_PT' => array('code' => 'pt', 'language' => 'Portuguese', 'locale' => 'Portuguese_Portugal'),
        'ro_RO' => array('code' => 'ro', 'language' => 'Romanian', 'locale' => 'Romanian_Romania'),
        'ru_RU' => array('code' => 'ru', 'language' => 'Russian', 'locale' => 'Russian_Russian_Federation'),
        'sk_SK' => array('code' => 'sk', 'language' => 'Slovakian', 'locale' => 'Slovak_Slovakia'),
        'sl_SL' => array('code' => 'sl', 'language' => 'Slovenian', 'locale' => 'Slovenian_Slovenia'),
        'sr_ME' => array('code' => 'sr', 'language' => 'Serbian', 'locale' => 'Serbian_Republic_of_Montenegro'),
        'sr_RS' => array('code' => 'rs', 'language' => 'Serbian', 'locale' => 'Serbian_Republic_of_Serbia'),
        'sv_SE' => array('code' => 'sv', 'language' => 'Swedish', 'locale' => 'Swedish_Sweden'),
        'th_TH' => array('code' => 'th', 'language' => 'Thailand', 'locale' => 'Thai_Thailand'),
        'tr_TR' => array('code' => 'tr', 'language' => 'Turkish', 'locale' => 'Turkish_Turkey'),
        'zh_CN' => array('code' => 'cn', 'language' => 'Chinese', 'locale' => 'Chinese_China'),
        'zh_TW' => array('code' => 'zh', 'language' => 'Chinese', 'locale' => 'Chinese_Taiwan')
    );

    public function __construct($freepbx = null) {
        if ($freepbx == null) {
            throw new Exception("Not given a FreePBX Object");
        }
        $this->errors = array();
        $this->FreePBX = $freepbx;
        $this->db = $freepbx->Database;
        $this->cnf_wr = \FreePBX::WriteConfig();
        $this->cnf_read =\FreePBX::LoadConfig();
        
        
        $this->v = new \Respect\Validation\Validator();

        $this->getSccpSettingsDB(false); // Overwrite Exist 
//        $this->getSccpSetingINI(false); // get from sccep.ini
        $this->init_sccp_path();
        $this->initVarfromDefs();
        $this->initTftpLang();
        

        // Load Advanced Form Constuctor Data        
        if (file_exists(__DIR__ . '/views/sccpgeneral.xml')) {
            $this->xml_data = simplexml_load_file(__DIR__ . '/views/sccpgeneral.xml');
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
//                 $htmlret .= print_r($item,1);
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
        foreach ($this->sccpDefaults as $key => $value) {
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
                        if ($child['type'] == 'IS' || $child['type'] == 'IED' ) {
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
                        if ($child['type'] == 'SLD' || $child['type'] == 'SLS' || $child['type'] == 'SLT' || $child['type'] == 'SL' || $child['type'] == 'SLM' || $child['type'] == 'SLZ' || $child['type'] == 'SLZN') {
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
            case 'sccp_phone':
                if (empty($request['tech_hardware'])) {
                    break;
                }
                $buttons = array(
                    'submit' => array(
                        'name' => 'ajaxsubmit',
                        'id' => 'ajaxsubmit',
                        'value' => _("Submit")
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
                "sccpkeyset" => array(
                    "name" => _("SCCP Device Keyset"),
                    "page" => 'views/server.keyset.php'
                ),
                "sccpmodels" => array(
                    "name" => _("SCCP Model information"),
                    "page" => 'views/server.model.php'
                )
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

    public function ajaxRequest($req, &$setting) {
        switch ($req) {
            case 'savesettings':
            case "save_hardware":
            case "delete_hardware":
            case "getPhoneGrid":
            case "getExtensionGrid":
            case "getDeviceModel":
            case "getUserGrid":
            case "getSoftKey":
            case "create_hw_tftp":
            case "reset_dev":
            case "model_enabled":
            case "model_disabled":
            case "model_update":
            case "model_add":
            case "model_delete":
            case "updateSoftKey":
            case "deleteSoftKey":
                return true;
                break;
        }
        return false;
    }

    public function ajaxHandler() {
        $request = $_REQUEST;
        $msg = '';
        switch ($request['command']) {
            case 'savesettings':
                $action = isset($request['sccp_createlangdir']) ? $request['sccp_createlangdir'] : '';
                if ($action == 'yes') {
                    $this->init_tftp_lang_path();
                }
                $this->save_submit($request);
                $this->sccp_db_save_setting();
                $res = $this->sccp_core_comands(array('cmd' => 'sccp_reload'));
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
            case 'delete_hardware':
                if (!empty($request['idn'])) {
                    foreach ($request['idn'] as $idv) {
                        $msg = strpos($idv, 'SEP-');
                        if (!(strpos($idv, 'SEP') === false)) {
                            $this->sccp_save_db('sccpdevice', array('name' => $idv), 'delete', "name");
                            $this->sccp_save_db("sccpbuttons", array(), 'delete', '', $idv);
                        }
                    }
                    return array('status' => true, 'table_reload' => true, 'message' => 'HW is Delete ! ');
                }
                break;
            case 'create_hw_tftp':
                $this->sccp_create_tftp_XML();
                $models = $this->get_db_SccpTableData("SccpDevice");
                foreach ($models as $data) {
                    $ver_id = $this->sccp_create_device_XML($data['name']);
                };
                return array('status' => true, 'message' => 'Create new CNF files Ver :' . $ver_id);

                break;
            case 'reset_dev':
                $msg = '';
                if (!empty($request['name'])) {
                    foreach ($request['name'] as $idv) {
                        $msg = strpos($idv, 'SEP-');
                        if (!(strpos($idv, 'SEP') === false)) {
                            $res = $this->sccp_core_comands(array('cmd' => 'reset_phone', 'name' => $idv));
//                            $msg = print_r($this->sccp_core_comands(array('cmd' => 'reset_phone', 'name' => $idv)), 1);
                            $msg = $res['Response'] . ' ' . $res['data'];
                        }
                        if ($idv == 'all') {
                            $dev_list = $this->sccp_get_active_devise();
                            foreach ($dev_list as $key => $data) {
                                $res = $this->sccp_core_comands(array('cmd' => 'reset_phone', 'name' => $key));
                                $msg .= $res['Response'] . '' . $res['data'] . ' ';
//                                $msg = print_r($this->sccp_core_comands(array('cmd' => 'reset_phone', 'name' => $key)), 1);
                            }
                        }
                    }
                }
                return array('status' => true, 'message' => 'Reset comand send ' . $msg, 'reload' => true);
//                }
                break;
            case 'model_add':
                $save_settings = array();
                $key_name = array('model', 'vendor', 'dns', 'buttons', 'loadimage', 'loadinformationid', 'nametemplet');
                $upd_mode = 'replace';
            case 'model_update':
                if ($request['command'] == 'model_update') {
                    $key_name = array('model', 'loadimage', 'nametemplet');
                    $upd_mode = 'update';
                }
                if (!empty($request['model'])) {
                    foreach ($key_name as $key => $value) {
                        if (!empty($request[$value])) {
                            $save_settings[$value] = $request[$value];
                        } else {
                            $save_settings[$value] = 'none';
                        }
                    }
                    $this->sccp_save_db('sccpdevmodel', $save_settings, $upd_mode, "model");
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
                        $this->sccp_save_db('sccpdevmodel', array('model' => $idv, 'enabled' => $model_set), 'update', "model");
                    }
                }
                return array('status' => true, 'table_reload' => true);

                break;
            case 'model_delete':
                if (!empty($request['model'])) {
                    $this->sccp_save_db('sccpdevmodel', array('model' => $request['model']), 'delete', "model");
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
                    $msg = print_r($this->sccp_core_comands(array('cmd' => 'sccp_reload')), 1);
                    return array('status' => true, 'table_reload' => true);
                }
                break;
            case "updateSoftKey":
                if (!empty($request['id'])) {
                    $id_name = $request['id'];
                    $this->sccp_conf_init[$id_name]['type'] = "softkeyset";
                    foreach ($this->keysetdefault as $keyl => $vall) {
                        if (!empty($request[$keyl])) {
                            $this->sccp_conf_init[$id_name][$keyl] = $request[$keyl];
                        }
                    }
                    $this->sccp_create_sccp_init();
                    $msg = print_r($this->sccp_core_comands(array('cmd' => 'sccp_reload')), 1);

                    return array('status' => true, 'table_reload' => true);
//                    return $this->sccp_conf_init[$id_name];
                }

//                    sccp_conf_init
                break;
            case 'getSoftKey':
                $result = array();
                $i = 0;
                $keyl = 'default';
                foreach ($this->sccp_list_keysets() as $keyl => $vall) {
                    $result[$i]['softkeys'] = $keyl;
                    if ($keyl == 'default') {
                        foreach ($this->keysetdefault as $key => $value) {
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
                $result = $this->get_db_SccpTableData('SccpExtension');
                if (empty($result)) {
                    $result = array();
                }
                return $result;
                break;
            case "getPhoneGrid":
                $result = $this->get_db_SccpTableData('SccpDevice');
                if (empty($result)) {
                    $result = array();
                } else {
                    $staus = $this->sccp_get_active_devise();
                    foreach ($result as &$dev_id) {
                        $id_name = $dev_id['name'];
                        if (!empty($staus[$id_name])) {
                            $dev_id['description'] = $staus[$id_name]['descr'];
                            $dev_id['status'] = $staus[$id_name]['status'];
                            $dev_id['address'] = $staus[$id_name]['address'];
                        } else {
                            $dev_id['description'] = '- -';
                            $dev_id['status'] = 'no connect';
                            $dev_id['address'] = '- -';
                        }
                    }
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
        $save_buttons = array();
        $save_settings = array();
        $save_codec = array();
        $def_feature = array('parkinglot' => array('name'=>'P.slot','value' => 'default'),
                             'devstate' => array('name'=> 'Coffee', 'value' => 'coffee'),
                             'monitor' => array('name'=>'Record Calls', 'value'=>'')
                            );
        $name_dev = '';
        $db_field = $this->get_db_SccpTableData("get_colums_sccpdevice");
        $hw_id = (empty($get_settings['sccp_deviceid'])) ? 'new' : $get_settings['sccp_deviceid'];
        $update_hw = ($hw_id == 'new') ? 'update' : 'clear';
        foreach ($db_field as $data) {
            $key = (string) $data['Field'];
            $value = "";
            switch ($key) {
                case 'permit':
                case 'deny':
                    $value = $get_settings[$hdr_prefix . $key . '_net'] . '/' . $get_settings[$hdr_prefix . $key . '_mask'];
                    break;
                case 'name':
                    if (!empty($get_settings[$hdr_prefix . 'mac'])) {
                        $value = 'SEP' . $get_settings[$hdr_prefix . 'mac'];
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
                case 'hwlang':
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
            }
            if (!empty($value)) {
                $save_settings[$key] = $value;
            }
        }
//      Save / Updade Base        
        $this->sccp_save_db("sccpdevice", $save_settings, 'replace');
//        return print_r($save_settings,1);
//      Get Model Butons info
        $lines_list = $this->get_db_SccpTableData('SccpExtension');
        $max_btn = ((!empty($get_settings['butonscount']) ? $get_settings['butonscount'] : 100));
        for ($it = 0; $it < $max_btn; $it++) {
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
                                $btn_opt .= ','.$def_feature[$btn_f]['value'];
                            } else {
                                $btn_opt .= ','.$get_settings['button' . $it . '_fvalue'];
                            }
                        } 
                        break;
                    case 'monitor':
                        $btn_t = 'speeddial';
                        $btn_opt = (string) $get_settings['button' . $it . '_line'];
                        $db_res = $this->get_db_SccpTableData('SccpExtension',array('id'=>$btn_opt));
                        $btn_n = $db_res[0]['label'];
                        $btn_opt .= ',' . $btn_opt. $this->hint_context;                        
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
                        $btn_t = '';
                        break;
                }
                if (!empty($btn_t)) {
                    $save_buttons[] = array('device' => $name_dev, 'instance' => (string) ($it + 1), 'type' => $btn_t, 'name' => $btn_n, 'options' => $btn_opt);
                }
            }
        }
//      Sace Buttons config
        $this->sccp_save_db("sccpbuttons", $save_buttons, $update_hw, '', $name_dev);
//      Create Device XML 
        $this->sccp_create_device_XML($name_dev);
//      sccp restart  
//        $this->sccp_core_comands(array('cmd'=>'reset_phone', 'name' => $name_dev));
        $this->sccp_core_comands(array('cmd'=>'reload_phone', 'name' => $name_dev));
//        die();
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
                        $tmp_data ='';
                        foreach ($vval as $vkey => $vval) {
                            $tmp_data .= $vval. '/';
                        }                        
                        if (strlen($tmp_data)>2){
                            $arr_data .= substr($tmp_data,0,-1).';';
                        }
                    }
                    $arr_data = substr($arr_data,0,-1);
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
            }
        }
        if (!empty($save_settings)) {
            $this->sccp_db_save_setting($save_settings);
            $this->getSccpSettingsDB();         // Overwrite Exist 
            $this->sccp_create_sccp_init();
        }
        $save_settings[] = array('status' => true);
        return $save_settings;
    }

    /**
     * 
     * Core Comsnd Interface 
     * 
     * 
     */
    public function sccp_core_comands($params = array()) {
        global $astman;
        $cmd_list = array('get_softkey' => array('cmd' => "sccp show softkeyssets", 'param' => ''),
            'get_device' => array('cmd' => "sccp show devices", 'param' => ''),
            'get_hints' => array('cmd' => "core show hints", 'param' => ''),
            'sccp_reload' => array('cmd' => "sccp reload force", 'param' => ''),
            'reset_phone' => array('cmd' => "sccp reset ", 'param' => 'name'), // Жесткая перезагрузка 
            'reload_phone' => array('cmd' => "sccp reload device ", 'param' => 'name'),
        );
        $result = true;
        if (!empty($params['cmd'])) {
            $id_cmd = $params['cmd'];
            if (!empty($cmd_list[$id_cmd])) {
                $id_param = $cmd_list[$id_cmd]['param'];
                if (!empty($id_param)) {
                    if (!empty($params[$id_param])) {
                        $result = $astman->Command($cmd_list[$id_cmd]['cmd'] . $params[$id_param]);
                    }
                } else {
                    $result = $astman->Command($cmd_list[$id_cmd]['cmd']);
                }
            } else {
                switch ($params['cmd']) {
                    case 'phone_call':
                        if (!empty($params['name'])) {
                            $result = $astman->Command('sccp call ' . $params['name'] . ' ' . $params['call']);
                        }
                        break;
                    case 'phone_message':
                        if (!empty($params['msg'])) {
                            $msg = $params['msg'];
                        } else {
                            $msg = $this->sccpvalues['servername']['data'];
                        }
                        if (!empty($params['name'])) {
                            $astman->Command('sccp device ' . $params['name'] . ' ' . $msg);
                        } else {
                            
                        }
                        break;
                    default:
                        $result = false;
                        break;
                }
            }
        }
        return $result;
    }

    /**
     * 
     * return Html_ Input control code---------------------------------------------------------------------------------------temp
     * 
     * 
     */
    public function sccp_list_hints() {
        $ast_out = $this->sccp_core_comands(array('cmd' => 'get_hints'));
        $ast_out = preg_split("/[\n]/", $ast_out['data']);
        $ast_key = array();
        for ($i = 0; $i < 3; $i++) {
            $ast_out[$i] = "";
        }
        $i = count($ast_out) - 1;
        $ast_out[--$i] = "";
        $ast_out[--$i] = "";
        foreach ($ast_out as $line) {
            if (strlen($line) > 3) {
                list ($line, $junk) = explode(' ', $line);
                if (isset($ast_key[$line])) {
                    if (strlen($ast_key[$line]) < 1) {
                        $ast_key[$line] = $line;
                    }
                } else {
                    $ast_key[$line] = $line;
                }
            }
        }
        return $ast_key;
    }

    public function sccp_list_keysets() {
        $ast_out = $this->sccp_core_comands(array('cmd' => 'get_softkey'));

        $ast_out = preg_split("/[\n]/", $ast_out['data']);
        $ast_key = array();
        for ($i = 0; $i < 5; $i++) {
            $ast_out[$i] = "";
        }
        $i = count($ast_out) - 1;
        $ast_out[--$i] = "";
        foreach ($ast_out as $line) {
            if (strlen($line) > 3) {
                $line = substr($line, 2);
                list ($line, $junk) = explode(' ', $line);
                if (isset($ast_key[$line])) {
                    if (strlen($ast_key[$line]) < 1) {
                        $ast_key[$line] = $line;
                    }
                } else {
                    $ast_key[$line] = $line;
                }
            }
        }
        return $ast_key;
    }

    public function sccp_get_active_devise() {
        $ast_out = $this->sccp_core_comands(array('cmd' => 'get_device'));

        $ast_out = preg_split("/[\n]/", $ast_out['data']);

        $ast_key = array();
        for ($i = 0; $i < 5; $i++) {
            $ast_out[$i] = "";
        }
        $i = count($ast_out) - 1;
        $ast_out[--$i] = "";
        foreach ($ast_out as $line) {
            if (strlen($line) > 3) {
                $line = substr($line, 2);
                $line = preg_replace("/\s{2,}/", " ", $line);
                $line_arr = explode(' ', $line);
                $it = 1;
                do {
                    if (strpos($line_arr[$it + 1],'SEP') === false) {
                        $line_arr[0] .= ' '.$line_arr[$it];
                        unset($line_arr[$it]);
                    } else {
                        break;
                    }
                    $it++;                    
                } while ((count($line_arr)> 3) and ($it<count($line_arr)));
                explode(";|",implode(";|",$line_arr));
                list ($descr, $adress, $devname, $status, $junk) = explode(";|",implode(";|",$line_arr));
                
//                list ($descr, $adress, $devname, $status, $junk) = $line_arr;                

                if (isset($ast_key[$devname])) {
                    if (strlen($ast_key[$devname]) < 1) {
                        $ast_key[$devname] = Array('name' => $devname, 'status' => $status, 'address' => $adress, 'descr' => $descr);
                    }
                } else {
                    $ast_key[$devname] = Array('name' => $devname, 'status' => $status, 'address' => $adress, 'descr' => $descr);
                }
            }
        }
        return $ast_key;
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
                        foreach ($this->sccp_lang as $lang_key => $lang_value) {
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
        foreach ($this->sccp_lang as $lang_key => $lang_value) {
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
                if (file_exists($this->sccpDefaults["tftp_path"])) {
                    $this->sccppath["tftp_path"] = $this->sccpDefaults["tftp_path"];
                }
            }
        }
        if (!empty($this->sccppath["tftp_path"])) {
            $this->sccppath["tftp_templets"] = $this->sccppath["tftp_path"] . '/templets';
            if (!file_exists($this->sccppath["tftp_templets"])) {
                if (!mkdir($this->sccppath["tftp_templets"], 0777, true)) {
                    die('Error create templet dir');
                }
            }
        }
        if (empty($_SERVER['DOCUMENT_ROOT'])) {
            return;
        }

        if (!file_exists($this->sccppath["tftp_templets"] . '/XMLDefault.cnf.xml_template')) {
            $src_path = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/conf/';
            $dst_path = $this->sccppath["tftp_templets"] . '/';
            foreach (glob($src_path . '*.*_template') as $filename) {
                copy($filename, $dst_path . basename($filename));
            }
        }

        $dst = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/core/functions.inc/drivers/Sccp.class.php';
        if (!file_exists($dst)) {
            $src_path = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/conf/' . basename($dst);
            copy($src_path, $dst);
        }

        if (!file_exists($this->sccppath["sccp_conf"])) { // System re Config 
            $sccpfile = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/conf/sccp.conf');
            file_put_contents($this->sccppath["sccp_conf"], $sccpfile);
        }

        $this->sccp_conf_init = $this->cnf_read->getConfig('sccp.conf');
        
//        $this->sccp_conf_init = @parse_ini_file($this->sccppath["sccp_conf"], true);
    }

    /*
     *      Save Config Value to mysql DB
     *      sccp_db_save_setting(empty) - Save All settings from $sccpvalues
     */

    private function sccp_save_db($db_name = "", $save_value = array(), $mode = 'update', $key_fld = "", $hwid = "") {
        // mode clear  - Empty tabele before update 
        // mode update - update / replace record
        global $db;
//        global $amp_conf;
        $result = "Error";

        switch ($db_name) {
            case 'sccpsettings':
                if ($mode == 'clear') {
                    $sql = 'truncate `sccpsettings`';
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                    $stmt = $db->prepare('INSERT INTO `sccpsettings` (`keyword`, `data`, `seq`, `type`) VALUES (?,?,?,?)');
                    $result = $db->executeMultiple($stmt, $save_value);
                } else {
                    $stmt = $db->prepare('REPLACE INTO `sccpsettings` (`keyword`, `data`, `seq`, `type`) VALUES (?,?,?,?)');
                    $result = $db->executeMultiple($stmt, $save_value);
                }
                break;
            case 'sccpdevmodel':
            case 'sccpdevice':
                $sql_db = $db_name;
                $sql_key = "";
                $sql_var = "";
                foreach ($save_value as $key_v => $data) {
                    if (!empty($sql_var)) {
                        $sql_var .= ', ';
                    }
                    if ($data === 'none') {
                        $sql_var .= '`' . $key_v . '`=NULL';
                    } else {
                        $sql_var .= '`' . $key_v . '`="' . $data . '"';
                    }
                    if ($key_fld == $key_v) {
                        $sql_key = '`' . $key_v . '`="' . $data . '"';
                    }
                }
                if (!empty($sql_var)) {
                    if ($mode == 'delete') {
                        $req = 'DELETE FROM `' . $sql_db . '` WHERE ' . $sql_key . ';';
                    } else {
                        if ($mode == 'update') {
                            $req = 'UPDATE `' . $sql_db . '` SET ' . $sql_var . ' WHERE ' . $sql_key . ';';
                        } else {
                            $req = 'REPLACE INTO `' . $sql_db . '` SET ' . $sql_var . ';';
                        }
                    }
                }
                $stmt = $db->prepare($req);
                $result = $stmt->execute();
                break;
            case 'sccpbuttons':
                if (($mode == 'clear') || ($mode == 'delete')) {
                    $sql = 'DELETE FROM `buttonconfig` WHERE device="' . $hwid . '";';
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                }
                if ($mode == 'delete') {
                    break;
                }
                if (!empty($save_value)) {
                    $sql = 'INSERT INTO `buttonconfig` (`device`, `instance`, `type`, `name`, `options`) VALUES (?,?,?,?,?);';
                    $stmt = $db->prepare($sql);
                    $res = $db->executeMultiple($stmt, $save_value);
                }

                break;
        }
        return $result;
    }

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
            $this->sccp_save_db('sccpsettings', $save_settings, 'clear');
        } else {
            $this->sccp_save_db('sccpsettings', $save_value, 'update');
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
        $xml_templet = $this->sccppath["tftp_templets"] . '/XMLDefault.cnf.xml_template';

        if (file_exists($xml_templet)) {
            $xml_work = simplexml_load_file($xml_templet);


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
                            $xnode->name = $this->sccp_lang[$lang]['locale'];
                            $xnode->langCode = $this->sccp_lang[$lang]['code'];
//                            $this -> replaceSimpleXmlNode($xml_work->$key,$xnode); 
                            break;
                        case 'networkLocale':
                            $xnode = $this->sccp_lang[$this->sccpvalues['netlang']['data']]['language'];
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
            'idleURL' => 'dev_idleURL', 'sshUserId' => 'dev_sshUserId', 'sshPassword' => 'dev_sshPassword', 'deviceProtocol' => 'dev_deviceProtocol');
        if (empty($dev_id)) {
            return false;
        }
        $var_hw_config = $this->get_db_SccpTableData("get_sccpdevice_byid", array('id' => $dev_id));

        if (empty($var_hw_config)) {
            return false;
        }

        if (!(empty($var_hw_config['nametemplet']))) {
            $xml_templet = $this->sccppath["tftp_templets"] . '/' . $var_hw_config['nametemplet'];
        } else {
            $xml_templet = $this->sccppath["tftp_templets"] . '/SEP0000000000.cnf.xml_79df_template';
        }
        $xml_name = $this->sccppath["tftp_path"] . '/' . $dev_id . '.cnf.xml';
        if (file_exists($xml_templet)) {
            $xml_work = simplexml_load_file($xml_templet);
            /*
              $node = $xml_work -> certHash;
              if ( !empty($node)) {F
              unset($node[0][0]);
              }
             * 
             */
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
                                    $xnode->name = $this->sccpvalues['ntp_timezone']['data'];
                                    $xnode->dateTemplate = $this->sccpvalues['dateformat']['data'];
                                    $xnode->timeZone = $this->sccpvalues['ntp_timezone']['data'];
                                    if ($this->sccpvalues['ntp_config_enabled']['data'] == 'yes') {
                                        $xnode->ntps->ntp->name = $this->sccpvalues['ntp_server']['data'];
                                        $xnode->ntps->ntp->ntpMode = $this->sccpvalues['ntp_server_mode']['data'];
                                    } else {
                                        $xnode->ntps = '';
                                    }
                                    // Ntp Config
                                    break;
                                case 'srstInfo':
                                    $xml_node->$dkey = '';
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
                            $xnode = $xml_work -> addChild('addOnModules');
                            $ti = 1;
                            foreach ($hw_addon as $key ) {
                                $hw_inf = $this-> getSccp_model_information('byid',false, "all",array('model'=>$key ));
                                $xnode_obj = $xnode -> addChild('addOnModule');
//                                if $hw_inf['loadimage']
                                $xnode_obj -> addAttribute('idx', $ti);
                                $xnode_obj -> addChild('loadInformation',$hw_inf[0]['loadimage']);
                                $ti ++;
                            }
//                            $this->appendSimpleXmlNode($xml_work , $xnode_obj);
                       }
                        break;
                    case 'userLocale':
                    case 'networkLocaleInfo':
                    case 'networkLocale':
                        $hwlang = '';
                        if (!empty($var_hw_config["hwlang"])) {
                            $hwlang = explode(':', $var_hw_config["hwlang"]);
                        }
                        if (($key == 'networkLocaleInfo') || ($key == 'networkLocale') ){
//                            $lang=$this->sccpvalues['netlang']['data'];
                            $lang = (empty($hwlang[0])) ? $this->sccpvalues['netlang']['data'] : $hwlang[0];
                        } else {
                            $lang = (empty($hwlang[1])) ? $this->sccpvalues['devlang']['data'] : $hwlang[1];
//                            $lang=$this->sccpvalues['devlang']['data'];
                        }
                        if ($key == 'networkLocale'){
                            $xml_work->$key = $lang;
                        } else {
                            $xml_node->name = $this->sccp_lang[$lang]['locale'];
                            $xml_node->langCode = $this->sccp_lang[$lang]['code'];
                            $this->replaceSimpleXmlNode($xml_work->$key, $xml_node);
                        }
                        break;
//                    case 'networkLocale':
//                        $xml_work->$key = $this->sccp_lang[$this->sccpvalues['netlang']['data']]['language'];
//                        $xml_work->$key = $this->sccp_lang[$this->sccpvalues['netlang']['data']]['language'];
//                        break;
                    case 'mobility':
//                       break;
                    case 'phoneServices':
//                        break;
                        $xml_work->$key = '';
                    default:
                        break;
                }
            }

//            print_r($xml_work);
            $xml_work->asXml($xml_name);  // Save  
        } else {
            die('Error Hardware templete :' . $xml_templet . ' not found');
        }
        return time();
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

    /*
     *      Load Config Value from mysql DB
     *      sccp_db_save_setting(empty) - Save All settings from $sccpvalues
     */

    public function get_db_SccpTableData($dataid, $data = array()) {
        if ($dataid == '') {
            return False;
        }
        switch ($dataid) {
            case "SccpExtension":
                if (empty($data['id'])) {
                    $sql = "SELECT * FROM `sccpline` ORDER BY `id`";
                    $raw_settings = sql($sql, "getAll", DB_FETCHMODE_ASSOC);
                } else {
                    $sql = "SELECT * FROM `sccpline` WHERE `id`=".$data['id'];
                    $raw_settings = sql($sql, "getAll", DB_FETCHMODE_ASSOC);                    
                }
                break;
            case "SccpDevice":
//                $sql = "SELECT * FROM `sccpdeviceconfig` ORDER BY `name`";
                $sql = "select `name`,`name` as `mac`, `type`, `button` from `sccpdeviceconfig` ORDER BY `name`";
                $raw_settings = sql($sql, "getAll", DB_FETCHMODE_ASSOC);
                break;
            case "HWDevice":
                $raw_settings = $this->getSccp_model_information($get = "phones", $validate = false, $format_list = "model");
                break;
            case "HWextension":
                $raw_settings = $this->getSccp_model_information($get = "extension", $validate = false, $format_list = "model");
                break;
            case "get_colums_sccpdevice":
                $sql = "DESCRIBE sccpdevice";
                $raw_settings = sql($sql, "getAll", DB_FETCHMODE_ASSOC);
                break;
            case "get_sccpdevice_byid":
                $sql = 'SELECT t1.*, types.dns,  types.buttons, types.loadimage, types.nametemplet as nametemplet, '
                        . 'addon.buttons as addon_buttons FROM sccpdevice AS t1 '
                        . 'LEFT JOIN sccpdevmodel as types ON t1.type=types.model '
                        . 'LEFT JOIN sccpdevmodel as addon ON t1.addon=addon.model WHERE name="' . $data['id'] . '";';
                $raw_settings = sql($sql, "getRow", DB_FETCHMODE_ASSOC);
                break;
            case "get_sccpdevice_buttons":
                $sql = 'SELECT * FROM buttonconfig WHERE  device="' . $data['id'] . '";';
                $raw_settings = sql($sql, "getAll", DB_FETCHMODE_ASSOC);
                break;
        }

        return $raw_settings;
    }

    public function getSccpSettingsDB($returnraw = false) {
        $sql = "SELECT `keyword`, `data`, `type`, `seq` FROM `sccpsettings` ORDER BY `type`, `seq`";
        $raw_settings = sql($sql, "getAll", DB_FETCHMODE_ASSOC);

        if ($returnraw === true) {
            return $raw_settings;
        }


        foreach ($raw_settings as $var) {
            $this->sccpvalues[$var['keyword']] = array('keyword' => $var['keyword'], 'data' => $var['data'], 'seq' => $var['seq'], 'type' => $var['type']);
        }
        return;
    }

    /*
     *      Get Sccp Device Model information
     *      
     */

    function getSccp_model_information($get = "all", $validate = false, $format_list = "all", $filter = array()) {
        global $db;
        $file_ext = array('.loads','.sbn','.SBN');

        $dir = $this->sccppath["tftp_path"];
        switch ($format_list) {
            case "model":
                $sel_inf = "model, vendor, dns, buttons";
                break;
            case "all":
            default:
                $sel_inf = "*";
                break;
        }

        if ($validate) {
            $sel_inf .= ", '0' as 'validate'";
        }
        switch ($get) {
            case "byid":
                if (!empty($filter)) {
                    if (!empty($filter['model'])) {
                        $sql = "SELECT " . $sel_inf . " FROM sccpdevmodel WHERE (`model` =".$filter['model'].") ORDER BY model ";                                            
                    } else {
//                          $sql = "SELECT ".$filter['model'];
                        $sql = "SELECT " . $sel_inf . " FROM sccpdevmodel ORDER BY model ";
                    }
                    break;
                }
                break;
            case "extension":
                $sql = "SELECT " . $sel_inf . " FROM sccpdevmodel WHERE (dns = 0)ORDER BY model ";                    
                break;
            case "enabled":
            case "phones":
                $sql = "SELECT " . $sel_inf . " FROM sccpdevmodel WHERE (dns > 0) and (enabled > 0) ORDER BY model ";
//                $sql = "SELECT " . $sel_inf . " FROM sccpdevmodel WHERE (enabled > 0) ORDER BY model ";
                break;
            case "all":
            default:
                $sql = "SELECT " . $sel_inf . " FROM sccpdevmodel ORDER BY model ";
                break;
        }
        $raw_settings = sql($sql, "getAll", DB_FETCHMODE_ASSOC);
        $i = 0;
        if ($validate) {
            for ($i = 0; $i < count($raw_settings); $i++) {
                $file = $dir . '/' . $raw_settings[$i]['loadimage'];
                $raw_settings[$i]['validate'] = 'no';
                if (strtolower($raw_settings[$i]['vendor']) == 'cisco') {                   
                    foreach ($file_ext as $value) {
                        if (file_exists($file.$value)) {
                            $raw_settings[$i]['validate'] = 'yes';
                            break; 
                        }
                    }
                } else {
                    if (file_exists($file)) {
                        $raw_settings[$i]['validate'] = 'yes';
                    }
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