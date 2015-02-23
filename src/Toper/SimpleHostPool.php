<?php

namespace Toper;

use Toper\Exception\NextHostException;

class SimpleHostPool implements HostPoolInterface
{
    /**
     * @var array
     */
    private $hosts;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var string
     */
    private $name;

    /**
     * @param array  $hosts
     * @param string $name
     */
    public function __construct(array $hosts, $name = "default")
    {
        $this->hosts = $hosts;
        $this->name = $name;
    }

    /**
     * @return string
     *
     * @throws NextHostException
     */
    public function getNext()
    {
        if (!isset($this->hosts[$this->index])) {
            throw new NextHostException();
        }

        return $this->hosts[$this->index++];
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return isset($this->hosts[$this->index]);
    }

    /**
     * @return string[]
     */
    public function toArray()
    {
        return $this->hosts;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
