<?php

namespace arash\rlr;

use Exception;

/**
 * @property RedisHandler|MemcachedHandler $handler
 * @property int $handlerClass
 */
class RLRService
{
    const HANDLER_MEMCACHED = 1;
    const HANDLER_REDIS = 2;

    public $handler;
    private $handlerClass;

    /**
     * @throws Exception
     */
    public function __construct($handlerClass = self::HANDLER_MEMCACHED, $limit = 10, $window = 60)
    {
        $this->handlerClass = $handlerClass;
        if ($this->handlerClass == self::HANDLER_REDIS) {
            $this->handler = new RedisHandler();
        } elseif ($this->handlerClass == self::HANDLER_MEMCACHED) {
            $this->handler = new MemcachedHandler();
        }

        $this->handler->window = $window;
        $this->handler->limit = $limit;
    }
}
