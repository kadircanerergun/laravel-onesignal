<?php
namespace KCE\OneSignal;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use KCE\OneSignal\Exceptions\ClientException;

class Client
{
    /**
     * OneSignal API Endpoint
     */
    const ONESIGNAL_API_URL = "https://onesignal.com/api/v1";
    /**
     * URL Path for notifications
     */
    const NOTIFICATIONS_PATH = "/notifications";
    /**
     * URL Path for players
     */
    const PLAYERS_PATG = "/players";
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
    protected $postParams = [];
    /**
     * @var $params array
     */
    protected $params = [
        'tags' => []
    ];
    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var array
     */
    private $buttons = [];

    /**
     * @var array
     */
    private $webButtons = [];


    /**
     * @var string $notificationLanguage
     */
    private $notificationLanguage = "en";

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
        $this->postParams['headers']['Authorization'] = 'Basic ' . $this->restApiKey;
        $this->postParams['headers']['Content-Type'] = 'application/json';
        $this->guzzleClient = new GuzzleClient();
    }

    /**
     * @param $message
     * @param $tags
     * @param null $title
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendToTags($message, $tags)
    {
        $contents = [
            $this->notificationLanguage => $message
        ];
        $params = array(
            'app_id' => $this->appId,
            'contents' => $contents,
        );
        $this->addTags($tags);
        return $this->sendNotification($params);
    }

    /**
     * @param $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendToAll($message)
    {
        $contents = array(
            $this->notificationLanguage => $message
        );

        $params = array(
            'app_id' => $this->appId,
            'contents' => $contents,
            'included_segments' => array('All')
        );
        return $this->sendNotification($params);
    }

    /**
     * @param $message
     * @param $segments
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendToSegments($message, $segments)
    {
        $contents = array(
            $this->notificationLanguage => $message
        );

        $params = array(
            'app_id' => $this->appId,
            'contents' => $contents,
            'included_segments' => is_array($segments) ? $segments : [$segments]
        );
        return $this->sendNotification($params);
    }

    /**
     * @param $message
     * @param $country
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendToCountry($message, $country)
    {
        $contents = array(
            $this->notificationLanguage => $message
        );
        $params = array(
            'app_id' => $this->appId,
            'contents' => $contents,
        );
        $this->addFilter('country', '=', strtoupper($country));
        return $this->sendNotification($params);
    }

    /**
     * @param $message
     * @param $userId string|array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendToUser($message, $userId)
    {
        $contents = array(
            $this->notificationLanguage => $message
        );

        $params = array(
            'app_id' => $this->appId,
            'contents' => $contents,
            'include_player_ids' => is_array($userId) ? $userId : [$userId]
        );
        return $this->sendNotification($params);
    }

    /**
     * Send notification to specific area
     * @param $message
     * @param $radius
     * @param $lat
     * @param $long
     */
    public function sendToLocation($message, $radiusInMeter, $lat, $long)
    {
        $contents = array(
            $this->notificationLanguage => $message
        );
        $params = array(
            'app_id' => $this->appId,
            'contents' => $contents,
            'filters' => [
                [
                    'field' => 'location',
                    'radius' => $radiusInMeter,
                    'lat' => $lat,
                    'long' => $long
                ]
            ]
        );
        return $this->sendNotification($params);
    }

    /**
     * @param $url string
     * @return $this
     */
    public function setUrl($url)
    {
        return $this->setParam('url', $url);
    }

    /**
     * @param $data array
     * @return Client
     */
    public function setData($data)
    {
        return $this->setParam('data', $data);
    }

    /**
     * @param $dateTime
     * @return Client
     */
    public function setSchedule($dateTime)
    {
        return $this->setParam('send_after', $dateTime);
    }

    /**
     * @param $time
     * @return $this
     */
    public function scheduleByUserTimezone($time)
    {
        $this->setParam('delayed_option', 'timezone');
        $this->setParam('delivery_time_of_day', $time);
        return $this;
    }

    /**
     * @param array $params
     * @param string|null $title
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function sendNotification(array $params = [])
    {
        if (!empty($this->buttons)) {
            $this->setParam('buttons', $this->buttons);
        }
        if (!empty($this->webButtons)) {
            $this->setParam('web_buttons', $this->webButtons);
        }
        $parameters = array_merge($params, $this->params);
        if (count($this->filters) > 0) {
            $parameters['filters'] = isset($parameters['filters']) ? array_merge($parameters['filters'], $this->filters) : $this->filters;
        }

        $this->postParams['body'] = json_encode($parameters);

        $this->postParams['verify'] = false;
        return $this->post(self::NOTIFICATIONS_PATH);
    }




    /**
     * @param $tags
     * @return $this
     */
    public function addTags($tags)
    {
        if (!is_array($tags[0])) {
            return $this->addTag($tags);
        }
        $formatTags = [];
        foreach ($tags as $tag) {
            $tag = $this->prepareTag($tag);
            $formatTags[] = $tag;
        }
        $this->params['tags'] = array_merge($this->params['tags'], $formatTags);
        return $this;
    }

    /**
     * @param $tag
     * @return $this
     */
    public function addTag($tag)
    {
        $this->addTags([$tag]);
        return $this;
    }

    /**
     * @param $tag
     * @return $this
     */
    public function addOrTag($tag)
    {
        $this->params['tags'][] = [
            "operator" =>  "OR"
        ];
        $this->addTag($tag);
        return $this;
    }


    /**
     * @param $endPoint
     * @return \Psr\Http\Message\ResponseInterface|Promise|\Closure|PromiseInterface
     */
    public function post($endPoint)
    {
        $fullUrl = self::ONESIGNAL_API_URL.$endPoint;
        return $this->guzzleClient->post($fullUrl, $this->postParams);
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
     * @param $tag array
     * @return array
     * @throws ClientException
     */
    private function prepareTag($tag)
    {
        if (count($tag) < 2) {
            throw new ClientException("Tags must be at least key, value pair. Ex: ['key', 'value'] or ['key', '>=', 'value']");
        }
        $key = $tag[0];
        $operand = count($tag) > 2 ? $tag[1] : '=';
        $value = count($tag) > 2 ? $tag[2] : $tag[1];
        return ["field" => "tag", 'key' => $key ,  "relation" => $operand, 'value' => $value];
    }

    /**
     * @param array $params
     * @return $this
     */
    public function addParams(array $params = [])
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setParam($key, $value)
    {
        $this->addParams([$key => $value]);
        return $this;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setParam('headings', [
            $this->notificationLanguage => $title
        ]);
        return $this;
    }

    /**
     * @param $key
     * @param $relation
     * @param $value
     * @return $this
     */
    public function addFilter($key, $relation, $value, $valueKey = 'value')
    {
        $this->filters[] = ['field' => $key, 'relation' => $relation, $valueKey => $value];
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
     * @param $id
     * @param $text
     * @param null $icon
     * @return $this
     */
    public function addButton($buttonId, $text, $icon = null)
    {
        $newButton = ['id' => $buttonId, 'text' => $text];
        if ($icon) {
            $newButton['icon'] = $icon;
        }
        $this->buttons[] = $newButton;
        return $this;
    }

    /**
     * @param $id
     * @param $text
     * @param null $url
     * @param null $icon
     * @return $this
     */
    public function addWebButton($buttonId, $text, $url = null, $icon = null)
    {
        $newButton = ['id' => $buttonId, 'text' => $text];
        if ($icon) {
            $newButton['icon'] = $icon;
        }
        if ($url) {
            $newButton['url'] = $url;
        }
        $this->webButtons[] = $newButton;
        return $this;
    }
}
