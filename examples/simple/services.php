<?php
use Virge\Virge;
use Virge\Stork;
use Virge\Stork\Service\ZMQMessagingService;

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

Virge::registerService('virge.stork.service.zmq_messaging', new ZMQMessagingService($zmqServer, $zmqPort, $websocketServers));

class MyMessage extends \Virge\Stork\Component\Websocket\Message {
    const MESSAGE_TYPE = 'my_message';
    
    public function getData()
    {
        return [
            'foo' => 'bar'
        ];
    }
}

Stork::authenticator(function($conn) {
    return $conn->Session->getSecret() == '123';
});

//setup topics
Stork::topic('v1', 'test')
    ->verify(function($session, $topic, $feedId, &$reason) {
        return true;
    })
;