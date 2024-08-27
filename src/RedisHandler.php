<?php

namespace arash\rlr;

/**
 * @property \Redis $redis
 */
class RedisHandler extends RLRService implements RLEInterface
{
    public $redis;
    private $host = '127.0.0.1';
    private $port = 6379;

    public function __construct(private $limit = 10, private $window = 20)
    {
        $this->connect();
        $this->prepareIdentifier();
    }

    public function connect()
    {
        $this->redis = new \Redis([
            'host' => $this->host,
            'port' => $this->port,
        ]);
        $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_JSON);
    }

    public function isRateLimited()
    {
        $currentTime = time();
        $key = $this->getKey();

        // Retrieve current request timestamps from Redis
        $rateData = $this->redis->get($key) ?: $this->identifier;

        if (!isset($rateData['count'])) {
            $rateData['count'] = 0;
        }

        if (!isset($rateData['time'])) {
            $rateData['time'] = $currentTime;
        } else if (($rateData['time'] > ($currentTime - $this->window)) and $rateData['count'] >= $this->limit) {
            return true;
        }

        $rateData['count'] += 1;

        // Save the updated data back to redis
        $this->redis->setex($key, $this->window, $rateData);

        return false;
    }
}