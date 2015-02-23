<?php

namespace Toper;

use Guzzle\Common\Collection;
use Guzzle\Http\ClientInterface as GuzzleClientInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Guzzle\Http\Message\Request as GuzzleRequest;
use Guzzle\Http\Message\Response as GuzzleResponse;
use Guzzle\Http\QueryString;

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

        $guzzleResponse = new GuzzleResponse(200, array(), 'ok');

        $guzzleRequest = $this->createGuzzleRequest($guzzleResponse);

        $this->prepareGuzzleClientMock($guzzleClient, $guzzleRequest);

        $this->returnValue($guzzleResponse);

        $instance = $this->createInstance(
            Request::GET,
            array(),
            $guzzleClient
        );
        $response = $instance->send();

        $this->assertEquals($guzzleResponse->getStatusCode(), $response->getStatusCode());
        $this->assertEquals($guzzleResponse->getBody(true), $response->getBody());
    }

    /**
     * @test
     */
    public function shouldSendRequestWithBinds()
    {
        $guzzleClient = $this->createGuzzleClientMock();

        $binds = array('key' => 'value');

        $guzzleResponse = new GuzzleResponse(200, array(), 'ok');

        $guzzleRequest = $this->createGuzzleRequest($guzzleResponse);

        $this->prepareGuzzleClientMock($guzzleClient, $guzzleRequest, Request::GET, $binds);

        $instance = $this->createInstance(
            Request::GET,
            $binds,
            $guzzleClient
        );

        $instance->send();
    }

    /**
     * @test
     */
    public function shouldCatchGuzzleServerErrorExceptionAndCallNextHost()
    {
        $guzzleResponse = new GuzzleResponse(200, array(), 'ok');

        $e = $this->createGuzzleServerErrorResponseException();
        $guzzleRequest1 = $this->createGuzzleRequestMockWithException($e);

        $guzzleRequest2 = $this->createGuzzleRequest($guzzleResponse);

        $url1 = self::BASE_URL1;
        $url2 = self::BASE_URL2;

        $behaviors = array(
            'get' =>
                function (GuzzleClientInterface $guzzleClient) use ($url1, $url2, $guzzleRequest1, $guzzleRequest2) {
                    if ($guzzleClient->getBaseUrl() == $url1) {
                        return $guzzleRequest1;
                    }

                    if ($guzzleClient->getBaseUrl() == $url2) {
                        return $guzzleRequest2;
                    }

                    return null;
                }
        );

        $guzzleClientStub = new GuzzleClientStub($behaviors);


        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1, self::BASE_URL2));

        $instance = $this->createInstance(Request::GET, array(), $guzzleClientStub);
        $response = $instance->send();

        $this->assertEquals($guzzleResponse->getStatusCode(), $response->getStatusCode());
        $this->assertEquals($guzzleResponse->getBody(true), $response->getBody());
    }


    /**
     * @test
     */
    public function shouldCatchGuzzleCurlExceptionAndCallNextHost()
    {
        $guzzleResponse = new GuzzleResponse(200, array(), 'ok');

        $e = $this->createGuzzleCurlException();
        $guzzleRequest1 = $this->createGuzzleRequestMockWithException($e);

        $guzzleRequest2 = $this->createGuzzleRequest($guzzleResponse);

        $url1 = self::BASE_URL1;
        $url2 = self::BASE_URL2;

        $behaviors = array(
            'get' => function (GuzzleClientInterface $client) use ($guzzleRequest1, $guzzleRequest2, $url1, $url2) {
                if ($client->getBaseUrl() == $url1) {
                    return $guzzleRequest1;
                }

                if ($client->getBaseUrl() == $url2) {
                    return $guzzleRequest2;
                }

                return null;
            }
        );

        $guzzleClient = new GuzzleClientStub($behaviors);
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1, self::BASE_URL2));

        $instance = $this->createInstance(Request::GET, array(), $guzzleClient);
        $response = $instance->send();

        $this->assertEquals($guzzleResponse->getStatusCode(), $response->getStatusCode());
        $this->assertEquals($guzzleResponse->getBody(true), $response->getBody());
    }

    /**
     * @test
     */
    public function shouldThrowLastExceptionIfAllHostFailed()
    {
        $e = $this->createGuzzleServerErrorResponseException();
        $guzzleRequest1 = $this->createGuzzleRequestMockWithException($e);

        $guzzleRequest2 = $this->createGuzzleRequestMockWithException($e);

        $url1 = self::BASE_URL1;
        $url2 = self::BASE_URL2;

        $behaviors = array(
            'get' => function (GuzzleClientInterface $client) use ($guzzleRequest1, $guzzleRequest2, $url1, $url2) {
                if ($client->getBaseUrl() == $url1) {
                    return $guzzleRequest1;
                }

                if ($client->getBaseUrl() == $url2) {
                    return $guzzleRequest2;
                }

                return null;
            }
        );

        $guzzleClient = new GuzzleClientStub($behaviors);

        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1, self::BASE_URL2));

        $instance = $this->createInstance(Request::GET, array(), $guzzleClient);

        $this->setExpectedException('Toper\Exception\ServerErrorException');

        $instance->send();
    }


    /**
     * @test
     */
    public function shouldThrowExceptionIfEmptyHostPool()
    {
        $behaviors = array();

        $guzzleClient = new GuzzleClientStub($behaviors);

        $this->hostPool = new SimpleHostPool(array());

        $instance = $this->createInstance(Request::GET, array(), $guzzleClient);

        $this->setExpectedException('Toper\Exception\EmptyHostPoolException');

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

        $guzzleResponse = new GuzzleResponse(200, array(), 'ok');

        $guzzleRequest = $this->createGuzzleEntityEnclosingRequest($guzzleResponse);

        $this->prepareGuzzleClientMock(
            $guzzleClient1,
            $guzzleRequest,
            Request::POST
        );

        $guzzleRequest->expects($this->once())
            ->method('setBody')
            ->with($body);

        $instance = $this->createInstance(Request::POST, array(), $guzzleClient1);
        $instance->setBody($body);

        $instance->send();
    }


    /**
     * @test
     */
    public function shouldSetBodyIfRequestIsPut()
    {
        $body = "some body";
        $guzzleClient = $this->createGuzzleClientMock();
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1));

        $guzzleResponse = new GuzzleResponse(200, array(), 'ok');

        $guzzleRequest = $this->createGuzzleEntityEnclosingRequest($guzzleResponse);

        $this->prepareGuzzleClientMock($guzzleClient, $guzzleRequest, Request::PUT);

        $guzzleRequest->expects($this->once())
            ->method('setBody')
            ->with($body);

        $instance = $this->createInstance(Request::PUT, array(), $guzzleClient);
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

        $guzzleClient = $this->createGuzzleClientMock();
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1));

        $guzzleResponse = new GuzzleResponse($responseErrorCode, array(), $responseBody);

        $clientErrorResponseException = new ClientErrorResponseException();
        $clientErrorResponseException->setResponse($guzzleResponse);

        $e = $this->createGuzzleClientException($guzzleResponse);
        $guzzleRequest = $this->createGuzzleRequestMockWithException($e);
        $this->prepareGuzzleClientMock($guzzleClient, $guzzleRequest);

        $instance = $this->createInstance(Request::GET, array(), $guzzleClient);

        $result = $instance->send();
        $this->assertEquals($responseErrorCode, $result->getStatusCode());
        $this->assertEquals($responseBody, $result->getBody());
    }

    /**
     * @test
     */
    public function shouldSetQueryParam()
    {
        $paramName1 = 'name';
        $paramValue1 = 'value';

        $paramName2 = 'name';
        $paramValue2 = 'value';

        $responseErrorCode = 404;
        $responseBody = 'not found';

        $guzzleClient = $this->createGuzzleClientMock();
        $this->hostPool = new SimpleHostPool(array(self::BASE_URL1));

        $guzzleResponse = new GuzzleResponse($responseErrorCode, array(), $responseBody);

        $clientErrorResponseException = new ClientErrorResponseException();
        $clientErrorResponseException->setResponse($guzzleResponse);

        $guzzleRequest = $this->createGuzzleRequest($guzzleResponse);
        $this->prepareGuzzleClientMock($guzzleClient, $guzzleRequest);

        $instance = $this->createInstance(Request::GET, array(), $guzzleClient);
        $instance->addQueryParam($paramName1, $paramValue1);
        $instance->addQueryParam($paramName2, $paramValue2);

        $instance->send();

        $this->assertEquals($paramValue1, $guzzleRequest->getQuery()->get($paramName1));
        $this->assertEquals($paramValue2, $guzzleRequest->getQuery()->get($paramName2));
    }

    /**
     * @test
     */
    public function shouldReturnBinds()
    {
        $binds = array('key' => 'value');
        $instance = $this->createInstance(
            Request::GET,
            $binds,
            $this->createGuzzleClientMock()
        );

        $this->assertEquals($binds, $instance->getBinds());
    }

    /**
     * @param string $method
     * @param array $binds
     * @param GuzzleClientInterface $guzzleClient
     *
     * @return Request
     */
    private function createInstance(
        $method,
        array $binds,
        GuzzleClientInterface $guzzleClient
    ) {
        return new Request(
            $method,
            self::URL,
            $binds,
            $this->hostPool,
            $guzzleClient
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | GuzzleClientInterface
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

        $guzzleQueryParams = new QueryString();
        $guzzleRequest->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($guzzleQueryParams));

        return $guzzleRequest;
    }

    /**
     * @param \Exception $e
     * @return GuzzleRequest | \PHPUnit_Framework_MockObject_MockObject
     */
    private function createGuzzleRequestMockWithException(\Exception $e)
    {
        $guzzleRequest = $this->getMockBuilder('Guzzle\Http\Message\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $guzzleRequest->expects($this->once())
            ->method('send')
            ->will($this->throwException($e));

        return $guzzleRequest;
    }


    /**
     * @param GuzzleResponse $guzzleResponse
     * @return EntityEnclosingRequest | \PHPUnit_Framework_MockObject_MockObject
     */
    private function createGuzzleEntityEnclosingRequest(GuzzleResponse $guzzleResponse)
    {
        $guzzleParams = new Collection();
        $guzzleRequest = $this->getMockBuilder('Guzzle\Http\Message\EntityEnclosingRequest')
            ->disableOriginalConstructor()
            ->getMock();

        $guzzleRequest->expects($this->once())
            ->method('send')
            ->will($this->returnValue($guzzleResponse));

        $guzzleRequest->expects($this->any())
            ->method('getParams')
            ->will($this->returnValue($guzzleParams));

        return $guzzleRequest;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $guzzleClient
     * @param GuzzleRequest $guzzleRequest
     * @param string $method
     * @param array $binds
     */
    private function prepareGuzzleClientMock(
        \PHPUnit_Framework_MockObject_MockObject $guzzleClient,
        GuzzleRequest $guzzleRequest,
        $method = Request::GET,
        array $binds = array()
    ) {
        $guzzleClient->expects($this->any())
            ->method($method)
            ->with(array(self::URL, $binds))
            ->will($this->returnValue($guzzleRequest));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mockObject
     * @param string $method
     * @param \Closure $callback
     *
     * @return \PHPUnit_Framework_MockObject_MockObject | GuzzleClientInterface
     */
    private function mockGuzzleClientMethod(
        \PHPUnit_Framework_MockObject_MockObject $mockObject,
        $method,
        \Closure $callback
    ) {
        $mockObject->expects($this->any())
            ->method($method)
            ->will($this->returnCallback($callback));

        return $mockObject;
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


    /**
     * @param GuzzleResponse $response
     * @return ClientErrorResponseException
     */
    private function createGuzzleClientException(GuzzleResponse $response)
    {
        $e = new ClientErrorResponseException();

        $e->setResponse($response);

        return $e;
    }
}
