<?php
namespace Virge\Stork\Component\ZMQ;

use Virge\Stork\Component\Websocket\Message as WebsocketMessage;

/**
 * A message to be sent through the ZMQ sockets to the publishing server, and
 * forwarded onto the Websocket servers
 */
class Message
{
    /**
     * @var WebsocketMessage 
     */
    protected $websocketMessage;
    
    /**
     * The full topic this message is meant for (version.feedName.feedId)
     * @var string
     */
    protected $topicStr;
    
    /**
     * @param WebsocketMessage $message
     * @param string $topicStr
     */
    public function __construct(WebsocketMessage $message, $topicStr)
    {
        $this->websocketMessage = $message;
        $this->topicStr = $topicStr;
    }
    
    /**
     * @return WebsocketMessage
     */
    public function getWebsocketMessage()
    {
        return $this->websocketMessage;
    }
    
    /**
     * @return string
     */
    public function getTopicStr()
    {
        return $this->topicStr;
    }
}