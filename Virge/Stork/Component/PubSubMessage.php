<?php
namespace Virge\Stork\Component;

use Thruway\ClientSession;
use Virge\Stork\Component\Websocket\Message as WebsocketMessage;

/**
 * A message to be sent through the to the publishing server, and
 * forwarded onto the Websocket servers
 */
class PubSubMessage
{
    /**
     * @var WebsocketMessage 
     */
    protected $websocketMessage;
    
    /**
     * The full topic this message is meant for (version.feedName.feedId)
     * @var string
     */
    protected $topics;

    /**
     * @var \DateTime
     */
    protected $timestamp;
    
    /**
     * @param WebsocketMessage $message
     * @param string[] $topicStr
     */
    public function __construct(WebsocketMessage $message, $topics = [])
    {
        $this->websocketMessage = $message;
        $this->topics = $topics;
        $this->timestamp = new \DateTime;
    }
    
    /**
     * @return WebsocketMessage
     */
    public function getWebsocketMessage()
    {
        return $this->websocketMessage;
    }
    
    /**
     * @return string[]
     */
    public function getTopics()
    {
        return $this->topics;
    }

    public function getTimestamp() : \DateTime
    {
        return $this->timestamp;
    }
}