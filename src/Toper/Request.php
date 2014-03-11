<?php

namespace Toper;

use Guzzle\Http\Exception\ServerErrorResponseException;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Toper\Exception\ServerException;
use \Guzzle\Http\Message\Request as GuzzleRequest;

class Request
{
    const GET = "get";

    const POST = "post";

    /**
     * @var HostPoolInterface
     */
    private $hostPool;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $method;

    /**
     * @var GuzzleClientFactoryInterface
     */
    private $guzzleClientFactory;

    /**
     * @var string
     */
    private $body;

    /**
     * @param string $method
     * @param string $url
     * @param HostPoolInterface $hostPool
     * @param GuzzleClientFactoryInterface $guzzleClientFactory
     */
    public function __construct(
        $method,
        $url,
        HostPoolInterface $hostPool,
        GuzzleClientFactoryInterface $guzzleClientFactory
    ) {
        $this->method = $method;
        $this->hostPool = $hostPool;
        $this->url = $url;
        $this->guzzleClientFactory = $guzzleClientFactory;
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

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
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

                /** @var GuzzleRequest $guzzleRequest */
                $guzzleRequest = $guzzleClient->{$this->method}($this->url);
                //TODO write unit tests
                if ($this->body && $guzzleRequest instanceof EntityEnclosingRequest) {
                    /** @var EntityEnclosingRequest $guzzleRequest */
                    $guzzleRequest->setBody($this->body);
                }

                return new Response($guzzleRequest->send());
            } catch (ServerErrorResponseException $e) {
                $exception = new ServerException(
                    new Response($e->getResponse()),
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
        }

        throw $exception;
    }
}