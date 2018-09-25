<?php
namespace KCE\OneSignalTest;

use Carbon\Carbon;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use KCE\OneSignal\Client;
use KCE\OneSignal\Exceptions\ClientException;
use KCE\OneSignal\OneSignalClient;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Client */
    private $client;
    public function setUp()
    {
        parent::setUp();
        $this->client = new Client("test_app_id", "test_key", 'test_key');

    }
    /** @test */
    public function it_test_send_to_tags()
    {
        $this->setMock();
        $this->client
            ->setSchedule(date('2018-12-12 13:45'))
            ->setUrl("http://canerergun.net")
            ->addTags(['name', 'caner'])
            ->addOrTag(['os', 'ios'])
            ->setTitle('TestNotifTitle')
            ->addFilter("country" ,'=', 'TR')
            ->sendToTags("Message", ['user', 12]);
        $postParamsBody = json_decode($this->client->getPostParams()['body']);
        $this->assertEquals($postParamsBody->app_id, 'test_app_id');
        $this->assertAttributeEquals("user", 'key',$postParamsBody->tags[3]);
        $this->assertEquals($postParamsBody->send_after, '2018-12-12 13:45');
        $this->assertEquals($postParamsBody->headings->en, 'TestNotifTitle');
        $this->assertEquals($postParamsBody->url, "http://canerergun.net");
        $this->assertEquals($postParamsBody->tags[0]->value, 'caner');
    }

    /** @test */
    public function it_test_change_credentials()
    {
        $this->client->setAppId("new_app_id")->setRestApiKey("new_rest_api_key")->setUserAuthKey('new_user_auth_key');
        $this->assertEquals('new_app_id', $this->client->getAppId());
        $this->assertEquals('new_rest_api_key', $this->client->getRestApiKey());
        $this->assertEquals('new_user_auth_key', $this->client->getUserAuthKey());
    }

    /**
     * @test
     */
    public function it_test_wrong_tag_formats()
    {
        $this->expectException(ClientException::class);
        $this->client->addTag(['tag']);
    }

    /**
     * @test
     */
    public function it_test_send_to_all()
    {
        $this->setMock();
        $this->client
            ->scheduleByUserTimezone("19:05")
            ->setData([
                'type' => 1
            ])
            ->sendToAll("New Message");
        $postParamsBody = json_decode($this->client->getPostParams()['body']);
        $this->assertEquals($postParamsBody->delivery_time_of_day, "19:05");
        $this->assertEquals($postParamsBody->delayed_option, "timezone");
        $this->assertEquals($postParamsBody->data->type, 1);
    }

    /**
     * @test
     */
    public function it_test_send_to_segments()
    {
        $this->setMock();
        $this->client
            ->scheduleByUserTimezone("19:05")
            ->setData([
                'type' => 1
            ])
            ->sendToSegments("New Message", "Test Segment");
        $postParamsBody = json_decode($this->client->getPostParams()['body']);
        $this->assertEquals($postParamsBody->included_segments[0], "Test Segment");
    }
    /**
     * @test
     */
    public function it_test_send_to_location()
    {
        $this->setMock();
        $this->client
            ->scheduleByUserTimezone("19:05")
            ->setData([
                'type' => 1
            ])
            ->sendToLocation("New Message", 40, 34.43, 34.43);
        $postParamsBody = json_decode($this->client->getPostParams()['body']);
        $this->assertEquals($postParamsBody->filters[0]->radius, 40);
    }
    /**
     * @test
     */
    public function it_test_send_to_country()
    {
        $this->setMock();
        $this->client
            ->scheduleByUserTimezone("19:05")
            ->setData([
                'type' => 1
            ])
            ->sendToCountry("New Message", "TR");
        $postParamsBody = json_decode($this->client->getPostParams()['body']);
        $this->assertEquals($postParamsBody->filters[0]->value, "TR");
    }

    /**
     * @test
     */
    public function it_test_add_button()
    {
        $this->setMock();
        $this->client->addButton('tstbtn1', 'Test Button', 'icon-drawable')->addWebButton('tstbtn2', 'Test Web Button', 'http://canerergun.net', 'icon-url')->sendToAll("New Message");
        $postParamsBody = json_decode($this->client->getPostParams()['body']);
        $this->assertEquals($postParamsBody->buttons[0]->id, "tstbtn1");
        $this->assertEquals($postParamsBody->web_buttons[0]->id, "tstbtn2");
    }


    /**
     * @test
     */
    public function it_test_send_to_user()
    {
        $this->setMock();
        $this->client
            ->sendToUser("New Message", "user_id");
        $postParamsBody = json_decode($this->client->getPostParams()['body']);
        $this->assertEquals($postParamsBody->include_player_ids, ['user_id']);
    }

    private function setMock()
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar']),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new \GuzzleHttp\Client(['handler' => $handler]);
        $this->client->setGuzzleClient($client);
    }
}