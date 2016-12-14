<?php
use Virge\Virge;
use Virge\Stork;
use Virge\Stork\Service\ZMQMessagingService;
use Virge\Stork\Service\WebsocketClientService;
use Virge\Stork\Service\AuthClientService;
use Virge\Stork\Service\PushMessagingService;

$autoloader = require '../../vendor/autoload.php';

$autoloader->add('Virge', '../../');

$zmqServer = 'localhost';
$zmqPort = '5555';

$websocketServers = [
    [
        'host'  =>  'localhost',
        'port'  =>  '5556',
    ]
];

$websocketUrl = 'ws://127.0.0.1:8080/';

Virge::registerService(ZMQMessagingService::class, new ZMQMessagingService($zmqServer, $zmqPort, $websocketServers));
Virge::registerService(WebsocketClientService::class, new WebsocketClientService($websocketUrl , "realm1", "backend", "yeahyeah"));
Virge::registerService(AuthClientService::class, new AuthClientService($websocketUrl , "realm1", "backend", "yeahyeah"));
Virge::registerService(PushMessagingService::class, new PushMessagingService("localhost"));

class MyMessage extends \Virge\Stork\Component\Websocket\Message {
    const MESSAGE_TYPE = 'my_message';
    
    public function getData()
    {
        return [
            'foo' => 'bar'
        ];
    }
}

Stork::authenticator(function($session, &$returnData) {
    
    if($session->ticket === 'testtest') {
        $returnData['authid'] = "1";
        return true;
    }

    return false;
});

//setup topics
Stork::topic('v1', 'test')
    ->verify(function($session, $topic, $feedId, &$reason) {
        return true;
    })
;