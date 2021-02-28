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

    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
        $this->_events = array();
        $this->_eventsCount = 0;
        $this->_completed = !$this->isList();
    }

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
        // returns true if response message does not contain error
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
                    case 'actionid':
                        $this->_temptable['ActionID'] = trim($content[1]);
                        break;
                    case 'response':
                        $this->_temptable['Response'] = trim($content[1]);
                        break;
                    case 'privilege':
                        $this->_temptable['Privilege'] = trim($content[1]);
                        break;
                    case 'output':
                        // included for backward compatibility with earlier versions of chan_sccp_b. AMI api does not precede command output with Output
                        $this->_temptable['Output'] = explode(PHP_EOL,str_replace(PHP_EOL.'--END COMMAND--', '',trim($content[1])));
                        break;
                    default:
                        $this->_temptable['Output'] = explode(PHP_EOL,str_replace(PHP_EOL.'--END COMMAND--', '', trim($line)));
                        break;
                }
            }
        }
/*      Not required $_temptable cannot be empty as has at least an actionID - see also getResult
        if (!empty($this->_temptable)) {
            $this->setKey('output', 'array');
        }
*/
        $this->_completed = $this->isSuccess();
//        return $this->isSuccess();
    }
    public function getResult()
    {
/*      Below test no longer valid as key no longer set
        if (stristr($this->getKey('output'), 'array') !== false) {
            $result = $this->_temptable;
        } else {
            $result = $this->getMessage();
        }
*/      return $this->_temptable;
    }
}

class SCCPGeneric_Response extends Response
{
    protected $_tables;
    private $_temptable;

    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
        $_fields = array("EventList" => "EventList:", "Message" => "Message:");
        $this->_completed = !$this->isList();
    }

    public function addEvent($event)
    {
        if ($event->getEventList() === 'start') {
            // Have started a list of events; this may include tables
            // Nothing to do with this event, only need to handle
            // the events that follow
            return;
        }

        if ( empty($thisSetEventEntryType)) {
            // This is empty as soon as we have received a TableStart.
            // The next message is the first of the data sets
            // We use this variable in the switch to add set entries
            if (strpos($event->getName(), 'Entry')) {
                $thisSetEventEntryType = $event->getName();
            } else {
                $thisSetEventEntryType = 'undefinedAsThisIsNotASet';
            }
        }
        $unknownevent = "FreePBX\\modules\\Sccp_manager\\aminterface\\UnknownEvent";
        if ($event instanceof $unknownevent) {
            $this->_events[] = $event;
            return;
        }
        switch ( $event->getName()) {
            case $thisSetEventEntryType :
                $this->_temptable['Entries'][] = $event;
                break;
            case 'TableStart':
                //initialise
                $this->_temptable = array();
                $this->_temptable['Name'] = $event->getTableName();
                $this->_temptable['Entries'] = array();
                $thisSetEventEntryType = '';
                break;
            case 'TableEnd':
                //Close
                if (!is_array($this->_tables)) {
                    $this->_tables = array();
                }
                $this->_tables[$event->getTableName()] = $this->_temptable;
                $this->_temptable = array();
                $thisSetEventEntryType = 'undefinedAsThisIsNotASet';

                // Finished the table. Now check to see if everything was received
                // If counts do not match return false and table will not be
                //loaded
                if ($event->getKey('TableEntries') != count($this->_tables[$event->getTableName()]['Entries'])) {
                    return $this->_completed = false;
                }
                break;
            default:
                // add regular list event
                $this->_events[] = $event;
        }

        if ($event->getEventList() === 'Complete')  {
              // Received a complete eventList.
              return $this->_completed = true;
        }
    }

    protected function ConvertTableData(String $_tablename, Array $_fkey, Array $_fields)
    {
        $result = array();
        $_rawtable = $this->Table2Array($_tablename);
        // Check that there is actually data to be converted
        if (empty($_rawtable)) { return $result;}
        foreach ($_rawtable as $_row) {
            $all_key_ok = true;
            // No need to test if $_fkey is arrray as array required
            foreach ($_fkey as $_fid) {
                if (empty($_row[$_fid])) {
                    $all_key_ok = false;
                } else {
                    $set_name[$_fid] = $_row[$_fid];
                }
            }
            $Data = &$result;

            if ($all_key_ok) {
                foreach ($set_name as $value_id) {
                    $Data = &$Data[$value_id];
                }
                // Label converter in case labels and keys are different
                foreach ($_fields as $value_key => $value_id) {
                    $Data[$value_id] = $_row[$value_key];
                }
            }
        }
        return $result;
    }

    protected function ConvertEventData(Array $_fkey, Array $_fields)
    {
        $result = array();

        foreach ($this->_events as $_row) {
            $all_key_ok = true;
            $tmp_result = $_row->getKeys();
            $set_name = array();
            // No need to test if $_fkey is arrray as array required
            foreach ($_fkey as $_fid) {
                if (empty($tmp_result[$_fid])) {
                    $all_key_ok = false;
                } else {
                    $set_name[$_fid] = $tmp_result[$_fid];
                }
            }
            $Data = &$result;
            if ($all_key_ok) {
                foreach ($set_name as $value_id) {
                    $Data = &$Data[$value_id];
                }
                // Label converter in case labels and keys are different - not actually required.
                foreach ($_fields as $value_id) {
                    $Data[$value_id] = $tmp_result[$value_id];
                }
            }
        }
        return $result;
    }

