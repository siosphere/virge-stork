<?php
namespace Virge\Stork\Service;

use Virge\Stork\Service\PubSubProviderInterface;
use Virge\Virge;

class PubSubService
{
    public function __construct(string $pubSubProviderService)
    {
        $this->pubSubProviderService = $pubSubProviderService;
    }

    public function startPublishingServer()
    {
        //push to redis
    }

    protected function getPubSubProvider() : PubSubProviderInterface
    {
        return Virge::service($this->pubSubProviderService);
    }
}