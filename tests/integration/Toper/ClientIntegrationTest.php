<?php

namespace Toper;

class ClientIntegrationTest extends \PHPUnit_Framework_TestCase
{
    const HOST1 = "http://localhost:7820";

    const HOST2 = "http://localhost:7850";

    const HOST3 = "http://localhost:7800";

    const HOST4 = "http://localhost:7900";

    const HOST_404 = "http://localhost:7844";

    const HOST_302 = "http://localhost:7832";

    /**
     * @test
     */
    public function shouldCallByGetMethod()
    {
        $hostPoolProvider = new StaticHostPoolProvider(array(self::HOST1));
        $client = new Client($hostPoolProvider, new GuzzleClientFactory());

        $request = $client->get("/request");

        $response = $request->send();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getBody());
    }

    /**
     * @test
     */
    public function shouldReturn4xxResponse() {

        $hostPoolProvider = new StaticHostPoolProvider(array(self::HOST_404));
        $client = new Client($hostPoolProvider, new GuzzleClientFactory());

        $request = $client->get("/request");

        $response = $request->send();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('not found', $response->getBody());
    }

    /**
     * @test
     */
    public function shouldReturn3xxResponse() {

        $hostPoolProvider = new StaticHostPoolProvider(array(self::HOST_302));
        $client = new Client($hostPoolProvider, new GuzzleClientFactory());

        $request = $client->get("/request");

        $response = $request->send();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('redirect', $response->getBody());
    }

    /**
     * @test
     */
    public function shouldCallByPostMethod()
    {
        $hostPoolProvider = new StaticHostPoolProvider(array(self::HOST1));
        $client = new Client($hostPoolProvider, new GuzzleClientFactory());

        $request = $client->post("/request");

        $response = $request->send();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getBody());
    }

    /**
     * @test
     */
    public function shouldCallNextHostIfFirstFailed()
    {
        $hostPoolProvider = new StaticHostPoolProvider(array(self::HOST2, self::HOST2, self::HOST1));
        $client = new Client($hostPoolProvider, new GuzzleClientFactory());

        $request = $client->get("/request");

        $response = $request->send();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getBody());
    }

    /**
     * @test
     */
    public function shouldSendPostRequest()
    {
        $hostPoolProvider = new StaticHostPoolProvider(array(self::HOST4, self::HOST1));
        $client = new Client($hostPoolProvider, new GuzzleClientFactory());

        $request = $client->post("/should_be_post");
        $request->setBody("data");

        $response = $request->send();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getBody());
    }


    /**
     * @test
     */
    public function shouldSendPutRequest()
    {
        $hostPoolProvider = new StaticHostPoolProvider(array(self::HOST4, self::HOST1));
        $client = new Client($hostPoolProvider, new GuzzleClientFactory());

        $request = $client->put("/should_be_put");
        $request->setBody("data");

        $response = $request->send();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getBody());
    }

    /**
     * @test
     */
    public function shouldSendHeaderSetByGuzzleClientOptions() {

        $hostPoolProvider = new StaticHostPoolProvider(array(self::HOST4, self::HOST1));
        $client = new Client($hostPoolProvider, new GuzzleClientFactory(
                array(
                    'request.options' => array(
                        'headers' => array(
                            'Content-Type' => 'application/json'
                        )
                    )
                )
            )
        );

        $request = $client->post("/should_be_post_application_json");
        $request->setBody("data");

        $response = $request->send();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getBody());
    }
} 