<?php

/**
 * 
 * Core Comsnd Interface 
 * 
 *  https://www.voip-info.org/asterisk-manager-example-php/
 */
/* !TODO!: Re-Indent this file.  -TODO-: What do you mean? coreaccessinterface  ??  */

namespace FreePBX\modules\Sccp_manager;

namespace FreePBX\modules\Sccp_manager\aminterface;

abstract class Message {

    const EOL = "\r\n";
    const EOM = "\r\n\r\n";

    protected $lines;
    protected $variables;
    protected $keys;
    protected $createdDate;
    private $_responseHandler;

    public function getResponseHandler() {
        if (strlen($this->_responseHandler) > 0) {
            //throw new PAMIException('Hier:' . $this->_responseHandler);
            return (string) $this->_responseHandler;
        } else {
            return "";
        }
    }

    public function setResponseHandler($responseHandler) {
        if (0 == strlen($responseHandler)) {
            return ;
        }
        $className = '\\FreePBX\\modules\\Sccp_manager\\aminterface\\Message\\Response\\' . $responseHandler . 'Response';
        if (class_exists($className, true)) {
            $this->_responseHandler = $responseHandler;
        } else {
            return ;
        }
    }

    public function setVariable($key, $value) {
        $key = strtolower($key);
        $this->variables[$key] = $value;
    }

    public function getVariable($key) {
        $key = strtolower($key);
        if (!isset($this->variables[$key])) {
            return null;
        }
        return $this->variables[$key];
    }

    protected function setKey($key, $value) {
        $key = strtolower((string) $key);
        $this->keys[$key] = (string) $value;
    }

    public function getKey($key) {
        $key = strtolower($key);
        if (!isset($this->keys[$key])) {
            return null;
        }
        //return (string)$this->keys[$key];
        return $this->keys[$key];
    }

    public function getVariables() {
        return $this->variables;
    }

    public function getActionID() {
        return $this->getKey('ActionID');
    }

    public function getKeys() {
        return $this->keys;
    }

    private function serializeVariable($key, $value) {
        return "Variable: $key=$value";
    }

    protected function finishMessage($message) {
        return $message . self::EOL . self::EOL;
    }

    public function serialize() {
        $result = array();
        foreach ($this->getKeys() as $k => $v) {
            $result[] = $k . ': ' . $v;
        }
        foreach ($this->getVariables() as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $singleValue) {
                    $result[] = $this->serializeVariable($k, $singleValue);
                }
            } else {
                $result[] = $this->serializeVariable($k, $v);
            }
        }
        $mStr = $this->finishMessage(implode(self::EOL, $result));
        return $mStr;
    }

    public function setActionID($actionID) {
        if (0 == strlen($actionID)) {
//            throw new PAMIException('ActionID cannot be empty.');
            return;
        }

        if (strlen($actionID) > 69) {
//            throw new PAMIException('ActionID can be at most 69 characters long.');
            return;
        }

        $this->setKey('ActionID', $actionID);
    }

    public function __construct() {
        $this->lines = array();
        $this->variables = array();
        $this->keys = array();
        $this->createdDate = time();
    }

}

namespace FreePBX\modules\Sccp_manager\aminterface\Message;

abstract class Response {

    protected $_events;
    protected $_completed;
    protected $keys;

    public function isSuccess() {
        return stristr($this->getKey('Response'), 'Error') === false;
    }

    public function isComplete() {
        return $this->_completed;
    }

    public function isList() {
        return
                stristr($this->getKey('EventList'), 'start') !== false || stristr($this->getMessage(), 'follow') !== false
        ;
    }

    public function setActionId($actionId) {
        $this->setKey('ActionId', $actionId);
    }

    public function getMessage() {
        return $this->getKey('Message');
    }

    public function __construct($rawContent) {
        parent::__construct($rawContent);

        $this->_events = array();
        $this->_eventsCount = 0;
        $this->_completed = !$this->isList();
    }

}

