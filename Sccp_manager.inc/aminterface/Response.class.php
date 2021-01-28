<?php

/**
 *
 * Core Comsnd Interface
 *
 *  https://www.voip-info.org/asterisk-manager-example-php/
 */
/* !TODO!: Re-Indent this file.  -TODO-: What do you mean? coreaccessinterface  ??  */

namespace FreePBX\modules\Sccp_manager\aminterface;

// ************************************************************************** Response *********************************************

namespace FreePBX\modules\Sccp_manager\aminterface;

abstract class Response extends IncomingMessage
{

    protected $_events;
    protected $_completed;
    protected $keys;

    public function isComplete()
    {
        return $this->_completed;
    }

    public function __sleep()
    {
        $ret = parent::__sleep();
        $ret[] = '_completed';
        $ret[] = '_events';
        return $ret;
    }

    public function addEvent($event)
    {
        $this->_events[] = $event;
        if (stristr($event->getEventList(), 'complete') !== false
            || stristr($event->getName(), 'complete') !== false
            || stristr($event->getName(), 'DBGetResponse') !== false
        ) {
            $this->_completed = true;
        }
    }
    public function getEvents()
    {
        return $this->_events;
    }

    public function isSuccess()
    {
        return stristr($this->getKey('Response'), 'Error') === false;
    }

    public function isList()
    {
        return
            stristr($this->getKey('EventList'), 'start') !== false
            || stristr($this->getMessage(), 'follow') !== false
        ;
    }

    public function getMessage()
    {
        return $this->getKey('Message');
    }

    public function setActionId($actionId)
    {
        $this->setKey('ActionId', $actionId);
    }

    public function getVariable($_rawContent, $_fields = '')
    {
        $lines = explode(Message::EOL, $_rawContent);
        foreach ($_fields as $key => $value) {
            foreach ($lines as $data) {
                $_pst = strpos($data, $value);
                if ($_pst !== false) {
                    $this->setKey($key, substr($data, $_pst + strlen($value)));
                }
            }
        }
    }

    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
        $this->_events = array();
        $this->_eventsCount = 0;
        $this->_completed = !$this->isList();
    }
}
//****************************************************************************
class Generic_Response extends Response
{

    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
//        print_r('<br>---- r --<br>');
//        print_r($rawContent);
    }
}

class Login_Response extends Response
{

    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
        return $this->isSuccess();
    }
}

class Command_Response extends Response
{
    private $_temptable;
    public function __construct($rawContent)
    {
//        print_r('<br>---- r --<br>');
//        print_r($rawContent);
//        print_r('<br>---- re --<br>');
        $this->_temptable = array();
        parent::__construct($rawContent);
        $lines = explode(Message::EOL, $rawContent);
        foreach ($lines as $line) {
            $content = explode(':', $line);
            if (is_array($content)) {
                switch (strtolower($content[0])) {
                    case 'output':
                        $_tmp_str = trim(substr($line, 7));
                        if (!empty($_tmp_str)) {
                            $this->_temptable['output'][]=  trim(substr($line, 7));
                        }
                        break;
                    default:
                        $this->_temptable[$content[0]][]=  trim(substr($line, strlen($content[0])+1));
                        break;
                }
            }
        }
        if (!empty($this->_temptable)) {
            $this->setKey('output', 'array');
        }

        $this->_completed = $this->isSuccess();
//        return $this->isSuccess();
    }
    public function getResult()
    {
        if (stristr($this->getKey('output'), 'array') !== false) {
            $result = $this->_temptable;
        } else {
            $result = $this->getMessage();
        }
        return $result;
    }
}

class SCCPGeneric_Response extends Response
{

    protected $_tables;
    private $_temptable;

