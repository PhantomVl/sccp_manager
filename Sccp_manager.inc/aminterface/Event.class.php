<?php

/**
 *
 * Core Comsnd Interface
 *
 *  https://www.voip-info.org/asterisk-manager-example-php/
 */
/* !TODO!: Re-Indent this file.  -TODO-: What do you mean? coreaccessinterface  ??  */

namespace FreePBX\modules\Sccp_manager\aminterface;

// ************************************************************************** Event  *********************************************

abstract class Event extends IncomingMessage
{

    protected $_events;

    public function getName()
    {
        return $this->getKey('Event');
    }

    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
        $this->_events = array();
        $this->_eventsCount = 0;
//        $this->_completed = !$this->isList();
    }
}

class UnknownEvent extends Event
{
    public function __construct($rawContent = '')
    {
//        print_r($rawContent);
//        die();
    }
}

class TableStart_Event extends Event
{

    public function getTableName()
    {
        return $this->getKey('TableName');
    }
}

class TableEnd_Event extends Event
{

    public function getTableName()
    {
        return $this->getKey('TableName');
    }
}

class SCCPSoftKeySetEntry_Event extends Event
{

    protected $_data;

    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
        return null;
    }
}

class SCCPShowSoftKeySetsComplete_Event extends Event
{

    public function getListItems()
    {
        return intval($this->getKey('ListItems'));
    }
}

class ExtensionStatus_Event extends Event
{

    public function getPrivilege()
    {
        return $this->getKey('Privilege');
    }

    public function getExtension()
    {
        return $this->getKey('Exten');
    }

    public function getContext()
    {
        return $this->getKey('Context');
    }

    public function getHint()
    {
        return $this->getKey('Hint');
    }

    public function getStatus()
    {
        return $this->getKey('Status');
    }
}

class SCCPDeviceEntry_Event extends Event
{

}

class SCCPShowDeviceComplete_Event extends Event
{

    public function getListItems()
    {
        return intval($this->getKey('ListItems'));
    }
    public function __construct($rawContent)
    {
        parent::__construct($rawContent);
        $this->_completed = $this->getKey('EventList');
//        return null;
    }
}

class SCCPShowDevice_Event extends Event
{

    public function getCapabilities()
    {
        $ret = array();
        $codecs = explode(", ", substr($this->getKey('Capabilities'), 1, -1));
        foreach ($codecs as $codec) {
            $codec_parts = explode(" ", $codec);
            $ret[] = array("name" => $codec_parts[0], "value" => substr($codec_parts[1], 1, -1));
        }
        return $ret;
    }

    public function getCodecsPreference()
    {
        $ret = array();
        $codecs = explode(", ", substr($this->getKey('CodecsPreference'), 1, -1));
        foreach ($codecs as $codec) {
            $codec_parts = explode(" ", $codec);
            $ret[] = array("name" => $codec_parts[0], "value" => substr($codec_parts[1], 1, -1));
        }
        return $ret;
    }
}

class SCCPShowDevicesComplete_Event extends Event
{

    public function getListItems()
    {
        return intval($this->getKey('ListItems'));
    }
}
class SCCPDeviceButtonEntry_Event extends Event
{
}
class SCCPDeviceFeatureEntry_Event extends Event
{
// Returned by SCCPShowDevice
}
class SCCPVariableEntry_Event extends Event
{
// Returned by SCCPShowDevice
}
class SCCPDeviceLineEntry_Event extends Event
{
}
class SCCPDeviceStatisticsEntry_Event extends Event
{
}
class SCCPDeviceSpeeddialEntry_Event extends Event
{
}
class ExtensionStateListComplete_Event extends Event
{

}
