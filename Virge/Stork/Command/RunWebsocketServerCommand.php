<?php

namespace Virge\Stork\Command;

use Virge\Cli;
use Virge\Stork\Service\PushMessagingService;
use Virge\Stork\Service\WebsocketServer;

/**
 * listens for incoming ZMQ messages, and then broadcasts those out
 */
class RunWebsocketServerCommand
{
    const COMMAND = 'virge:stork:run_websocket_server';
    
    public function run()
    {
        Cli::output("starting server");
        $loop   = React\EventLoop\Factory::create();
        $app = new PushMessagingService;

        $context = new React\ZMQ\Context($loop);
        $sub = $context->getSocket(ZMQ::SOCKET_SUB);
        $sub->bind("tcp://*:5556");
        $sub->subscribe("virge:stork");
        $sub->on('message', [$app, 'onReceiveZMQMessage']);

        $server = new React\Socket\Server($loop);
        $server->listen(8080, '0.0.0.0');
        $webServer = new Ratchet\Server\IoServer(
            new Ratchet\Http\HttpServer(
                new Ratchet\WebSocket\WsServer(
                    new WebsocketServer(
                        $app
                    )
                )
            ),
            $server
        );

        $loop->run();
    }
}