    public function addEvent($event)
    {
        // not eventlist (start/complete)
//        print_r('<br>---- addEvent --<br>');
//        print_r($event);
//        print_r('<br>---- Event List--<br>');
//        print_r($event->getEventList());
        if (stristr($event->getEventList(), 'start') === false && stristr($event->getEventList(), 'complete') === false && stristr($event->getName(), 'complete') === false
        ) {
            $unknownevent = "FreePBX\\modules\\Sccp_manager\\aminterface\\UnknownEvent";
            if (!($event instanceof $unknownevent)) {
                // Handle TableStart/TableEnd Differently
                if (stristr($event->getName(), 'TableStart') != false) {
                    $this->_temptable = array();
                    $this->_temptable['Name'] = $event->getTableName();
                    $this->_temptable['Entries'] = array();
                } elseif (stristr($event->getName(), 'TableEnd') != false) {
                    if (!is_array($this->_tables)) {
                        $this->_tables = array();
                    }
                    $this->_tables[$event->getTableName()] = $this->_temptable;
                    unset($this->_temptable);
                } elseif (is_array($this->_temptable)) {
                    $this->_temptable['Entries'][] = $event;
                } else {
                    // add regular event
                    $this->_events[] = $event;
                }
            } else {
                // add regular event
                $this->_events[] = $event;
            }
        }
        // finish eventlist
        if (stristr($event->getEventList(), 'complete') != false || stristr($event->getName(), 'complete') != false
        ) {
            $this->_completed = true;
        }
    }

    protected function ConvertTableData($_tablename, $_fkey, $_fields)
    {
        $_rawtable = $this->Table2Array($_tablename);
        $result = array();
        foreach ($_rawtable as $_row) {
            $all_key_ok = true;
            if (is_array($_fkey)) {
                foreach ($_fkey as $_fid) {
                    if (empty($_row[$_fid])) {
                        $all_key_ok = false;
                    } else {
                        $set_name[$_fid] = $_row[$_fid];
                    }
                }
            } else {
                if (empty($_row[$_fkey])) {
                    $all_key_ok = false;
                } else {
                    $set_name[$_fkey] = $_row[$_fkey];
                }
            }
            $Data = &$result;
            if ($all_key_ok) {
                foreach ($set_name as $value_id) {
                    $Data = &$Data[$value_id];
                }
                foreach ($_fields as $value_key => $value_id) {
                    $Data[$value_id] = $_row[$value_key];
                }
            }
        }
        return $result;
    }

    protected function ConvertEventData($_fkey, $_fields)
    {
        $result = array();

        foreach ($this->_events as $_row) {
            $all_key_ok = true;
            $tmp_result = $_row->getKeys();
            $set_name = array();
            if (is_array($_fkey)) {
                foreach ($_fkey as $_fid) {
                    if (empty($tmp_result[$_fid])) {
                        $all_key_ok = false;
                    } else {
                        $set_name[$_fid] = $tmp_result[$_fid];
                    }
                }
            } else {
                if (empty($tmp_result[$_fkey])) {
                    $all_key_ok = false;
                } else {
                    $set_name[$_fkey] = $tmp_result[$_fkey];
                }
            }
            $Data = &$result;
            if ($all_key_ok) {
                foreach ($set_name as $value_id) {
                    $Data = &$Data[$value_id];
                }
                foreach ($_fields as $value_id) {
                    $Data[$value_id] = $tmp_result[$value_id];
                }
            }
        }
        return $result;
    }


    public function hasTable()
    {
        if (is_array($this->_tables)) {
            return true;
        }
        return false;
    }
    public function getTableNames()
    {
        return (is_array($this->_tables)) ? array_keys($this->_tables) : null;
    }

