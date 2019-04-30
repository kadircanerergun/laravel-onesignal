<?php
namespace KCE\OneSignal;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use KCE\OneSignal\Exceptions\ClientException;
use Psy\Util\Json;

class Client
{
    /**
     * OneSignal API Endpoint
     */
    const ONESIGNAL_API_URL = "https://onesignal.com/api/v1";

    /**
     * URL Path for players
     */
    const PLAYERS_PATG = "/players";

    /**
     * URL Path for apps
     */
    const APPS_PATH = '/apps';

    /**
     * @var $guzzleClient Client
     */
    private $guzzleClient;
    /**
     * @var $appId string
     */
    private $appId;
    /**
     * @var $restApiKey string
     */
    private $restApiKey;
    /**
     * @var $userAuthKey string
     */
    private $userAuthKey;
    /**
     * @var $postParams array
     */
    public $postParams = [];


    /**
     * OneSignal constructor.
     * @param string $appId
     * @param string $restApiKey
     * @param string $userAuthKey
     */
    public function __construct($appId, $restApiKey, $userAuthKey)
    {
        $this->appId = $appId;
        $this->restApiKey = $restApiKey;
        $this->userAuthKey = $userAuthKey;
        $this->setAuthorizationKey($restApiKey);
        $this->postParams['headers']['Content-Type'] = 'application/json';
        $this->guzzleClient = new GuzzleClient();
    }





    /**
     * @param $endPoint
     * @return \Psr\Http\Message\ResponseInterface|Promise|\Closure|PromiseInterface|array|object
     */
    public function post($endPoint)
    {
        $fullUrl = self::ONESIGNAL_API_URL.$endPoint;
        $this->postParams['verify'] = false;
        return $this->guzzleClient->post($fullUrl, $this->postParams);
    }

    /**
     * @param $endPoint
     * @return \Psr\Http\Message\ResponseInterface|Promise|\Closure|PromiseInterface|array|object
     */
    public function get($endPoint)
    {
        $fullUrl = self::ONESIGNAL_API_URL.$endPoint;
        return json_decode($this->guzzleClient->get($fullUrl, $this->postParams)->getBody()->getContents());
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param string $appId
     * @return self
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
        return $this;
    }

    /**
     * @return string
     */
    public function getRestApiKey()
    {
        return $this->restApiKey;
    }

    /**
     * @param string $restApiKey
     * @return self
     */
    public function setRestApiKey($restApiKey)
    {
        $this->restApiKey = $restApiKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserAuthKey()
    {
        return $this->userAuthKey;
    }

    /**
     * @param string $userAuthKey
     * @return $this
     */
    public function setUserAuthKey($userAuthKey)
    {
        $this->userAuthKey = $userAuthKey;
        return $this;
    }


    /**
     * @param Client $guzzleClient
     */
    public function setGuzzleClient($guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @return array
     */
    public function getPostParams()
    {
        return $this->postParams;
    }



    /**
     * @param $authKey
     * @return $this
     */
    public function setAuthorizationKey($authKey)
    {
        $this->postParams['headers']['Authorization'] = 'Basic ' . $authKey;
        return $this;
    }
}
