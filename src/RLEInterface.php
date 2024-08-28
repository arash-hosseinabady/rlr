<?php

namespace arash\rlr;

interface RLEInterface
{
    public function connect();
    public function isRateLimited();

    public function getValue($key);
    public function setValue($key, $value);
}