//namespace FreePBX\modules\Sccp_manager\aminterface\Message;

class LoginAction extends \FreePBX\modules\Sccp_manager\aminterface\Message {

    /**
     * Constructor.
     *
     * @param string $user     AMI username.
     * @param string $password AMI password.
     *
     * @return void
     */
    public function __construct($user, $password) {
        parent::__construct('Login');
        $this->setKey('Action', $what);
        $this->setKey('ActionID', microtime(true));
        $this->setKey('Username', $user);
        $this->setKey('Secret', $password);
    }

}

namespace FreePBX\modules\Sccp_manager;

class aminterface {

    var $_socket;
    var $_error;
    var $_config;
//    var $ProcessingMessage;
    private $_lastActionClass;
    private $_lastActionId;
    private $_lastRequestedResponseHandler;
    private $_ProcessingMessage;
    private $_responseFactory;

    public function __construct($parent_class = null) {
        global $amp_conf;
        $this->paren_class = $parent_class;
        $this->_socket = false;
        $this->_error = array();
        $this->_config = array('host' => 'localhost', 'user' => '', 'pass' => '', 'port' => '5038', 'tsoket' => 'tcp://', 'timeout' => 30, 'enabled' => false);
        $fld_conf = array('user' => 'AMPMGRUSER', 'pass' => 'AMPMGRPASS');
        if (isset($amp_conf['AMPMGRUSER'])) {
            foreach ($fld_conf as $key => $value) {
                if (isset($amp_conf[$value])) {
                    $this->_config[$key] = $amp_conf[$value];
                }
            }
        }
    }

    public function info() {
        $Ver = '13.0.4';
        if ($this->_config['enabled']) {
            return Array('Version' => $Ver,
                'about' => 'AMI data ver: ' . $Ver);
        } else {
            return Array('Version' => $Ver,
                'about' => 'Disabled AMI  ver: ' . $Ver);
        }
    }

    /**
     * Opens a tcp connection to ami.
     *
     */
    public function open() {
        $cString = $this->_config['tsoket'] . $this->_config['host'] . ':' . $this->_config['port'];
        $this->_context = stream_context_create();
        $errno = 0;
        $errstr = '';
        $this->_socket = @stream_socket_client(
                        $cString, $errno, $errstr,
                        $this->_config['timeout'], STREAM_CLIENT_CONNECT, $this->_context
        );
        if ($this->_socket === false) {
            $this->_errorException('Error connecting to ami: ' . $errstr . $cString);
            return false;
        }
//        FreePBX\modules\Sccp_manager\aminterface\Message\LoginAction::
        $msg = new aminerface\Message\LoginAction($this->_config['user'], $this->_config['pass']);

        $response = $this->send($msg);
        return $response;
        /*
          $params = array('Action' => 'Login', 'UserName' => $this->_config['user'], 'Secret' => $this->_config['pass'], 'Events' => 'on');
          $id = @stream_get_line($this->_socket, 1024, aminterface\Message::EOL);

          if (strstr($id, 'Asterisk') === false) {
          $this->_errorException('Unknown peer. Is this an ami?: ' . $id);
          return false;
          }
          $response = $this->send($params);
          if (!$response->isSuccess()) {
          $this->_errorException('Could not connect: ' . $response->getMessage());
          return false;
          }
          @stream_set_blocking($this->_socket, 0);
          $this->_ProcessingMessage = '';
          //register_tick_function(array($this, 'process'));
         * 
         */
    }

    /**
     * Closes the connection to ami.
     */
    public function close() {
        @stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
    }

