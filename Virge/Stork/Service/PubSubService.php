<?php
namespace Virge\Stork\Service;

use Thruway\ClientSession;
use Virge\Stork\Component\PubSubMessage;
use Virge\Stork\Service\PubSubProviderInterface;
use Virge\Virge;

class PubSubService
{
    public function __construct(string $pubSubProviderService)
    {
        $this->pubSubProviderService = $pubSubProviderService;
    }

    public function onSessionStart(ClientSession $session, $loop, callable $callback)
    {
        return $this->getPubSubProvider()->onSessionStart($session, $loop, $callback);
    }

    public function onSessionEnd()
    {
        return $this->getPubSubProvider()->onSessionEnd();
    }

    public function push(PubSubMessage $message)
    {
        return $this->getPubSubProvider()->push($message);
    }

    public function onReceiveMessage($message)
    { 
        return $this->getPubSubProvider()->onReceiveMessage($message);
    }

    public function startPublishingServer()
    {
        //push to redis
        return $this->getPubSubProvider()->startPublishingServer();
    }

    protected function getPubSubProvider() : PubSubProviderInterface
    {
        return Virge::service($this->pubSubProviderService);
    }
}