<?php

/**
 *
 * Core Comsnd Interface
 *
 *
 */

namespace FreePBX\modules\Sccp_manager;

class dbinterface
{

    private $val_null = 'NONE'; /// REPLACE to null Field

    public function __construct($parent_class = null)
    {
        $this->paren_class = $parent_class;
    }

    public function info()
    {
        $Ver = '13.0.2';    // This should be updated
        return array('Version' => $Ver,
            'about' => 'Data access interface ver: ' . $Ver);
    }

    /*
     * Core Access Function
     */
    public function get_db_SccpTableByID($dataid, $data = array(), $indexField = '')
    {
        $resut = array();
        $raw = $this->HWextension_db_SccpTableData($dataid, $data);
        if (empty($raw) || empty($indexField)) {
            return $raw;
        }
        foreach ($raw as $value) {
            $id = $value[$indexField];
            $resut[$id] = $value;
        }
        return $resut;
    }

    public function HWextension_db_SccpTableData($dataid, $data = array())
    {
        global $db;
        $stmt = '';
        $stmts = '';
        if ($dataid == '') {
            return false;
        }
        switch ($dataid) {
            case "SccpExtension":
                if (empty($data['name'])) {
                    $stmts = $db->prepare('SELECT * FROM sccpline ORDER BY name');
                } else {
                    $stmts = $db->prepare('SELECT * FROM sccpline WHERE name = $data[name]');
                }
                break;
            case "SccpDevice":
                $filtered ='';
                $singlerow = false;
                if (empty($data['fields'])) {
                    $fld = 'name, name as mac, type, button, addon, _description as description';
                } else {
                    switch ($data['fields']) {
                        case "all":
                            $fld ='*';
                            break;
                        case "sip_ext":
                            $fld ='button as sip_lines, description as description, addon';
                            break;
                        default:
                            $fld = $data['fields'];
                            break;
                    }
                }
                if (!empty($data['name'])) {
                    $filtered = 'name =' . $data['name']. '';
                    $singlerow = true;
                }
                if (!empty($data['type'])) {
                    switch ($data['type']) {
                        case "cisco-sip":
                            $filtered = 'TYPE LIKE \'%-sip\'';
                            break;
                        case "cisco":
                        default:
                            $filtered = 'TYPE not LIKE \'%-sip\'';
                            break;
                    }
                }
                if (empty($filtered)) {
                    $sql = 'SELECT ' . $fld . ' FROM sccpdeviceconfig ORDER BY name';
                } else {
                    $sql = 'SELECT ' . $fld . ' FROM sccpdeviceconfig WHERE '. $filtered . ' ORDER BY name';
                }
                if ($singlerow) {
                    $stmt = $db->prepare($sql);
                } else {
                    $stmts = $db->prepare($sql);
                }
                break;
            case "HWSipDevice":
                $raw_settings = $this->getDb_model_info($get = "sipphones", $format_list = "model");
                break;
            case "HWDevice":
                $raw_settings = $this->getDb_model_info($get = "ciscophones", $format_list = "model");
                break;
            case "HWextension":
                $raw_settings = $this->getDb_model_info($get = "extension", $format_list = "model");
                break;
            case "get_colums_sccpdevice":
                $sql = "DESCRIBE sccpdevice";
                $stmt = $db->prepare($sql);
                break;
            case "get_colums_sccpuser":
                $sql = "DESCRIBE sccpuser";
                $stmts = $db->prepare($sql);
                break;
            case "get_sccpdevice_byid":
                $sql = 'SELECT t1.*, types.dns,  types.buttons, types.loadimage, types.nametemplate as nametemplate, '
                        . 'addon.buttons as addon_buttons FROM sccpdevice AS t1 '
                        . 'LEFT JOIN sccpdevmodel as types ON t1.type=types.model '
                        . 'LEFT JOIN sccpdevmodel as addon ON t1.addon=addon.model WHERE name="' . $data['id'] . '';
                $stmt = $db->prepare($sql);
                break;
            case "get_sccpuser":
                $sql = 'SELECT * FROM sccpuser ';
                if (!empty($data['id'])) {
                    $sql .= 'WHERE name= ' . $data['id'] . '';
                }
                $sql .= ' ORDER BY name';
                $stmt = $db->prepare($sql);
                break;
            case "get_sccpdevice_buttons":
                $sql = '';
                if (!empty($data['buttontype'])) {
                    $sql .= 'buttontype="' . $data['buttontype'] . '" ';
                }
                if (!empty($data['id'])) {
                    $sql .= (empty($sql)) ? 'ref="' . $data['id'] . '" ' : 'and ref="' . $data['id'] . '" ';
                }
                if (!empty($sql)) {
                    $sql = 'SELECT * FROM sccpbuttonconfig WHERE ' .$sql. ' ORDER BY `instance`;';
                    $stmts = $db->prepare($sql);
                } else {
                    $raw_settings = array();
                }
                break;
        }
        if (!empty($stmt)) {
            $stmt->execute();
            $raw_settings = $stmt->fetch(\PDO::FETCH_ASSOC);
        } elseif (!empty($stmts)) {
            $stmts->execute();
            $raw_settings = $stmts->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $raw_settings;
    }

    public function get_db_SccpSetting()
    {
        global $db;
        $stmt = $db->prepare('SELECT keyword, data, type, seq FROM sccpsettings ORDER BY type, seq');
        $stmt->execute();
        foreach ($stmt->fetchAll() as $var) {
            $mysccpvalues[$var['keyword']] = array('keyword' => $var['keyword'], 'data' => $var['data'], 'seq' => $var['seq'], 'type' => $var['type']);
        }
        return $mysccpvalues;
    }

    public function get_db_sysvalues()
    {
        global $db;
        $stmt = $db->prepare('SHOW VARIABLES LIKE \'%group_concat%\'');
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /*
     *      Get Sccp Device Model information
     */

    function getDb_model_info($get = "all", $format_list = "all", $filter = array())
    {
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
                        if (strpos($filter['model'], 'loadInformation')) {
                            $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (loadinformationid =' . $filter['model'] . ') ORDER BY model';
                        } else {
                            $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (loadinformationid =loadInformation' . $filter['model'] . ') ORDER BY model';
                        }
                    } else {
//                          $sql = "SELECT ".$filter['model'];
                        $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel ORDER BY model';
                    }
                    break;
                }
                break;
            case "byid":
                if (!empty($filter)) {
                    if (!empty($filter['model'])) {
                        $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (model =' . $filter['model'] . ') ORDER BY model';
                    } else {
//                          $sql = "SELECT ".$filter['model'];
                        $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel ORDER BY model';
                    }
                    break;
                }
                break;
            case "extension":
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (dns = 0) and (enabled > 0) ORDER BY model'; //check table
                break;
            case "enabled":
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE enabled > 0 ORDER BY model ';
                break;
            case "phones":
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (dns > 0) and (enabled > 0) ORDER BY model '; //check table
                break;
            case "ciscophones":
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (dns > 0) and (enabled > 0) AND vendor NOT LIKE \'%-sip\' ORDER BY model';
                break;
            case "sipphones":
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (dns > 0) and (enabled > 0) AND `vendor` LIKE \'%-sip\' ORDER BY model';
                break;
            case "all":
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel ORDER BY model';
                break;
            default:
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel ORDER BY model';
                break;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function write($table_name = "", $save_value = array(), $mode = 'update', $key_fld = "", $hwid = "")
    {
        // mode clear  - Empty table before update
        // mode update - update / replace record
        global $db;
//        global $amp_conf;
        $result = false;
        $delete_value = array();
        switch ($table_name) {
            case 'sccpsettings':
                foreach ($save_value as $key_v => $data) {
                    if (!empty($data) && isset($data['data'])) {
                            if ($data['data'] == $this->val_null) {
                                $delete_value[] = $save_value[$key_v]['keyword'];
                                unset($save_value[$key_v]);
                            }
/*                      if (isset($data['data'])) {
                            if ($data['data'] == $this->val_null) {
                                $delete_value[] = $save_value[$key_v]['keyword'];
                                unset($save_value[$key_v]);
                            }
                        }
*/                    }
                }
                if ($mode == 'clear') {
//                    $sql = 'truncate `sccpsettings`';
                    $db->prepare('TRUNCATE sccpsettings')->execute();
                    $stmt = $db->prepare('INSERT INTO sccpsettings (`keyword`, `data`, `seq`, `type`) VALUES (?,?,?,?)');
                    $result = $db->executeMultiple($stmt, $save_value);
                } else {
                    if (!empty($delete_value)) {
                        $stmt = $db->prepare('DELETE FROM sccpsettings WHERE `keyword`=?');
                        $result = $db->executeMultiple($stmt, $delete_value);
                    }
                    if (!empty($save_value)) {
                        $stmt = $db->prepare('REPLACE INTO sccpsettings (`keyword`, `data`, `seq`, `type`) VALUES (?,?,?,?)');
                        $result = $db->executeMultiple($stmt, $save_value);
                    }
                }
                break;
            case 'sccpdevmodel':
                break;
            case 'sccpdevice':
                break;
            case 'sccpuser':
                $sql_db = $table_name;
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
                        $req = 'DELETE FROM sccpuser WHERE ' . $sql_key . '';
                    } else {
                        if ($mode == 'update') {
                            $req = 'UPDATE sccpuser SET ' . $sql_var .  WHERE  . $sql_key . '';
                        } else {
                            $req = 'REPLACE INTO sccpuser SET ' . $sql_var . '';
                        }
                    }
                }
                $result = $db->prepare($req)->execute();
                break;
            case 'sccpbuttons':
                if (($mode == 'clear') || ($mode == 'delete')) {
                    $sql = 'DELETE FROM sccpbuttonconfig WHERE ref=' . $hwid . '';
                    $result = $db->prepare($sql)->execute();
                }
                if ($mode == 'delete') {
                    break;
                }
                if (empty($save_value)) {
                    break;
                }
                if ($mode == 'replace') {
                    $sql = 'UPDATE sccpbuttonconfig SET `name`=? WHERE  `ref`= ? AND `reftype`=? AND `instance`=?  AND `buttontype`=?';
//                    $sql = 'INSERT INTO `sccpbuttonconfig` (`ref`, `reftype`,`instance`, `buttontype`, `name`, `options`) VALUES (?,?,?,?,?,?);';
//                    die(print_r($save_value,1));
                    $stmt = $db->prepare($sql);
                    $result= $db->executeMultiple($stmt, $save_value);
                } else {
                    $sql = 'INSERT INTO sccpbuttonconfig (`ref`, `reftype`,`instance`, `buttontype`, `name`, `options`) VALUES (?,?,?,?,?,?)';
//                    die(print_r($save_value,1));
                    $stmt = $db->prepare($sql);
                    $result = $db->executeMultiple($stmt, $save_value);
                }

                break;
        }
        return $result;
    }

    /*
     *  My be Replace by SccpTables ??!
     *
     */
    public function dump_sccp_tables($data_path, $database, $user, $pass)
    {
        $filename = $data_path.'/sccp_backup_'.date('G_a_m_d_y').'.sql';
        $result = exec('mysqldump '.$database.' --password='.$pass.' --user='.$user.' --single-transaction >'.$filename, $output);
        return $filename;
    }

/*
 *  Check Table structure
 */
    public function validate()
    {
        global $db;
        $result = false;
        $check_fields = array('430' => array('_hwlang' => "varchar(12)"), '431' => array('private'=> "enum('on','off')"), '433' => array('directed_pickup'=>'') );
        $stmt = $db->prepare('DESCRIBE sccpdevice');
        $stmt->execute();
        foreach ($stmt->fetchAll() as $value) {
            $id_result[$value['Field']] = $value['Type'];
        }
        foreach ($check_fields as $key => $value) {
            $sub_result = true;
            foreach ($value as $skey => $svalue) {
                if (!empty($svalue)) {
                    if (empty($id_result[$skey])) {
                        $sub_result = false;
                    } else {
                        if (strtolower($id_result[$skey]) != strtolower($svalue)) {
                            $sub_result = false;
                        }
                    }
                } else {
                    if (!empty($id_result[$skey])) {
                        $sub_result = false;
                    }
                }
            }
            if ($sub_result) {
                $result = $key;
            } else {
                break;
            }
        }

        return $result;
    }
}
