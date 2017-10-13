<?php
    /**
     * 
     * Core Comsnd Interface 
     * 
     * 
     */

namespace FreePBX\modules\Sccp_manager;
class srvinterface {

    public function __construct() {
        }
       public function info() {
           return Array('Ver' =>'server interface data');
       }

/*
     Core Access Function
 */      
    public function sccp_core_comands($params = array()) {
        global $astman;
        $cmd_list = array('get_softkey' => array('cmd' => "sccp show softkeyssets", 'param' => ''),
            'get_version' => array('cmd' => "sccp show version", 'param' => ''),
            'get_device' => array('cmd' => "sccp show devices", 'param' => ''),
            'get_dev_info' => array('cmd' => "sccp show device", 'param' => 'name'),
            'get_hints' => array('cmd' => "core show hints", 'param' => ''),
            'sccp_reload' => array('cmd' => "sccp reload force", 'param' => ''),
            'reset_phone' => array('cmd' => "sccp reset ", 'param' => 'name'), // Жесткая перезагрузка 
            'reload_phone' => array('cmd' => "sccp reload device ", 'param' => 'name'),
            'reset_token' => array('cmd' => "sccp tokenack ", 'param' => 'name'),
        );
        $result = true;
        if (!empty($params['cmd'])) {
            $id_cmd = $params['cmd'];
            if (!empty($cmd_list[$id_cmd])) {
                $id_param = $cmd_list[$id_cmd]['param'];
                if (!empty($id_param)) {
                    if (!empty($params[$id_param])) {
                        $result = $astman->Command($cmd_list[$id_cmd]['cmd'] .' '. $params[$id_param]);
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
    
    public function sccp_getdevice_info($dev_id) {
        if (empty($dev_id)) {
            return array();
        }
        $res = $this->sccp_core_comands(array('cmd' => 'get_dev_info', 'name' => $dev_id));
        $res1 = str_replace(array("\r\n", "\r", "\n"), ';',  strip_tags((string)$res['data'])); 
        if (strpos($res1,'MAC-Address')) {
            $res2 = substr($res1,0,strpos($res1,'+--- Buttons '));
            $res1 = explode(';',substr($res2,strpos($res2,'MAC-Address')));
            foreach ($res1 as $data ){
                if (!empty($data)) {
                    $tmp = explode(':',$data);
                    $data_key =str_replace(array(" ", "-", "\t"), '_',  trim($tmp[0])); 
                    $res3[$data_key] =$tmp[1];
                }
            }
            
            $res1 = $res3['Skinny_Phone_Type'];
            if (!empty($res3['Addons'])) {
                $res2 = $res3['Addons'];
            } else {
                $res2 = '';
            }
            $res3['SCCP_Vendor']= Array('vendor' => strtok($res1,' '),'model' => strtok('('), 'model_id' => strtok(')'), 'vendor_addon' => strtok($res2,' '), 'model_addon' => strtok(' '));
            return $res3;
        } else {
            return array();
        }
    }
/*  Current not use */    
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
    
    public function get_comatable_sccp() {
        $res = 0;        
        $ast_out = $this->sccp_version();
        if ($ast_out[0] >= '4.3.0'){
            $res = 1;
        }
        if (!empty($ast_out[1]) && $ast_out[1] == 'develop'){           
            $res = 10;
            if (!empty($ast_out[3])) {
                if (base_convert($ast_out[3],16,10) >= base_convert('702487a',16,10)){           
                            $res  += 1;
                }
            }
        }
        return $res;
        
    }
// rename public - >  privat 
    public function sccp_version() {
        $ast_out = $this->sccp_core_comands(array('cmd' => 'get_version'));
        if (preg_match("/Release.*\(/", $ast_out['data'] , $matches)) {
            $ast_out = substr($matches[0],9,-1);
            return explode(' ', $ast_out);
        } else {
            return aray('unknown');
        }
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
                    if (strpos($line_arr[$it + 1], 'SEP') === false) {
                        $line_arr[0] .= ' ' . $line_arr[$it];
                        unset($line_arr[$it]);
                    } else {
                        break;
                    }
                    $it++;
                } while ((count($line_arr) > 3) and ( $it < count($line_arr)));
                explode(";|", implode(";|", $line_arr));
                list ($descr, $adress, $devname, $status, $token, $junk) = explode(";|", implode(";|", $line_arr));

//                list ($descr, $adress, $devname, $status, $junk) = $line_arr;                

//                if (strlen($ast_key[$devname]) < 1) {
                if (strlen($devname) > 1) {
                    $ast_key[$devname] = Array('name' => $devname, 'status' => $status, 'address' => $adress, 'descr' => $descr, 'token' => $token);
                }
/*
                if (isset($ast_key[$devname])) {
                    if (strlen($ast_key[$devname]) < 1) {
                        $ast_key[$devname] = Array('name' => $devname, 'status' => $status, 'address' => $adress, 'descr' => $descr, 'token' => $descr);
                    }
                } else {
                    $ast_key[$devname] = Array('name' => $devname, 'status' => $status, 'address' => $adress, 'descr' => $descr, 'token' => $token);
                }
 * 
 */
            }
        }
        return $ast_key;
    }

        
}