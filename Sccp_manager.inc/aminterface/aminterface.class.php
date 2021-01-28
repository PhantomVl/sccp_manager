<?php

/**
 *
 * Core Comsnd Interface
 *
 *  https://www.voip-info.org/asterisk-manager-example-php/
 */
/* !TODO!: Re-Indent this file.  -TODO-: What do you mean? coreaccessinterface  ??  */

namespace FreePBX\modules\Sccp_manager;

class aminterface
{

    var $_socket;
    var $_error;
    var $_config;
    var $_test;
    var $_countE;
    private $_connect_state;
//    var $ProcessingMessage;
    private $_lastActionClass;
    private $_lastActionId;
    private $_lastRequestedResponseHandler;
    private $_ProcessingMessage;
    private $_DumpMessage;
    private $_eventFactory;
    private $_responseFactory;
    private $debug_level = 1;

    public function load_subspace($parent_class = null)
    {
        $driverNamespace = "\\FreePBX\\Modules\\Sccp_manager\\aminterface";

        $drivers = array('Message' => 'Message.class.php', 'Response' => 'Response.class.php', 'Event' => 'Event.class.php');
        foreach ($drivers as $key => $value) {
            $class = $driverNamespace . "\\" . $key;
            $driver = __DIR__ . "/" . $value;
            if (!class_exists($class, false)) {
                if (file_exists($driver)) {
                    include(__DIR__ . "/" . $value);
                } else {
                    throw new \Exception("Class required but file not found " . $driver);
                }
            }
        }
    }

    public function __construct($parent_class = null)
    {
        global $amp_conf;
        $this->paren_class = $parent_class;
        $this->_socket = false;
        $this->_connect_state = false;
        $this->_error = array();
        $this->_config = array('host' => 'localhost', 'user' => '', 'pass' => '', 'port' => '5038', 'tsoket' => 'tcp://', 'timeout' => 30, 'enabled' => false);


        $this->_eventListeners = array();
//  $this->_eventFactory = new EventFactoryImpl(\Logger::getLogger('EventFactory'));
//  $this->_responseFactory = new ResponseFactoryImpl(\Logger::getLogger('ResponseFactory'));
        $this->_incomingQueue = array();
        $this->_lastActionId = false;
        
        $fld_conf = array('user' => 'AMPMGRUSER', 'pass' => 'AMPMGRPASS');
        if (isset($amp_conf['AMPMGRUSER'])) {
            foreach ($fld_conf as $key => $value) {
                if (isset($amp_conf[$value])) {
                    $this->_config[$key] = $amp_conf[$value];
                }
            }
        }
        if ($this->_config['enabled']) {
            $this->load_subspace();
        }
    }

    public function status()
    {
        if ($this->_config['enabled']) {
            return true;
        } else {
            return false;
        }
    }

    public function info()
    {
        $Ver = '13.0.4';
        if ($this->_config['enabled']) {
            return array('Version' => $Ver,
                'about' => 'AMI data ver: ' . $Ver, 'test' => get_declared_classes());
        } else {
            return array('Version' => $Ver,
                'about' => 'Disabled AMI  ver: ' . $Ver);
        }
    }

    /**
     * Opens a tcp connection to ami.
     *
     */
    public function open()
    {
        $cString = $this->_config['tsoket'] . $this->_config['host'] . ':' . $this->_config['port'];
        $this->_context = stream_context_create();
        $errno = 0;
        $errstr = '';
        $this->_ProcessingMessage = '';
        $this->_socket = @stream_socket_client(
            $cString,
            $errno,
            $errstr,
            $this->_config['timeout'],
            STREAM_CLIENT_CONNECT,
            $this->_context
        );
        if ($this->_socket === false) {
            $this->_errorException('Error connecting to ami: ' . $errstr . $cString);
            return false;
        }
        $msg = new aminterface\LoginAction($this->_config['user'], $this->_config['pass']);
        $response = $this->send($msg);

        if ($response != false) {
            if (!$response->isSuccess()) {
                $this->_errorException('Could not connect: ' . $response->getMessage());
                return false;
            } else {
                @stream_set_blocking($this->_socket, 0);
                $this->_connect_state = true;
                $this->_ProcessingMessage = '';
            }
        }
        return true;
    }

    /**
     * Closes the connection to ami.
     */
    public function close()
    {
        $this->_connect_state = false;
        $this->_ProcessingMessage = '';
        @stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
    }

