<?php

namespace Toper;

class StaticHostPoolProviderTest extends \PHPUnit_Framework_TestCase
{
    const HOST_1 = "http://123.123.123.123";
    const HOST_2 = "http://234.123.123.123";

    /**
     * @test
     */
    public function shouldCreateNewHostPool()
    {
        $hosts = array(self::HOST_1, self::HOST_2);
        $instance = new StaticHostPoolProvider($hosts);

        $hostPool = $instance->get();

        $this->assertHostsArrays($hosts, $hostPool->toArray());
    }

    /**
     * @param string[] $hosts1
     * @param string[] $hosts2
     */
    private function assertHostsArrays($hosts1, $hosts2)
    {
        sort($hosts1);
        sort($hosts2);

        $this->assertEquals($hosts1, $hosts2);
    }
}
