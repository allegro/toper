<?php

namespace Toper;

class Clock implements ClockInterface
{
    /**
     * @return int - current timestamp
     */
    public function getTime()
    {
        return time();
    }
}
