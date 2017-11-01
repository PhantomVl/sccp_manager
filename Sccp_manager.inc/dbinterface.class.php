<?php
    /**
     * 
     * Core Comsnd Interface 
     * 
     * 
     */

namespace FreePBX\modules\Sccp_manager;
class dbinterface {
    private $val_null = 'NONE'; /// REPLACE to null Field

    public function __construct() {
        }
       public function info() {
           return Array('Version' => '13.0.2',
                        'about' =>'Data access interface v. 13.0.2');
       }

/*
     Core Access Function
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
                    $sql = "SELECT * FROM `sccpline` WHERE `id`=" . $data['id'];
                    $raw_settings = sql($sql, "getAll", DB_FETCHMODE_ASSOC);
                }
                break;
            case "SccpDevice":
//                $sql = "SELECT * FROM `sccpdeviceconfig` ORDER BY `name`";
                $sql = "select `name`,`name` as `mac`, `type`, `button`, `addon` from `sccpdeviceconfig` ORDER BY `name`";
                $raw_settings = sql($sql, "getAll", DB_FETCHMODE_ASSOC);
                break;
            case "HWDevice":
                $raw_settings = $this->getDb_model_info($get = "phones", $format_list = "model");
                break;
            case "HWextension":
                $raw_settings = $this->getDb_model_info($get = "extension", $format_list = "model");
                break;
            case "get_colums_sccpdevice":
                $sql = "DESCRIBE sccpdevice";
                $raw_settings = sql($sql, "getAll", DB_FETCHMODE_ASSOC);
                break;
            case "get_sccpdevice_byid":
                $sql = 'SELECT t1.*, types.dns,  types.buttons, types.loadimage, types.nametemplate as nametemplate, '
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

    public function get_db_SccpSetting() {
        $sql = "SELECT `keyword`, `data`, `type`, `seq` FROM `sccpsettings` ORDER BY `type`, `seq`";
        $raw_settings = sql($sql, "getAll", DB_FETCHMODE_ASSOC);
        return $raw_settings;
    }

    /*
     *      Get Sccp Device Model information
     *      
     */

    function getDb_model_info($get = "all", $format_list = "all", $filter = array()) {
        global $db;
        switch ($format_list) {
            case "model":
                $sel_inf = "model, vendor, dns, buttons";
                break;
            case "all":
            default:
                $sel_inf = "*";
                break;
        }

        $sel_inf .= ", '0' as 'validate'";
        switch ($get) {
            case "byciscoid":
                if (!empty($filter)) {
                    if (!empty($filter['model'])) {
                        if (strpos($filter['model'],'loadInformation')) { 
                            $sql = "SELECT " . $sel_inf . " FROM sccpdevmodel WHERE (`loadinformationid` ='" . $filter['model'] . "') ORDER BY model ";
                        } else  {
                            $sql = "SELECT " . $sel_inf . " FROM sccpdevmodel WHERE (`loadinformationid` ='loadInformation" . $filter['model'] . "') ORDER BY model ";
                        }
                    } else {
//                          $sql = "SELECT ".$filter['model'];
                        $sql = "SELECT " . $sel_inf . " FROM sccpdevmodel ORDER BY model ";
                    }
                    break;
                }
                break;
            case "byid":
                if (!empty($filter)) {
                    if (!empty($filter['model'])) {
                        $sql = "SELECT " . $sel_inf . " FROM sccpdevmodel WHERE (`model` ='" . $filter['model'] . "') ORDER BY model ";
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
        return $raw_settings;
    }

    function sccp_save_db($db_name = "", $save_value = array(), $mode = 'update', $key_fld = "", $hwid = "") {
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
                    if ($data === $this->val_null) {
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

        
}