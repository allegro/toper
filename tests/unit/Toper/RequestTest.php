<?php

namespace Toper;

use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Message\Response;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    const URL = "/test";

    const BASE_URL = "http://123:123:123:123";

    /**
     * @var GuzzleClient | \PHPUnit_Framework_MockObject_MockObject
     */
    private $guzzleClient;

    /**
     * @var GuzzleClientFactoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $guzzleClientFactory;

    private $hostPool;

    public function setUp()
    {
        $this->guzzleClient = $this->createGuzzleClientMock();
        $this->guzzleClientFactory = $this->createGuzzleClientFactoryMock();
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL));
    }

    /**
     * @test
     */
    public function shouldSendRequest()
    {
        $this->guzzleClientFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->guzzleClient));

        $guzzleRequest = $this->getMockBuilder('Guzzle\Http\Message\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $guzzleResponse = new Response(200, array(), 'ok');

        $guzzleRequest->expects($this->once())
            ->method('send')
            ->will($this->returnValue($guzzleResponse));

        $this->guzzleClient->expects($this->any())
            ->method('get')
            ->with(self::URL)
            ->will($this->returnValue($guzzleRequest));


        $this->returnValue($guzzleResponse);

        $instance = $this->createInstance();
        $response = $instance->send();

        $this->assertEquals($guzzleResponse->getStatusCode(), $response->getStatusCode());
        $this->assertEquals($guzzleResponse->getBody(true), $response->getBody());
    }

    /**
     * @return Request
     */
    private function createInstance()
    {
        return new Request(
            self::URL, $this->hostPool, $this->guzzleClientFactory
        );
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | GuzzleClientFactoryInterface
     */
    private function createGuzzleClientFactoryMock()
    {
        return $this->getMockBuilder('Toper\GuzzleClientFactoryInterface')
            ->getMock();
    }
}
 