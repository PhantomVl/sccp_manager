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
                                 "secondary_dialtone_digits" => 'secondary_dialtone_digits', "secondary_dialtone_tone" => 'secondary_dialtone_tone',            
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
				"value" => "UserDefined",
				"flag" => $flag++
			),
			"silencesuppression" => array(
				"value" => "no",
				"flag" => $flag++
			),
			"secondary_dialtone_digits" => array(
				"value" => "9",
				"flag" => $flag++
			),
			"secondary_dialtone_tone" => array(
				"value" => "0x22",
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

# ??? Would it not be better to put this part in the view directory (MVC) ?
	public function getDeviceDisplay($display, $deviceInfo, $currentcomponent, $primarySection) {
		$section = _("Settings");
		$category = "general";
		$tmparr = array();
		$tt = _("The maximum number of incoming calls to this line.");
		$tmparr['incominglimit'] = array('prompttext' => _('Line incoming limit'), 'value' => '2', 'tt' => $tt, 'level' => 0, 'jsvalidation' => 'isEmpty()', 'failvalidationmsg' => $msgInvalidChannel);

                $tt = _("Asterisk context this line will use send calls to/from (Note: Only change this is you know what you are doing).");
		$tmparr['context'] = array('prompttext' => _('Line context'), 'value' => 'from-internal', 'tt' => $tt, 'level' => 1);

                $tt = _("Phone call group (numeric only, example:1,3-4)");
		$tmparr['callgroup'] = array('prompttext' => _('Call group id'),'value' => '', 'tt' => $tt, 'level' => 1);

# ??? multiple allowed (not sure if that is implemented here)
                $tt = _("Phone named call group (>asterisk-11)");
		$tmparr['namedcallgroup'] = array('prompttext' => _('Named Call Group'),'value' => '', 'tt' => $tt, 'level' => 1);

                $tt = _("Sets the pickup group (numeric only, example:1,3-4) this line is a member of. Allows this line to pickup calls from remote phones which are in this callhroup.");
                $tmparr['pickupgroup'] = array('prompttext' => _('Pickup group id'),'value' => '', 'tt' => $tt, 'level' => 1);

# ??? multiple allowed (not sure if that is implemented here)
                $tt = _("Sets the named pickup name group this line is a member of. Allows this line to pickup calls from remote phones which are in this name callgroup (>asterisk-11).");
		$tmparr['namedpickupgroup'] = array('prompttext' => _('Named Pickup Group'),'value' => '', 'tt' => $tt, 'level' => 1);

                $tt = _("Phone pincode (Note used)");
		$tmparr['pin'] = array('value' => '', 'tt' => $tt, 'level' => 1);

                $tt = _("Digits to indicate an external line to user (secondary dialtone) Sample 9 or 8 (max 9 digits)");
		$tmparr['secondary_dialtone_digits'] = array('prompttext' => _('Secondary dialtone digits'), 'value' => '', 'tt' => $tt, 'level' => 1);

                $tt = _("Outside dialtone frequency (default 0x22)");
		$tmparr['secondary_dialtone_tone'] = array('prompttext' => _('Secondary dialtone tone'), 'value' => '', 'tt' => $tt, 'level' => 1);

# ??? is there no easier way to specify a boolean radio group ?
                unset($select);
                $select[] = array('value' => 'yes', 'text' => 'Yes');
                $select[] = array('value' => 'no', 'text' => 'No');
                $tt = _("Allow call transfer.");
                $tmparr['transfer'] = array('prompttext' => _('Call Transfer'), 'value' => 'yes', 'tt' => $tt, 'select' => $select, 'level' => 1, 'type' => 'radio');

                unset($select);
                $select[] = array('value' => 'yes', 'text' => 'Yes');
                $select[] = array('value' => 'no', 'text' => 'No');
                $tt = _("Echo cancel");
                $tmparr['echocancel'] = array('prompttext' => _('Echo cancel'), 'value' => 'yes', 'tt' => $tt, 'select' => $select, 'level' => 1, 'type' => 'radio');

                unset($select);
                $select[] = array('value' => 'off', 'text' => 'Off');
                $select[] = array('value' => 'reject', 'text' => 'Reject');
                $select[] = array('value' => 'silent', 'text' => 'Silent');
                $select[] = array('value' => 'UserDefined', 'text' => 'UserDefined');
                $tt = _("DND: Means how will dnd react when it is set on the device level dnd can have three states: off / busy(reject) / silent / UserDefined").'<br>'.
# ??? The next entry should be "null/empty" (not UserDefined) -> to indicate the trie-state behaviour
                      _("UserDefined - dnd that cycles through all three states off -> reject -> silent -> off (this is the normal behaviour)").'<br>'.
# ??? Userdefined is also a possible state, but it is not used or implemented (and it should not be implemented here, i think)
                      _("Reject - Usesr can only switch off and on (in reject/busy mode)").'<br>'.
                      _("Silent  - Usesr can only switch off and on (in silent mode)");
                $tmparr['dnd'] = array('prompttext' => _('DND'), 'value' => 'UserDefined', 'tt' => $tt, 'select' => $select, 'level' => 1, 'type' => 'radio');

                unset($select);
                $select[] = array('value' => 'yes', 'text' => 'Yes');
                $select[] = array('value' => 'no', 'text' => 'No');
                $tt = _("Silence Suppression. Asterisk Not suported");
                $tmparr['silencesuppression'] = array('prompttext' => _('Silence Suppression'), 'value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1, 'type' => 'radio');

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

                $tt = _("Music On Hold");
                $tmparr['musicclass'] = array('prompttext' => _('Music On Hold'), 'value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);
                
		$devopts = $tmparr;
		return $devopts;
	}
}
