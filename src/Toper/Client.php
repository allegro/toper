<?php

namespace Toper;

use \Toper\Http\Client as GuzzleClient;
use Toper\Request;

class Client implements ClientInterface
{
    /**
     * @var HostPoolProviderInterface
     */
    private $hostPoolProvider;

    /**
     * @var GuzzleClientFactoryInterface
     */
    private $guzzleClientFactory;

    /**
     * @param HostPoolProviderInterface $hostPoolProvider
     * @param GuzzleClientFactoryInterface $guzzleClientFactory
     */
    public function __construct(
        HostPoolProviderInterface $hostPoolProvider,
        GuzzleClientFactoryInterface $guzzleClientFactory
    ) {
        $this->hostPoolProvider = $hostPoolProvider;
        $this->guzzleClientFactory = $guzzleClientFactory;
    }

    /**
     * @param string $url
     *
     * @return Request
     */
    public function get($url)
    {
        return new Request(
            $url,
            $this->hostPoolProvider->get(),
            $this->guzzleClientFactory
        );
    }
}
