<?php

namespace App\Core\Contracts\Support;

use Countable;

interface MessageBag extends Countable
{
    /**
     * Determine if messages exist for a given key.
     *
     * @param  string|array $key
     * @return bool
     */
    public function has(string|array $key);

    /**
     * Get all of the messages from the message bag for a given key.
     *
     * @param string $key
     * @return array
     */
    public function get(string $key);

    /**
     * Add a message to the bag.
     *
     * @param string $key
     * @param string $message
     * @return $this
     */
    public function add(string $key, string $message);

    /**
     * Modifies the existing message.
     *
     * @param string $key
     * @param string $message
     * 
     * @return $this
     */
    public function modify(string $key, string $new_message);

    /**
     * Remove a message from the bag.
     *
     * @param  string  $key
     * @return $this
     */
    public function forget(string $key);
    
    /**
     * Merge a new array of messages into the bag.
     *
     * @param  MessageBag|array  $messages
     * @return $this
     */
    public function merge(MessageBag|array $messages);

    /**
     * Determine if the message bag has any messages.
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Get all of the messages for every key in the bag.
     *
     * @return array
     */
    public function getMessages();

    /**
     * Get the messages for the instance.
     *
     * @return MessageBag
     */
    public function getMessageBag();
}
