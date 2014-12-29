<?php
namespace Toper;

interface ClockInterface
{
    /**
     * @return int - current timestamp
     */
    public function getTime();
}
