<?php

namespace Toper;

use Guzzle\Http\ClientInterface as GuzzleClientInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Guzzle\Http\Message\Request as GuzzleRequest;
use Toper\Exception\ConnectionErrorException;
use Toper\Exception\EmptyHostPoolException;
use Toper\Exception\ServerErrorException;

class Request
{
    const GET = "get";
    const POST = "post";
    const PUT = "put";
    const DELETE = "delete";

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
     * @var GuzzleClientInterface
     */
    private $guzzleClient;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $queryParams = array();

    /**
     * @var array
     */
    private $binds;

    /**
     * @param string                $method
     * @param string                $url
     * @param array                 $binds
     * @param HostPoolInterface     $hostPool
     * @param GuzzleClientInterface $guzzleClient
     */
    public function __construct(
        $method,
        $url,
        array $binds,
        HostPoolInterface $hostPool,
        GuzzleClientInterface $guzzleClient
    ) {
        $this->method = $method;
        $this->hostPool = $hostPool;
        $this->url = $url;
        $this->guzzleClient = $guzzleClient;
        $this->binds = $binds;
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
     * @return Response
     * @throws ConnectionErrorException
     * @throws EmptyHostPoolException
     * @throws ServerErrorException
     */
    public function send()
    {
        $exception = null;

        while ($this->hostPool->hasNext()) {
            try {
                $this->guzzleClient->setBaseUrl($this->hostPool->getNext());

                /** @var GuzzleRequest $guzzleRequest */
                $guzzleRequest = $this->guzzleClient->{$this->method}(
                    array($this->url, $this->binds)
                );

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

        if ($exception == null) {
            throw new EmptyHostPoolException(sprintf("Empty host pool: %s", $this->hostPool->getName()));
        }

        throw $exception;
    }

    /**
     * @return array
     */
    public function getBinds()
    {
        return $this->binds;
    }

    /**
     * @param string $name
     * @param mixed  $value
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
