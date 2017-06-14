<?php
namespace Virge\Stork\Component\RPC;

use Virge\Stork\Service\RPCProviderService;
use Virge\Virge;

abstract class MethodController
{
    abstract public function registerMethods() : array;

    public function setup()
    {
        $this->getRPCProviderService()->addMethods($this->registerMethods());
    }

    public function getRPCProviderService() : RPCProviderService
    {
        return Virge::service(RPCProviderService::class);
    }
}