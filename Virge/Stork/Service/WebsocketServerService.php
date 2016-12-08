<?php
namespace Virge\Stork\Service;

use Thruway\Authentication\ClientWampCraAuthenticator;
use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;
use Virge\Stork\Component\Websocket\TopicManager;
use Virge\Stork\Service\PushMessagingService;
use Virge\Stork;
use Virge\Virge;

/**
 * Websocket Server, used to setup the session
 */
class WebsocketServerService
{
    protected $websocketUrl;

    protected $realm;

    protected $role;

    protected $secret;

    /**
     * Setup to use our own TopicManager, so we can validate per topic 
     * subscriptions
     */
    public function __construct(string $websocketUrl, string $realm, string $role, string $secret) 
    {
        $this->websocketUrl = $websocketUrl;
        $this->realm = $realm;
        $this->role = $role;
        $this->secret = $secret;
    }
    
    /**
     * Start a WAMP client that connects to a WAMP router. Registers two functions
     * that will allow it to authenticate connections, and do topic authentication
     */
    public function startServer()
    {
        $client = new Client($this->realm);
        $client->setAuthId($this->role);
        $client->addClientAuthenticator(new ClientWampCraAuthenticator($this->role, $this->secret));
        $client->addTransportProvider(new PawlTransportProvider($this->getIpUrl($this->websocketUrl)));
        $client->on('open', function(ClientSession $session) use($client) {

            $this->getPushMessagingService()->onSessionStart($session,  $client->getLoop());

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

        });
        $client->start();
    }

    protected function getIpUrl($websocketUrl)
    {
        $urlData = parse_url($websocketUrl);
        if(!$urlData) {
            throw new \RuntimeException("Invalid websocket url, should be ws/wss://hostname|ip:80/");
        }

        $host = $urlData['host'];
        if(!filter_var($host, FILTER_VALIDATE_IP) === false) {
            return $websocketUrl;
        }

        return $urlData['scheme'] . '://' . gethostbyname($urlData['host']) . ':' . $urlData['port'] . $urlData['path'];
    }

    protected function getPushMessagingService() : PushMessagingService
    {
        return Virge::service(PushMessagingService::class);
    }
}