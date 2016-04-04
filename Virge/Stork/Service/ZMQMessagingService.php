<?php
namespace Virge\Stork\Service;

use Virge\Stork\Component\ZMQ\Message as ZMQMessage;
use ZMQ;
use ZMQContext;

/**
 * Used to start a ZMQ Publish server, as well as push messages to the Publish
 * server, which will ultimately broadcast them out to the Websocket Servers.
 */
class ZMQMessagingService
{
    const SERVICE_ID = 'virge.stork.service.zmq_messaging';
    
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
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'virge:stork');
        $socket->setSockOpt(ZMQ::SOCKOPT_LINGER, 10);
        $socket->connect(sprintf("tcp://%s:%s", $this->zmqServer, $this->zmqPort));
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
            $host = $serverConfig['host'];
            $port = $serverConfig['port'];
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
    protected function getLoop() {
        if($this->loop) {
            return $this->loop;
        }
        
        return $this->loop = \React\EventLoop\Factory::create();
    }
}