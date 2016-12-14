<?php
namespace Virge\Stork\Service;

use Thruway\ClientSession;
use Virge\Stork;

/**
 * Websocket Server, used to setup the session
 */
class AuthClientService extends AbstractClientService
{
    public function onOpen(ClientSession $session)
    {
        $session->register('io.virge.stork.auth', function($details) {
            $realm = $details[0];
            $session = $details[2];

            return Stork::authenticate($realm, $session);
        });

        $session->register('io.virge.stork.topic_auth', function($details) {
            $session = $details[0];
            $uri = $details[1];
            $action = $details[2];
            $userId = $session->authid;

            return $action === 'subscribe' && Stork::verify($session, $uri);
        });
    }

    public function onClose()
    {
        
    }
}