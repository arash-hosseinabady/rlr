<?php

namespace arash\rlr;
class RLRService
{
    const HANDLER_MEMCACHED = 1;
    const HANDLER_REDIS = 2;

    protected $identifier;

    public $handler;

    public function __construct(private $handlerClass = self::HANDLER_MEMCACHED)
    {
        $this->handler = match ($this->handlerClass) {
            self::HANDLER_MEMCACHED => new MemcachedHandler(),
            self::HANDLER_REDIS => new RedisHandler(),
        };
    }

    protected function prepareIdentifier()
    {
        $this->setIp();
        $this->setAgent();
        $this->setUrl();
    }

    /**
     * get client ip
     *
     */
    protected function setIp()
    {
        $headers = getallheaders();
        $ip = [
            'true_client_ip' => '',
            'http_x_forwarded_for' => '',
        ];

        if (isset($headers['HTTP_TRUE_CLIENT_IP'])) {
            $ip['http_true_client_ip'] = $headers['HTTP_TRUE_CLIENT_IP'];
        }
        if (isset($headers['HTTP_X_FORWARDED_FOR'])) {
            $ip['http_x_forwarded_for'] = $headers['HTTP_X_FORWARDED_FOR'];
        }

        $this->identifier['ip'] = $ip;
    }

    protected function setAgent()
    {
        $this->identifier['agent'] = $_SERVER['HTTP_USER_AGENT'];
    }

    protected function setUrl()
    {
        $this->identifier['url'] = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Generate a unique key for Memcached storage based on the identifier.
     *
     * @return string
     */
    protected function getKey()
    {
        return md5(json_encode($this->identifier));
    }
}

?>