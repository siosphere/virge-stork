<?php
namespace Virge\Stork\Component\RPC;

class Method
{
    protected $uri;
    
    protected $options;

    protected $callback;

    public function __construct(string $uri, array $options = [])
    {
        $this->uri = $uri;
        $this->options = $options;
    }

    public function getURI() : string
    {
        return $this->uri;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function callback(callable $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    public function getOptions() : array
    {
        return $this->options;
    }
}