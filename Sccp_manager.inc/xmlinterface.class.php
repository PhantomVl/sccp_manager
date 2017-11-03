<?php

/**
 * 
 * Core Comsnd Interface 
 * 
 * 
 */
/* !TODO!: Would you like to use my XSD file to check if the provided template file is a correct cisco cnf.xml file ? */

namespace FreePBX\modules\Sccp_manager;

class xmlinterface {

    private $val_null = 'NONE'; /// REPLACE to null Field

    public function __construct() {
        
    }

    public function info() {
        $Ver = '13.0.2';
        return Array('Version' => $Ver,
            'about' => 'Create XML data interface ver: ' . $Ver);
    }

    function create_default_XML($data_path = '', $data_values = array(), $model_information = array(), $lang_info = array()) {
        if (empty($data_path) || empty($data_values)) {
            return;
        }
        $def_xml_fields = array('authenticationURL', 'informationURL', 'messagesURL', 'servicesURL', 'directoryURL', 'proxyServerURL', 'idleTimeout', 'idleURL');
        $def_xml_locale = array('userLocale', 'networkLocaleInfo', 'networkLocale');
        $xml_name = $data_path . '/XMLDefault.cnf.xml';
        $xml_template = $data_path . '/templates/XMLDefault.cnf.xml_template';

        if (file_exists($xml_template)) {
            $xml_work = simplexml_load_file($xml_template);


            $xnode = &$xml_work->callManagerGroup->members;
            if ($data_values['bindaddr'] == '0.0.0.0') {
                $ifc = 0;
                foreach ($data_values['server_if_list'] as $value) {
                    if (!empty($value[0])) {
                        if (!in_array($value[0], array('0.0.0.0', '127.0.0.1'), true)) {
                            $xnode_obj = clone $xnode->member;
                            $xnode_obj['priority'] = $ifc;
                            //$xnode_obj =  &$xnode -> member -> callManager;
                            $xnode_obj->callManager->name = $data_values['servername'];
                            $xnode_obj->callManager->ports->ethernetPhonePort = $data_values['port'];
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
                $xnode_obj->name = $data_values['servername'];
                $xnode_obj->ports->ethernetPhonePort = $data_values['port'];
                $xnode_obj->processNodeName = $data_values['bindaddr'];
            }
            $this->replaceSimpleXmlNode($xml_work->callManagerGroup->members, $xnode);

            foreach ($def_xml_fields as $value) {
                if (!empty($data_values['dev_' . $value])) {
                    $xml_work->$value = trim($data_values['dev_' . $value]);
                }
            }
            foreach ($def_xml_locale as $key) {
                if (!empty($xml_work->$key)) {
                    $xnode = &$xml_work->$key;
                    switch ($key) {
                        case 'userLocale':
                        case 'networkLocaleInfo':
                            if ($key == 'networkLocaleInfo') {
                                $lang = $data_values['netlang'];
                            } else {
                                $lang = $data_values['devlang'];
                            }
//                            configs->getConfig('sccp_lang')
                            if (isset($lang_info[$lang])) {
                                $xnode->name = $lang_info[$lang]['locale'];
                                $xnode->langCode = $lang_info[$lang]['code'];
                            } else {
                                $xnode->name = '';
                                $xnode->langCode = '';
                            }
//                            $this -> replaceSimpleXmlNode($xml_work->$key,$xnode); 
                            break;
                        case 'networkLocale':
                            $lang = $data_values['netlang'];
                            if (isset($lang_info[$lang])) {
                                $xnode = $lang_info[$lang]['language'];
                            } else {
                                $xnode = '';
                            }
                            break;
                    }
                    //$this-> replaceSimpleXmlNode($xml_work->$value, $xnode );                     
                }
            }

            foreach ($model_information as $var) {
                if (!empty($var['loadinformationid'])) {
                    $node = $xml_work->addChild($var['loadinformationid'], $var['loadimage']);
                    $node->addAttribute('model', $var['vendor'] . ' ' . $var['model']);
                }
            }
            $xml_work->asXml($xml_name);  // Save  XMLDefault1.cnf.xml
        }
    }

    function create_SEP_XML($data_path = '', $data_values = array(), $dev_config = array(), $dev_id = '', $lang_info = array()) {

        $var_xml_general_fields = array('authenticationURL' => 'dev_authenticationURL', 'informationURL' => 'dev_informationURL', 'messagesURL' => 'dev_messagesURL',
            'servicesURL' => 'dev_servicesURL', 'directoryURL' => 'dev_directoryURL', 'proxyServerURL' => 'dev_proxyServerURL', 'idleTimeout' => 'dev_idleTimeout',
            'idleURL' => 'dev_idleURL', 'sshUserId' => 'dev_sshUserId', 'sshPassword' => 'dev_sshPassword', 'deviceProtocol' => 'dev_deviceProtocol'
        );
        $var_xml_general_vars = array('capfAuthMode' => 'null', 'capfList' => 'null', 'mobility' => 'null',
            'phoneServices' => 'null', 'certHash' => 'null',
            'deviceSecurityMode' => '1');

        if (empty($dev_id)) {
            return false;
        }
//        $var_hw_config = $this->dbinterface->get_db_SccpTableData("get_sccpdevice_byid", array('id' => $dev_id));

        if (empty($dev_config)) {
            return false;
        }

        if (!empty($dev_config['nametemplate'])) {
            $xml_template = $data_path . '/templates/' . $dev_config['nametemplate'];
        } else {
            $xml_template = $data_path . '/templates/SEP0000000000.cnf.xml_79df_template';
        }
        $xml_name = $data_path . '/' . $dev_id . '.cnf.xml';
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
                    $xml_work->$key = $data_values[$var_xml_general_fields[$key]];
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
                                    $tz_id = $data_values['ntp_timezone'];
                                    $TZdata = $data_values['ntp_timezone_id'];
                                    if (empty($TZdata)) {
                                        $TZdata = array('offset' => '0', 'daylight' => '', 'cisco_code' => 'Greenwich Standard Time');
                                    }
                                    $xnode->name = $tz_id;
                                    $xnode->dateTemplate = $data_values['dateformat'];
                                    $xnode->timeZone = $TZdata['cisco_code'];
//                                    $xnode->timeZone = $tz_id.' Standard'.((empty($TZdata['daylight']))? '': '/'.$TZdata['daylight']).' Time';

                                    if ($data_values['ntp_config_enabled'] == 'yes') {
                                        $xnode->ntps->ntp->name = $data_values['ntp_server'];
                                        $xnode->ntps->ntp->ntpMode = $data_values['ntp_server_mode'];
                                    } else {
                                        $xnode->ntps = '';
                                    }
                                    // Ntp Config
                                    break;
                                case 'srstInfo':
                                    if ($data_values['srst_Option'] == 'user') {
                                        break;
                                    }
                                    $xnode = &$xml_node->$dkey;
                                    $xnode->name = $data_values['srst_Name'];
                                    $xnode->srstOption = $data_values['srst_Option'];
                                    $xnode->userModifiable = $data_values['srst_userModifiable'];
                                    $xnode->isSecure = $data_values['srst_isSecure'];

                                    $srst_fld = array('srst_ip' => array('ipAddr', 'port'));
//                                    $srst_fld = array('srst_ip' => array('ipAddr','port') , 'srst_sip' => array('sipIpAddr','sipPort') );
                                    foreach ($srst_fld as $srst_pro => $srs_put) {
                                        $srst_data = explode(';', $data_values[$srst_pro]);
                                        $si = 1;
//                                        $xnode['test'] = $srst_data[0];
                                        foreach ($srst_data as $value) {
                                            $srs_val = explode('/', $value);
                                            $nod = $srs_put[0] . $si;
                                            $xnode->$nod = $srs_val[0];
                                            $nod = $srs_put[1] . $si;
                                            $xnode->$nod = $srs_val[1];
                                            $si ++;
                                        }
                                        while ($si < 4) {
                                            $nod = $srs_put[0] . $si;
                                            $xnode->$nod = '';
                                            $nod = $srs_put[1] . $si;
                                            $xnode->$nod = '';
                                            $si ++;
                                        }
                                    }
                                    break;
                                case 'connectionMonitorDuration':
                                    $xml_node->$dkey = strval(intval(intval($data_values['keepalive']) * 0.75));
                                    break;
                                case 'callManagerGroup':
                                    $xnode = &$xml_node->$dkey->members;
                                    if ($data_values['bindaddr'] == '0.0.0.0') {
                                        $ifc = 0;
                                        foreach ($data_values['server_if_list'] as $value) {
                                            if (!empty($value[0])) {
                                                if (!in_array($value[0], array('0.0.0.0', '127.0.0.1'), true)) {
                                                    $xnode_obj = clone $xnode->member;
//                                                $xnode_obj = $xnode -> member;
//                                                $xnode_obj = $xnode -> addChild($xnode->member);
                                                    $xnode_obj['priority'] = $ifc;
                                                    //$xnode_obj =  &$xnode -> member -> callManager;
                                                    $xnode_obj->callManager->name = $data_values['servername'];
                                                    $xnode_obj->callManager->ports->ethernetPhonePort = $data_values['port'];
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
                                        $xnode_obj->name = $data_values['servername'];
                                        $xnode_obj->ports->ethernetPhonePort = $data_values['port'];
                                        $xnode_obj->processNodeName = $data_values['bindaddr'];
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
                        $xml_work->$key = $dev_config["loadimage"];
                        if (!empty($dev_config['addon'])) {
                            $xnode = $xml_work->addChild('addOnModules');
                            $ti = 1;
                            foreach ($dev_config['addon_info'] as $add_key => $add_val) {
                                $xnode_obj = $xnode->addChild('addOnModule');
                                $xnode_obj->addAttribute('idx', $ti);
                                $xnode_obj->addChild('loadInformation', $add_val);
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
                        if (!empty($dev_config["_hwlang"])) {
                            $hwlang = explode(':', $dev_config["_hwlang"]);
                        }
                        if (($key == 'networkLocaleInfo') || ($key == 'networkLocale')) {
                            $lang = (empty($hwlang[0])) ? $data_values['netlang'] : $hwlang[0];
                        } else {
                            $lang = (empty($hwlang[1])) ? $data_values['devlang'] : $hwlang[1];
                        }
                        if (($lang != 'null') && (!empty(trim($lang)))) {
                            if ($key == 'networkLocale') {
                                $xml_work->$key = $lang;
                            } else {
                                if (isset($lang_info[$lang])) {
                                    $xml_node->name = $lang_info[$lang]['locale'];
                                    $xml_node->langCode = $lang_info[$lang]['code'];
                                    $this->replaceSimpleXmlNode($xml_work->$key, $xml_node);
                                }
                            }
                        } else {
                            $xml_work->$key = '';
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
            die('Error Hardware template :' . $xml_template . ' not found');
        }
        return time();
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

}
