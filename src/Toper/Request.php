<?php

namespace Toper;

use Guzzle\Http\Client as GuzzleClient;

class Request
{
    /**
     * @var HostPoolInterface
     */
    private $hostPool;

    /**
     * @var string
     */
    private $url;

    /**
     * @var GuzzleClientFactoryInterface
     */
    private $guzzleClientFactory;

    /**
     * @param string $url
     * @param HostPoolInterface $hostPool
     * @param GuzzleClientFactoryInterface $guzzleClientFactory
     */
    public function __construct(
        $url,
        HostPoolInterface $hostPool,
        GuzzleClientFactoryInterface $guzzleClientFactory
    ) {
        $this->hostPool = $hostPool;
        $this->url = $url;
        $this->guzzleClientFactory = $guzzleClientFactory;
    }

    /**
     * @return Response
     */
    public function send()
    {
        while ($this->hostPool->hasNext()) {
            $host = $this->hostPool->getNext();
            $guzzleClient = $this->guzzleClientFactory->create(
                $host,
                array()
            );

            $guzzleRequest = $guzzleClient->get($this->url);

            return new Response($guzzleRequest->send());
        }

        return null;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return HostPoolInterface
     */
    public function getHostPool()
    {
        return $this->hostPool;
    }
}