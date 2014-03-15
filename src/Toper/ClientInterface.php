<?php
namespace Toper;

interface ClientInterface
{
    /**
     * @param string $url
     * @param array $binds
     *
     * @return Request
     */
    public function get($url, array $binds = array());


    /**
     * @param string $url
     * @param array $binds
     *
     * @return Request
     */
    public function post($url, array $binds = array());

    /**
     * @param string $url
     * @param array $binds
     *
     * @return Request
     */
    public function put($url, array $binds = array());
}
