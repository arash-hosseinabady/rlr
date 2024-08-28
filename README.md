## Rate limit request

### Requirement
- minimum php 5.4 
- redis
- memcached
- For connect to Redis define and set ``REDIS_HOST`` and ``REDIS_PORT`` environment variable.
  Default value for host is ``127.0.0.1`` and port is ``6379``.
- For connect to Memcached define and set ``MEMCACHED_HOST`` and ``MEMCACHED_PORT`` environment variable. Default value for host is ``127.0.0.1`` and port is ``11211``.

### Installation

``composer require arash/rlr``

### Usage

```php
$limiter = new RLRService(RLRService::HANDLER_REDIS);
//default is 10 request per 60 seconds.
$limiter->handler->limit = 5;
$limiter->handler->window = 60;
if ($limiter->handler->isRateLimited()) {
    echo 'Rate limit exceeded. Please try again later.';
} else {
    echo 'Request successful.';
}
```