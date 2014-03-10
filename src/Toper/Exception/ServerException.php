<?php

namespace Toper\Exception;


use Toper\Response;

class ServerException extends \Exception
{
    private $response;

    /**
     * @param Response $response
     * @param int $message
     * @param int $code
     * @param $previous
     */
    public function __construct(Response $response, $message, $code, $previous)
    {
        parent::__construct($message, $code, $previous);

        $this->response = $response;
    }
} 