<?php

namespace arash\rlr;

interface RLEInterface
{
    public function connect();
    public function isRateLimited();
}