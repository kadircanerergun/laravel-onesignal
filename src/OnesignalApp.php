<?php
namespace KCE\OneSignal;

class OnesignalApp
{
    /**
     * URL Path for apps
     */
    const APPS_PATH = '/apps';

    /**
     * @var $params array
     */
    protected $params = [
    ];

    /**
     * @var Client;
     */
    private $client;


    /**
     * Notification constructor.
     */
    public function __construct($client = null)
    {
        if ($client) {
            $this->client = $client;
        }
    }

    /**
     * @return array|\Closure|\GuzzleHttp\Promise\Promise|\GuzzleHttp\Promise\PromiseInterface|object|\Psr\Http\Message\ResponseInterface
     */
    public function getApps()
    {
        $this->client->setAuthorizationKey($this->client->getUserAuthKey());
        return $this->client->get(self::APPS_PATH);
    }

    /**
     * @param $appId
     */
    public function getAppDetail($appId)
    {
        $this->client->setAuthorizationKey($this->client->getUserAuthKey());
        return $this->client->get(self::APPS_PATH.'/'.$appId);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }
}
