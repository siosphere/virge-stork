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
abstract class AbstractClientService
{
    protected $websocketUrl;

    protected $realm;

    protected $role;

    protected $secret;

    protected $client;

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

    public abstract function onOpen(ClientSession $session);

    public abstract function onClose();
    
    /**
     * Start a WAMP client that connects to a WAMP router. Registers two functions
     * that will allow it to authenticate connections, and do topic authentication
     */
    public function startClient()
    {
        $this->client = new Client($this->realm);
        $this->client->setAuthId($this->role);
        $this->client->addClientAuthenticator(new ClientWampCraAuthenticator($this->role, $this->secret));
        $this->client->addTransportProvider(new PawlTransportProvider($this->getIpUrl($this->websocketUrl)));
        $this->client->on('open', function(ClientSession $session) {
            return $this->onOpen($session);
        });

        $this->client->on('close', function() {
            return call_user_func_array([$this, 'onClose'], func_get_args());
        });

        $this->client->start();
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
}