    public function send($message) {
        $messageToSend = $message->serialize();
        /*        foreach ($params as $key => $value) {
          $messageToSend .= $key . ': ' . $value . aminterface\Message::EOL;
          }
          $messageToSend .= aminterface\Message::EOL;
         * 
         */
        $length = strlen($messageToSend);

        $this->_lastActionId = $message->getActionId();
        $this->_lastRequestedResponseHandler = $message->getResponseHandler();
        $this->_lastActionClass = $message;

        if (@fwrite($this->_socket, $messageToSend) < $length) {
            $this->_errorException('Could not send message');
            return false;
        }
        while (1) {
            stream_set_timeout($this->_socket, 1);
//            stream_set_timeout($this->_socket, (isset($this->socket_param['timeout']) ? $this->socket_param['timeout'] : 1));
            $this->process();
            $info = stream_get_meta_data($this->_socket);
            if ($info['timed_out'] == false) {
                $response = $this->getRelated($message);
                if ($response != false) {
                    $this->_lastActionId = false;
                    return $response;
                }
            } else {
                break;
            }
        }
        $this->_errorException("Read waittime: " . ($this->socket_param['timeout']) . " exceeded (timeout).\n");
        return false;
    }

    protected function getRelated($message) {
        $ret = false;
        $id = 0;
//        $id = $message->getActionID('ActionID');
        if (isset($this->_incomingQueue[$id])) {
            $response = $this->_incomingQueue[$id];
            if ($response->isComplete()) {
                unset($this->_incomingQueue[$id]);
                $ret = $response;
            }
        }
        return $ret;
    }

    protected function getMessages() {
        $msgs = array();
        // Read something.
        $read = @fread($this->_socket, 65535);
        if ($read === false || @feof($this->_socket)) {
            $this->_errorException('Error reading');
        }
        $this->_ProcessingMessage .= $read;
        // If we have a complete message, then return it. Save the rest for
        // later.
        return $msgs;
        while (($marker = strpos($this->_ProcessingMessage, Message::EOM))) {
            $msg = substr($this->_ProcessingMessage, 0, $marker);
            $this->_ProcessingMessage = substr(
                    $this->_ProcessingMessage, $marker + strlen(Message::EOM)
            );
            $msgs[] = $msg;
        }
        return $msgs;
    }

    public function process() {
        $msgs = $this->getMessages();
        foreach ($msgs as $aMsg) {
            $resPos = strpos($aMsg, 'Response:');
            $evePos = strpos($aMsg, 'Event:');
            if (($resPos !== false) && (($resPos < $evePos) || $evePos === false)) {
                $response = $this->_msgToResponse($aMsg);
                $this->_incomingQueue[$this->_lastActionId] = $response;
            } else if ($evePos !== false) {
                /*                $event = $this->_messageToEvent($aMsg);
                  $response = $this->findResponse($event);
                  if ($response === false || $response->isComplete()) {
                  $this->dispatch($event);
                  } else {
                  $response->addEvent($event);
                  }
                 * 
                 */
            } else {
                // broken ami.. sending a response with events without
                // Event and ActionId
                $bMsg = 'Event: ResponseEvent' . "\r\n";
                $bMsg .= 'ActionId: ' . $this->_lastActionId . "\r\n" . $aMsg;
                $event = $this->_messageToEvent($bMsg);
                $response = $this->findResponse($event);
                $response->addEvent($event);
            }
        }
    }

    private function _msgToResponse($msg) {
        $response = $this->_msgFromRaw($msg, $this->_lastActionClass, $this->_lastRequestedResponseHandler);
        /*        $actionId = $response->getActionId();
          if ($actionId === null) {
          $actionId = $this->_lastActionId;
          $response->setActionId($this->_lastActionId);
          }
         * 
         */
        return $response;
    }

