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
        return $result;
    }

    public function HWextension_db_SccpTableData($dataid, $data = array())
    {
        // $stmt is a single row fetch, $stmts is a fetchAll.
        $dbh = \FreePBX::Database();
        $stmt = '';
        $stmts = '';
        if ($dataid == '') {
            return false;
        }
        switch ($dataid) {
            case 'SccpExtension':
                if (empty($data['name'])) {
                    $stmts = $dbh->prepare('SELECT * FROM sccpline ORDER BY name');
                } else {
                    $stmts = $dbh->prepare('SELECT * FROM sccpline WHERE name = :name');
                    $stmts->bindParam(':name', $data['name'],\PDO::PARAM_STR);
                }
                break;
            case 'SccpDevice':
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
                if (!empty($data['name'])) {      //either filter by name or by type
                    $stmt = $dbh->prepare('SELECT ' . $fld . ' FROM sccpdeviceconfig WHERE name = :name  ORDER BY name');
                    $stmt->bindParam(':name', $data['name'],\PDO::PARAM_STR);
                } elseif (!empty($data['type'])) {
                    switch ($data['type']) {
                        case "cisco-sip":
                            $stmts = $dbh->prepare('SELECT ' . $fld . ' FROM sccpdeviceconfig WHERE TYPE LIKE \'%-sip\' ORDER BY name');
                            break;
                        case "cisco":      // Fall through to default intentionally
                        default:
                            $stmts = $dbh->prepare('SELECT ' . $fld . ' FROM sccpdeviceconfig WHERE TYPE not LIKE \'%-sip\' ORDER BY name');
                            break;
                    }
                } else {      //no filter and no name provided - return all
                    $stmts = $dbh->prepare('SELECT ' . $fld . ' FROM sccpdeviceconfig WHERE ORDER BY name');
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
                $stmts = $dbh->prepare('DESCRIBE sccpdevice');
                break;
            case 'get_columns_sccpuser':
                $stmts = $dbh->prepare('DESCRIBE sccpuser');
                break;
            case 'get_sccpdevice_byid':
                $stmt = $dbh->prepare('SELECT t1.*, types.dns,  types.buttons, types.loadimage, types.nametemplate as nametemplate,
                        addon.buttons as addon_buttons FROM sccpdevice AS t1
                        LEFT JOIN sccpdevmodel as types ON t1.type=types.model
                        LEFT JOIN sccpdevmodel as addon ON t1.addon=addon.model WHERE name = :name');
                $stmt->bindParam(':name', $data['id'],\PDO::PARAM_STR);
                break;
            case 'get_sccpuser':
                $stmt = $dbh->prepare('SELECT * FROM sccpuser WHERE name = :name');
                $stmt->bindParam(':name', $data['id'],\PDO::PARAM_STR);
                break;
            case 'get_sccpdevice_buttons':
                $sql = '';
                if (!empty($data['buttontype'])) {
                    $sql .= 'buttontype = :buttontype';
                }
                if (!empty($data['id'])) {
                    $sql .= (empty($sql)) ? 'ref = :ref' : ' and ref = :ref';
                }
                if (!empty($sql)) {
                    $stmts = $dbh->prepare("SELECT * FROM sccpbuttonconfig WHERE {$sql} ORDER BY instance");
                    // Now bind labels - only bind label if it exists or bind will create exception.
                    // can only bind once have prepared, so need to test again.
                    if (!empty($data['buttontype'])) {
                        $stmts->bindParam(':buttontype', $data['buttontype'],\PDO::PARAM_STR);
                    }
                    if (!empty($data['id'])) {
                        $stmts->bindParam(':ref', $data['id'],\PDO::PARAM_STR);
                    }
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
        $dbh = \FreePBX::Database();
        try {
            $stmt = $dbh->prepare('SELECT keyword, data, type, seq FROM sccpsettings ORDER BY type, seq');
            $stmt->execute();
            foreach ($stmt->fetchAll() as $var) {
                $mysccpvalues[$var['keyword']] = array('keyword' => $var['keyword'], 'data' => $var['data'], 'seq' => $var['seq'], 'type' => $var['type']);
            }
            return $mysccpvalues;
        } catch(\PDOException $e) {
            // sccpsettings table does not yet exist. FreePBX is instantiating
            // a SCCP_Manager object from the Installer before the installer can
            // create the table so will create here.
            $stmt = $dbh-> prepare('CREATE TABLE IF NOT EXISTS sccpsettings (
                    keyword VARCHAR (50) NOT NULL,
                    data    VARCHAR (255) NOT NULL,
                    seq     TINYINT (1),
                    type    TINYINT (1) NOT NULL default 0,
                    PRIMARY KEY (keyword, seq, type )
                    );');
            $stmt->execute();
            //if ($dbh::IsError($check)) {
            //    die_freepbx("Can not create sccpsettings table, error: $check\n");
            //}
            return array();
        }
    }

    public function get_db_sysvalues()
    {
        $dbh = \FreePBX::Database();
        $stmt = $dbh->prepare('SHOW VARIABLES LIKE \'%group_concat%\'');
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /*
     *      Get Sccp Device Model information
     */

    function getDb_model_info($get = 'all', $format_list = 'all', $filter = array())
    {
        $dbh = \FreePBX::Database();
        $sel_inf = '*, 0 as validate';
        if ($format_list === 'model') {
            $sel_inf = 'model, vendor, dns, buttons, 0 as validate';
        }
        switch ($get) {
            case 'byciscoid':
                if (!empty($filter)) {
                    if (!empty($filter['model'])) {
                        if (strpos($filter['model'], 'loadInformation')) {
                            $stmt = $dbh->prepare('SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (loadinformationid =' . $filter['model'] . ') ORDER BY model');
                        } else {
                            $stmt = $dbh->prepare('SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (loadinformationid = loadInformation' . $filter['model'] . ') ORDER BY model');
                        }
                    } else {
                        $stmt = $dbh->prepare('SELECT ' . $sel_inf . ' FROM sccpdevmodel ORDER BY model');
                    }
                    break;
                }
                break;
            case 'byid':
                if (!empty($filter)) {
                    if (!empty($filter['model'])) {
                        $stmt = $dbh->prepare('SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE model = :model ORDER BY model');
                        $stmt->bindParam(':model', $filter['model'],\PDO::PARAM_STR);
                    } else {
                        $stmt = $dbh->prepare('SELECT ' . $sel_inf . ' FROM sccpdevmodel ORDER BY model');
                    }
                    break;
                }
                break;
            case 'extension':
                $stmt = $dbh->prepare('SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (dns = 0) and (enabled = 1) ORDER BY model');
                break;
            case 'enabled':
                //$stmt = $db->prepare('SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE enabled = 1 ORDER BY model'); //previously this fell through to phones.
                //break;  // above includes expansion modules but was not original behaviour so commented out. Falls through to phones.
            case 'phones':
                $stmt = $dbh->prepare('SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (dns > 0) and (enabled = 1) ORDER BY model ');
                break;
            case 'ciscophones':
                $stmt = $dbh->prepare('SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (dns > 0) and (enabled = 1) AND vendor NOT LIKE \'%-sip\' ORDER BY model');
                break;
            case 'sipphones':
                $stmt = $dbh->prepare('SELECT ' . $sel_inf . ' FROM sccpdevmodel WHERE (dns > 0) and (enabled = 1) AND `vendor` LIKE \'%-sip\' ORDER BY model');
                break;
            case 'all':     // Fall through to default
            default:
                $stmt = $dbh->prepare('SELECT ' . $sel_inf . ' FROM sccpdevmodel ORDER BY model');
                break;
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    function write($table_name = "", $save_value = array(), $mode = 'update', $key_fld = "", $hwid = "")
    {
        // mode clear  - Empty table before update
        // mode update - update / replace record
        $dbh = \FreePBX::Database();
        $result = false;
        $delete_value = array();
        switch ($table_name) {
            case 'sccpsettings':
                if ($mode == 'replace') {  // Change mode name to be more transparent
                    $dbh->prepare('TRUNCATE sccpsettings')->execute();
                    $stmt = $dbh->prepare('INSERT INTO sccpsettings (keyword, data, seq, type) VALUES (:keyword,:data,:seq,:type)');
                } else {
                    $stmt = $dbh->prepare('REPLACE INTO sccpsettings (keyword, data, seq, type) VALUES (:keyword,:data,:seq,:type)');
                }
                foreach ($save_value as $key => $dataArr) {
                    if (!empty($dataArr) && isset($dataArr['data'])) {
                        if ($dataArr['data'] == $this->val_null) {
                              $delete_value[] = $save_value[$key]['keyword'];
                              break;
                        }
                    }
                    $stmt->bindParam(':keyword',$dataArr['keyword'],\PDO::PARAM_STR);
                    $stmt->bindParam(':data',$dataArr['data'],\PDO::PARAM_STR);
                    $stmt->bindParam(':seq',$dataArr['seq'],\PDO::PARAM_INT);
                    $stmt->bindParam(':type',$dataArr['type'],\PDO::PARAM_INT);
                    $result = $stmt->execute();
                }
                if (!empty($delete_value)) {
                    $stmt = $dbh->prepare('DELETE FROM sccpsettings WHERE keyword = :keyword');
                    foreach ($delete_value as $del_key) {
                        $stmt->bindParam(':keyword',$del_key,\PDO::PARAM_STR);
                        $result = $stmt->execute();
                    }
                }
                break;
            case 'sccpdevmodel':    // Fall through to next intentionally
            case 'sccpdevice':      // Fall through to next intentionally
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
                        $sql_var .= $key_v . ' = \'' . $data . '\''; //quote data as normally is string
                    }
                    if ($key_v === $key_fld) {
                        $sql_key = $key_v . ' = \'' . $data . '\'';  //quote data as normally is string
                    }
                }
                if (!empty($sql_var)) {
                    switch ($mode) {
                        case 'delete':
                            $stmt = $dbh->prepare('DELETE FROM '. $table_name . ' WHERE ' . $sql_key);
                            break;
                        case 'update':
                            $stmt = $dbh->prepare('UPDATE ' . $table_name . ' SET ' . $sql_var . ' WHERE ' . $sql_key);
                            break;
                        case 'replace':
                            $stmt = $dbh->prepare('REPLACE INTO ' . $table_name . ' SET ' . $sql_var);
                            break;
                        // no default mode - must be explicit.
                    }
                }
                $result = $stmt->execute();
                break;
            case 'sccpbuttons':
                switch ($mode) {
                    case 'delete':
                        $sql = 'DELETE FROM sccpbuttonconfig WHERE ref = :hwid';
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindParam(':hwid', $hwid,\PDO::PARAM_STR);
                        $result = $stmt->execute();
                        break;
                    case 'replace':
                        foreach ($save_value as $button_array) {
                            $stmt = $dbh->prepare('UPDATE sccpbuttonconfig SET name =:name WHERE  ref = :ref AND reftype =:reftype AND instance = :instance  AND buttontype = :buttontype');
                            $stmt->bindParam(':ref', $button_array['ref'],\PDO::PARAM_STR);
                            $stmt->bindParam(':reftype', $button_array['reftype'],\PDO::PARAM_STR);
                            $stmt->bindParam(':instance', $button_array['instance'],\PDO::PARAM_INT);
                            $stmt->bindParam(':buttontype', $button_array['type'],\PDO::PARAM_STR);
                            $stmt->bindParam(':name', $button_array['name'],\PDO::PARAM_STR);
                            $result= $dbh->execute();
                        }
                        break;
                    case 'add':
                        foreach ($save_value as $button_array) {
                            $stmt = $dbh->prepare('INSERT INTO sccpbuttonconfig (ref, reftype, instance, buttontype, name, options) VALUES (:ref, :reftype, :instance, :buttontype, :name, :options)');
                            $stmt->bindParam(':ref', $button_array['ref'],\PDO::PARAM_STR);
                            $stmt->bindParam(':reftype', $button_array['reftype'],\PDO::PARAM_STR);
                            $stmt->bindParam(':instance', $button_array['instance'],\PDO::PARAM_INT);
                            $stmt->bindParam(':buttontype', $button_array['type'],\PDO::PARAM_STR);
                            $stmt->bindParam(':name', $button_array['name'],\PDO::PARAM_STR);
                            $stmt->bindParam(':options', $button_array['options'],\PDO::PARAM_STR);
                            $result = $stmt->execute();
                        }
                        break;
                    case 'clear';
                        // Clear is equivalent of delete + insert.
                        $this->write('sccpbuttons', '', $mode = 'delete','', $hwid);
                        $this->write('sccpbuttons', $save_value, $mode = 'add','', $hwid);
                        break;
                    // No default case - must be specific in request.
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
        $dbh = \FreePBX::Database();
        $result = 0;
        $check_fields = [
                        '430' => ['_hwlang' => "varchar(12)"],
                        '431' => ['private'=> "enum('on','off')"],
                        '433' => ['directed_pickup'=>'']
                        ];
        $stmt = $dbh->prepare('DESCRIBE sccpdevice');
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
