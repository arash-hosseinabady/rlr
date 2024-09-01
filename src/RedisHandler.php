<?php

namespace arash\rlr;

use Redis;
use Exception;

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
    private $port = 63791;

    public function __construct()
    {
        $this->connect();

        $this->prepareIdentifier();
    }

    /**
     * @throws Exception
     */
    public function connect()
    {
        try {
            if (!$this->redis) {
                $this->redis = new Redis([
                    'host' => getenv('REDIS_HOST') ?: $this->host,
                    'port' => getenv('REDIS_PORT') ?: $this->port,
                ]);
            }
            $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_JSON);
            $this->redis->ping();
        } catch (Exception $e) {
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