    public function send($message)
    {
        $messageToSend = $message->serialize();
        $length = strlen($messageToSend);
        $this->_countE = 0;
        $this->_DumpMessage = '';
        $this->_lastActionId = $message->getActionId();
        $this->_lastRequestedResponseHandler = $message->getResponseHandler();
        $this->_lastActionClass = $message;
        if (@fwrite($this->_socket, $messageToSend) < $length) {
            $this->_errorException('Could not send message');
            return false;
        }
        $time_connect = microtime_float();
        $this->_msgToDebug(90, 'Time: '. ($time_connect));
        while (1) {
            stream_set_timeout($this->_socket, 1);
//            stream_set_timeout($this->_socket, (isset($this->socket_param['timeout']) ? $this->socket_param['timeout'] : 1));
            $this->process();
            $time_co = microtime_float();
            $this->_msgToDebug(90, 'Time: '. ($time_co-$time_connect));
            $info = stream_get_meta_data($this->_socket);
            if ($info['timed_out'] == false) {
                $response = $this->getRelated($message);
                if ($response != false) {
                    $this->_lastActionId = false;
                    $this->_msgToDebug(98, '---- Dump MSG -------');
                    $this->_msgToDebug(98, $this->_DumpMessage);
                    return $response;
                }
            } else {
                break;
            }
        }
        $this->_errorException("Read waittime: " . ($this->socket_param['timeout']) . " exceeded (timeout).\n");
    }

    protected function getRelated($message)
    {
        $ret = false;
        $id = 0;
        $id = $message->getActionID('ActionID');
        if (isset($this->_incomingQueue[$id])) {
            $response = $this->_incomingQueue[$id];
            if ($response->isComplete()) {
                unset($this->_incomingQueue[$id]);
                $ret = $response;
            }
        }
        return $ret;
    }

    private function _messageToEvent($msg)
    {
        return $this->_eventFromRaw($msg);
    }

    protected function getMessages()
    {
        $msgs = array();
        // Read something.
        $read = @fread($this->_socket, 65535);
        if ($read === false || @feof($this->_socket)) {
            $this->_errorException('Error reading');
        }

        if ($read == "") {
            usleep(100);
        } else {
                $this->_msgToDebug(98, '--- Not Empy AMI MSG --- ');
        }
        $this->_ProcessingMessage .= $read;
        $this->_DumpMessage .= $read;
        while (($marker = strpos($this->_ProcessingMessage, aminterface\Message::EOM))) {
            $msg = substr($this->_ProcessingMessage, 0, $marker);
            $this->_ProcessingMessage = substr(
                $this->_ProcessingMessage,
                $marker + strlen(aminterface\Message::EOM)
            );
            $msgs[] = $msg;
        }
        return $msgs;
    }

    public function process()
    {
        $msgs = $this->getMessages();
        $this->_msgToDebug(90, $msgs);
        $this->_countE++;
        if ($this->_countE > 10000) {
            $this->_msgToDebug(9, '--- Procecc Die, Dump --- ');
            $this->_msgToDebug(9, $this->_DumpMessage);
            $this->_msgToDebug(9, '--- END Procecc Die, Dump --- ');
            die();
        }
        foreach ($msgs as $aMsg) {
            $resPos = strpos($aMsg, 'Response:');
            $evePos = strpos($aMsg, 'Event:');
            if (($resPos !== false) && (($resPos < $evePos) || $evePos === false)) {
                $response = $this->_msgToResponse($aMsg); // resp Ok
                $this->_incomingQueue[$this->_lastActionId] = $response;
            } elseif ($evePos !== false) {
                $event = $this->_messageToEvent($aMsg); // Event  Ok

                $this->_msgToDebug(99, '--- Response Type 2 --- ');
                $this->_msgToDebug(99, $aMsg);
                $this->_msgToDebug(99, '--- Event Response Type 2 --- ');
                $this->_msgToDebug(99, $event);

                if ($event != null) {
                    $response = $this->findResponse($event);
//                    print_r($response);
//                    print_r('<br>--- E2 Response Type 2 ----------<br>');

                    if ($response === false || $response->isComplete()) {
                        $this->dispatch($event);  // не работает
                    } else {
                        $response->addEvent($event);
                    }
                }
            } else {
                // broken ami.. sending a response with events without
                // Event and ActionId
                $this->_msgToDebug(1, 'resp broken ami');
                $bMsg = 'Event: ResponseEvent' . "\r\n";
                $bMsg .= 'ActionId: ' . $this->_lastActionId . "\r\n" . $aMsg;
                $event = $this->_messageToEvent($bMsg);
                $response = $this->findResponse($event);
                $response->addEvent($event);
            }
        }
//        print_r('<br>--- EProcecc ----------<br>');
    }

