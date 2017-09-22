<?php

namespace Virge\Stork\Service;

use Thruway\ClientSession;

use Virge\Stork;
use Virge\Stork\Component\PubSubMessage;
use Virge\Stork\Component\Websocket\Message as WebsocketMessage;
use Virge\Virge;

/**
 * Push messaging service is used to receive messages from PubSubProvider and broadcast
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

        $this->getPubSubService()->onSessionStart($session, $loop, [$this, 'onReceiveMessage']);
    }

    public function onSessionEnd()
    {
        $this->getPubSubServer()->onSessionEnd();
    }
    
    /**
     * When we receive a message from the PubSub Provider, attempt to broadcast it
     * out to any connected clients
     * @param string $rawMessage
     */
    public function onReceiveMessage($rawMessage)
    {
        Stork::debug("Received Message");
        $message = $this->getMessage($rawMessage);
        if(!$message) {
            Stork::debug("Invalid Message");
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
     * Take in a serialized string and return a valid PubSubMessage or null
     * 
     * @param string $rawMessage
     * @return PubSubMessage|null
     */
    protected function getMessage($rawMessage) 
    {
        $message = unserialize($rawMessage);
        
        return $message instanceof PubSubMessage ? $message : null;
    }

    protected function getPubSubService() : PubSubService
    {
        return Virge::service(PubSubService::class);
    }
}