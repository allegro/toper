<?php

namespace Toper;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Toper\Exception\ConnectionErrorException;
use Toper\Exception\ServerErrorException;
use \Guzzle\Http\Message\Request as GuzzleRequest;

class Request
{
    const GET = "get";

    const POST = "post";

    const PUT = "put";

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
     * @var array
     */
    private $queryParams = array();

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
     * @throws Exception\ServerErrorException
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
                if ($this->body && $guzzleRequest instanceof EntityEnclosingRequest) {
                    /** @var EntityEnclosingRequest $guzzleRequest */
                    $guzzleRequest->setBody($this->body);
                }

                $this->updateQueryParams($guzzleRequest);

                return new Response($guzzleRequest->send());
            } catch (ClientErrorResponseException $e) {
                return new Response($e->getResponse());
            } catch (ServerErrorResponseException $e) {
                $exception = new ServerErrorException(
                    new Response($e->getResponse()),
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            } catch (CurlException $e) {
                $exception = new ConnectionErrorException($e->getMessage(), $e->getCode(), $e);
            }
        }

        throw $exception;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function addQueryParam($name, $value)
    {
        $this->queryParams[$name] = $value;
    }

    /**
     * @param GuzzleRequest $request
     */
    private function updateQueryParams(GuzzleRequest $request)
    {
        foreach ($this->queryParams as $name => $value) {
            $request->getQuery()->add($name, $value);
        }
    }
}