    public function _msgFromRaw($message, $requestingaction = false, $responseHandler = false) {

        $_className = false;
        if ($responseHandler != false) {
            $_className = '\\FreePBX\\modules\\Sccp_manager\\aminterface\\Response';
//            $_className = '\\FreePBX\\modules\\Sccp_manager\\aminterface\\' . $responseHandler . 'Response';
        } else if ($requestingaction != false) {
            switch ($requestingaction) {
                case 'login':

                    break;

                default:
                    break;
            }
//            $_className = '\\FreePBX\\modules\\Sccp_manager\\Response\\' . substr(get_class($requestingaction), 20, -6) . 'Response';
            $_className = '\\FreePBX\\modules\\Sccp_manager\\Response';
        }
        if ($_className) {
            if (class_exists($_className, true)) {
                $responseclass = $_className;
            } else if ($responseHandler != false) {
                $this->_errorException('Response Class ' . $_className . '  requested via responseHandler, could not be found');
            }
        }
//        return new $responseclass($message);
    }

    protected function _errorException($msg) {
        $this->_error[] = $msg;
    }

    /*
     *    Replace or dublicate to AMI interface   
     */

//-------------------------------------------------------------------------------
//-------------------------------------------------------------------------------
    function core_list_all_exten($keyfld = '', $filter = array()) {
        $result = array();
        return $result;
    }

