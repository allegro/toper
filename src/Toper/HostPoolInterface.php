<?php

namespace Toper;

interface HostPoolInterface
{
    /**
     * @return string
     */
    public function getNext();

    /**
     * @return boolean
     */
    public function hasNext();

    /**
     * @return string[]
     */
    public function toArray();

    /**
     * @return string
     */
    public function getName();
}