    private function _msgToDebug($level, $msg)
    {
        if ($level > $this->debug_level) {
            return;
        }
        print_r('<br> level: '.$level.' ');
        print_r($msg);
        print_r('<br>');
    }

    private function _msgToResponse($msg)
    {
        //      print_r("<br>------------hmsg----------<br>");
        //      print_r($this->_lastActionClass);
//        print_r($this->_lastRequestedResponseHandler);
//        print_r("<br>------------emsg----------<br>");
//        print_r($msg);
        $response = $this->_msgFromRaw($msg, $this->_lastActionClass, $this->_lastRequestedResponseHandler);
//        print_r("<br>------------rmsg----------<br>");
        //      print_r($response);
//        print_r("<br>------------ermsg----------<br>");

        $actionId = $response->getActionId();
        if ($actionId === null) {
            $actionId = $this->_lastActionId;
            $response->setActionId($this->_lastActionId);
        }
        return $response;
    }

    /*
     *
     *
     */

    public function _msgFromRaw($message, $requestingaction = false, $responseHandler = false)
    {

        $_className = false;

        $responseclass = '\\FreePBX\\modules\\Sccp_manager\\aminterface\\Generic_Response';
        if ($responseHandler != false) {
            $_className = '\\FreePBX\\modules\\Sccp_manager\\aminterface\\' . $responseHandler . '_Response';
        } elseif ($requestingaction != false) {
            $_className = '\\FreePBX\\modules\\Sccp_manager\\' . substr(get_class($requestingaction), 20, -6) . '_Response';
        }
        if ($_className) {
            if (class_exists($_className, true)) {
                $responseclass = $_className;
            } elseif ($responseHandler != false) {
                $this->_errorException('Response Class ' . $_className . '  requested via responseHandler, could not be found');
            }
        }
        return new $responseclass($message);
    }

    protected function _errorException($msg)
    {
        $this->_error[] = $msg;
    }

    /*
     *    Replace or dublicate to AMI interface
     */

    public function _eventFromRaw($message)
    {
        $eventStart = strpos($message, 'Event: ') + 7;

        if ($eventStart > strlen($message)) {
            return new aminterface\UnknownEvent($message);
        }

        $eventEnd = strpos($message, aminterface\Message::EOL, $eventStart);
        if ($eventEnd === false) {
            $eventEnd = strlen($message);
        }
        $name = substr($message, $eventStart, $eventEnd - $eventStart);
        $className = '\\FreePBX\\modules\\Sccp_manager\\aminterface\\' . $name . '_Event';
        if (class_exists($className, true) === false) {
            $className = '\\FreePBX\\modules\\Sccp_manager\\aminterface\\UnknownEvent';
        }
        return new $className($message);
    }

    public function _respnceFromRaw($message, $requestingaction = false, $responseHandler = false)
    {

        $responseclass = '\\FreePBX\\modules\\Sccp_manager\\aminterface\\Response';

        $_className = false;
        if ($responseHandler != false) {
            $_className = '\\FreePBX\\modules\\Sccp_manager\\aminterface\\' . $responseHandler . '_Response';
        } elseif ($requestingaction != false) {
            $_className = '\\FreePBX\\modules\\Sccp_manager\\aminterface\\' . substr(get_class($requestingaction), 20, -6) . '_Response';
        }
        if ($_className) {
            if (class_exists($_className, true)) {
                $responseclass = $_className;
            } elseif ($responseHandler != false) {
                throw new AMIException('Response Class ' . $_className . '  requested via responseHandler, could not be found');
            }
        }
//      if ($this->_logger->isDebugEnabled()) $this->_logger->debug('Created: ' . $responseclass . "\n");
        print_r($responseclass);
        die();
        return new $responseclass($message);
    }

//    protected function findResponse(IncomingMessage $message) {
    protected function findResponse($message)
    {
        $actionId = $message->getActionId();
        if (isset($this->_incomingQueue[$actionId])) {
            return $this->_incomingQueue[$actionId];
        }
        return false;
    }

