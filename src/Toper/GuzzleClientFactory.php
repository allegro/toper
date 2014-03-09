<?php

namespace Toper;


use Guzzle\Http\Client as GuzzleClient;

class GuzzleClientFactory implements GuzzleClientFactoryInterface
{
    public function create($host, array $options)
    {
        return new GuzzleClient($host, $options);
    }
} 