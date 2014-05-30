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
     * @param array $binds
     * @return Request
     */
    public function get($url, array $binds = array())
    {
        return new Request(
            Request::GET,
            $url,
            $binds,
            $this->hostPoolProvider->get(),
            $this->guzzleClientFactory->create()
        );
    }

    /**
     * @param string $url
     *
     * @param array $binds
     * @return Request
     */
    public function post($url, array $binds = array())
    {
        return new Request(
            Request::POST,
            $url,
            $binds,
            $this->hostPoolProvider->get(),
            $this->guzzleClientFactory->create()
        );
    }


    /**
     * @param string $url
     *
     * @param array $binds
     * @return Request
     */
    public function put($url, array $binds = array())
    {
        return new Request(
            Request::PUT,
            $url,
            $binds,
            $this->hostPoolProvider->get(),
            $this->guzzleClientFactory->create()
        );
    }
}
