<?php
namespace Toper;

class ClockStub implements ClockInterface
{
    /**
     * @var int
     */
    private $timestamp;

    /**
     * @param int $timestamp
     */
    public function __construct($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return int - current timestamp
     */
    public function getTime()
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setTime($timestamp)
    {
        $this->timestamp = $timestamp;
    }
}
