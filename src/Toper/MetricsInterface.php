<?php

namespace Toper;

interface MetricsInterface
{
    public function increment($method, $url);
}
