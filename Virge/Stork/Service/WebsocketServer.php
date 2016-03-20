<?php
namespace Virge\Stork\Service;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\ServerProtocol;
use Ratchet\Wamp\WampServerInterface;
use Virge\Stork\Component\Websocket\TopicManager;
use Virge\Stork;

/**
 * Websocket Server, used to setup the session
 */
class WebsocketServer extends \Ratchet\Wamp\WampServer
{
    /**
     * Setup to use our own TopicManager, so we can validate per topic 
     * subscriptions
     */
    public function __construct(WampServerInterface $app) {
        $this->wampProtocol = new ServerProtocol(new TopicManager($app));
    }
    
    /**
     * {@inheritdoc}
     * Setup our session, and make sure we pass the basic authentication before
     * continuing
     */
    public function onOpen(ConnectionInterface $conn) 
    {
        Stork::setupSession($conn);
        if(Stork::authenticate($conn)) {
            parent::onOpen($conn);
        }
    }
}