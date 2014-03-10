<?php

namespace Toper;

use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Message\Request as GuzzleRequest;
use Guzzle\Http\Message\Response;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    const URL = "/test";

    const BASE_URL1 = "http://123.123.123.123";

    const BASE_URL2 = "http://123.123.123.124";

    /**
     * @var GuzzleClientFactoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $guzzleClientFactory;

    private $hostPool;

    public function setUp()
    {
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1));
    }

    /**
     * @test
     */
    public function shouldSendRequest()
    {
        $guzzleClient = $this->createGuzzleClientMock();
        $guzzleClientFactory = new GuzzleClientFactoryStub(
            array($guzzleClient)
        );

        $guzzleResponse = new Response(200, array(), 'ok');

        $guzzleRequest = $this->createGuzzleRequest($guzzleResponse);

        $guzzleClient->expects($this->any())
            ->method('get')
            ->with(self::URL)
            ->will($this->returnValue($guzzleRequest));


        $this->returnValue($guzzleResponse);

        $instance = $this->createInstance(
            $guzzleClientFactory
        );
        $response = $instance->send();

        $this->assertEquals($guzzleResponse->getStatusCode(), $response->getStatusCode());
        $this->assertEquals($guzzleResponse->getBody(true), $response->getBody());
    }

    /**
     * @test
     */
    public function shouldCatchCurlExceptionAndCallNextHost()
    {
        $guzzleClient1 = $this->createGuzzleClientMock();
        $guzzleClient2 = $this->createGuzzleClientMock();
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1, self::BASE_URL2));

        $guzzleClientFactory = new GuzzleClientFactoryStub(
            array($guzzleClient1, $guzzleClient2)
        );

        $e = new CurlException();
        $guzzleClient1->expects($this->any())
            ->method('get')
            ->will($this->throwException($e));

        $guzzleResponse = new Response(200, array(), 'ok');

        $guzzleRequest = $this->createGuzzleRequest($guzzleResponse);

        $guzzleClient2->expects($this->any())
            ->method('get')
            ->with(self::URL)
            ->will($this->returnValue($guzzleRequest));

        $instance = $this->createInstance($guzzleClientFactory);
        $response = $instance->send();

        $this->assertEquals($guzzleResponse->getStatusCode(), $response->getStatusCode());
        $this->assertEquals($guzzleResponse->getBody(true), $response->getBody());
    }

    /**
     * @test
     */
    public function shouldThrowServerExceptionIfAllHostFailed()
    {
        $guzzleClient1 = $this->createGuzzleClientMock();
        $guzzleClient2 = $this->createGuzzleClientMock();
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1, self::BASE_URL2));

        $guzzleClientFactory = new GuzzleClientFactoryStub(
            array($guzzleClient1, $guzzleClient2)
        );

        $e = new CurlException();
        $guzzleClient1->expects($this->any())
            ->method('get')
            ->will($this->throwException($e));

        $guzzleClient2->expects($this->any())
            ->method('get')
            ->with(self::URL)
            ->will($this->throwException($e));

        $instance = $this->createInstance($guzzleClientFactory);

        $this->setExpectedException('Toper\Exception\ServerException');

        $instance->send();
    }

    /**
     * @param GuzzleClientFactoryInterface $guzzleClientFactory
     *
     * @return Request
     */
    private function createInstance(GuzzleClientFactoryInterface $guzzleClientFactory)
    {
        return new Request(
            self::URL, $this->hostPool, $guzzleClientFactory
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

    /**
     * @param Response $guzzleResponse
     * @return GuzzleRequest
     */
    private function createGuzzleRequest(Response $guzzleResponse)
    {

        $guzzleRequest = $this->getMockBuilder('Guzzle\Http\Message\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $guzzleRequest->expects($this->once())
            ->method('send')
            ->will($this->returnValue($guzzleResponse));

        return $guzzleRequest;
    }
}
