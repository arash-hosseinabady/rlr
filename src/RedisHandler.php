<?php

namespace arash\rlr;

use Redis;
use Exception;
use RedisException;

/**
 * @property Redis $redis
 * @property string $host
 * @property int $port
 */
class RedisHandler extends RLRService implements RLEInterface
{
    use RLRTrait;

    public $redis;
    private $host = '127.0.0.1';
    private $port = 6379;

    public function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public function connect()
    {
        if (!$this->redis) {
            $this->redis = new Redis([
                'host' => getenv('REDIS_HOST') ?: $this->host,
                'port' => getenv('REDIS_PORT') ?: $this->port,
            ]);
        }
        try {
            $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_JSON);
        } catch (RedisException $e) {
            throw new Exception('Redis connection failed: ' . $e->getMessage());
        }
    }

    public function getValue($key)
    {
        return $this->redis->get($key) ?: $this->identifier;
    }

    public function setValue($key, $value)
    {
        $this->redis->setex($key, $this->window, $value);
    }
}