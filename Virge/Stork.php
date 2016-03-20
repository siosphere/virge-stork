<?php

namespace Virge;

use Ratchet\ConnectionInterface;
use Virge\Stork\Component\Session;
use Virge\Stork\Component\Websocket\Message as WebsocketMessage;
use Virge\Stork\Component\Websocket\Topic;
use Virge\Stork\Component\ZMQ\Message as ZMQMessage;

/**
 * Stork is used to setup the websocket and ZMQ servers, provide authentication
 * and per-topic subscription validation. 
 * 
 * It is used to register available topics by version and feedName, setup new
 * topic verifiers, and to ultimately push messages through to connected
 * clients.
 */
class Stork
{
    /**
     * Holds available re-usable topic verifiers
     * @var array
     */
    protected static $verifiers = [];
    
    /**
     * Our connection authenticator
     */
    protected static $authenticator = null;
    
    /**
     * @var \SessionHandler 
     */
    protected static $sessionHandler = null;
    
    /**
     * Holds our available topics (by version.feedName)
     * @var array 
     */
    protected static $topics = [];
    
    public static function push($topicStr, WebsocketMessage $message)
    {
        $zmqMessage = new ZMQMessage($message, $topicStr);
        Virge::service('virge.stork.service.zmq_messaging')->push($zmqMessage);
    }
    
    /**
     * Create a new topic by version and feedName that will be available to 
     * subscribe to. You can also chain verifiers to it (either callables, or
     * existing verifiers registered with Stork)
     * 
     * @param string $version
     * @param string $feedName
     * @return Topic
     */
    public static function topic($version, $feedName)
    {
        if(!isset(self::$topics[$version])) {
            self::$topics[$version] = [];
        }
        
        if(!isset(self::$topics[$version][$feedName])) {
            self::$topics[$version][$feedName] = [];
        }
        
        return self::$topics[$version][$feedName] = new Topic($version, $feedName);
    }
    
    /**
     * Verify the user is able to subscribe to their chosen topic
     * 
     * @param ConnectionInterface $conn
     * @param string $topicString
     * @param string $reason
     * @return boolean
     */
    public static function verify(ConnectionInterface $conn, $topicString, &$reason ) {
        
        $topicData = self::getTopicFromString($topicString);
        if(!$topicData) {
            $reason = "Invalid topic";
            return false;
        }
        
        $topic = $topicData[0];
        $feedId = $topicData[1];
        
        $verifiers = $topic->getVerifiers();
        $allowed = true;
        foreach($verifiers as $verifier) {
            if(is_callable($verifier)) {
                $allowed = call_user_func_array($verifier, [$conn->Session, $topic, $feedId, &$reason]);
            } elseif(isset(self::$verifiers[$verifier])) {
                $allowed = call_user_func_array(self::$verifiers[$verifier], [$conn->Session, $topic, $feedId, &$reason]);
            } else {
                $allowed = false;
                $reason = "Invalid topic verifier: {$verifier}";
            }
            if(!$allowed) {
                break;
            }
        }
        
        return $allowed;
    }
    
    /**
     * Add a reusable verifier for topics
     * @param string $verifierName
     * @param callable $callable
     */
    public static function verifier($verifierName, $callable)
    {
        self::$verifiers[$verifierName] = $callable;
    }
    
    /**
     * Set our authenticator for initial websocket connection, will receive the
     * ConnectionInterface $conn,
     * @param callable $callable
     */
    public static function authenticator($callable)
    {
        self::$authenticator = $callable;
    }
    
    /**
     * Set the session handler for reading in the connecting user's session
     * @param \SessionHandlerInterface $handler
     */
    public static function sessionHandler(\SessionHandlerInterface $handler)
    {
        self::$sessionHandler = $handler;
    }
    
    /**
     * Authenticate the incoming websocket connection using our authenticator.
     * If not authenticator given, grant access by default
     * 
     * @param ConnectionInterface $conn
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public static function authenticate(ConnectionInterface $conn)
    {
        if(self::$authenticator === null) {
            return true;
        }
        
        if(!is_callable(self::$authenticator)) {
            throw new \InvalidArgumentException("Authenticator must be callable");
        }
        
        $authenticated = call_user_func_array(self::$authenticator, [$conn]);
        if(!$authenticated) {
            $conn->close();
            return false;
        }
        
        return true;
    }
    
    /**
     * Setup the user's session and tie it to their Websocket Connection
     * @param ConnectionInterface $conn
     */
    public static function setupSession(ConnectionInterface $conn)
    {
        $handler = self::$sessionHandler ?: new \SessionHandler();
        
        if ($handler instanceof \SessionHandlerInterface) {
            session_set_save_handler($handler, false);
        }
        $sessionId = $conn->WebSocket->request->getCookie(ini_get('session.name'));
        session_id($sessionId);
        session_start();
        $sessionData = $_SESSION;
        session_write_close();
        
        $conn->Session = new Session($sessionData);
    }
    
    /**
     * Get the topic based on the topic string, return an array if valid
     * where index [0] is the Stork Topic, and index [1] is the chosen feedId
     * @param string $topicStr
     * @return boolean|array
     */
    public static function getTopicFromString($topicStr)
    {
        $topicData = explode('.', $topicStr);
        if(count($topicData) !== 3) {
            return false;
        }
        
        $version = $topicData[0];
        $feedName = $topicData[1];
        $feedId = $topicData[2];
        
        if(!isset(self::$topics[$version]) || !isset(self::$topics[$version][$feedName])) {
            return false;
        }
        
        $topic = self::$topics[$version][$feedName];
        
        return [$topic, $feedId];
    }
}