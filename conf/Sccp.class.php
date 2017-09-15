<?php
// vim: set ai ts=4 sw=4 ft=php:
// Version for SCCP Manager 13.0.0.A 
namespace FreePBX\modules\Core\Drivers;
class Sccp extends \FreePBX\modules\Core\Driver {
        private $data_fld = array("pin"=>'pin', "label" => 'label', "accountcode" => 'account',
                                 "context" =>'context',"incominglimit"=>'incominglimit',
                                 "callgroup"=>'callgroup',"pickupgroup"=>'pickupgroup',
                                 "transfer" => 'transfer', "echocancel" => 'echocancel',
                                 "language" => 'language', "description" => 'callerid',
                                 "cid_num" => 'cid_num', "cid_name" => 'label', "mailbox" => 'description',
                                 "musicclass" => 'musicclass',
                                 "dnd" => 'dnd', "silencesuppression" => 'silencesuppression',
                                 'namedcallgroup'=>'namedcallgroup', 'namedpickupgroup' => 'namedpickupgroup'
                            );

	public function getInfo() {
		return array(
			"rawName" => "sccp",
			"hardware" => "sccp_custom",
			"prettyName" => _("Sccp Custom Driver"),
			"shortName" => _("Sccp"),
			"description" => _("Sccp Device")
		);
	}
       public function addDevice1($id, $settings) {
                $sql = 'INSERT INTO sccp (id, keyword, data, flags) values (?,?,?,?)';
                $sth = $this->database->prepare($sql);
                $settings = is_array($settings)?$settings:array();
                foreach($settings as $key => $setting) {
                        $sth->execute(array($id,$key,$setting['value'],$setting['flag']));
                }
                return true;
        }

	public function addDevice($id, $settings) {
                $add_fld = array ("name"=>'label',"outboundcid"=>'cid_num',"langcode"=>'language',"extdisplay"=>'description');
//                print_r($_REQUEST);
//                echo '<br><br>';
//                die(print_r($settings));
                $settings['cid_num']['value']='';
                if (isset($_REQUEST)){
                    foreach($add_fld as $key => $val) {
                        if (!empty($_REQUEST[$key])){
                            $settings[$val]['value'] = $_REQUEST[$key];
                        }
                    }
                }
                if (empty($settings['cid_num']['value'])) {
                    $settings['cid_num']['value']= $id;
                }
                $sql = 'INSERT INTO sccpline (name, id';
                $sqlv = 'values ("'.$id.'", "'.$id.'"';
		foreach($this->data_fld as $key => $val) {
                    if (!empty($settings[$val]) ) {
                        if (!empty($settings[$val]['value'])){
                            $sql .= ', '.$key;
                            $sqlv .= ", '".$settings[$val]['value']."' ";
                        }
                    }
                }
                $sql .= ") ".$sqlv.");";              
		$sth = $this->database->prepare($sql);
                $sth->execute();
		return true;
        }

	public function delDevice($id) {
		$sql = "DELETE FROM sccpline WHERE id = ?";
		$sth = $this->database->prepare($sql);
		$sth->execute(array($id));
		return true;
	}

        
	public function getDevice($id) {
                $sccp_line = array();
		$sql = "SELECT id";
		foreach($this->data_fld as $key => $val) {
                    $sql .= ',`'. $key .'` as '.$val;
                }
		$sql .= " FROM sccpline WHERE id = ?";
		$sth = $this->database->prepare($sql);
		$result = array();
		$tech = array();
    		try {
		    $sth->execute(array($id));
		    $result = $sth->fetch(\PDO::FETCH_ASSOC);
                    $tech = $result;
                    $tech['dial']='SCCP/'.$id;
		} catch(\Exception $e) {}

		return $tech;
	}

	public function getDefaultDeviceSettings($id, $displayname, &$flag) {
		$dial = 'SCCP';
		$settings  = array(
			"pin" => array(
				"value" => "",
				"flag" => $flag++
			),
			"incominglimit" => array(
				"value" => "",
				"flag" => $flag++
			),
			"context" => array(
				"value" => "from-internal",
				"flag" => $flag++
			),
			"callgroup" => array(
				"value" => "",
				"flag" => $flag++
			),
			"namedcallgroup" => array(
				"value" => "",
				"flag" => $flag++
			),
			"pickupgroup" => array(
				"value" => "",
				"flag" => $flag++
			),
			"namedpickupgroup" => array(
				"value" => "",
				"flag" => $flag++
			),
			"transfer" => array(
				"value" => "yes",
				"flag" => $flag++
			),
			"adhocNumber" => array(
				"value" => "",
				"flag" => $flag++
			),
			"echocancel" => array(
				"value" => "no",
				"flag" => $flag++
			),
			"dnd" => array(
				"value" => "no",
				"flag" => $flag++
			),
			"silencesuppression" => array(
				"value" => "no",
				"flag" => $flag++
			),
			"musicclass" => array(
				"value" => "default",
				"flag" => $flag++
			),
		);
		return array(
			"dial" => $dial,
			"settings" => $settings
		);
	}