    public function Table2Array($tablename = '')
    {
        $result =array();
        if (!is_string($tablename) || empty($tablename)) {
            return false;
        }
        if ($this->hasTable()) {
            foreach ($this->_tables[$tablename]['Entries'] as $trow) {
                $result[]= $trow->getKeys();
            }
            return $result;
        } else {
            return false;
        }
    }
    public function Events2Array()
    {
        $result =array();
        if (is_array($this->_events)) {
            foreach ($this->_events as $trow) {
                $tmp_result = $trow->getKeys();
                if (is_array($tmp_result)) {
                    $result = array_merge($result, $tmp_result);
                } else {
                    $result [] = $tmp_result;
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    public function getTable($tablename)
    {
        if ($this->hasTable() && array_key_exists($tablename, $this->_tables)) {
            return $this->_tables[$tablename];
        }
        throw new PAMIException("No such table.");
    }
    public function getJSON()
    {
        if (strlen($this->getKey('JSON')) > 0) {
            if (($json = json_decode($this->getKey('JSON'), true)) != false) {
                return $json;
            }
        }
        throw new AMIException("No JSON Key found to return.");
    }

    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
        $_fields = array("EventList" => "EventList:", "Message" => "Message:");
//        $this->getVariable($rawContent, $_fields);
        $this->_completed = !$this->isList();
    }

    public function getResult()
    {
        if ($this->getKey('JSON') != null) {
            $result = $this->getJSON();
        } else {
            $result = $this->getMessage();
        }
        return $result;
    }
}

class SCCPJSON_Response extends Response
{

    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
        $_fields = array("DataType" => "DataType:", "JSONRAW" => "JSON:");
        $this->getVariable($rawContent, $_fields);
        $js_res = $this->getKey('JSONRAW');
        if (isset($js_res)) {
            $this->setKey('Response', 'Success');
        }
        return $this->isSuccess();
    }
}

class SCCPShowSoftkeySets_Response extends SCCPGeneric_Response
{
    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
    }
    public function getResult()
    {
        $_fields = array('description'=>'description','label'=>'label','lblid'=>'lblid');
        $result = $this->ConvertTableData('SoftKeySets', array('set','mode'), $_fields);
        return $result;
    }
}

class SCCPShowDevices_Response extends SCCPGeneric_Response
{
    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
    }
    public function getResult()
    {
        $_fields = array('mac'=>'mac','address'=>'address','descr'=>'descr','regstate'=>'status',
                         'token'=>'token','act'=>'act', 'lines'=>'lines','nat'=>'nat','regtime'=>'regtime');
        $result = $this->ConvertTableData('Devices', array('mac'), $_fields);
        return $result;
    }
}

class SCCPShowDevice_Response extends SCCPGeneric_Response
{
    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
    }
    public function getResult()
    {
        $result = array();
        $result = $this->Events2Array();
        $result['Buttons'] = $this->ConvertTableData(
            'Buttons',
            array('id'),
            array('id'=>'id','channelobjecttype'=>'channelobjecttype','inst'=>'inst',
            'typestr'=>'typestr',
            'type'=>'type',
            'pendupdt'=>'pendupdt',
            'penddel'=>'penddel',
            'default'=>'default')
        );
        $result['SpeeddialButtons'] = $this->ConvertTableData(
            'Buttons',
            array('id'),
            array('id'=>'id','channelobjecttype'=>'channelobjecttype','name'=>'name','number'=>'number','hint'=>'hint')
        );
        $result['CallStatistics'] = $this->ConvertTableData(
            'CallStatistics',
            array('type'),
            array('type'=>'type','channelobjecttype'=>'channelobjecttype','calls'=>'calls','pcktsnt'=>'pcktsnt','pcktrcvd'=>'pcktrcvd',
                                  'lost'=>'lost','jitter'=>'jitter','latency'=>'latency', 'quality'=>'quality','avgqual'=>'avgqual','meanqual'=>'meanqual',
            'maxqual'=>'maxqual',
            'rconceal'=>'rconceal',
            'sconceal'=>'sconceal')
        );
        $result['SCCP_Vendor'] = array('vendor' => strtok($result['skinnyphonetype'], ' '), 'model' => strtok('('),
                                       'model_id' => strtok(')'), 'vendor_addon' => strtok($result['configphonetype'], ' '),
                                       'model_addon' => strtok(' '));
        if (empty($result['SCCP_Vendor']['vendor']) || $result['SCCP_Vendor']['vendor'] == 'Undefined') {
                $result['SCCP_Vendor'] = array('vendor' => 'Undefined', 'model' => $result['configphonetype'],
                                               'model_id' => '', 'vendor_addon' => $result['SCCP_Vendor']['vendor_addon'],
                                               'model_addon' => $result['SCCP_Vendor']['model_addon']);
        }
        $result['MAC_Address'] =$result['macaddress'];
        return $result;
    }
}

class ExtensionStateList_Response extends SCCPGeneric_Response
{
    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
    }
    public function getResult()
    {
        $result = $this->ConvertEventData(array('exten','context'), array('exten','context','hint','status','statustext'));
        return $result;
    }
}
