<?php

namespace Toper;

use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Guzzle\Http\Message\Request as GuzzleRequest;
use Guzzle\Http\Message\Response as GuzzleResponse;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    const URL = "/test";

    const BASE_URL1 = "http://123.123.123.123";

    const BASE_URL2 = "http://123.123.123.124";

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

        $guzzleResponse = new GuzzleResponse(200, array(), 'ok');

        $guzzleRequest = $this->createGuzzleRequest($guzzleResponse);

        $guzzleClient->expects($this->any())
            ->method('get')
            ->with(self::URL)
            ->will($this->returnValue($guzzleRequest));


        $this->returnValue($guzzleResponse);

        $instance = $this->createInstance(
            Request::GET,
            $guzzleClientFactory
        );
        $response = $instance->send();

        $this->assertEquals($guzzleResponse->getStatusCode(), $response->getStatusCode());
        $this->assertEquals($guzzleResponse->getBody(true), $response->getBody());
    }

    /**
     * @test
     */
    public function shouldCatchGuzzleServerErrorExceptionAndCallNextHost()
    {
        $guzzleClient1 = $this->createGuzzleClientMock();
        $guzzleClient2 = $this->createGuzzleClientMock();
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1, self::BASE_URL2));

        $guzzleClientFactory = new GuzzleClientFactoryStub(
            array($guzzleClient1, $guzzleClient2)
        );

        $e = $this->createGuzzleServerErrorResponseException();
        $guzzleClient1->expects($this->any())
            ->method('get')
            ->will($this->throwException($e));

        $guzzleResponse = new GuzzleResponse(200, array(), 'ok');

        $guzzleRequest = $this->createGuzzleRequest($guzzleResponse);

        $guzzleClient2->expects($this->any())
            ->method('get')
            ->with(self::URL)
            ->will($this->returnValue($guzzleRequest));

        $instance = $this->createInstance(Request::GET, $guzzleClientFactory);
        $response = $instance->send();

        $this->assertEquals($guzzleResponse->getStatusCode(), $response->getStatusCode());
        $this->assertEquals($guzzleResponse->getBody(true), $response->getBody());
    }


    /**
     * @test
     */
    public function shouldCatchGuzzleCurlExceptionAndCallNextHost()
    {
        $guzzleClient1 = $this->createGuzzleClientMock();
        $guzzleClient2 = $this->createGuzzleClientMock();
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1, self::BASE_URL2));

        $guzzleClientFactory = new GuzzleClientFactoryStub(
            array($guzzleClient1, $guzzleClient2)
        );

        $e = $this->createGuzzleCurlException();
        $guzzleClient1->expects($this->any())
            ->method('get')
            ->will($this->throwException($e));

        $guzzleResponse = new GuzzleResponse(200, array(), 'ok');

        $guzzleRequest = $this->createGuzzleRequest($guzzleResponse);

        $guzzleClient2->expects($this->any())
            ->method('get')
            ->with(self::URL)
            ->will($this->returnValue($guzzleRequest));

        $instance = $this->createInstance(Request::GET, $guzzleClientFactory);
        $response = $instance->send();

        $this->assertEquals($guzzleResponse->getStatusCode(), $response->getStatusCode());
        $this->assertEquals($guzzleResponse->getBody(true), $response->getBody());
    }

    /**
     * @test
     */
    public function shouldThrowLastExceptionIfAllHostFailed()
    {
        $guzzleClient1 = $this->createGuzzleClientMock();
        $guzzleClient2 = $this->createGuzzleClientMock();
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1, self::BASE_URL2));

        $guzzleClientFactory = new GuzzleClientFactoryStub(
            array($guzzleClient1, $guzzleClient2)
        );

        $e = $this->createGuzzleServerErrorResponseException();
        $guzzleClient1->expects($this->any())
            ->method('get')
            ->will($this->throwException($e));

        $guzzleClient2->expects($this->any())
            ->method('get')
            ->with(self::URL)
            ->will($this->throwException($e));

        $instance = $this->createInstance(Request::GET, $guzzleClientFactory);

        $this->setExpectedException('Toper\Exception\ServerErrorException');

        $instance->send();
    }

    /**
     * @test
     */
    public function shouldSetPostBodyIfRequestIsPost()
    {
        $body = "some body";
        $guzzleClient1 = $this->createGuzzleClientMock();
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1));

        $guzzleClientFactory = new GuzzleClientFactoryStub(
            array($guzzleClient1)
        );

        $guzzleResponse = new GuzzleResponse(200, array(), 'ok');

        $guzzleRequest = $this->createGuzzleEntityEnclosingRequest($guzzleResponse);

        $guzzleClient1->expects($this->any())
            ->method('post')
            ->with(self::URL)
            ->will($this->returnValue($guzzleRequest));

        $guzzleRequest->expects($this->once())
            ->method('setBody')
            ->with($body);

        $instance = $this->createInstance(Request::POST, $guzzleClientFactory);
        $instance->setBody($body);


        $instance->send();
    }


    /**
     * @test
     */
    public function shouldSetBodyIfRequestIsPut()
    {
        $body = "some body";
        $guzzleClient1 = $this->createGuzzleClientMock();
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1));

        $guzzleClientFactory = new GuzzleClientFactoryStub(
            array($guzzleClient1)
        );

        $guzzleResponse = new GuzzleResponse(200, array(), 'ok');

        $guzzleRequest = $this->createGuzzleEntityEnclosingRequest($guzzleResponse);

        $guzzleClient1->expects($this->any())
            ->method('put')
            ->with(self::URL)
            ->will($this->returnValue($guzzleRequest));

        $guzzleRequest->expects($this->once())
            ->method('setBody')
            ->with($body);

        $instance = $this->createInstance(Request::PUT, $guzzleClientFactory);
        $instance->setBody($body);


        $instance->send();
    }

    /**
     * @test
     */
    public function shouldReturnResponseIfGuzzleThrowsClientErrorResponseException()
    {
        $responseErrorCode = 404;
        $responseBody = 'not found';

        $guzzleClient1 = $this->createGuzzleClientMock();
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1));

        $guzzleClientFactory = new GuzzleClientFactoryStub(
            array($guzzleClient1)
        );

        $guzzleResponse = new GuzzleResponse($responseErrorCode, array(), $responseBody);

        $clientErrorResponseException = new ClientErrorResponseException();
        $clientErrorResponseException->setResponse($guzzleResponse);

        $guzzleRequest = $this->createGuzzleRequest($guzzleResponse);

        $guzzleClient1->expects($this->any())
            ->method('get')
            ->with(self::URL)
            ->will($this->returnValue($guzzleRequest));

        $instance = $this->createInstance(Request::GET, $guzzleClientFactory);

        $result = $instance->send();
        $this->assertEquals($responseErrorCode, $result->getStatusCode());
        $this->assertEquals($responseBody, $result->getBody());
    }

    /**
     * @param string $method
     * @param GuzzleClientFactoryInterface $guzzleClientFactory
     *
     * @return Request
     */
    private function createInstance($method, GuzzleClientFactoryInterface $guzzleClientFactory)
    {
        return new Request(
            $method, self::URL, $this->hostPool, $guzzleClientFactory
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
     * @param GuzzleResponse $guzzleResponse
     * @return GuzzleRequest | \PHPUnit_Framework_MockObject_MockObject
     */
    private function createGuzzleRequest(GuzzleResponse $guzzleResponse)
    {

        $guzzleRequest = $this->getMockBuilder('Guzzle\Http\Message\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $guzzleRequest->expects($this->once())
            ->method('send')
            ->will($this->returnValue($guzzleResponse));

        return $guzzleRequest;
    }

    /**
     * @param GuzzleResponse $guzzleResponse
     * @return EntityEnclosingRequest | \PHPUnit_Framework_MockObject_MockObject
     */
    private function createGuzzleEntityEnclosingRequest(GuzzleResponse $guzzleResponse)
    {

        $guzzleRequest = $this->getMockBuilder('Guzzle\Http\Message\EntityEnclosingRequest')
            ->disableOriginalConstructor()
            ->getMock();

        $guzzleRequest->expects($this->once())
            ->method('send')
            ->will($this->returnValue($guzzleResponse));

        return $guzzleRequest;
    }


    /**
     * @return ServerErrorResponseException
     */
    private function createGuzzleServerErrorResponseException()
    {
        $e = new ServerErrorResponseException();
        $response = new GuzzleResponse(500);
        $e->setResponse($response);

        return $e;
    }

    /**
     * @return ServerErrorResponseException
     */
    private function createGuzzleCurlException()
    {
        $e = new CurlException();

        return $e;
    }
}
