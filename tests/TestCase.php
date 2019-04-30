<?php
namespace KCE\OneSignalTest;

use Carbon\Carbon;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use KCE\OneSignal\Client;
use KCE\OneSignal\Exceptions\ClientException;
use KCE\OneSignal\OneSignalClient;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    protected $client;
    public function setUp()
    {
        parent::setUp();
        $this->client = new Client("test_app_id", "test_key", 'test_key');
    }

    protected function setMock($data = ['X-Foo' => 'Bar'])
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, $data),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new \GuzzleHttp\Client(['handler' => $handler]);
        $this->client->setGuzzleClient($client);
    }
}
