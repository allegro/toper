<?php
namespace Toper;

interface ClientInterface
{
    /**
     * @param string $url
     *
     * @return Request
     */
    public function get($url);


    /**
     * @param string $url
     *
     * @return Request
     */
    public function post($url);
}
