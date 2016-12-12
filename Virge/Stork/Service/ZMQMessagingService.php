<?php
namespace Virge\Stork\Service;

use Virge\Stork;
use Virge\Stork\Component\ZMQ\Message as ZMQMessage;
use ZMQ;
use ZMQContext;

/**
 * Used to start a ZMQ Publish server, as well as push messages to the Publish
 * server, which will ultimately broadcast them out to the Websocket Servers.
 */
class ZMQMessagingService
{
    /**
     * Hold the server configuration of all our websocket servers
     * [
     *      [
     *          'host'  =>  '', //hostname of your zmq
     *          'port'  =>  '', //port ZMQ is listening on
     *      ]
     * ]
     */
    protected $websocketServers;
    
    /**
     * ZMQ Publish server hostname
     */
    protected $zmqServer;
    
    /**
     * ZMQ Publish server port
     */
    protected $zmqPort;
    
    /**
     * ZMQ Context
     */
    protected $context;
    
    /**
     * React Loop
     */
    protected $loop;
    
    /**
     * 
     * @param string $zmqServer
     * @param string $zmqPort
     * @param array $websocketServers
     */
    public function __construct($zmqServer, $zmqPort, $websocketServers) 
    {
        $this->websocketServers = $websocketServers;
        $this->zmqServer = $zmqServer;
        $this->zmqPort = $zmqPort;
    }
    
    /**
     * @param ZMQMessage $message
     */
    public function push(ZMQMessage $message)
    {
        Stork::debug("Pushing ZMQ Message to ZMQ Publishers");
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'virge:stork');
        $socket->setSockOpt(ZMQ::SOCKOPT_LINGER, 10);
        if(!filter_var($this->zmqServer, FILTER_VALIDATE_IP) === false) {
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
        $this->pub->setSockOpt(ZMQ::SOCKOPT_LINGER, 10);
        foreach($this->websocketServers as $serverConfig) {
            $port = $serverConfig['port'];
            if(!filter_var($serverConfig['host'], FILTER_VALIDATE_IP) === false) {
                $host = $serverConfig['host'];
            } else {
                $host = gethostbyname($serverConfig['host']);
            }
            Stork::debug("Connecting to Websocket servers for broadcast: " . sprintf("tcp://%s:%s", $host, $port));
            $this->pub->connect(sprintf("tcp://%s:%s", $host, $port));
        }
        
        $pull = $context->getSocket(ZMQ::SOCKET_PULL);
        $pull->bind(sprintf("tcp://*:%s", $this->zmqPort));
        $pull->on('message', [$this, 'onZMQMessage']);
        
        $this->getLoop()->run();
    }
    
    /**
     * When the publishing server receives a ZMQ message, broadcast it out
     * to all websocket servers we are connected to
     * @param type $message
     */
    public function onZMQMessage($message) 
    {
        Stork::debug("Received ZMQ Message, publishing to all connected Websocket Servers");
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
}