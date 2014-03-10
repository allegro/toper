<?php

namespace Toper;

use Guzzle\Http\Client as GuzzleClient;

class GuzzleClientFactoryStub implements GuzzleClientFactoryInterface
{
    /**
     * @var GuzzleClient[]
     */
    private $guzzleClients;

    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @param array $guzzleClients
     */
    public function __construct(array $guzzleClients)
    {
        $this->guzzleClients = $guzzleClients;
    }

    /**
     * @param string $host
     * @param array $options
     *
     * @return GuzzleClient
     */
    public function create($host, array $options)
    {
        return $this->guzzleClients[$this->counter++];
    }
} 