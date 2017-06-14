<?php
namespace Virge\Stork\Service;

use Thruway\ClientSession;
use Virge\Stork;
use Virge\Stork\Component\RPC\Method;

/**
 * Websocket Server, used to setup the session
 */
class RPCProviderService extends AbstractClientService
{
    protected $methods = [];

    public function onOpen(ClientSession $session)
    {
        foreach($this->methods as $method)
        {
            $session->register($method->getURI(), $this->wrapRPCRequest($method), $method->getOptions());
        }
    }

    public function wrapRPCRequest(Method $method) : \Closure
    {
        return function(array $arguments) use($method) {
            $data = $arguments[0];
            if($data) {
                $data = json_decode($data, true);
            }

            if(!$data) {
                $data = [];
            }

            return json_encode(call_user_func($method->getCallback(), $data));
        };
    }

    public function onClose()
    {
        
    }

    public function addMethods(array $methods)
    {
        $this->methods += $methods;

        return $this;
    }
}