    /*
      public function sccp_list_extnint() {
      $hint_key = array();
      $hint_all = $this->sccp_list_all_hints();
      foreach ($hint_all as $value) {
      $res = $this->loc_after('@', $value);
      //           array_search($res, $hint_key)) != NULL)
      if (!isset($hint_key[$res])) {
      $hint_key[$res] = '@' . $res;
      }
      }
      return $hint_key;
      }

      private function astman_retrieveJSFromMetaData($segment = "") {
      global $astman;
      $params = array();
      if ($segment != "") {
      $params["Segment"] = $segment;
      }
      $response = $astman->send_request('SCCPConfigMetaData', $params);
      if ($response["Response"] == "Success") {
      //outn(_("JSON-content:").$response["JSON"]);
      $decode = json_decode($response["JSON"], true);
      return $decode;
      } else {
      return false;
      }
      }

      function getеtestChanSCC() {
      global $astman;
      //        $action = Array('SCCPShowGlobals',);
      $params = array();
      $action = 'SCCPShowSoftkeySets';
      //        $params = array('Segment' => 'device', 'ResultFormat'=>'command' );
      //        $params = array('Segment' => 'device');
      //        $params = array();
      $metadata = $astman->send_request($action, $params);
      return $metadata;
      }


      function core_list_all_exten($keyfld = '', $filter = array()) {
      $result = array();
      $fld_data = array('exten', 'context', 'statustext', 'status');
      $row_data = $this->astman_GetRaw('ExtensionStateList');
      if (empty($row_data) || empty($row_data['eventlist'])) {
      return $result;
      }
      if ($row_data['eventlist'] == 'Complete') {
      foreach ($row_data['list'] as $value) {
      $exten = $value['exten'];
      $context = $value['context'];
      $exclude = empty($exten);
      switch ($keyfld) {
      case 'exten':
      $store_key = $exten;
      break;
      case 'hint':
      default:
      $store_key = $exten . '@' . $context;
      break;
      }

      if (!empty($filter)) {
      foreach ($filter as $fkey => $fvalue) {
      if (!empty($value[$fkey])) {
      if (strpos(';' . $fvalue . ';', ';' . $value[$fkey] . ';') !== false) {
      $exclude = true;
      }
      }
      }
      }
      if (!$exclude) {
      foreach ($fld_data as $key) {
      $result[$store_key][$key] = (empty($value[$key]) ? '' : $value[$key] );
      }
      $result[$store_key]['key'] = $exten . '@' . $context;
      $result[$store_key]['label'] = $exten . '@' . $context;
      }
      }
      }
      return $result;
      }

      private function astLogin($host = "", $username = "", $password = "") {
      if ($this->Sok_param['enabled'] != true) {
      return FALSE;
      }

      if (empty($host) || empty($username) || empty($password)) {
      if (empty($this->Sok_param['host']) || empty($this->Sok_param['user']) || empty($this->Sok_param['pass'])) {
      return FALSE;
      }
      $host = (empty($host) ? $this->Sok_param['host'] : $host);
      $username = (empty($username) ? $this->Sok_param['user'] : $username);
      $password = (empty($password) ? $this->Sok_param['pass'] : $password);
      }
      $this->socket = @fsockopen($host, "5038", $errno, $errstr, 1);

      if (!$this->socket) {
      $this->error = "Could not connect - $errstr ($errno)";
      return FALSE;
      } else {
      stream_set_timeout($this->socket, 1);

      //            $wrets = $this->astQuery("Action: Login\r\nUserName: $username\r\nSecret: $password\r\nEvents: off\r\n\r\n");
      $wrets = $this->astQuery("Action: Login\r\nUserName: $username\r\nSecret: $password\r\nEvents: on\r\n\r\n");

      if (strpos($wrets['raw'], "Message: Authentication accepted") != FALSE) {
      return TRUE;
      } else {
      $this->error = "Could not login - Authentication failed ";
      fclose($this->socket);
      $this->socket = FALSE;
      return FALSE;
      }
      }
      }

      private function astLogout() {
      if ($this->socket) {
      fputs($this->socket, "Action: Logoff\r\n\r\n");
      while (!feof($this->socket)) {
      $wrets .= fread($this->socket, 8192);
      }
      fclose($this->socket);
      $this->socket = "FALSE";
      }
      return;
      }

      private function astQuery($query, $rawdata = false) {
      $wrets = "";
      //        return $this->socket;
      if ($this->socket === FALSE) {
      return FALSE;
      }
      $time_Query = microtime(true);
      $parameters = array();
      $parameters['raw'] = '';
      $data_store = 'data';
      $parameters[$data_store] = '';
      fputs($this->socket, $query);
      $parameters['raw_q'] = $query;
      $stop_data = false;
      $row_list = false;
      $row_list_arr = Array();
      $stop_data = false;
      $row_list = !$rawdata;
      do {
      $line = fgets($this->socket, 4096);
      $parameters['raw'] .= $line;
      if (!$rawdata) {
      //            if (true) {
      $a = strpos($line, ':');
      if ($a) {
      $key = trim(strtolower(substr($line, 0, $a)));
      switch ($key) {
      case 'eventlist':
      if (strpos($line, 'start') !== false) {
      $row_list = true;
      } else {
      $row_list = false;
      }
      case 'response':
      case 'message':
      $parameters[$key] = trim(substr($line, $a + 2));
      if (!empty($parameters['response']) && !empty($parameters['message'])) {
      if ($parameters['response'] == 'Error') {
      $stop_data = true;
      }
      }
      break;
      case 'json':
      $parameters[$key] = substr($line, $a + 2);
      $data_store = $key;
      break;
      default:
      if ($row_list) {
      $row_list_arr[$key] = trim(str_replace("\r\n", "", substr($line, $a + 2)));
      ;
      }
      $parameters[$data_store] .= $line;
      break;
      }
      // store parameter in $parameters
      } else {
      if (!isset($parameters[$data_store])) {
      $parameters[$data_store] = '';
      }
      $parameters[$data_store] .= $line;
      }
      }
      if ($line == "\r\n") {
      if ($row_list == false) {
      $stop_data = true;
      } else {
      $parameters['list'][] = $row_list_arr;
      $row_list_arr = array();
      }
      }
      $info = stream_get_meta_data($this->socket);
      } while ($stop_data == false && $info['timed_out'] == false);

      $parameters['time_Query'] = microtime(true) - $time_Query;
      $this->Sok_param['total'] = $this->Sok_param['total'] + $parameters['time_Query'];
      //        $this->astLogout();
      return $parameters;
      }

      function GetError() {
      return $this->error;
      }

      function astman_GetRaw($action = "", $parameters = array()) {
      $option = "";
      $result = array();

      if ($this->_socket === FALSE) {
      if (!$this->astLogin()) {
      $result["Response"] = "Faild";
      $result["Error"] = $this->error;
      return $result;
      }
      }

      $query = "Action: $action\r\n";

      foreach ($parameters as $var => $val) {
      if (is_array($val)) {
      foreach ($val as $k => $v) {
      $query .= "$var: $k=$v\r\n";
      }
      } else {
      $query .= "$var: $val\r\n";
      }
      }
      $result = $this->astQuery($query . "\r\n", true);
      $result = $this->astQuery($query . "\r\n", false);
      return $result;
      }

      /*
      private function astman_retrieveMeta($action = "", $parameters=array(), $rawdata = false) {
      // $parameters=array()
      global $amp_conf;

      if (empty($action)) {
      $action = 'SCCPConfigMetaData';
      }
      $query = "Action: $action\r\n";

      foreach($parameters as $var=>$val) {
      if (is_array($val)) {
      foreach($val as $k => $v) {
      $query .= "$var: $k=$v\r\n";
      }
      } else {
      $query .= "$var: $val\r\n";
      }
      }

      $result =  $this->astQuery($query."\r\n",$rawdata);

      if ($result["Response"] == "Success") {
      if ($rawdata) {
      return $result;
      } else {
      if (!empty($result["JSON"])) {
      $decode = json_decode($response["JSON"], true);
      return $decode;
      } else {
      return $result;
      }
      }
      } else {
      return $result;
      return array();
      }
      }
     */

