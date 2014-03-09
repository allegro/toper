<?php

namespace Toper;

class StaticHostPoolProviderTest extends \PHPUnit_Framework_TestCase
{
    const HOST_1 = "http://123:123:123:123";
    const HOST_2 = "http://234:123:123:123";

    /**
     * @test
     */
    public function shouldCreateNewHostPool()
    {
        $hosts = array(self::HOST_1, self::HOST_2);
        $instance = new StaticHostPoolProvider($hosts);

        $hostPool = $instance->get();
        $this->assertEquals(self::HOST_1, $hostPool->getNext());
        $this->assertEquals(self::HOST_2, $hostPool->getNext());
    }
}
 