<?php

namespace Virge\Stork\Service;

use Thruway\ClientSession;

use Virge\Stork;
use Virge\Stork\Component\ZMQ\Message as ZMQMessage;
use Virge\Stork\Component\Websocket\Message as WebsocketMessage;

/**
 * Push messaging service is used to receive messages from ZMQ and broadcast
 * them to any clients connected on the given topic
 */
class PushMessagingService
{
    /**
     * @var ClientSession
     */
    protected $session;

    protected $subscription;

    protected $websocketHostname;

    public function __construct($websocketHostname)
    {
        $this->websocketHostname = $websocketHostname;
    }

     /**
     * 
     * @param ClientSession $session
     * @param type $loop
     */
    public function onSessionStart(ClientSession $session, $loop)
    {
        $this->session = $session;
        $context = new \React\ZMQ\Context($loop);
        
        $this->subscription = $context->getSocket(\ZMQ::SOCKET_SUB);
        $this->subscription->bind(sprintf("tcp://%s:5556", $this->websocketHostname));
        $this->subscription->subscribe("virge:stork");
        $this->subscription->on('message', [$this, 'onReceiveZMQMessage']);
    }

    public function onSessionEnd()
    {
        $endpoints = $this->subscription->getEndpoints();
        foreach($endpoints['bind'] as $endpoint) {
            $this->subscription->unbind($endpoint);
        }
    }
    
    /**
     * When we receive a message from the ZMQ Socket, attempt to broadcast it
     * out to any connected clients
     * @param string $rawMessage
     */
    public function onReceiveZMQMessage($rawMessage)
    {
        Stork::debug("Received ZMQ Message");
        $message = $this->getZMQMessage($rawMessage);
        if(!$message) {
            Stork::debug("Invalid ZMQ Message");
            return;
        }
        
        $websocketMessage = $message->getWebsocketMessage();
        if(!$websocketMessage) {
            Stork::debug("Invalid Websocket Message");
            return false;
        }

        foreach($message->getTopics() as $topicId) {
            $this->broadcastMessageToTopic($websocketMessage, (string) $topicId);
        }
    }

    /**
     * Broadcast a given message to any subscribers on the topicId
     * @param AbstractPushMessage $message
     * @param string $topicId
     * @return \React\Promise\Promise
     */
    protected function broadcastMessageToTopic(WebsocketMessage $message, string $topicId)
    {
        if(strlen($topicId) === 0) {
            return;
        }
        
        //make sure assoc array
        $jsonArray = json_decode(json_encode($message->getData()), true);
        Stork::debug("Broadcasting message to topic: " . $topicId);
        return $this->session->publish($topicId, [
            [
                'type'      =>      $message->getType(),
                'data'      =>      $jsonArray,
                'timestamp' =>      $message->getTimestamp(),
            ]
        ], [], ["acknowledge" => true]);
    }
    
    /**
     * Take in a serialized string and return a valid ZMQMessage or null
     * 
     * @param string $rawZMQMessage
     * @return ZMQMessage|null
     */
    protected function getZMQMessage($rawZMQMessage) 
    {
        $message = unserialize(substr($rawZMQMessage, strlen('virge:stork') + 1) );
        
        return $message instanceof ZMQMessage ? $message : null;
    }
}