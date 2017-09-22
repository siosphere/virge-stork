<?php
namespace Virge\Stork\Service;

use Virge\Stork\Component\PubSubMessage;

interface PubSubProviderInterface
{
    public function push(PubSubMessage $message);

    public function startPublishingServer();

    public function onReceiveMessage($message);
}