    protected function dispatch($message)
    {
        print_r("<br>------------dispatch----------<br>");
        print_r($message);
        return false;
        die();
        foreach ($this->_eventListeners as $data) {
            $listener = $data[0];
            $predicate = $data[1];
            print_r($data);

            if (is_callable($predicate) && !call_user_func($predicate, $message)) {
                continue;
            }
            if ($listener instanceof \Closure) {
                $listener($message);
            } elseif (is_array($listener)) {
                $listener[0]->$listener[1]($message);
            } else {
                $listener->handle($message);
            }
        }
        print_r("<br>------------E dispatch----------<br>");
    }

//-------------------------------------------------------------------------------
    function core_list_all_exten($keyfld = '', $filter = array())
    {
        $result = array();
        return $result;
    }

//-------------------Adaptive Function ------------------------------------------------------------

    function core_list_hints()
    {
        $result = array();
        if ($this->_connect_state) {
            $_action = new \FreePBX\modules\Sccp_manager\aminterface\ExtensionStateListAction();
            $_response = $this->send($_action);
            $_res = $_response->getResult();
            foreach ($_res as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    $result[$key2] = '@' . $key2;
                }
            }
        }
        return $result;
    }

    function core_list_all_hints()
    {
        $result = array();
        if ($this->_connect_state) {
            $_action = new \FreePBX\modules\Sccp_manager\aminterface\ExtensionStateListAction();
            $_response = $this->send($_action);
            $_res = $_response->getResult();
            foreach ($_res as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    $result[$key.'@'.$key2] = $key.'@'.$key2;
                }
            }
        }
        return $result;
    }
// --------------------- SCCP Comands
    function sccp_list_keysets()
    {
        $result = array();
        if ($this->_connect_state) {
            $_action = new \FreePBX\modules\Sccp_manager\aminterface\SCCPShowSoftkeySetsAction();
            $_response = $this->send($_action);
            $_res = $_response->getResult();
            foreach ($_res as $key => $value) {
                $result[$key] = $key;
            }
        }
        return $result;
    }
    function sccp_get_active_device()
    {
        $result = array();
        if ($this->_connect_state) {
            $_action = new \FreePBX\modules\Sccp_manager\aminterface\SCCPShowDevicesAction();
            $_response = $this->send($_action);
            $result = $_response->getResult();
            foreach ($result as $key => $value) {
                $result[$key]['name'] = $key;
            }
        }
        return $result;
    }
    function sccp_getdevice_info($devicename)
    {
        $result = array();
        if ($this->_connect_state) {
            $_action = new \FreePBX\modules\Sccp_manager\aminterface\SCCPShowDeviceAction($devicename);
            $_response = $this->send($_action);
            $result = $_response->getResult();
            $result['MAC_Address'] = $result['macaddress'];
        }
        return $result;
    }
    function sccpDeviceReset($devicename, $action = '')
    {
        if ($this->_connect_state) {
            if ($action == 'tokenack') {
                $_action = new \FreePBX\modules\Sccp_manager\aminterface\SCCPTokenAckAction($devicename);
            } else {
                $_action = new \FreePBX\modules\Sccp_manager\aminterface\SCCPDeviceRestartAction($devicename, $action);
            }
            $_response = $this->send($_action);
            $result['data'] = 'Device :'.$devicename.' Result: '.$_response->getMessage();
            $result['Response']=$_response->getKey('Response');
         //   $result = $_response->getResult();
        }
        return $result;
    }

//------------------- Core Comands ----
    function core_sccp_reload()
    {
        $result = array();
        if ($this->_connect_state) {
            $_action = new \FreePBX\modules\Sccp_manager\aminterface\ReloadAction('chan_sccp');
//            $_action = new \FreePBX\modules\Sccp_manager\aminterface\CommandAction('sccp reload force'); // No Response Result !!
            $_response = $this->send($_action);
            $result = $_response->getMessage();
        }
        return $result;
    }
    function getSCCPVersion()
    {
        $result = array();
        if ($this->_connect_state) {
            $_action = new \FreePBX\modules\Sccp_manager\aminterface\SCCPConfigMetaDataAction();
            $_response = $this->send($_action);
            $result = $_response->getResult();
        }
        return $result;
    }

    function getRealTimeStatus()
    {
        $result = array();
        if ($this->_connect_state) {
            $_action = new \FreePBX\modules\Sccp_manager\aminterface\CommandAction('realtime mysql status');
            $_response = $this->send($_action);
            $res = $_response->getResult();
            if (!empty($res['output'])) {
                $result = $res['output'];
            } else {
                $result = $_response->getMessage();
            }
        }
        return $result;
    }
}
