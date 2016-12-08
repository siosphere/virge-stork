<?php

namespace Virge\Stork\Service;

use Thruway\ClientSession;

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

     /**
     * 
     * @param ClientSession $session
     * @param type $loop
     */
    public function onSessionStart(ClientSession $session, $loop)
    {
        $this->session = $session;
        $context = new \React\ZMQ\Context($loop);
        
        $sub = $context->getSocket(\ZMQ::SOCKET_SUB);
        $sub->bind("tcp://*:5556");
        $sub->subscribe("virge:stork");
        $sub->on('message', [$this, 'onReceiveZMQMessage']);
    }
    
    /**
     * When we receive a message from the ZMQ Socket, attempt to broadcast it
     * out to any connected clients
     * @param string $rawMessage
     */
    public function onReceiveZMQMessage($rawMessage)
    {
        $message = $this->getZMQMessage($rawMessage);
        if(!$message) {
            return;
        }
        
        $websocketMessage = $message->getWebsocketMessage();
        if(!$websocketMessage) {
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