<?php

namespace arash\rlr;

use Memcached;

/**
 * @property \Memcached $memcached
 */
class MemcachedHandler extends RLRService implements RLEInterface
{
    public $memcached;
    private $hmemcachHost = '127.0.0.1';
    private $port = '11211';

    public function __construct(private $limit = 10, private $window = 20)
    {
        $this->connect();
        $this->prepareIdentifier();
    }

    public function connect()
    {
        $this->memcached = new \Memcached();
        $this->memcached->addServer($this->hmemcachHost, $this->port);
    }

    public function isRateLimited()
    {
        $currentTime = time();
        $key = $this->getKey();

        // Retrieve current request timestamps from Memcached
        $rateData = $this->memcached->get($key) ?: $this->identifier;

        if (!isset($rateData['count'])) {
            $rateData['count'] = 0;
        }

        if (!isset($rateData['time'])) {
            $rateData['time'] = $currentTime;
        } else if (($rateData['time'] > ($currentTime - $this->window)) and $rateData['count'] >= $this->limit) {
            return true;
        }

        $rateData['count'] += 1;

        // Save the updated data back to Memcached
        $this->memcached->set($key, $rateData, $this->window);

        return false;
    }
}