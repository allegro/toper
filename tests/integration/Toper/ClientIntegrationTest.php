<?php

namespace Toper;

class ClientIntegrationTest extends \PHPUnit_Framework_TestCase
{
    const HOST1 = "http://localhost:7820";

    const HOST2 = "http://localhost:7850";

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
        $hostPoolProvider = new StaticHostPoolProvider(array(self::HOST2, self::HOST1));
        $client = new Client($hostPoolProvider, new GuzzleClientFactory());

        $request = $client->get("/request");

        $response = $request->send();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getBody());
    }
} 