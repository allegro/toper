<?php

namespace Toper;

class StaticHostPoolProvider implements HostPoolProviderInterface
{
    /**
     * @var array
     */
    private $hosts;

    /**
     * @param array $hosts
     */
    public function __construct(array $hosts)
    {
        $this->hosts = $hosts;
    }

    /**
     * @return SimpleHostPool
     */
    public function get()
    {
        return new SimpleHostPool($this->hosts);
    }
}