/*    public function hasTable()
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
*/
    public function Table2Array( String $tablename )
    {
        $result =array();
        if (empty($tablename) || !is_array($this->_tables)) {
            return $result;
        }
        foreach ($this->_tables[$tablename]['Entries'] as $trow) {
            $result[]= $trow->getKeys();
        }
        return $result;
    }

/*    public function Events2Array()
    {
        $result =array();
        foreach ($this->_events as $trow) {
          //  $tmp_result = $trow->getKeys();
          //  if (is_array($tmp_result)) {
                $result = array_merge($result, $trow->getKeys());
            //} else {
            //    $result [] = $tmp_result;
          //  }
        }
        return $result;
    }

    public function getTable($tablename)
    {
        if (is_array($this->_tables) && array_key_exists($tablename, $this->_tables)) {
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
*/
    public function getResult()
    {
        if ($this->getKey('JSON') !== null && !empty($this->getKey('JSON'))) {
            if (($json = json_decode($this->getKey('JSON'), true)) != false) {
                return $json;
            }
        } else {
            return $this->getMessage();
        }
    }
}

class SCCPJSON_Response extends Response
{

    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
        $this->getVariable($rawContent, array("DataType" => "DataType:", "JSONRAW" => "JSON:"));
        if (null !== $this->getKey('JSONRAW')) {
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
  //      $_fields = array('description'=>'description','label'=>'label','lblid'=>'lblid');
        return $this->ConvertTableData(
            'SoftKeySets',
            array('set','mode'),
            array('description'=>'description','label'=>'label','lblid'=>'lblid')
            );
  //      return $result;
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
//        $_fields = array('mac'=>'mac','address'=>'address','descr'=>'descr','regstate'=>'status',
//                         'token'=>'token','act'=>'act', 'lines'=>'lines','nat'=>'nat','regtime'=>'regtime');
        return $this->ConvertTableData(
            'Devices',
            array('mac'),
            array('mac'=>'name','address'=>'address','descr'=>'descr','regstate'=>'status',
                  'token'=>'token','act'=>'act', 'lines'=>'lines','nat'=>'nat','regtime'=>'regtime')
            );
//        return $result;
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
        // This object has a list of events _events, and a list of tables _tables.
        $result = array();

        foreach ($this->_events as $trow) {
                $result = array_merge($result, $trow->getKeys());
        }
        $result['Buttons'] = $this->ConvertTableData(
            'Buttons',
            array('id'),
            array('id'=>'id','channelobjecttype'=>'channelobjecttype','inst'=>'inst',
                  'typestr'=>'typestr', 'type'=>'type', 'pendupdt'=>'pendupdt', 'penddel'=>'penddel', 'default'=>'default'
                  )
        );
        $result['SpeeddialButtons'] = $this->ConvertTableData(
            'SpeeddialButtons',
            array('id'),
            array('id'=>'id','channelobjecttype'=>'channelobjecttype','name'=>'name','number'=>'number','hint'=>'hint')
        );
        $result['CallStatistics'] = $this->ConvertTableData(
            'CallStatistics',
            array('type'),
            array('type'=>'type','channelobjecttype'=>'channelobjecttype','calls'=>'calls','pcktsnt'=>'pcktsnt','pcktrcvd'=>'pcktrcvd',
                  'lost'=>'lost','jitter'=>'jitter','latency'=>'latency', 'quality'=>'quality','avgqual'=>'avgqual','meanqual'=>'meanqual',
                  'maxqual'=>'maxqual', 'rconceal'=>'rconceal', 'sconceal'=>'sconceal'
                  )
        );
        $result['SCCP_Vendor'] = array('vendor' => strtok($result['skinnyphonetype'], ' '), 'model' => strtok('('),
                                       'model_id' => strtok(')'), 'vendor_addon' => strtok($result['configphonetype'], ' '),
                                       'model_addon' => strtok(' '));
        if (empty($result['SCCP_Vendor']['vendor']) || $result['SCCP_Vendor']['vendor'] == 'Undefined') {
            $result['SCCP_Vendor'] = array('vendor' => 'Undefined', 'model' => $result['configphonetype'],
                                          'model_id' => '', 'vendor_addon' => $result['SCCP_Vendor']['vendor_addon'],
                                          'model_addon' => $result['SCCP_Vendor']['model_addon']
                                          );
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
