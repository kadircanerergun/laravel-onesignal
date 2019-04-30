<?php
namespace KCE\OneSignalTest;

use KCE\OneSignal\Exceptions\ClientException;
use KCE\OneSignal\Notification;
use KCE\OneSignal\OnesignalApp;

class AppsTest extends TestCase
{


    /**
     * @var OnesignalApp $notification
     */
    private $onesignalApp;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->onesignalApp = new OnesignalApp($this->client);
    }

    /** @test */
    public function it_test_get_apps()
    {
        $this->setMock($this->getMockData());
        $this->onesignalApp->setClient($this->client);
        $response = $this->onesignalApp
            ->getApps();
        print_r($response);
        exit();

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


    public function getMockData()
    {
        return json_decode('
        [  
           {  
              "id":"xxxyyy",
              "name":"TestApp",
              "gcm_key":null,
              "chrome_key":null,
              "chrome_web_key":null,
              "chrome_web_origin":null,
              "chrome_web_gcm_sender_id":null,
              "chrome_web_default_notification_icon":null,
              "chrome_web_sub_domain":null,
              "apns_env":"production",
              "apns_certificates":"--",
              "safari_apns_certificate":null,
              "safari_site_origin":null,
              "safari_push_id":null,
              "safari_icon_16_16":"public\/safari_packages\/5763ef61-e5d6-476a-920f-6372f653dc8d\/icons\/16x16.png",
              "safari_icon_32_32":"public\/safari_packages\/5763ef61-e5d6-476a-920f-6372f653dc8d\/icons\/16x16@2x.png",
              "safari_icon_64_64":"public\/safari_packages\/5763ef61-e5d6-476a-920f-6372f653dc8d\/icons\/32x32@2x.png",
              "safari_icon_128_128":"public\/safari_packages\/5763ef61-e5d6-476a-920f-6372f653dc8d\/icons\/128x128.png",
              "safari_icon_256_256":"public\/safari_packages\/5763ef61-e5d6-476a-920f-6372f653dc8d\/icons\/128x128@2x.png",
              "site_name":null,
              "created_at":"2018-09-30T21:27:25.978Z",
              "updated_at":"2018-09-30T21:48:32.493Z",
              "players":6,
              "messageable_players":5,
              "basic_auth_key":"key"
           }
        ]', true);
    }
}