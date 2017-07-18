<?php
namespace Virge\Stork\Event;

use Thruway\ClientSession;
use Virge\Event\Component\Event;

class SessionLeaveEvent extends Event
{

    protected $sessionId;
    protected $authMethod;

    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }
}