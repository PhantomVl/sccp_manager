<?php

/**
 * 
 */

namespace FreePBX\modules\Sccp_manager;

class sipconfigs {
//    protected $database;
//    protected $freepbx;
    
    public function __construct($parent_class = null) {
        $this->paren_class = $parent_class;
//        $freepbx
//        $this->database = $freepbx->Database;
    }

    public function info() {
        $Ver = '13.0.4';
        return Array('Version' => $Ver,
            'about' => 'Sip Setings ver: ' . $Ver);
    }

    public function get_db_sip_TableData($dataid, $data = array()) {
        global $db;
        if ($dataid == '') {
            return False;
        }
        switch ($dataid) {
            case "Device":
                $sql = "SELECT * FROM sip ORDER BY `id`";
                $tech = array();
                try {
                    $raw_settings = sql($sql, "getAll", DB_FETCHMODE_ASSOC);
                    foreach ($raw_settings as $value) {
                        if (empty($tech[$value['id']]['id'])) {
                            $tech[$value['id']]['id']= $value['id'];
                        }
                        $tech[$value['id']][$value['keyword']]=$value['data'];
                    }
                } catch (\Exception $e) {
            
                }
                return $tech;
            case "DeviceById":
                $sql = "SELECT keyword,data FROM sip WHERE id = ?";
                $sth = $db->prepare($sql);
                $tech = array();
                try {
                    $id = $data['id'];
                    $sth->execute(array($id));
                    $tech = $sth->fetchAll(\PDO::FETCH_COLUMN | \PDO::FETCH_GROUP);
                    foreach ($tech as &$value) {
                        $value = $value[0];
                    }
                } catch (\Exception $e) {
            
                }
                return $tech;
        }
        
    }
    
    public function getSipConfig() {
        $result = array();
        $def_sip_proto = 'sip';
        $def_proto = 'tcp';
        $supp_proto = '';
        
        $result['sipport'] = \FreePBX::Sipsettings()->getConfig('bindport');
        $result['tlsport'] = \FreePBX::Sipsettings()->getConfig('tlsbindport');
        $tmp_sipsetigs = \FreePBX::Sipsettings()->getChanSipSettings();
        $tmp_sip_binds = \FreePBX::Sipsettings()->getBinds();
        
        $tmp_bind_ip = !empty($tmp_sipsetigs['externhost_val']) ? $tmp_sipsetigs['externhost_val'] : '';
        $tmp_bind_ip = !empty($tmp_sipsetigs['externip_val']) ? $tmp_sipsetigs['externip_val'] : $tmp_bind_ip;
        $tmp_bind_ip = !empty($tmp_sipsetigs['bindaddr']) ? $tmp_sipsetigs['bindaddr'] : $tmp_bind_ip;

//        $result['sipbind']  =  $tmp_bind_ip;
        if (empty($tmp_sip_binds[$def_sip_proto])){
            $def_proto = 'pjsip';
        }
        
        foreach ($tmp_sip_binds[$def_sip_proto] as $key => $value) {
            if (empty($value[$def_proto])) {
                $def_proto = 'udp';
                $supp_proto = 'udp';
            }  else {
                $supp_proto = !empty($value['udp']) ? 'tcp;udp' : 'tcp';
            }
            if (empty($def_key)) {
                $def_key = $key;
            }
            if ($key != '0.0.0.0') {
                $tmp_bind_ip = $key;
            }
            $result['sipbindport'] = $value[$def_proto];
        }  
        $result['sipbind'] = $tmp_bind_ip;
        $result['sipsuportproto'] = $supp_proto;
        return $result;
    }
}
