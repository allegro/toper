<?php

namespace Toper;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HostPoolProviderInterface
     */
    private $hostPoolProvider;

    /**
     * @var GuzzleClientFactoryInterface
     */
    private $guzzleClientFactory;

    /**
     * @var HostPoolInterface
     */
    private $hostPool;

    public function setUp()
    {
        $this->hostPoolProvider = $this->createHostPoolProviderMock();
        $this->hostPool = $this->createHostPoolMock();

        $this->hostPoolProvider->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->hostPool));

        $this->guzzleClientFactory = $this->createGuzzleClientFactoryMock();
    }

    /**
     * @test
     */
    public function shouldGetCreateRequest()
    {
        $url = "/test";
        $client = $this->createClient();
        $request = $client->get($url);

        $this->assertEquals($url, $request->getUrl());
        $this->assertEquals($this->hostPool, $request->getHostPool());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | HostPoolProviderInterface
     */
    private function createHostPoolProviderMock()
    {
        return $this->getMockBuilder('Toper\HostPoolProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function createClient()
    {
        return new Client($this->hostPoolProvider, $this->guzzleClientFactory);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | HostPoolInterface
     */
    private function createHostPoolMock()
    {
        return $this->getMockBuilder('Toper\HostPoolInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | GuzzleClientFactoryInterface
     */
    private function createGuzzleClientFactoryMock()
    {
        return $this->getMockBuilder('Toper\GuzzleClientFactoryInterface')
            ->getMock();
    }
}
