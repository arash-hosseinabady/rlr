<?php

namespace app\components;

use arash\rlr\RLRService;
use Yii;
use yii\base\ActionFilter;
use yii\web\TooManyRequestsHttpException;

class RLRFilter extends ActionFilter
{
    /**
     * @var RLRService delegate RateLimiter component
     */
    private $component;
    private $message;

    /**
     * Constructor.
     *
     * @param array $config name-value pairs that will be used to initialize the
     *     object properties.
     */
    public function __construct($config = [])
    {
        $thisConfig = $this->preInit($config);
        $componentConfig = $this->preInitComponent($config);

        $this->component = Yii::createObject(RLRService::class, $componentConfig);

        parent::__construct($thisConfig);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($this->component->handler->isRateLimited()) {
            throw new TooManyRequestsHttpException($this->message ?: 'There were too many requests');
        }

        return true;
    }

    private function preInit($config)
    {
        $thisConfig = [];
        foreach (['except', 'only', 'owner'] as $key) {
            if (isset($config[$key])) {
                $thisConfig[$key] = $config[$key];
            }
        }

        return $thisConfig;
    }

    private function preInitComponent($config)
    {
        $params = [];
        if (isset($config['handlerClass'])) {
            $params['handlerClass'] = $config['handlerClass'];
        }
        if (isset($config['limit'])) {
            $params['limit'] = $config['limit'];
        }
        if (isset($config['window'])) {
            $params['window'] = $config['window'];
        }
        if (isset($config['banList'])) {
            $params['banList'] = $config['banList'];
        }
        if (isset($config['message'])) {
            $this->message = $config['message'];
        }

        return $params;
    }
}
