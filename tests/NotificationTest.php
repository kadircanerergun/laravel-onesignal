<?php
namespace KCE\OneSignalTest;

use KCE\OneSignal\Exceptions\ClientException;
use KCE\OneSignal\Notification;

class NotificationTest extends TestCase
{


    /**
     * @var Notification $notification
     */
    private $notification;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->notification = new Notification($this->client);
    }

    /** @test */
    public function it_test_send_to_tags()
    {
        $this->setMock();
        $this->notification
            ->setSchedule(date('2018-12-12 13:45'))
            ->setUrl("http://canerergun.net")
            ->addTags(['name', 'caner'])
            ->addOrTag(['os', 'ios'])
            ->setTitle('TestNotifTitle')
            ->addFilter("country" ,'=', 'TR')
            ->sendToTags("Message", ['user', 12]);
        $postParamsBody = json_decode($this->notification->getClient()->getPostParams()['body']);
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
        $this->notification->getClient()->setAppId("new_app_id")->setRestApiKey("new_rest_api_key")->setUserAuthKey('new_user_auth_key');
        $this->assertEquals('new_app_id', $this->notification->getClient()->getAppId());
        $this->assertEquals('new_rest_api_key', $this->notification->getClient()->getRestApiKey());
        $this->assertEquals('new_user_auth_key', $this->notification->getClient()->getUserAuthKey());
    }

    /**
     * @test
     */
    public function it_test_wrong_tag_formats()
    {
        $this->expectException(ClientException::class);
        $this->notification->addTag(['tag']);
    }

    /**
     * @test
     */
    public function it_test_send_to_all()
    {
        $this->setMock();
        $this->notification
            ->scheduleByUserTimezone("19:05")
            ->setData([
                'type' => 1
            ])
            ->sendToAll("New Message");
        $postParamsBody = json_decode($this->notification->getClient()->getPostParams()['body']);
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
        $this->notification
            ->scheduleByUserTimezone("19:05")
            ->setData([
                'type' => 1
            ])
            ->sendToSegments("New Message", "Test Segment");
        $postParamsBody = json_decode($this->notification->getClient()->getPostParams()['body']);
        $this->assertEquals($postParamsBody->included_segments[0], "Test Segment");
    }
    /**
     * @test
     */
    public function it_test_send_to_location()
    {
        $this->setMock();
        $this->notification
            ->scheduleByUserTimezone("19:05")
            ->setData([
                'type' => 1
            ])
            ->sendToLocation("New Message", 40, 34.43, 34.43);
        $postParamsBody = json_decode($this->notification->getClient()->getPostParams()['body']);
        $this->assertEquals($postParamsBody->filters[0]->radius, 40);
    }
    /**
     * @test
     */
    public function it_test_send_to_country()
    {
        $this->setMock();
        $this->notification
            ->scheduleByUserTimezone("19:05")
            ->setData([
                'type' => 1
            ])
            ->sendToCountry("New Message", "TR");
        $postParamsBody = json_decode($this->notification->getClient()->getPostParams()['body']);
        $this->assertEquals($postParamsBody->filters[0]->value, "TR");
    }

    /**
     * @test
     */
    public function it_test_add_button()
    {
        $this->setMock();
        $this->notification->addButton('tstbtn1', 'Test Button', 'icon-drawable')->addWebButton('tstbtn2', 'Test Web Button', 'http://canerergun.net', 'icon-url')->sendToAll("New Message");
        $postParamsBody = json_decode($this->notification->getClient()->getPostParams()['body']);
        $this->assertEquals($postParamsBody->buttons[0]->id, "tstbtn1");
        $this->assertEquals($postParamsBody->web_buttons[0]->id, "tstbtn2");
    }


    /**
     * @test
     */
    public function it_test_send_to_user()
    {
        $this->setMock();
        $this->notification
            ->sendToUser("New Message", "user_id");
        $postParamsBody = json_decode($this->notification->getClient()->getPostParams()['body']);
        $this->assertEquals($postParamsBody->include_player_ids, ['user_id']);
    }

    /**
     * @test
     */
    public function it_test_send_multi_language_notifications()
    {
        $this->setMock();
        $this->notification->setTitle([
            'en' => 'English Title',
            'tr' => 'Türkçe Başlık',
        ])->sendToAll([
            'en' => 'English notification message',
            'tr' => 'Türkçe bildirim mesajı'
        ]);
        $postParamsBody = json_decode($this->notification->getClient()->getPostParams()['body']);
        $this->assertEquals($postParamsBody->contents->tr, "Türkçe bildirim mesajı");
        $this->assertEquals($postParamsBody->contents->en, "English notification message");
        $this->assertEquals($postParamsBody->headings->tr, "Türkçe Başlık");
        $this->assertEquals($postParamsBody->headings->en, "English Title");
    }
}