<?php

namespace Toper;

use Guzzle\Http\Client as GuzzleClient;

class GuzzleClientFactory implements GuzzleClientFactoryInterface
{
    /**
     * @var array
     */
    private $guzzleClientOptions = array();

    /**
     * @param array $guzzleClientOptions
     */
    public function __construct(array $guzzleClientOptions = array())
    {
        $this->guzzleClientOptions = $guzzleClientOptions;
    }

    /**
     * @return GuzzleClient
     */
    public function create()
    {
        return new GuzzleClient('', $this->guzzleClientOptions);
    }
}
