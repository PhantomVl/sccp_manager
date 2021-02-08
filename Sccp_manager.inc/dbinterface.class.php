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
        $result = array();
        $raw = $this->HWextension_db_SccpTableData($dataid, $data);
        if (empty($raw) || empty($indexField)) {
            return $raw;
        }
        foreach ($raw as $value) {
            $id = $value[$indexField];
            $result[$id] = $value;
        }
        return $resut;
    }

    public function HWextension_db_SccpTableData($dataid, $data = array())
    {
        // $stmt is a single row fetch, $stmts is a fetchAll.
        global $db;
        $stmt = '';
        $stmts = '';
        if ($dataid == '') {
            return false;
        }
        switch ($dataid) {
            case 'SccpExtension':
                if (empty($data['name'])) {
                    $stmts = $db->prepare('SELECT * FROM sccpline ORDER BY name');
                } else {
                    $stmts = $db->prepare('SELECT * FROM sccpline WHERE name = $data[name]');
                }
                break;
            case 'SccpDevice':
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
            case 'HWSipDevice':
                $raw_settings = $this->getDb_model_info($get = "sipphones", $format_list = "model");
                break;
            case 'HWDevice':
                $raw_settings = $this->getDb_model_info($get = "ciscophones", $format_list = "model");
                break;
            case 'HWextension':
                $raw_settings = $this->getDb_model_info($get = "extension", $format_list = "model");
                break;
            case 'get_columns_sccpdevice':
                $sql = 'DESCRIBE sccpdevice';
                $stmt = $db->prepare($sql);
                break;
            case 'get_columns_sccpuser':
                $sql = 'DESCRIBE sccpuser';
                $stmts = $db->prepare($sql);
                break;
            case 'get_sccpdevice_byid':
                $sql = 'SELECT t1.*, types.dns,  types.buttons, types.loadimage, types.nametemplate as nametemplate,
                        addon.buttons as addon_buttons FROM sccpdevice AS t1
                        LEFT JOIN sccpdevmodel as types ON t1.type=types.model
                        LEFT JOIN sccpdevmodel as addon ON t1.addon=addon.model WHERE name =\'' . $data['id'] . '\'';
                $stmt = $db->prepare($sql);
                break;
            case 'get_sccpuser':
                $sql = 'SELECT * FROM sccpuser ';
                if (!empty($data['id'])) {
                    $sql .= 'WHERE name= ' . $data['id'] . '';
                }
                $sql .= ' ORDER BY name';
                $stmt = $db->prepare($sql);
                break;
            case 'get_sccpdevice_buttons':
                $sql = '';
                if (!empty($data['buttontype'])) {
                    $sql .= 'buttontype="' . $data['buttontype'] . '" ';
                }
                if (!empty($data['id'])) {
                    $sql .= (empty($sql)) ? 'ref="' . $data['id'] . '" ' : 'and ref="' . $data['id'] . '';
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

    function getDb_model_info($get = 'all', $format_list = 'all', $filter = array())
    {
        global $db;
        $sel_inf = '*, 0 as validate';
        if ($format_list === 'model') {
            $sel_inf = 'model, vendor, dns, buttons, 0 as validate';
        }
        switch ($get) {
            case 'byciscoid':
                if (!empty($filter)) {
                    if (!empty($filter['model'])) {
                        if (strpos($filter['model'], 'loadInformation')) {
                            $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (loadinformationid =' . $filter['model'] . ') ORDER BY model';
                        } else {
                            $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (loadinformationid = loadInformation' . $filter['model'] . ') ORDER BY model';
                        }
                    } else {
//                          $sql = "SELECT ".$filter['model'];
                        $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel ORDER BY model';
                    }
                    break;
                }
                break;
            case 'byid':
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
            case 'extension':
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (dns = 0) and (enabled > 0) ORDER BY model'; //check table
                break;
            case 'enabled':
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE enabled > 0 ORDER BY model '; //previously this fell through to phones.
                break;
            case 'phones':
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (dns > 0) and (enabled > 0) ORDER BY model '; //check table
                break;
            case 'ciscophones':
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (dns > 0) and (enabled > 0) AND vendor NOT LIKE \'%-sip\' ORDER BY model';
                break;
            case 'sipphones':
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (dns > 0) and (enabled > 0) AND `vendor` LIKE \'%-sip\' ORDER BY model';
                break;
            case 'all':     // Fall through to default
            default:
                $sql = 'SELECT ' . $sel_inf . ' FROM sccpdevmodel ORDER BY model';
                break;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    function write($table_name = "", $save_value = array(), $mode = 'update', $key_fld = "", $hwid = "")
    {
//dbug('entering write for table', $table_name);
if ($table_name === 'sccpdevmodel'){
dbug('entering write with save_value', $save_value);
dbug('entering write with mode', $mode);
dbug('entering write with key_fld', $key_fld);
dbug('entering write with hwid', $hwid);
}
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
                    $stmt = $db->prepare('INSERT INTO sccpsettings (keyword, data, seq, type) VALUES (?,?,?,?)');
                    $result = $db->executeMultiple($stmt, $save_value);
                } else {
                    if (!empty($delete_value)) {
                        $stmt = $db->prepare('DELETE FROM sccpsettings WHERE keyword = ?');
                        $result = $db->executeMultiple($stmt, $delete_value);
                    }
                    if (!empty($save_value)) {
                        $stmt = $db->prepare('REPLACE INTO sccpsettings (keyword, data, seq, type) VALUES (?,?,?,?)');
                        $result = $db->executeMultiple($stmt, $save_value);
                    }
                }
                break;
            case 'sccpdevmodel':    // Fall through to next intentionally
            case 'sccpdevice':    // Fall through to next intentionally
            case 'sccpuser':
                $sql_key = "";
                $sql_var = "";
                foreach ($save_value as $key_v => $data) {
                    if (!empty($sql_var)) {
                        $sql_var .= ', ';
                    }
                    if ($data === $this->val_null) {
                        $sql_var .= $key_v . '= NULL';
                    } else {
                        $sql_var .= $key_v . ' = \'' . $data . '\'';
                    }
                    if ($key_fld == $key_v) {
                        $sql_key = $key_v . ' = \'' . $data . '\'';
                    }
                }
                if (!empty($sql_var)) {
                    switch ($mode) {
                        case 'delete':
                            $req = 'DELETE FROM '. $table_name . ' WHERE ' . $sql_key;
                            break;
                        case 'update':
                            $req = 'UPDATE ' . $table_name . ' SET ' . $sql_var . ' WHERE ' . $sql_key;
                            break;
                        default:
                            $req = 'REPLACE INTO ' . $table_name . ' SET ' . $sql_var;
                    }
                }
                $result = $db->prepare($req)->execute();
                break;
            case 'sccpbuttons':
                switch ($mode) {
                    case 'clear':   // no break here as clear is same as delete
                    case 'delete':
                        $sql = 'DELETE FROM sccpbuttonconfig WHERE ref=' . $hwid . '';
                        $result = $db->prepare($sql)->execute();
                        break;
                    case 'replace':
                        if (!empty($save_value)) {
                            $sql = 'UPDATE sccpbuttonconfig SET name =? WHERE  ref = ? AND reftype =? AND instance =?  AND buttontype =?';
                            $stmt = $db->prepare($sql);
                            $result= $db->executeMultiple($stmt, $save_value);
                        }
                        break;
                    default:
                        if (!empty($save_value)) {
                            $sql = 'INSERT INTO sccpbuttonconfig (ref, reftype, instance, buttontype, name, options) VALUES (?,?,?,?,?,?)';
                            $stmt = $db->prepare($sql);
                            $result = $db->executeMultiple($stmt, $save_value);
                        }
                }
        }
        return $result;
    }

    /*
     *  Maybe Replace by SccpTables ??!
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
        $result = 0;
        $check_fields = [
                        '430' => ['_hwlang' => "varchar(12)"],
                        '431' => ['private'=> "enum('on','off')"],
                        '433' => ['directed_pickup'=>'']
                        ];
        $stmt = $db->prepare('DESCRIBE sccpdevice');
        $stmt->execute();
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $value) {
            $id_result[$value['Field']] = $value['Type'];
        }
        foreach ($check_fields as $key => $value) {
            if (!empty(array_intersect_assoc($value, $id_result))) {
                  $result = $key;
            } else {
                // no match but maybe checking against an empty string so just need to check key does not exist
                foreach ($value as $skey => $svalue) {
                    if (empty($svalue) && (!isset($id_result[$skey]))) {
                        $result = $key;
                    }
                }
            }
        }
        return $result;
    }
}
