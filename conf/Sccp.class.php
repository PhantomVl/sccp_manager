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
			"shortName" => "SCCP",
			"description" => _("Sccp Device"),
			"sccp_driver_ver" => "11.2"
                    
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
                $sql = 'INSERT INTO sccpline (name';
                $sqlv = 'values ("'.$id.'"';
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
		$sql = "DELETE FROM sccpline WHERE name = ?";
		$sth = $this->database->prepare($sql);
		$sth->execute(array($id));
		return true;
	}

        
	public function getDevice($id) {
                $sccp_line = array();
		$sql = "SELECT name as id, name as name";
		foreach($this->data_fld as $key => $val) {
                    $sql .= ',`'. $key .'` as '.$val;
                }
		$sql .= " FROM sccpline WHERE name = ?";
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
//		$tmparr['incominglimit'] = array('prompttext' => _('Incoming Call Limit'), 'value' => '2', 'tt' => $tt, 'level' => 0, 'jsvalidation' => 'isEmpty()', 'failvalidationmsg' => $msgInvalidChannel);
		$tmparr['incominglimit'] = array('prompttext' => _('Incoming Call Limit'), 'value' => '2', 'tt' => $tt, 'level' => 1);

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

                unset($select);
                $select[] = array( 'value' => '0x21', 'text' => 'Inside Dial Tone');
                $select[] = array( 'value' => '0x22', 'text' => 'Outside Dial Tone');
                $select[] = array( 'value' => '0x23', 'text' => 'Line Busy Tone');
                $select[] = array( 'value' => '0x24', 'text' => 'Alerting Tone');
                $select[] = array( 'value' => '0x25', 'text' => 'Reorder Tone');
                $select[] = array( 'value' => '0x26', 'text' => 'Recorder Warning Tone');
                $select[] = array( 'value' => '0x27', 'text' => 'Recorder Detected Tone');
                $select[] = array( 'value' => '0x28', 'text' => 'Reverting Tone');
                $select[] = array( 'value' => '0x29', 'text' => 'Receiver OffHook Tone');
                $select[] = array( 'value' => '0x2A', 'text' => 'Partial Dial Tone');
                $select[] = array( 'value' => '0x2B', 'text' => 'No Such Number Tone');
                $select[] = array( 'value' => '0x2C', 'text' => 'Busy Verification Tone');
                $select[] = array( 'value' => '0x2D', 'text' => 'Call Waiting Tone');
                $select[] = array( 'value' => '0x2E', 'text' => 'Confirmation Tone');
                $select[] = array( 'value' => '0x2F', 'text' => 'Camp On Indication Tone');
                $select[] = array( 'value' => '0x30', 'text' => 'Recall Dial Tone');
                $select[] = array( 'value' => '0x31', 'text' => 'Zip Zip');
                $select[] = array( 'value' => '0x32', 'text' => 'Zip');
                $select[] = array( 'value' => '0x33', 'text' => 'Beep Bonk');
                $select[] = array( 'value' => '0x34', 'text' => 'Music Tone');
                $select[] = array( 'value' => '0x35', 'text' => 'Hold Tone');
                $select[] = array( 'value' => '0x36', 'text' => 'Test Tone');
                $select[] = array( 'value' => '0x37', 'text' => 'DT Monitor Warning Tone');
                $select[] = array( 'value' => '0x40', 'text' => 'Add Call Waiting');
                $select[] = array( 'value' => '0x41', 'text' => 'Priority Call Wait');
                $select[] = array( 'value' => '0x42', 'text' => 'Recall Dial');
                $select[] = array( 'value' => '0x43', 'text' => 'Barg In');
                $select[] = array( 'value' => '0x44', 'text' => 'Distinct Alert');
                $select[] = array( 'value' => '0x45', 'text' => 'Priority Alert');
                $select[] = array( 'value' => '0x46', 'text' => 'Reminder Ring');
                $select[] = array( 'value' => '0x47', 'text' => 'Precedence RingBank');
                $select[] = array( 'value' => '0x48', 'text' => 'Pre-EmptionTone');
                $select[] = array( 'value' => '0x67', 'text' => '2105 HZ');
                $select[] = array( 'value' => '0x68', 'text' => '2600 HZ');
                $select[] = array( 'value' => '0x69', 'text' => '440 HZ');
                $select[] = array( 'value' => '0x6A', 'text' => '300 HZ');
                $select[] = array( 'value' => '0x77', 'text' => 'MLPP Pala');
                $select[] = array( 'value' => '0x78', 'text' => 'MLPP Ica');
                $select[] = array( 'value' => '0x79', 'text' => 'MLPP Vca');
                $select[] = array( 'value' => '0x7A', 'text' => 'MLPP Bpa');
                $select[] = array( 'value' => '0x7B', 'text' => 'MLPP Bnea');
                $select[] = array( 'value' => '0x7C', 'text' => 'MLPP Upa');
                $select[] = array( 'value' => '0x7F', 'text' => 'No Tone');
                $select[] = array( 'value' => '0x80', 'text' => 'Meetme Greeting Tone');
                $select[] = array( 'value' => '0x81', 'text' => 'Meetme Number Invalid Tone');
                $select[] = array( 'value' => '0x82', 'text' => 'Meetme Number Failed Tone');
                $select[] = array( 'value' => '0x83', 'text' => 'Meetme Enter Pin Tone');
                $select[] = array( 'value' => '0x84', 'text' => 'Meetme Invalid Pin Tone');
                $select[] = array( 'value' => '0x85', 'text' => 'Meetme Failed Pin Tone');
                $select[] = array( 'value' => '0x86', 'text' => 'Meetme CFB Failed Tone');
                $select[] = array( 'value' => '0x87', 'text' => 'Meetme Enter Access Code Tone');
                $select[] = array( 'value' => '0x88', 'text' => 'Meetme Access Code Invalid Tone');
                $select[] = array( 'value' => '0x89', 'text' => 'Meetme Access Code Failed Tone');
                $select[] = array('value' => 'yes', 'text' => 'Yes');
                $select[] = array('value' => 'no', 'text' => 'No');

                $tt = _("Outside dialtone frequency (defaul 0x22)");
                $tmparr['secondary_dialtone_tone'] = array('prompttext' => _('Secondary dialtone'), 'value' => '0x22', 'tt' => $tt, 'select' => $select, 'level' => 1, 'type' => 'select');

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

                $tt = _("Music on hold");
                $tmparr['musicclass'] = array('prompttext' => _('Music on hold'), 'value' => 'no', 'tt' => $tt, 'select' => $select, 'level' => 1);
                
		$devopts = $tmparr;
		return $devopts;
	}
}
