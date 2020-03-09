<?php

/**
 * 
 * Core Comsnd Interface 
 * 
 * 
 */
/* !TODO!: -TODO-: Would you like to use my XSD file to check if the provided template file is a correct cisco cnf.xml file ? 
 * !TODO!: -TODO-: I just don't understand how to use it here.. To check the incoming pattern ? To check the result of my script ?
 * !TODO!: -TODO-: The most correct variant is to generate xml based on XSD template.  
 */

namespace FreePBX\modules\Sccp_manager;

class xmlinterface {

    private $val_null = 'NONE'; /// REPLACE to null Field

    public function __construct($parent_class = null) {
        $this->paren_class = $parent_class;
    }

    public function info() {
        $Ver = '13.0.4';
        return Array('Version' => $Ver,
            'about' => 'Create XML data interface ver: ' . $Ver);
    }

    function create_default_XML($store_path = '', $data_values = array(), $model_information = array(), $lang_info = array()) {
        $data_path = $data_values['tftp_path'];
        if (empty($store_path) || empty($data_path) || empty($data_values)) {
            return;
        }
        $def_xml_fields = array('authenticationURL', 'informationURL', 'messagesURL', 'servicesURL', 'directoryURL', 'proxyServerURL', 'idleTimeout', 'idleURL');
        $def_xml_locale = array('userLocale', 'networkLocaleInfo', 'networkLocale');
        $xml_name = $store_path . '/XMLDefault.cnf.xml';
        $xml_template = $data_values['tftp_path'] . '/templates/XMLDefault.cnf.xml_template';

        if (file_exists($xml_template)) {
            $xml_work = simplexml_load_file($xml_template);


            $xnode = &$xml_work->callManagerGroup->members;
            if ($data_values['bindaddr'] == '0.0.0.0') {
                $ifc = 0;
//                $xnode->member['priority'] = print_r($data_values['server_if_list'], true);
                foreach ($data_values['server_if_list'] as $value) {
                    if (!empty($value['ip'])) {
                        $ip_valid = true;
                        if (!empty($data_values['ccm_address'])) {
                            if (strpos($data_values['ccm_address'], 'internal') !== false || strpos($data_values['ccm_address'], '0.0.0.0') !== false) {
                                // Skip
                            } else {
                                if (strpos($data_values['ccm_address'], $value['ip']) === false) {
                                    $ip_valid = false;
                                }
                            }
                        }
                        if (!in_array($value['ip'], array('0.0.0.0', '127.0.0.1'), true) && ($ip_valid)) {
                            $xnode_obj = clone $xnode->member;
                            $xnode_obj['priority'] = $ifc;
                            //$xnode_obj =  &$xnode -> member -> callManager;
                            $xnode_obj->callManager->name = $data_values['servername'];
                            $xnode_obj->callManager->ports->ethernetPhonePort = $data_values['port'];
                            $xnode_obj->callManager->processNodeName = $value['ip'];
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
                } else {
//                    $xml_work->$value = '';
                    $node = $xml_work->$value;
                    unset($node[0][0]);
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

    function create_SEP_XML($store_path = '', $data_values = array(), $dev_config = array(), $dev_id = '', $lang_info = array()) {

        $var_xml_general_fields = array('authenticationURL' => 'dev_authenticationURL', 'informationURL' => 'dev_informationURL', 'messagesURL' => 'dev_messagesURL',
            'servicesURL' => 'dev_servicesURL', 'directoryURL' => 'dev_directoryURL', 'proxyServerURL' => 'dev_proxyServerURL', 'idleTimeout' => 'dev_idleTimeout',
            'idleURL' => 'dev_idleURL', 'sshUserId' => 'dev_sshUserId', 'sshPassword' => 'dev_sshPassword', 'deviceProtocol' => 'dev_deviceProtocol',
            'phonePersonalization' => 'phonePersonalization'
        );
        $var_xml_general_vars = array('capfAuthMode' => 'null', 'capfList' => 'null', 'mobility' => 'null',
            'phoneServices' => 'null', 'certHash' => 'null',
            'deviceSecurityMode' => '1');

//        $var_hw_config = $this->dbinterface->get_db_SccpTableData("get_sccpdevice_byid", array('id' => $dev_id));

        if (empty($dev_config)) {
            return false;
        }
        $data_path = $dev_config['tftp_path'];

        if (empty($store_path) || empty($data_path) || empty($data_values) || empty($dev_id)) {
            return;
        }

        if (!empty($dev_config['nametemplate'])) {
            $xml_template = $data_path . '/templates/' . $dev_config['nametemplate'];
        } else {
            $xml_template = $data_path . '/templates/SEP0000000000.cnf.xml_79df_template';
        }
        $xml_name = $store_path . '/' . $dev_id . '.cnf.xml';
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
                                        $xnode->ntps = null;
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
                                    $bind_tmp = $this->get_server_sccp_bind($data_values);
                                    $ifc = 0;
                                    foreach ($bind_tmp as $bind_value) {
                                        $xnode_obj = clone $xnode->member;
                                        $xnode_obj['priority'] = $ifc;
                                        $xnode_obj->callManager->name = $data_values['servername'];
                                        $xnode_obj->callManager->ports->ethernetPhonePort = $bind_value['port'];
                                        $xnode_obj->callManager->processNodeName = $bind_value['ip'];
                                        if ($ifc === 0) {
                                            $this->replaceSimpleXmlNode($xnode->member, $xnode_obj);
                                        } else {
                                            $this->appendSimpleXmlNode($xnode->member, $xnode_obj);
                                        }
                                        $ifc ++;
                                    }
                                /*                                    if ($data_values['bindaddr'] == '0.0.0.0') {
                                  $ifc = 0;
                                  foreach ($data_values['server_if_list'] as $value) {

                                  if (!empty($value['ip'])) {
                                  $ip_valid = true;
                                  if (!empty($data_values['ccm_address'])) {
                                  if (strpos($data_values['ccm_address'], 'internal') !== false || strpos($data_values['ccm_address'], '0.0.0.0') !== false ) {
                                  // Skip
                                  } else {
                                  if (strpos($data_values['ccm_address'], $value['ip']) === false) {
                                  $ip_valid = false;
                                  }
                                  }
                                  }
                                  if (!in_array($value['ip'], array('0.0.0.0', '127.0.0.1'), true) && ($ip_valid)) {

                                  $xnode_obj = clone $xnode->member;
                                  //                                                $xnode_obj = $xnode -> member;
                                  //                                                $xnode_obj = $xnode -> addChild($xnode->member);
                                  $xnode_obj['priority'] = $ifc;
                                  //$xnode_obj =  &$xnode -> member -> callManager;
                                  $xnode_obj->callManager->name = $data_values['servername'];
                                  $xnode_obj->callManager->ports->ethernetPhonePort = $data_values['port'];
                                  $xnode_obj->callManager->processNodeName = $value['ip'];
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
                                 * 
                                 */
                            }
                        }
                        $this->replaceSimpleXmlNode($xml_work->$key, $xml_node);
                        break;
                    case 'versionStamp':
                        $xml_work->$key = time();
                        break;
                    case 'loadInformation':
//                      Set Path Image ???? 
                        if (isset($dev_config["tftp_firmware"])) {
                            $xml_work->$key = (isset($dev_config["loadimage"])) ? $dev_config["tftp_firmware"] . $dev_config["loadimage"] : '';
                        } else {
                            $xml_work->$key = (isset($dev_config["loadimage"])) ? $dev_config["loadimage"] : '';
                        }
//                        $xml_work->$key = $dev_config["loadimage"];
                        if (!empty($dev_config['addon'])) {
                            $xnode = $xml_work->addChild('addOnModules');
                            $ti = 1;
                            $hw_addon = explode(';', $dev_config['addon']);
                            foreach ($hw_addon as $add_key) {
//                            foreach ($dev_config['addon_info'] as $add_key => $add_val) {
                                if (!empty($dev_config['addon_info'][$add_key])) {
                                    $add_val = $dev_config['addon_info'][$add_key];
                                    $xnode_obj = $xnode->addChild('addOnModule');
                                    $xnode_obj->addAttribute('idx', $ti);
                                    $xnode_obj->addChild('loadInformation', $add_val);
                                    $ti ++;
                                }
                            }
//                            $this->appendSimpleXmlNode($xml_work , $xnode_obj);
                        }
                        break;
                    case 'commonProfile':
                        $xml_node->phonePassword = $data_values['dev_sshPassword'];
                        $xml_node->backgroundImageAccess = (($data_values['backgroundImageAccess'] == 'on') || ($data_values['backgroundImageAccess'] == 'true') ) ? 'true' : 'false';
                        $xml_node->callLogBlfEnabled = $data_values['callLogBlfEnabled'];
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
                        if (($lang != 'null') && (!empty($lang))) {
                            if ($key == 'networkLocale') {
                                $xml_work->$key = $lang;
                            } else {
                                if (isset($lang_info[$lang])) {
                                    $xml_node->name = $lang_info[$lang]['locale'];
                                    $xml_node->langCode = $lang_info[$lang]['code'];
                                    if ($key == 'userLocale') {
                                        $xml_node->winCharSet = $lang_info[$lang]['codepage'];
                                    }
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

    private function get_server_sccp_bind($data_values = array()) {
        $res = array();

        if ($data_values['bindaddr'] == '0.0.0.0') {
            $ifc = 0;
            foreach ($data_values['server_if_list'] as $value) {
                if (!empty($value['ip'])) {
                    $ip_valid = true;
                    if (!empty($data_values['ccm_address'])) {
                        if (strpos($data_values['ccm_address'], 'internal') !== false || strpos($data_values['ccm_address'], '0.0.0.0') !== false) {
                            // Skip
                        } else {
                            if (strpos($data_values['ccm_address'], $value['ip']) === false) {
                                $ip_valid = false;
                            }
                        }
                    }
                    if (!in_array($value['ip'], array('0.0.0.0', '127.0.0.1'), true) && ($ip_valid)) {
                        $res[] = array('ip' => $value['ip'], 'port' => $data_values['port']);
                    }
                }
            }
        } else {
            $res[0] = array('ip' => $data_values['bindaddr'], 'port' => $data_values['port']);
        }
        return $res;
    }

    /*
    private function get_server_sip_bind($data_values = array()) {
        $res = array();

        if (!empty($data_values['sipbind']) and ( $data_values['sipbind'] != '0.0.0.0')) {
            $res[0] = array('ip' => $data_values['sipbind'], 'port' => $data_values['sipbindport'], 'tlsport' => $data_values['tlsport'], 'proto' => $data_values['sipsuportproto']);
        }

        $ifc = 0;
        foreach ($data_values['server_if_list'] as $value) {
            if (!empty($value['ip'])) {
                $ip_valid = true;
                if (!empty($data_values['ccm_address'])) {
                    if (strpos($data_values['ccm_address'], 'internal') !== false || strpos($data_values['ccm_address'], '0.0.0.0') !== false) {
                        // Skip
                    } else {
                        if (strpos($data_values['ccm_address'], $value['ip']) === false) {
                            $ip_valid = false;
                        }
                    }
                }
                if (!in_array($value['ip'], array('0.0.0.0', '127.0.0.1', $data_values['sipbind']), true) && ($ip_valid)) {
                    $res[] = array('ip' => $value['ip'], 'port' => $data_values['sipbindport'], 'tlsport' => $data_values['tlsport'], 'proto' => $data_values['sipsuportproto']);
                }
            }
        }
        return $res;
    }
     * 
     */

    function create_SEP_SIP_XML($store_path = '', $data_values = array(), $dev_config = array(), $dev_id = '', $lang_info = array()) {

        $var_xml_general_fields = array('authenticationURL' => 'dev_authenticationURL', 'informationURL' => 'dev_informationURL', 'messagesURL' => 'dev_messagesURL',
            'servicesURL' => 'dev_servicesURL', 'directoryURL' => 'dev_directoryURL', 'proxyServerURL' => 'dev_proxyServerURL', 'idleTimeout' => 'dev_idleTimeout',
            'idleURL' => 'dev_idleURL', 'sshUserId' => 'dev_sshUserId', 'sshPassword' => 'dev_sshPassword',
            'phonePersonalization' => 'phonePersonalization'
        );
        $var_xml_sipProfile = array('phoneLabel' => 'description',
            'transferOnhookEnabled' => 'transferOnhookEnabled', 'enableVad' => 'enableVad', 'voipControlPort' => 'sipport'
        );
        $var_xml_sipline = array('name' => 'account', 'featureLabel' => 'account', 'displayName' => 'callerid', 'contact' => 'account',
            'authName' => 'account', 'authPassword' => 'secret');
        $var_xml_general_vars = array('capfAuthMode' => 'null', 'capfList' => 'null', 'mobility' => 'null',
            'phoneServices' => 'null', 'certHash' => 'null', 'deviceProtocol' => 'SIP',
            'deviceSecurityMode' => '1');

//        $var_hw_config = $this->dbinterface->get_db_SccpTableData("get_sccpdevice_byid", array('id' => $dev_id));

        if (empty($dev_config)) {
            return false;
        }
        $data_path = $dev_config['tftp_path'];

        if (empty($store_path) || empty($data_path) || empty($data_values) || empty($dev_id)) {
            return;
        }

        if (!empty($dev_config['nametemplate'])) {
            $xml_template = $data_path . '/templates/' . $dev_config['nametemplate'];
            if (!file_exists($xml_template)) {
                $xml_template = $data_path . '/templates/SEP0000000000.cnf.xml_79df_sip_template';
            }
        } else {
            $xml_template = $data_path . '/templates/SEP0000000000.cnf.xml_79df_sip_template';
        }
        $xml_name = $store_path . '/' . $dev_id . '.cnf.xml';
        //$sip_bind = $this->get_server_sip_bind($data_values);
        $sip_bind = $data_values['sbind'];
        $bind_proto = 'tcp';
        $bind_ip_def = '';
        foreach($sip_bind  as $key => $value) {
            if (empty($bind_ip_def)) {
                $bind_ip_def = $key;
                $bind_proto  = (isset($value['tcp'])) ? 'tcp' : 'udp';
            }
        } 

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
//                                    $xnode->name = $tz_id;
                                    $xnode->dateTemplate = $data_values['dateformat'];
                                    $xnode->timeZone = $TZdata['cisco_code'];
//                                    $xnode->timeZone = $tz_id.' Standard'.((empty($TZdata['daylight']))? '': '/'.$TZdata['daylight']).' Time';

                                    if ($data_values['ntp_config_enabled'] == 'yes') {
                                        $xnode->ntps->ntp->name = $data_values['ntp_server'];
                                        $xnode->ntps->ntp->ntpMode = $data_values['ntp_server_mode'];
                                    } else {
                                        $xnode->ntps = null;
                                    }
                                    // Ntp Config
                                    break;
                                /*                                case 'srstInfo':
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
                                 */
                                case 'callManagerGroup':
                                    $xnode = &$xml_node->$dkey->members;
                                    $ifc = 0;
                                    foreach ($sip_bind as $bind_ip => $bind_value) {
                                        $xnode_obj = clone $xnode->member;
                                        $xnode_obj['priority'] = $ifc;
                                        $xnode_obj->callManager->name = $data_values['servername'];
                                        $xnode_obj->callManager->ports->sipPort = $bind_value[$bind_proto];
//                                        $xnode_obj->callManager->ports->securedSipPort = $bind_value['tlsport'];
                                        $xnode_obj->callManager->processNodeName = $bind_ip;
                                        if ($ifc === 0) {
                                            $this->replaceSimpleXmlNode($xnode->member, $xnode_obj);
                                        } else {
                                            $this->appendSimpleXmlNode($xnode->member, $xnode_obj);
                                        }
                                        $ifc ++;
                                    }
                            }
                        }
                        $this->replaceSimpleXmlNode($xml_work->$key, $xml_node);
                        break;
                    case 'sipProfile':
                        $xml_node = $xml_work->$key;
                        foreach ($xml_work->$key->children() as $dkey => $ddata) {
                            if (!empty($var_xml_sipProfile[$dkey])) {
                                if (!empty($data_values[$var_xml_sipProfile[$dkey]])) {
                                    $xml_node->$dkey = $data_values[$var_xml_sipProfile[$dkey]];
                                } else {
                                    $xml_node->$dkey = null;
                                }
                            }
                            switch ($dkey) {
                                case 'sipProxies':
                                    $xnode = &$xml_node->$dkey;                                    
                                    $xnode->backupProxy = $bind_ip_def;
                                    $xnode->backupProxyPort = $sip_bind[$bind_ip_def][$bind_proto];
                                    $xnode->emergencyProxy = $bind_proto;
                                    $xnode->emergencyProxyPort = $sip_bind[$bind_ip_def][$bind_proto];
                                    $xnode->outboundProxy = $bind_proto;
                                    $xnode->outboundProxyPort = $sip_bind[$bind_ip_def][$bind_proto];
                                    $xnode->registerWithProxy = "true";

                                    break;
                                case 'sipLines':
                                    $xnode = &$xml_node->$dkey;
                                    $ifc = 0;
                                    if (!empty($data_values['siplines'])) {
                                        foreach ($data_values['siplines'] as $spkey => $spvalue) {
//                                            if $spvalue[]
                                            $xnode_obj = clone $xnode->line;
                                            $xnode_obj['button'] = $ifc + 1;
                                            $xnode_obj['lineIndex'] = $ifc + 1;
                                            //$xnode_obj->proxy = $data_values['bindaddr'];
                                            $xnode_obj-> featureID = "9";
                                            if ($xnode_obj->proxy != 'USECALLMANAGER') {
                                                $xnode_obj->proxy = $bind_proto;
                                                $xnode_obj->port = $sip_bind[$bind_ip_def][$bind_proto];
                                            }

                                            foreach ($var_xml_sipline as $line_key => $line_val) {
                                                $xnode_obj->$line_key = $spvalue[$line_val];
                                            }

                                            if ($ifc === 0) {
                                                $this->replaceSimpleXmlNode($xnode->line, $xnode_obj);
                                            } else {
                                                $this->appendSimpleXmlNode($xnode->line, $xnode_obj);
                                            }
                                            $ifc ++;
                                        }
                                    }
                                    if (!empty($data_values['speeddial'])) {
                                        foreach ($data_values['speeddial'] as $spkey => $spvalue) {
                                            $xmlstr = '<line button="'. ($ifc + 1) .'"> <featureID>22</featureID>'
                                                       .'<featureLabel>'.$spvalue["name"].'</featureLabel>'
                                                       .'<speedDialNumber>'.$spvalue["dial"].'</speedDialNumber>'
                                                       .'<contact>'.$spvalue["dial"].'</contact> <retrievalPrefix /></line>';
                                            $xnode_obj = simplexml_load_string($xmlstr);
                                            $this->appendSimpleXmlNode($xnode->line, $xnode_obj);
                                            $ifc ++;
                                        }
                                    }
//                                    $xnode = &$xml_node->$dkey->members;
                                    //$xnode = null;
                                    break;
                                case 'softKeyFile':
                                case 'dialTemplate': // Доработать !  
                                    $xml_ext_file = '';
                                    $templet_path = (($dkey == 'softKeyFile') ? $dev_config['tftp_softkey'] : $dev_config['tftp_dialplan']);
                                    $tmp_key = ($dkey == 'softKeyFile') ? 'softkeyset' : '_dialrules';
                                    if (!empty($dev_config[$tmp_key])) {
                                        $xml_ext_file = (($dkey == 'softKeyFile') ? 'softkey'.$dev_config[$tmp_key].'.xml' : $dev_config[$tmp_key].'.xml');
                                    }
//                                    $xml_node->$dkey = $templet_path . '/' . $xml_ext_file.'---'.$dev_config[$tmp_key];
//                                    break;
                                    if (empty($xml_ext_file) || !file_exists($templet_path . '/' . $xml_ext_file)) {
                                        $xml_ext_file = (($dkey == 'softKeyFile') ? 'softkeydefault.xml' : 'dialplan.xml');
                                    }
                                    if (file_exists($templet_path . '/' . $xml_ext_file)) {
                                        $xml_node->$dkey = $xml_ext_file;
                                    } else {
                                        $xml_node->$dkey = null;
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
//                      Set Path Image ???? 
                        if (isset($dev_config["tftp_firmware"])) {
                            $xml_work->$key = (isset($dev_config["loadimage"])) ? $dev_config["tftp_firmware"] . $dev_config["loadimage"] : '';
                        } else {
                            $xml_work->$key = (isset($dev_config["loadimage"])) ? $dev_config["loadimage"] : '';
                        }
//                        $xml_work->$key = $dev_config["loadimage"];
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
                    case 'commonProfile':
                        $xml_node->phonePassword = $data_values['dev_sshPassword'];
                        $xml_node->backgroundImageAccess = (($data_values['backgroundImageAccess'] == 'on') || ($data_values['backgroundImageAccess'] == 'true') ) ? 'true' : 'false';
                        $xml_node->callLogBlfEnabled = $data_values['callLogBlfEnabled'];
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
                        if (($lang != 'null') && (!empty($lang))) {
                            if ($key == 'networkLocale') {
                                $xml_work->$key = $lang;
                            } else {
                                if (isset($lang_info[$lang])) {
                                    $xml_node->name = $lang_info[$lang]['locale'];
                                    $xml_node->langCode = $lang_info[$lang]['code'];
                                    if ($key == 'userLocale') {
                                        $xml_node->winCharSet = $lang_info[$lang]['codepage'];
                                    }
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

    function save_DialPlan($confDir,$get_settings) {
        $xmlstr = "<DIALTEMPLATE>\n";
        $xmlstr .= "<versionStamp>".time()."</versionStamp>\n";
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
            $file = $confDir . '/dial' . $put_file . '.xml';
            file_put_contents($file, $xmlstr);
        }

        return $errors;
    }

    function create_xmlSoftkeyset($config, $confDir, $name) {
        if (empty($config[$name])) {
            if ($name =='default') {
                $typeSoft = $confDir["tftp_templates"].'/SIPDefaultSoftKey.xml_template';
                if(file_exists($typeSoft)){
                    $file = $confDir["tftp_softkey"] . '/softkey' . $name . '.xml';
                    if (!copy($typeSoft, $file)) {
                        return array('error'=>'Access error'.$name);
                    }
                }
                return array();
            } else {
                return array('error'=>'Invalid softkey Name'.$name);
            }
        }
        $errors = array();
        $xmlstr = "<softKeyCfg>\n";
        $xmlstr .= "<versionStamp>".time()."</versionStamp>\n";
//        $xmlstr .= "<typeSoftKey></typeSoftKey>\n";
        $typeSoft = $confDir["tftp_templates"].'/SIPTypeSoftKey.xml_template';
        $read_soft = "";
        if(file_exists($typeSoft)){
            $f_read = fopen($typeSoft,'r');
            while (!feof($f_read)) {
                $read_soft .= fread($f_read, 8192);
            }
            fclose($f_read);            
        }
        $xmlstr .= $read_soft;
//        $xmlstr .= $read_soft."\n</typeSoftKey>\n";
        $xmlstr .= "  <softKeySets>\n";
        foreach ($config[$name] as $key => $value) {
            $xmlstr .='    <softKeySet id="'.$key.'">'."\n";
            foreach (explode(",",$value) as $keyvalue ) {
                $xmlstr .= '      <softKey keyID="'.$keyvalue.'" />'."\n";
            }
            $xmlstr .="    </softKeySet>\n";
        }
        $xmlstr .= "  </softKeySets>\n";

        $xmlstr .= '</softKeyCfg>';
        if (empty($errors)) {
//            $put_file = str_replace(array("\n", "\r", "\t", "/", "\\", ".", ","), '', $put_file);
            $file = $confDir["tftp_softkey"] . '/softkey' . $name . '.xml';
            file_put_contents($file, $xmlstr);
        }

        return $errors;
        
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
