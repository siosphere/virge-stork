<?php
namespace Virge\Stork\Service;


use Thruway\ClientSession;
use Virge\Event\Dispatcher;
use Virge\Stork\Event\{
    SessionJoinEvent,
    SessionLeaveEvent
};
use Virge\Virge;

/**
 * Client to listen on and emit events on WAMP Meta Events
 */
class MetaClientService extends AbstractClientService
{
    public function onOpen(ClientSession $session)
    {
        //
        $session->subscribe('wamp.session.on_join', function($joinEventDatas) {
            $joinEventData = $joinEventDatas[0];
            $authId = intval($joinEventData->authid);
            $authProvider = $joinEventData->authprovider;
            $authRole = $joinEventData->authrole;
            $sessionId = $joinEventData->session;
            $authMethod = $joinEventData->authmethod;

            if($authId) {
                $event = new SessionJoinEvent($authId, $authProvider, $authRole, $sessionId, $authMethod);
                $result = Dispatcher::dispatch($event);
            }
        });

        $session->subscribe('wamp.session.on_leave', function($datas) {
            $sessionId = $datas[0];

            $event = new SessionLeaveEvent($sessionId);
            $result = Dispatcher::dispatch($event);
        });
    }

    public function onClose()
    {
    }
}