<?php

namespace Toper;


use Guzzle\Http\Client as GuzzleClient;

class GuzzleClientFactory implements GuzzleClientFactoryInterface
{
    private $guzzleClientOptions = array();

    public function __construct(array $guzzleClientOptions = array())
    {
        $this->guzzleClientOptions = $guzzleClientOptions;
    }

    public function create()
    {
        return new GuzzleClient('', $this->guzzleClientOptions);
    }
}