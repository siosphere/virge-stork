<?php
namespace Virge\Stork\Event;

use Thruway\ClientSession;
use Virge\Event\Component\Event;

class SessionJoinEvent extends Event
{

    protected $authId;
    protected $authProvider;
    protected $authRole;
    protected $sessionId;
    protected $authMethod;

    public function __construct($authId, $authProvider, $authRole, $sessionId, $authMethod)
    {
        $this->authId = $authId;
        $this->authProvider = $authProvider;
        $this->authRole = $authRole;
        $this->sessionId = $sessionId;
        $this->authMethod = $authMethod;
    }

    public function getAuthId()
    {
        return $this->authId;
    }

    public function getAuthProvider()
    {
        return $this->authProvider;
    }

    public function getAuthRole()
    {
        return $this->authRole;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function getAuthMethod()
    {
        return $this->authMethod;
    }
}