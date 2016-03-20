<?php

namespace Virge\Stork\Service;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Virge\Stork\Component\ZMQ\Message as ZMQMessage;

/**
 * Push messaging service is used to receive messages from ZMQ and broadcast
 * them to any clients connected on the given topic
 */
class PushMessagingService implements WampServerInterface
{
    protected $topics = [];
    
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
        $topicStr = $message->getTopicStr();
        
        if (!array_key_exists($topicStr, $this->topics)) {
            return;
        }
        
        $topic = $this->topics[$topicStr];

        // re-send the data to all the clients subscribed to that category
        $topic->broadcast([
            'type'      =>      $websocketMessage::MESSAGE_TYPE,
            'data'      =>      $websocketMessage->getData()
        ]);
    }
    
    /**
     * Store our topics by topicStr so we can broadcast out easily
     * @param ConnectionInterface $conn
     * @param mixed $topic
     */
    public function onSubscribe(ConnectionInterface $conn, $topic) 
    {
        $this->topics[$topic->getId()] = $topic;
    }
    public function onUnSubscribe(ConnectionInterface $conn, $topic) 
    {
        
    }
    public function onOpen(ConnectionInterface $conn) 
    {
    }
    public function onClose(ConnectionInterface $conn) 
    {
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) 
    {
        $conn->callError($id, $topic, 'You are not allowed to make calls')
        ->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) 
    {
        $conn->close();
    }
    
    public function onError(ConnectionInterface $conn, \Exception $ex) {
        
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