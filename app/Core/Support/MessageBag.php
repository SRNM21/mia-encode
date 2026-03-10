<?php

namespace App\Core\Support;
   
use App\Core\Contracts\Support\MessageBag as MessageBagContract;

class MessageBag implements MessageBagContract
{
    /**
     * All of the registered messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Create a new message bag instance.
     *
     * @param array $messages
     * @return void
     */
    public function __construct($messages = [])
    {
        foreach ($messages as $key => $value) 
        {
            $this->add($key, array_unique((array) $value));
        }
    }

    /**
     * Determine if messages exist for a given key.
     *
     * @param  string|array $key
     * @return bool
     */
    public function has($key)
    {
        if ($this->isEmpty()) 
        {
            return false;
        }
        
        return $this->get($key) !== [];
    }

    /**
     * Get all of the messages from the message bag for a given key.
     *
     * @param string $key
     * @return array
     */
    public function get($key)
    {
        return get_nested_value($this->getMessages(), $key, []);
    }

    /**
     * Add a message to the bag.
     *
     * @param string $key
     * @param string $message
     * @return $this
     */
    public function add($key, $message)
    {
        if ($this->isUnique($key, $message)) 
        {
            $this->messages[$key][] = $message;
        }

        return $this;
    }

    /**
     * Determine if a key and message combination already exists.
     *
     * @param  string  $key
     * @param  string  $message
     * @return bool
     */
    protected function isUnique($key, $message)
    {
        $messages = (array) $this->getMessages();

        return ! isset($messages[$key]) || ! in_array($message, $messages[$key]);
    }

    /**
     * Modifies the existing message.
     *
     * @param string $key
     * @param string $message
     * 
     * @return $this
     */
    public function modify($key, $new_message)
    {
        if (! $this->has($key))
        {
            throw new \RuntimeException("The key '$key' does not exist in the bag.");
        }

        $this->forget($key);
        $this->add($key, $new_message);
    }

    /**
     * Remove a message from the bag.
     *
     * @param  string  $key
     * @return $this
     */
    public function forget($key)
    {
        unset($this->messages[$key]);

        return $this;
    }
    
    /**
     * Merge a new array of messages into the bag.
     *
     * @param  MessageBag|array  $messages
     * @return $this
     */
    public function merge($messages)
    {
        if ($messages instanceof MessageBag) 
        {
            $messages = $messages->getMessageBag()->getMessages();
        }

        $this->messages = array_merge_recursive($this->messages, $messages);

        return $this;
    }

    /**
     * Determine if the message bag has any messages.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->count() > 0;
    }

    /**
     * Get all of the messages for every key in the bag.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Get the messages for the instance.
     *
     * @return \App\Core\Support\MessageBag
     */
    public function getMessageBag()
    {
        return $this;
    }

    /**
     * Get the number of messages in the message bag.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->getMessages(), COUNT_RECURSIVE) - count($this->getMessages());
    }
}