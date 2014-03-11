<?php

namespace Toper;

class GuzzleClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    const BASE_URL = "http://123.123.123.123";

    /**
     * @var array
     */
    private $options = array(
        'timeout' => 12
    );

    /**
     * @test
     */
    public function shouldCreateClient()
    {
        $factory = new GuzzleClientFactory($this->options);

        $client = $factory->create(self::BASE_URL);

        $this->assertEquals(self::BASE_URL, $client->getBaseUrl());
        $this->assertEquals($this->options['timeout'], $client->getConfig('timeout'));
    }
}
 