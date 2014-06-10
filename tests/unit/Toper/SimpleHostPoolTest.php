<?php

namespace Toper;

class SimpleHostPoolTest extends \PHPUnit_Framework_TestCase
{
    const HOST_1 = "http://123.232.232.123";
    const HOST_2 = "http://222.232.232.123";

    /**
     * @test
     */
    public function shouldGetNext()
    {
        $hostPool = $this->createInstance(
            array(self::HOST_1, self::HOST_2)
        );

        $this->assertEquals(self::HOST_1, $hostPool->getNext());
        $this->assertEquals(self::HOST_2, $hostPool->getNext());
    }

    /**
     * @test
     */
    public function shouldHaveNext()
    {
        $hostPool = $this->createInstance(
            array(self::HOST_1, self::HOST_2)
        );

        $this->assertTrue($hostPool->hasNext());
        $hostPool->getNext();
        $this->assertTrue($hostPool->hasNext());
    }

    /**
     * @test
     */
    public function shouldNotHaveNext()
    {
        $hostPool = $this->createInstance(
            array(self::HOST_1)
        );

        $hostPool->getNext();
        $this->assertFalse($hostPool->hasNext());
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfThereIsNoNextHost()
    {
        $hostPool = $this->createInstance(
            array(self::HOST_1)
        );

        $hostPool->getNext();

        $this->setExpectedException('Toper\Exception\NextHostException');
        $hostPool->getNext();
    }

    /**
     * @param array $hosts
     *
     * @return SimpleHostPool
     */
    private function createInstance($hosts)
    {
        return new SimpleHostPool($hosts);
    }
}
