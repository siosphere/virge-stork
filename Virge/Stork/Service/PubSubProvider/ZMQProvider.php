<?php
namespace Virge\Stork\Service\PubSubProvider;

use Thruway\ClientSession;
use Virge\Core\Config;
use Virge\Stork;
use Virge\Stork\Component\PubSubMessage;
use ZMQ;
use ZMQContext;

class ZMQProvider implements \Virge\Stork\Service\PubSubProviderInterface
{
    protected $subscription;

    public function __construct() 
    {
        $this->zmqServer = Config::get('stork', 'zmq_server');
        $this->zmqPort = Config::get('stork', 'zmq_port');
        
        $this->websocketServers = Config::get('stork', 'websocket_servers');
        $this->websocketHostname = Config::get('stork', 'websocket_hostname');
    }

    public function onSessionStart(ClientSession $session, $loop, callable $callback)
    {
        $context = new \React\ZMQ\Context($loop);
        $this->callback = $callback;
        
        $this->subscription = $context->getSocket(\ZMQ::SOCKET_SUB);
        $this->subscription->bind(sprintf("tcp://%s:5556", gethostbyname($this->websocketHostname)));
        $this->subscription->subscribe("virge:stork");
        $this->subscription->on('message', [$this, 'onReceiveWebsocketMessage']);
    }

    public function onSessionEnd()
    {
        $endpoints = $this->subscription->getEndpoints();
        foreach($endpoints['bind'] as $endpoint) {
            $this->subscription->unbind($endpoint);
        }
    }

    public function push(PubSubMessage $message)
    {
        Stork::debug("Pushing Message to ZMQ Publishers");
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'virge:stork');
        $socket->setSockOpt(ZMQ::SOCKOPT_LINGER, 20);
        if (filter_var($this->zmqServer, FILTER_VALIDATE_IP)) {
            $host = $this->zmqServer;
        } else {
            $host = gethostbyname($this->zmqServer);
        }
        $socket->connect(sprintf("tcp://%s:%s", $host, $this->zmqPort));
        $socket->send(serialize($message));
    }
    
    /**
     * 
     */
    public function startPublishingServer()
    {
        $context = $this->getContext();
        $this->pub = $context->getSocket(ZMQ::SOCKET_PUB);
        $this->pub->setSockOpt(ZMQ::SOCKOPT_LINGER, 20);
        foreach($this->websocketServers as $serverConfig) {
            $port = $serverConfig['port'];
            $host = $serverConfig['host'];

            if (filter_var($host, FILTER_VALIDATE_IP)) {
                $host = $host;
            } else {
                $host = gethostbyname($host);
            }

            Stork::debug("Connecting to Websocket servers for broadcast: " . sprintf("tcp://%s:%s", $host, $port));
            $this->pub->connect(sprintf("tcp://%s:%s", $host, $port));
        }
        
        $host = gethostbyname($this->zmqServer);

        $pull = $context->getSocket(ZMQ::SOCKET_PULL);
        $pull->bind(sprintf("tcp://%s:%s", $host, $this->zmqPort));
        $pull->on('message', [$this, 'onZMQMessage']);
        
        $this->getLoop()->run();
    }

    public function onReceiveWebsocketMessage($rawMessage)
    {
        call_user_func_array($this->callback, [substr($rawMessage, strlen('virge:stork') + 1)]);
    }
    
    /**
     * When the publishing server receives a ZMQ message, broadcast it out
     * to all websocket servers we are connected to
     * @param type $message
     */
    public function onZMQMessage($message) 
    {
        Stork::debug("Received ZMQ Message: \n ".$message." \n, publishing to all connected Websocket Servers\n\n");
        //publish the message
        $this->pub->send('virge:stork '.$message, ZMQ::MODE_NOBLOCK);
    }
    
    /**
     * Get react ZMQ Context
     */
    protected function getContext()
    {
        if($this->context) {
            return $this->context;
        }
        
        return $this->context = new \React\ZMQ\Context($this->getLoop());
    }
    
    /**
     * Get React loop
     */
    protected function getLoop() 
    {
        if($this->loop) {
            return $this->loop;
        }
        
        return $this->loop = \React\EventLoop\Factory::create();
    }

    public function onReceiveMessage($message)
    {
        
    }
}