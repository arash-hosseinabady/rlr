<?php

namespace arash\Rlr;

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
    public $banList;

    protected function prepareIdentifier()
    {
        if ($this->banList && !is_array($this->banList)) {
            $this->banList = explode(',', $this->banList);
        }
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

        $rateData = $this->getValue($key);

        if (in_array($rateData['ip']['true_client_ip'], $this->banList)) {
            return true;
        }

        if (!isset($rateData['allowance'])) {
            $rateData['allowance'] = 0;
        }

        if (!isset($rateData['time'])) {
            $rateData['time'] = 0;
        }
        $rateData['allowance'] += (int)(($currentTime - $rateData['time']) * $this->limit / $this->window);
        $rateData['allowance'] = min($this->limit, $rateData['allowance']);

        if ($rateData['allowance'] < 1) {
            $rateData['allowance'] = 0;
            $rateData['time'] = $currentTime;
            $this->setValue($key, $rateData);
            return true;
        }

        $rateData['time'] = $currentTime;

        $rateData['allowance'] -= 1;

        $this->setValue($key, $rateData);

        return false;
    }
}