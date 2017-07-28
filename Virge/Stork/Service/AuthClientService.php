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
        $authOptions = Config::get('stork', 'auth_register_options');
        if(!$authOptions || !is_array($authOptions)) {
            $authOptions = [];
        }

        $session->register('io.virge.stork.auth', function($details) {
            $realm = $details[0];
            $session = $details[2];

            return Stork::authenticate($realm, $session);
        }, array_merge([
            'invoke' => 'last'
        ], $authOptions));

        $topicAuthOptions = Config::get('stork', 'topic_auth_register_options');
        if(!$topicAuthOptions || !is_array($topicAuthOptions)) {
            $topicAuthOptions = [];
        }

        $session->register('io.virge.stork.topic_auth', function($details) {
            $session = $details[0];
            $uri = $details[1];
            $action = $details[2];
            $userId = $session->authid;

            return $action === 'subscribe' && Stork::verify($session, $uri);
        }, array_merge([
            'invoke' => 'last'
        ], $topicAuthOptions));
    }

    public function onClose()
    {
        
    }
}