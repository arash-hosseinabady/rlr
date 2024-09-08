## Rate limit request

### Requirement
- minimum php 5.4
- redis
- memcached
- For connect to Redis define and set ``REDIS_HOST`` and ``REDIS_PORT`` environment variable.
  Default value for host is ``127.0.0.1`` and port is ``6379``.
- For connect to Memcached define and set ``MEMCACHED_HOST`` and ``MEMCACHED_PORT`` environment variable. Default value for host is ``127.0.0.1`` and port is ``11211``.

### Installation

- with composer: ``composer require arash/rlr``
- manually:
  - yii:
    - copy this package to the desired directory
    - set blow config in `aliases` section
      ``'@arash/rlr' => __DIR__ . 'pathToDirectory/rlr/src',``
  - laravel:
    - copy this package to `app/Services`
    - add `"arash\\rlr\\": "app/Services/rlr/src"` in `psr-4` of `autoload` section
    - run `composer dumpautoload`

### Usage

use `RLRService` before the request arrive to action.
```php
use arash\rlr\RLRService;

//default handler is memcache
$limiter = new RLRService(RLRService::HANDLER_REDIS);

//default is 10 request per 60 seconds.
//count of request per window
$limiter->handler->limit = 5;
//time window
$limiter->handler->window = 60;

//list of IPs that you want banned
$limiter->handler->banList = [];

if ($limiter->handler->isRateLimited()) {
    echo 'Rate limit exceeded. Please try again later.';
} else {
    echo 'Request successful.';
}
```

#### Yii

Copy `RLRFilter.php` in `app\components` directory and add blow code in `behavior` of your component.
```php
$behaviors['rlr'] = [
    'class' => 'app\components\RLRFilter',
    'only' => ['array of actions'],
    'except' => ['array of actions'],
    'handlerClass' => RLRService::HANDLER_REDIS, //default handler is memcache
    'limit' => 3, //count of request per window
    'window' => 15, //time window
    'message' => 'your message',
    'banList' => 'list of IPs in array or string that separate with comma'
];
```