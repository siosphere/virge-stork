<?php

namespace Virge\Stork\Component\Websocket;

use Ratchet\ConnectionInterface;
use Virge\Stork;

/**
 * Manage our topic subscriptions. When a user attempts to subscribe to a topic,
 * make sure the topic is valid, and that the user is allowed to subscribe by
 * passing them through all verifiers attached to the topic
 */
class TopicManager extends \Ratchet\Wamp\TopicManager
{
    
    /**
     * {@inheritdoc}
     * Additionally verify the connecting user can connect to the given topic
     */
    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $reason = '';
        if(!Stork::verify($conn, $topic, $reason)) {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => sprintf('You cannot subscribe to %s: %s', (string) $topic, $reason),
            ]));
            return false;
        }
        
        parent::onSubscribe($conn, $topic);
    }
}