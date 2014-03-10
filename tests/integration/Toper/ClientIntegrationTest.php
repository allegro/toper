<?php

namespace Toper;

class ClientIntegrationTest extends \PHPUnit_Framework_TestCase
{
    const HOST = "http://localhost:7878";

    /**
     * @var Client
     */
    private $client;

    public function setUp() {
        $hostPoolProvider = new StaticHostPoolProvider(array(self::HOST));
        $this->client = new Client($hostPoolProvider, new GuzzleClientFactory());
    }

    /**
     * @test
     */
    public function shouldCallByGetMethod()
    {
        $request = $this->client->get("/code/200");

        $response = $request->send();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('200', $response->getBody());
    }
} 