<?php
namespace Virge\Stork\Service;

use Thruway\ClientSession;
use Virge\Stork\Service\PushMessagingService;
use Virge\Virge;

/**
 * Websocket Server, used to setup the session
 */
class WebsocketClientService extends AbstractClientService
{
    public function onOpen(ClientSession $session)
    {
        $this->getPushMessagingService()->onSessionStart($session,  $this->client->getLoop());
    }

    public function onClose()
    {
    }

    protected function getPushMessagingService() : PushMessagingService
    {
        return Virge::service(PushMessagingService::class);
    }
}