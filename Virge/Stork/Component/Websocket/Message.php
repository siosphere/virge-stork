<?php

namespace Virge\Stork\Component\Websocket;

/**
 * A message to broadcast through the websockets.
 * getData should return a json_encodable object/array
 * 
 * Your message should also supply a message type, messages sent through will 
 * appear in:
 * {
 *     type: 'your_message_type',
 *     data: {}
 * }
 */
abstract class Message
{
    /**
     * Unique message type to identify the type of message on the frontend
     */
    const MESSAGE_TYPE = '';

    protected $timestamp;

    /**
     * Return the data this message contains
     */
    public abstract function getData();

    public function __constructor()
    {
        $this->timestamp = new \DateTime();
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getType()
    {
        return $this::MESSAGE_TYPE;
    }
}