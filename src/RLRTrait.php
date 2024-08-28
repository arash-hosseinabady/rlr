<?php

namespace Arash\Rlr;

/**
 * @property array $identifier
 * @property int $limit
 * @property int $window
 */
trait RLRTrait
{
    protected $identifier;
    public $limit;
    public $window;
    protected function prepareIdentifier()
    {
        $this->setIp();
        $this->setAgent();
        $this->setUrl();
    }

    /**
     * set client ip in $identifier
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

    /**
     * set client agent in $identifier
     */
    protected function setAgent()
    {
        $this->identifier['agent'] = $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * set request uri in $identifier
     */
    protected function setUrl()
    {
        $this->identifier['url'] = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Generate a unique key for Memcached storage based on the identifier.
     */
    protected function getKey()
    {
        return md5(json_encode($this->identifier));
    }
    public function isRateLimited()
    {
        $currentTime = time();
        $key = $this->getKey();

        // Retrieve current request timestamps from Memcached
        $rateData = $this->getValue($key);

        if (!isset($rateData['count'])) {
            $rateData['count'] = 0;
        }

        if (!isset($rateData['time'])) {
            $rateData['time'] = $currentTime;
        } else if (($rateData['time'] > ($currentTime - $this->window)) and $rateData['count'] >= $this->limit) {
            return true;
        }

        $rateData['count'] += 1;

        $this->setValue($key, $rateData);

        return false;
    }
}