<?php

namespace Virge\Stork\Component\Websocket;

/**
 * A websocket topic that we can subscribe to. Each topic should be in the:
 * version.feedName.feedId format,
 * All authentication is based on version.feedName, and will pass the feedId
 * into the authenticator.
 * 
 * Valid topics can be created via Stork::topic('version', 'feedName')
 * ->verify('some_verifier')
 * ->verify(function($session, $topic, $feedId, &$reason){
 *     return true; //if returning false, set the reason that will be returned
 * });
 */
class Topic
{
    /**
     * @var string
     */
    protected $version;
    
    /**
     * @var string 
     */
    protected $feedName;

    /**
     * @var array
     */
    protected $verifiers;
    
    /**
     * @param string $version
     * @param string $feedName
     * @param mixed $feedId
     */
    public function __construct($version, $feedName)
    {
        $this->version = $version;
        $this->feedName = $feedName;
        $this->verifiers = [];
    }
    
    /**
     * Add a verifier to this topic. All verifiers must return true for the
     * user to be able to successfully subscribe to the topic.
     * @param callable|string $verifier
     * @return \Virge\Stork\Component\Websocket\Topic
     */
    public function verify($verifier)
    {
        $this->verifiers[] = $verifier;
        return $this;
    }
    
    /**
     * Get this topics verifiers
     * @return array
     */
    public function getVerifiers()
    {
        return $this->verifiers;
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s.%s', $this->version, $this->feedName);
    }
}