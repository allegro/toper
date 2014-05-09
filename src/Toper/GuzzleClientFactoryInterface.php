<?php

namespace Toper;

use Guzzle\Http\Client as GuzzleClient;

interface GuzzleClientFactoryInterface
{
    /**
     * @return GuzzleClient
     */
    public function create();
}