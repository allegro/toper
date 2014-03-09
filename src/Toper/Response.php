<?php

namespace Toper;

use Guzzle\Http\Message\Response as GuzzleResponse;

class Response
{
    /**
     * @var GuzzleResponse
     */
    private $guzzleResponse;

    public function __construct(GuzzleResponse $response)
    {
        $this->guzzleResponse = $response;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->guzzleResponse->getStatusCode();
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->guzzleResponse->getBody(true);
    }
} 