    function t_get_meta_data() {
        global $amp_conf;
        $fp = fsockopen("127.0.0.1", "5038", $errno, $errstr, 10);

        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
        } else {
            fputs($fp, "Action: login\r\n");
            fputs($fp, "Username: " . $amp_conf[AMPMGRUSER] . "\r\n");
//        fputs ($fp,"Secret: secret\r\n");
            fputs($fp, "Secret: " . $amp_conf[AMPMGRPASS] . "\r\n");
            fputs($fp, "Events: on\r\n\r\n");

//        fputs ($fp,"Action: SCCPShowDevices\r\n");
//        fputs ($fp,"Action: DeviceStateList\r\n");
            fputs($fp, "Action: ExtensionStateList\r\n");
            fputs($fp, "Action: Status\r\n");

//        fputs ($fp,"Segment: general\r\n");
//        "Segments":["general","device","line","softkey"]}
//        fputs ($fp,"Segment: device\r\n");
//        fputs ($fp,"ResultFormat: command\r\n");
            fputs($fp, "\r\n");

            /*
              fputs ($fp,"Action: SCCPConfigMetaData\r\n");
              fputs ($fp,"\r\n");

              fputs ($fp,"Action: SCCPConfigMetaData\r\n");
              fputs ($fp,"Segment: general\r\n");
              fputs ($fp,"\r\n");

              fputs ($fp,"Action: SCCPConfigMetaData\r\n");
              fputs ($fp,"Segment: general\r\n");
              fputs ($fp,"ListResult: yes\r\n");
              fputs ($fp,"Option: fallback\r\n");
              fputs ($fp,"\r\n");

              fputs ($fp,"Action: SCCPConfigMetaData\r\n");
              fputs ($fp,"Segment: device\r\n");
              fputs ($fp,"ListResult: freepbx\r\n");
              fputs ($fp,"\r\n");

              fputs ($fp,"Action: SCCPConfigMetaData\r\n");
              fputs ($fp,"Segment: device\r\n");
              fputs ($fp,"Option: dtmfmode\r\n");
              fputs ($fp,"ListResult: yes\r\n");
              fputs ($fp,"\r\n");
             */

            fputs($fp, "Action: logoff\r\n\r\n");
//        print_r(fgets($fp));
            $resp = '';
            while (!feof($fp)) {
                $resp .= fgets($fp);
            }
//            print_r(fgets($fp));
//            print_r('<br>');
//                echo fgets($fp, 128);
        }
        fclose($fp);
        return $resp;
    }

}
