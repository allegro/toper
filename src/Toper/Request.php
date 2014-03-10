<?php

namespace Toper;

use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Toper\Exception\ServerException;

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
     * @throws Exception\ServerException
     *
     * @return Response
     */
    public function send()
    {
        $exception = null;
        while ($this->hostPool->hasNext()) {
            try {
                $host = $this->hostPool->getNext();
                $guzzleClient = $this->guzzleClientFactory->create(
                    $host,
                    array()
                );

                $guzzleRequest = $guzzleClient->get($this->url);

                return new Response($guzzleRequest->send());
            } catch (ServerErrorResponseException $e) {
                $exception = new ServerException($e->getMessage(), $e->getCode(), $e);
            }
        }

        throw $exception;
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