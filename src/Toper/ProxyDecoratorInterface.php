<?php

namespace Toper;

use Guzzle\Http\Message\Request as GuzzleRequest;

interface ProxyDecoratorInterface
{
    /**
     * @return string
     */
    public function getBaseUrl();

    /**
     * @param GuzzleRequest $request
     * @return string
     */
    public function decorate(GuzzleRequest $request);
}
