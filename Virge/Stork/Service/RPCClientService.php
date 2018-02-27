<?php
namespace Virge\Stork\Service;

use Thruway\ClientSession;
use Virge\Stork;
use Virge\Stork\Component\RPC\Method;

/**
 * Websocket Server, used to setup the session
 */
class RPCClientService extends AbstractClientService
{
    protected $callable;
    protected $work;

    public function onOpen(ClientSession $session)
    {

        $this->session = $session;
        //do work in the loop
        $this->work = $this->client->getLoop()->addPeriodicTimer(1, $this->callable);
    }

    public function onClose()
    {
        $this->client->getLoop()->cancelTimer($this->work);
    }

    public function call(string $uri, array $arguments = [])
    {
        $deferred = new \React\Promise\Deferred();

        $this->session->call($uri, [json_encode($arguments)])->then(function($response) use($deferred) {
            $result = json_decode($response[0], true);
            $deferred->resolve($result);
        }, function($err) use($deferred) {
            $deferred->reject($err);
        });

        return $deferred->promise();
    }

    public function do(\Closure $callable)
    {
        $this->callable = \Closure::bind($callable, $this);

        return $this;
    }
}