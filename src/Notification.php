<?php
namespace KCE\OneSignal;

use KCE\OneSignal\Exceptions\ClientException;

class Notification
{
    /**
     * URL Path for notifications
     */
    const NOTIFICATIONS_PATH = "/notifications";

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
     * @var Client;
     */
    private $client;


    /**
     * @var string $notificationLanguage
     * Default notification lang
     */
    private $notificationLanguage = "en";

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
     * @param $message
     * @param $tags
     * @param null $title
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendToTags($message, $tags)
    {
        $this->addTags($tags);
        return $this->sendNotification($message);
    }

    /**
     * @param $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendToAll($message)
    {
        $params = array(
            'included_segments' => array('All')
        );
        return $this->sendNotification($message, $params);
    }

    /**
     * @param $message
     * @param $segments
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendToSegments($message, $segments)
    {
        $params = array(
            'included_segments' => is_array($segments) ? $segments : [$segments]
        );
        return $this->sendNotification($message, $params);
    }

    /**
     * @param $message
     * @param $country
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendToCountry($message, $country)
    {
        $this->addFilter('country', '=', strtoupper($country));
        return $this->sendNotification($message);
    }

    /**
     * @param $message
     * @param $userId string|array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendToUser($message, $userId)
    {
        $params = array(
            'include_player_ids' => is_array($userId) ? $userId : [$userId]
        );
        return $this->sendNotification($message, $params);
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
        $params = array(
            'filters' => [
                [
                    'field' => 'location',
                    'radius' => $radiusInMeter,
                    'lat' => $lat,
                    'long' => $long
                ]
            ]
        );
        return $this->sendNotification($message, $params);
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
    private function sendNotification($message, array $params = [])
    {
        if (!is_array($message)) {
            $message = [
                $this->notificationLanguage => $message
            ];
        }
        $params['contents'] = $message;
        $params['app_id'] = $this->client->getAppId();
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
        $this->client->postParams['body'] = json_encode($parameters);
        return $this->client->post(self::NOTIFICATIONS_PATH);
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
            "operator" => "OR"
        ];
        $this->addTag($tag);
        return $this;
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
     * @param $title string|array
     * @return $this
     */
    public function setTitle($title)
    {
        if (!is_array($title)) {
            $title = [
                $this->notificationLanguage => $title
            ];
        }
        $this->setParam('headings', $title);
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
