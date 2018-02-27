<?php
namespace Virge\Stork\Service;

use Thruway\ClientSession;
use Virge\Stork\Component\PubSubMessage;

interface PubSubProviderInterface
{
    public function onSessionStart(ClientSession $session, $loop, callable $callback);

    public function onSessionEnd();

    public function push(PubSubMessage $message);

    public function startPublishingServer();

    public function onReceiveMessage($message);
}