	public function getDeviceDisplay($display, $deviceInfo, $currentcomponent, $primarySection) {
		$section = _("Settings");
		$category = "general";
		$tmparr = array();
		$tt = _("The SCCP channel number for this port.");
		$tmparr['incominglimit'] = array('prompttext' => _('Line incoming limit'), 'value' => '2', 'tt' => $tt, 'level' => 0, 'jsvalidation' => 'isEmpty()', 'failvalidationmsg' => $msgInvalidChannel);
		$tt = _("Asterisk context this device will send calls to. Only change this is you know what you are doing.");
		$tmparr['context'] = array('prompttext' => _('Line context'), 'value' => 'from-internal', 'tt' => $tt, 'level' => 1);
		$tt = _("Phone call group callgroup=1,3-4");
		$tmparr['callgroup'] = array('prompttext' => _('Call group id'),'value' => '', 'tt' => $tt, 'level' => 1);
		$tt = _("Phone pickup group pickupgroup=1,3-4");
		$tmparr['namedcallgroup'] = array('prompttext' => _('Call group name'),'value' => '', 'tt' => $tt, 'level' => 1);
		$tt = _("sets the named caller groups this line is a member of (ast111)");
                $tmparr['pickupgroup'] = array('prompttext' => _('Pickup group id'),'value' => '', 'tt' => $tt, 'level' => 1);
		$tt = _("Phone pincode");
		$tmparr['namedpickupgroup'] = array('prompttext' => _('Pickup group name'),'value' => '', 'tt' => $tt, 'level' => 1);
		$tt = _("Sets the named pickup groups this line is a member of (this phone can pickup calls from remote phones which are in this caller group (ast111)");
		$tmparr['pin'] = array('value' => '', 'tt' => $tt, 'level' => 1);

                unset($select);
                $select[] = array('value' => 'yes', 'text' => 'Yes');
                $select[] = array('value' => 'no', 'text' => 'No');
                $tt = _("Allow call transfer.");
                $tmparr['transfer'] = array('prompttext' => _('Call Transfer'), 'value' => 'yes', 'tt' => $tt, 'select' => $select, 'level' => 1, 'type' => 'radio');

                unset($select);
                $select[] = array('value' => 'yes', 'text' => 'Yes');
                $select[] = array('value' => 'no', 'text' => 'No');
                $tt = _("Echo calcel");
                $tmparr['echocancel'] = array('prompttext' => _('Echo calcel'), 'value' => 'yes', 'tt' => $tt, 'select' => $select, 'level' => 1, 'type' => 'radio');

                unset($select);
                $select[] = array('value' => 'off', 'text' => 'Off');
                $select[] = array('value' => 'reject', 'text' => 'Reject');
                $select[] = array('value' => 'silent', 'text' => 'Silent');
                $select[] = array('value' => 'UserDefined', 'text' => 'UserDefined');
                $tt = _("Do Not Disturb.");
                $tmparr['dnd'] = array('prompttext' => _('DND'), 'value' => 'user', 'tt' => $tt, 'select' => $select, 'level' => 1, 'type' => 'radio');

                unset($select);
                $select[] = array('value' => 'yes', 'text' => 'Yes');
                $select[] = array('value' => 'no', 'text' => 'No');
                $tt = _("Silence Suppression.");
                $tmparr['silencesuppression'] = array('prompttext' => _('Silence Suppression'), 'value' => 'yes', 'tt' => $tt, 'select' => $select, 'level' => 1, 'type' => 'radio');

                unset($select);
                $select[] = array('value' => 'default', 'text' => _('default'));
                if (function_exists('music_list')){
                    $moh_list = music_list();
                } else { 
                    $moh_list  = array('default');
                }
                foreach ($moh_list as $value) {
                    $select[] = array('value' => $value, 'text' => _($value));
                }

                $tt = _("Musik On Hold ");
                $tmparr['musicclass'] = array('prompttext' => _('Musik On Hold'), 'value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);

                
                
		$devopts = $tmparr;
		return $devopts;
	}
}