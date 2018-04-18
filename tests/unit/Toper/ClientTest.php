<?php

namespace Toper;

use Guzzle\Http\Client as GuzzleClient;

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
     * @var GuzzleClient | \PHPUnit_Framework_MockObject_MockObject
     */
    private $guzzleClientMock;

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

        $this->guzzleClientMock = $this->createGuzzleClientMock();
        $this->guzzleClientFactory = $this->createGuzzleClientFactoryMock();
    }

    /**
     * @test
     */
    public function shouldGetCreateGetRequest()
    {
        $url = "/test";
        $client = $this->createClient();
        $request = $client->get($url);

        $this->assertEquals($url, $request->getUrl());
        $this->assertEquals($this->hostPool, $request->getHostPool());
        $this->assertEquals(Request::GET, $request->getMethod());
    }


    /**
     * @test
     */
    public function shouldPostCreatePostRequest()
    {
        $url = "/test";
        $client = $this->createClient();
        $request = $client->post($url);

        $this->assertEquals($url, $request->getUrl());
        $this->assertEquals($this->hostPool, $request->getHostPool());
        $this->assertEquals(Request::POST, $request->getMethod());
    }

    /**
     * @test
     */
    public function shouldPatchCreatePostRequest()
    {
        $url = "/test";
        $client = $this->createClient();
        $request = $client->patch($url);

        $this->assertEquals($url, $request->getUrl());
        $this->assertEquals($this->hostPool, $request->getHostPool());
        $this->assertEquals(Request::PATCH, $request->getMethod());
    }

    /**
     * @test
     */
    public function shouldPutCreatePostRequest()
    {
        $url = "/test";
        $client = $this->createClient();
        $request = $client->put($url);

        $this->assertEquals($url, $request->getUrl());
        $this->assertEquals($this->hostPool, $request->getHostPool());
        $this->assertEquals(Request::PUT, $request->getMethod());
    }

    /**
     * @test
     */
    public function shouldGetCreateGetRequestWithBinds()
    {
        $url = "/test";
        $client = $this->createClient();
        $binds = array('key' => 'value');
        $request = $client->get($url, $binds);

        $this->assertEquals($binds, $request->getBinds());
    }


    /**
     * @test
     */
    public function shouldPostCreatePostRequestWithBinds()
    {
        $url = "/test";
        $binds = array('key' => 'value');
        $client = $this->createClient();
        $request = $client->post($url, $binds);

        $this->assertEquals($binds, $request->getBinds());
    }

    /**
     * @test
     */
    public function shouldPatchCreatePostRequestWithBinds()
    {
        $url = "/test";
        $binds = array('key' => 'value');
        $client = $this->createClient();
        $request = $client->patch($url, $binds);

        $this->assertEquals($binds, $request->getBinds());
    }

    /**
     * @test
     */
    public function shouldPutCreatePostRequestWithBinds()
    {
        $url = "/test";
        $binds = array('key' => 'value');
        $client = $this->createClient();
        $request = $client->put($url, $binds);

        $this->assertEquals($binds, $request->getBinds());
    }

    /**
     * @test
     */
    public function deleteShouldCreateDeleteRequestWithBinds()
    {
        $url = "/test";
        $binds = array('key' => 'value');
        $client = $this->createClient();
        $request = $client->delete($url, $binds);

        $this->assertEquals($url, $request->getUrl());
        $this->assertEquals($this->hostPool, $request->getHostPool());
        $this->assertEquals(Request::DELETE, $request->getMethod());
        $this->assertEquals($binds, $request->getBinds());
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
        $clientFactory = $this->getMockBuilder('Toper\GuzzleClientFactoryInterface')
            ->getMock();

        $clientFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->guzzleClientMock));

        return $clientFactory;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | GuzzleClient
     */
    private function createGuzzleClientMock()
    {
        return $this->getMockBuilder('Guzzle\Http\Client')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
