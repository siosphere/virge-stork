<?php

namespace Virge;

use Thruway\ClientSession;
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
     * Holds our available topics (by version.feedName)
     * @var array 
     */
    protected static $topics = [];
    
    public static function push($topics, WebsocketMessage $message)
    {
        $zmqMessage = new ZMQMessage($message, $topics);
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
     * @param ClientSession $session
     * @param string $topicString
     * @return boolean
     */
    public static function verify($session, $topicString) {
        
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
                $allowed = call_user_func_array($verifier, [$session, $topic, $feedId, &$reason]);
            } elseif(isset(self::$verifiers[$verifier])) {
                $allowed = call_user_func_array(self::$verifiers[$verifier], [$session, $topic, $feedId, &$reason]);
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
     * ClientSession $session, and the return data array
     * @param callable $callable
     */
    public static function authenticator($callable)
    {
        self::$authenticator = $callable;
    }
    
    /**
     * Authenticate the incoming websocket connection using our authenticator.
     * If not authenticator given, grant access by default
     * 
     * @param string $realm
     * @param ClientSession $session
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public static function authenticate(string $realm, $session)
    {
        if(self::$authenticator === null) {
            return true;
        }
        
        if(!is_callable(self::$authenticator)) {
            throw new \InvalidArgumentException("Authenticator must be callable");
        }

        $returnData = [];
        $returnData['role'] = 'frontend';

        $authenticated = call_user_func_array(self::$authenticator, [$session, &$returnData]);
        if(!$authenticated) {
            return [];
        }
        
        return $returnData;
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