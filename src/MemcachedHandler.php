<?php

namespace arash\rlr;

use Memcached;

/**
 * @property Memcached $memcached
 * @property string $host
 * @property int $port
 */
class MemcachedHandler extends RLRService implements RLRInterface
{
    use RLRTrait;

    public $memcached;
    private $host = '127.0.0.1';
    private $port = 11211;

    public function __construct()
    {
        $this->connect();

        $this->prepareIdentifier();
    }

    public function connect()
    {
        if (!$this->memcached) {
            $this->memcached = new Memcached();
            $this->memcached->addServer(
                getenv('MEMCACHED_HOST') ?: $this->host,
                getenv('MEMCACHED_PORT') ?: $this->port
            );
        }
        if (!$this->memcached->getStats()) {
            throw new \Exception('Memcached connection failed');
        }
    }

    public function getValue($key)
    {
        return $this->memcached->get($key) ?: $this->identifier;
    }

    public function setValue($key, $value)
    {
        $this->memcached->set($key, $value, $this->window);
    }
}