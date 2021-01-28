<?php

/**
 *
 * Core Comsnd Interface
 *
 *  https://www.voip-info.org/asterisk-manager-example-php/
 */
/* !TODO!: Re-Indent this file.  -TODO-: What do you mean? coreaccessinterface  ??  */

namespace FreePBX\modules\Sccp_manager;

class oldinterface
{

    var $error;

    public function __construct($parent_class = null)
    {
        $this->paren_class = $parent_class;
        $this->error = "";
    }

    public function info()
    {
        $Ver = '13.0.4';
        return array('Version' => $Ver,
            'about' => 'Old interface data ver: ' . $Ver);
    }

    /*
      Core Access Function
     */


    /*
     *    Replace or dublicate to AMI interface
     */

    public function sccp_core_commands($params = array())
    {
        global $astman;
        $cmd_list = array('get_softkey' => array('cmd' => "sccp show softkeyssets", 'param' => ''),
            'get_version' => array('cmd' => "sccp show version", 'param' => ''),
            'get_device' => array('cmd' => "sccp show devices", 'param' => ''),
            'get_dev_info' => array('cmd' => "sccp show device", 'param' => 'name'),
            'get_hints' => array('cmd' => "core show hints", 'param' => ''),
            'sccp_reload' => array('cmd' => "sccp reload force", 'param' => ''),
            'reset_phone' => array('cmd' => "sccp reset ", 'param' => 'name'), // Жесткая перезагрузка
            'restart_phone' => array('cmd' => "sccp restart ", 'param' => 'name'),
            'reload_phone' => array('cmd' => "sccp reload device ", 'param' => 'name'),
            'reload_line' => array('cmd' => "sccp reload line ", 'param' => 'name'),
            'reset_token' => array('cmd' => "sccp tokenack ", 'param' => 'name'),
            'get_realtime_status' => array('cmd' => "realtime mysql status", 'param' => ''),
        );
        $result = true;
        if (!empty($params['cmd'])) {
            $id_cmd = $params['cmd'];
            if (!empty($cmd_list[$id_cmd])) {
                $id_param = $cmd_list[$id_cmd]['param'];
                if (!empty($id_param)) {
                    if (!empty($params[$id_param])) {
                        $result = $astman->Command($cmd_list[$id_cmd]['cmd'] . ' ' . $params[$id_param]);
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

    public function sccp_getdevice_info($dev_id)
    {
        if (empty($dev_id)) {
            return array();
        }
        $res = $this->sccp_core_commands(array('cmd' => 'get_dev_info', 'name' => $dev_id));
        $res1 = str_replace(array("\r\n", "\r", "\n"), ';', strip_tags((string) $res['data']));
        if (strpos($res1, 'MAC-Address')) {
            $res2 = substr($res1, 0, strpos($res1, '+--- Buttons '));
            $res1 = explode(';', substr($res2, strpos($res2, 'MAC-Address')));
            foreach ($res1 as $data) {
                if (!empty($data)) {
                    $tmp = explode(':', $data);
                    $data_key = str_replace(array(" ", "-", "\t"), '_', trim($tmp[0]));
                    $res3[$data_key] = $tmp[1];
                }
            }

            $res1 = $res3['Skinny_Phone_Type'];
            $res4 = $res3['Config_Phone_Type'];
            if (!empty($res3['Addons'])) {
                $res2 = $res3['Addons'];
            } else {
                $res2 = '';
            }
            $res3['SCCP_Vendor'] = array('vendor' => strtok($res1, ' '), 'model' => strtok('('), 'model_id' => strtok(')'), 'vendor_addon' => strtok($res2, ' '), 'model_addon' => strtok(' '));
            if (empty($res3['SCCP_Vendor']['vendor']) || $res3['SCCP_Vendor']['vendor'] == 'Undefined') {
                $res3['SCCP_Vendor'] = array('vendor' => 'Undefined', 'model' => $res4, 'model_id' => '', 'vendor_addon' => $res3['SCCP_Vendor']['vendor_addon'], 'model_addon' => $res3['SCCP_Vendor']['model_addon']);
//               return $res4;
//                return array();
            }
            return $res3;
        } else {
            return array();
        }
    }

    /*  Current not use */
    /*
     * A function should be used in the form of buttons for getting all hint. Not working. I don't know how to use properly.
     */

    public function sccp_list_hints()
    {
        $hint_key = array();
        $hint_all = $this->sccp_list_all_hints();
        foreach ($hint_all as $value) {
            $res = $this->loc_after('@', $value);
//           array_search($res, $hint_key)) != NULL)
            if (!isset($hint_key[$res])) {
                $hint_key[$res] = '@' . $res;
            }
        }
        return $hint_key;
    }

    public function sccp_list_all_hints()
    {
        $ast_out = $this->sccp_core_commands(array('cmd' => 'get_hints'));
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
                if (!is_bool(strpos($line, ':'))) {
                    $line = trim(substr($line, 0, strpos($line, ':')));
                }
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

    public function sccp_realtime_status()
    {
        $ast_res = array();
        // Below added for compatibility with AMI result and modified server.info
        $ast_res = ['sccp' => ['message' => 'default value', 'realm' => '', 'status' => 'ERROR']];
        $ast_out = $this->sccp_core_commands(array('cmd' => 'get_realtime_status'));
        $ast_out = preg_split("/[\n]/", $ast_out['data']);
        if (strpos($ast_out[0], 'Privilege') !== false) {
            $ast_out[0] = "";
        }
        foreach ($ast_out as $line) {
            if (strlen($line) > 3) {
                $ast_key = strstr(trim($line), ' ', true);
                $ast_res[$ast_key] = array('message' => $line, 'status' => strpos($line, 'connected') ? 'OK' : 'ERROR');
            }
        }
        return $ast_res;
    }

// !TODO!: -TODO-: install.php is still using the other version number. This is actually where I use another method ?


    public function get_compatible_sccp()
    {
        $res = $this->getSCCPVersion();
        if (empty($res)) {
            return 0;
        }
        switch ($res["vCode"]) {
            case 0:
                return 0;
            case 433:
                return 433;

            case 432:
            case 431:
                return 431;
            default:
                return 430;
        }
        /*        if ($res["vCode"] >= 433) {

          }
          if ($res["vCode"] >= 431) {
          return 431;
          } else {
          return 430;
          }
         *
         */
//        return $res["vCode"];
    }

    public function getSCCPVersion()
    {
        $res = $this->getChanSCCPVersion();
        if (empty($res)) {
            $res = $this->getCoreSCCPVersion();
        }
        return $res;
    }

    function getCoreSCCPVersion()
    {
        $result = array();
        $ast_out = $this->sccp_version();
        $result["Version"] = $ast_out[0];
        if ($ast_out[0] == '-1') {
            $result["vCode"] = 0;
            return $result;
        }
        $version_parts = explode(".", $ast_out[0]);
        $result["vCode"] = implode('', $version_parts);
        if (!empty($ast_out[1]) && $ast_out[1] == 'develop') {
            $result["develop"] = $ast_out[1];
            $res = 10;
// !TODO!: This does not work as you might expect
            if (base_convert($ast_out[3], 16, 10) == base_convert('702487a', 16, 10)) {
                $result["vCode"] = 431;
            }
            if (base_convert($ast_out[3], 16, 10) >= "10403") { // new method, RevisionNum is incremental
                $result["vCode"] = 432;
            }
        }
        return $result;
    }

    private function sccp_version()
    {
        $ast_out = $this->sccp_core_commands(array('cmd' => 'get_version'));
        if (($ast_out['Response'] == 'Error') ||  (strpos($ast_out['data'], 'No such command') != false)) {
            return array('-1');
        }
        if (preg_match("/Release.*\(/", $ast_out['data'], $matches)) {
            $ast_out = substr($matches[0], 9, -1);
            return explode(' ', $ast_out);
        } else {
            return array('unknown');
        }
    }

    function getChanSCCPVersion()
    {
        global $astman;
        $result = array();
        if (!$astman) {
            return $result;
        }
        $metadata = $this->astman_retrieveJSFromMetaData("");
//        return $metadata;
        if ($metadata && array_key_exists("Version", $metadata)) {
            $result["Version"] = $metadata["Version"];
            $version_parts = explode(".", $metadata["Version"]);
            $result["vCode"] = 0;

            # not sure about this sccp_ver numbering. Might be better to just check "Version" and Revision
            # $result["vCode"] = implode('', $version_parts);
            $result["vCode"] = 0;
            if ($version_parts[0] == "4") {
                $result["vCode"] = 400;
                if ($version_parts[1] == "1") {
                    $result["vCode"] = 410;
                } elseif ($version_parts[1] == "2") {
                    $result["vCode"] = 420;
                } elseif ($version_parts[1] >= "3") {
                    $result["vCode"] = 430;
                }
            }

            /*
              if (array_key_exists("Branch",$metadata)) {
              if ($metadata["Branch"] == "master") {

              } else
              if ($metadata["Branch"] == "develop") {

              }
              }
             */

            /* Revision got replaced by RevisionHash in 10404 (using the hash does not work) */
            if (array_key_exists("Revision", $metadata)) {
                if (base_convert($metadata["Revision"], 16, 10) == base_convert('702487a', 16, 10)) {
                    $result["vCode"] = 431;
                }
                if (base_convert($metadata["Revision"], 16, 10) >= "10403") {
                    $result["vCode"] = 431;
                }
            }
            if (array_key_exists("RevisionHash", $metadata)) {
                $result["RevisionHash"] = $metadata["RevisionHash"];
            } else {
                $result["RevisionHash"] = '';
            }
            if (array_key_exists("RevisionNum", $metadata)) {
                $result["RevisionNum"] = $metadata["RevisionNum"];
                if ($metadata["RevisionNum"] >= "10403") { // new method, RevisionNum is incremental
                    $result["vCode"] = 432;
                }
                if ($metadata["RevisionNum"] >= "10491") { // new method, RevisionNum is incremental
                    $result["vCode"] = 433;
                }
            }
            if (array_key_exists("ConfigureEnabled", $metadata)) {
                $result["futures"] = implode(';', $metadata["ConfigureEnabled"]);
            }
        } else {
            return null;
            die_freepbx("Version information could not be retrieved from chan-sccp, via astman::SCCPConfigMetaData");
        }
        return $result;
    }

    public function sccp_list_keysets()
    {
        $ast_out = $this->sccp_core_commands(array('cmd' => 'get_softkey'));

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

    public function sccp_get_active_device()
    {
        $ast_out = $this->sccp_core_commands(array('cmd' => 'get_device'));

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
                    if ($this->strpos_array($line_arr[$it + 1], array('SEP', 'ATA', 'VG')) === false) {
//                    if (strpos($line_arr[$it + 1], 'SEP') === false) {
                        $line_arr[0] .= ' ' . $line_arr[$it];
                        unset($line_arr[$it]);
                    } else {
                        break;
                    }
                    $it++;
                } while ((count($line_arr) > 3) and ( $it < count($line_arr)));
                explode(";|", implode(";|", $line_arr));
                list ($descr, $address, $devname, $status, $token, $junk) = explode(";|", implode(";|", $line_arr));

//                list ($descr, $address, $devname, $status, $junk) = $line_arr;
//                if (strlen($ast_key[$devname]) < 1) {
                if (strlen($devname) > 1) {
                    $ast_key[$devname] = array('name' => $devname, 'status' => $status, 'address' => $address, 'descr' => $descr, 'token' => $token);
                }
                /*
                  if (isset($ast_key[$devname])) {
                  if (strlen($ast_key[$devname]) < 1) {
                  $ast_key[$devname] = Array('name' => $devname, 'status' => $status, 'address' => $address, 'descr' => $descr, 'token' => $descr);
                  }
                  } else {
                  $ast_key[$devname] = Array('name' => $devname, 'status' => $status, 'address' => $address, 'descr' => $descr, 'token' => $token);
                  }
                 *
                 */
            }
        }
        return $ast_key;
    }

    /*
     *  Replace  sccp_core_commands($params = array()) {
     */

    private function astman_retrieveJSFromMetaData($segment = "")
    {
        global $astman;
        $params = array();
        if ($segment != "") {
            $params["Segment"] = $segment;
        }
        $response = $astman->send_request('SCCPConfigMetaData', $params);
        if ($response["Response"] == "Success") {
            //outn(_("JSON-content:").$response["JSON"]);
            $decode = json_decode($response["JSON"], true);
            return $decode;
        } else {
            return false;
        }
    }

    private function strpos_array($haystack, $needles)
    {
        if (is_array($needles)) {
            foreach ($needles as $str) {
                if (is_array($str)) {
                    $pos = $this->strpos_array($haystack, $str);
                } else {
                    $pos = strpos($haystack, $str);
                }
                if ($pos !== false) {
                    return $pos;
                }
            }
        } else {
            return strpos($haystack, $needles);
        }
        return false;
    }

    private function loc_after($value, $inthat)
    {
        if (!is_bool(strpos($inthat, $value))) {
            return substr($inthat, strpos($inthat, $value) + strlen($value));
        }
    }

    function getеtestChanSCC()
    {
        global $astman;
//        $action = Array('SCCPShowGlobals',);
        $params = array();
        $action = 'SCCPShowSoftkeySets';
        $params = array('Segment' => 'device', 'ResultFormat'=>'command' );
//        $params = array('Segment' => 'device');
//        $params = array();
        $metadata = $astman->send_request($action, $params);
        return $metadata;
    }

    /*
     *    [Segments] => ( [0] => general  [1] => device  [2] => line [3] => softkey  )
     */
    function getGlobalsFromMetaData($Segment = '')
    {
        global $astman;
        $params = array();

        $action = 'SCCPConfigMetaData';
        if (empty($Segment)) {
            $Segment = 'general';
        }
        $params = array('Segment' => $Segment, 'ResultFormat' => 'command');
        $metadata = $astman->send_request($action, $params);
        if (!empty($metadata['data'])) {
            $tmp_data = $metadata['data'];
            if (strpos($tmp_data, 'JSON:') !== false) {
                $decode = json_decode(substr($tmp_data, strpos($tmp_data, 'JSON:') + 5), true);
                $result = array();
                if (!empty($decode['Options'])) {
                    foreach ($decode['Options'] as $value) {
                        $result[$value['Name']] = $value;
                    }
                    return $result;
                }
                return $decode;
            }
        }
        return $metadata;
    }
}
