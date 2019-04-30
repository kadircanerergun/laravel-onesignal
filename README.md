# Laravel OneSignal

[![Build Status](https://travis-ci.org/kadircanerergun/laravel-onesignal.svg?branch=master)](https://travis-ci.org/kadircanerergun/laravel-onesignal)

## Send push notifications easily with [OneSignal](https://onesignal.com)

## Contents

- [Installation](#installation)
- [Usage](#usage)
- [License](#license)

<a name="installation" />

## Installation

In order to add Laravel OneSignal to your project, just add

    composer require kce/onesignal-laravel

Composer auto discovery will register the provider. If dont use it add the following lines to "config/app.php"
    
    'providers' => [
            ...
            .
            .
            KCE\OneSignal\OneSignalServiceProvider::class
    ]
    ...
    .
    .,
    'aliases' => [
        ...
        ..
        .
        'OneSignalClient' => KCE\OneSignal\Facades\OneSignalClient::class
    ]
    
Publish the configuration
    
    php artisan vendor:publish --provider="KCE\OneSignal\OneSignalServiceProvider"
              

<a name="usage" />

## Usage

### Configuration

This package requires you to change the fields in the  `config/onesignal.php` file:

```php
return array (
    /*
     |--------------------------------------------------------------------------
     | One Signal App Id
     |--------------------------------------------------------------------------
     |
     | You can find in : Project > Settings > Key & ID's > ONESIGNAL APP ID
     |
     */
    'app_id' => env("ONESIGNAL_APP_ID", 'default_app_id'),

    /*
     |--------------------------------------------------------------------------
     | Rest API Key
     |--------------------------------------------------------------------------
     |
     | You can find in : Project > Settings > Key & ID's > REST API KEY
     |
     */
    'rest_api_key' => env("ONESIGNAL_REST_API_KEY", 'rest_api_key'),

    /*
     |--------------------------------------------------------------------------
     | User Auth Key
     |--------------------------------------------------------------------------
     |
     | You can find in : Profile > ACCOUNT & API KEYS > AUTH KEY
     |
     */
    'user_auth_key' => env("ONESIGNAL_USER_AUTH_KEY", 'user_auth_key'),
);
```

#### Send to ALL

Send notification to all subscribed devices

```php
\KCE\OneSignal\Facades\OneSignalClient::sendToAll('Notification message');
```

#### Send to Country

Send notification to specific country

```php
\KCE\OneSignal\Facades\OneSignalClient::sendToCountry('Notification message', 'TR'); // Country ISO Code
```
#### Send to Location

Send notification to a particular area-wide. Use radius in meters

```php
\KCE\OneSignal\Facades\OneSignalClient::sendToLocation('Notification message', 10000, 37.4247, 41.33933); // Use Lat, Long and Radius
```

#### Send to single user or users.

Send notification to player ids.

```php
\KCE\OneSignal\Facades\OneSignalClient::sendToUser('Notification message', "player_id"); // Single player id
```
or
```php
\KCE\OneSignal\Facades\OneSignalClient::sendToUser('Notification message', ["player_id1", 'player_id2]); // Multiple player ids
```

#### Send to segment
   
   Send notification to one or more segments
   
   ```php
   \KCE\OneSignal\Facades\OneSignalClient::sendToSegment('Notification message', "segment");
   //or
   \KCE\OneSignal\Facades\OneSignalClient::sendToSegment('Notification message', ["segment", "segment2"]);
   ```
   
#### Send to Tags

Send notification filter by tags

```php
\KCE\OneSignal\Facades\OneSignalClient::sendToTags('Notification message', ["user_id", "=", 15]); //will send the notification to user that tagges as user_id 15
```

### Add Data and/or Title To Notifications
```php
\KCE\OneSignal\Facades\OneSignalClient::setTitle("Notification Title")->setData([
        'key' => 'value'
    ])->sendToAll("Example Message");
```

### Scheduling
You can schedule notification for future date time.
```php
\KCE\OneSignal\Facades\OneSignalClient::setSchedule("2018-10-29 10:00")->sendToAll("Cumhuriyet Bayramı Kutlu Olsun!");
```

#### Schedule based on User's Timezone
Notification will deliver at a specific time-of-day in each users own timezone.
```php
\KCE\OneSignal\Facades\OneSignalClient::scheduleByUserTimezone("04:44PM")->sendToAll("This message will deliver based on user timezone on 04:44PM!");
```

### addTag / addOrTag
If you use addTag or addTags method it will put AND between tags. If you want to use multiple tags with "OR" connector, you should use addOrTag method.  
```php
$client = app('onesignal');
$client->addTag(['fav_color', 'green'])->addOrTag(['fav_color', 'red'])->sendToAll("Users like yellow or red");
```

### Send by First / Last Session
Typically filters has 3 parameters (key, relation, value) but some filters like last_session, first session has their own value keys. You can add specific value key as 4th parameter.  If you want to send notification by users last or first active time you can use  addFilter method by value key.
```php
$client = app('onesignal');
$client->addFilter('last_session', '>', '48', 'hours_ago')->sendToAll("Notification by last active"); // Users who last session time more than 48 Hours.
$client->addFilter('last_session', '<', '48', 'hours_ago')->sendToAll("Notification by last active"); // Users who last session time less than 48 Hours.
$client->addFilter('first_session', '>', '48', 'hours_ago')->sendToAll("Notification by last active"); // Users who first session time more than 48 Hours.
$client->addFilter('first_session', '<', '48', 'hours_ago')->sendToAll("Notification by last active"); // Users who last session time less than 48 Hours.

```

### Multi Language Notifications
Default notification language is English. But if you want you can send notification to each user in their language. Just add an language => message array as message to any of send methods. 
```php
$client = app('onesignal');
$client
    ->setTitle([
             'en' => 'English Title',
             'tr' => 'Türkçe Başlık',
         ])
    ->sendToAll([
             'en' => 'English notification message',
             'tr' => 'Türkçe bildirim mesajı'
         ]);

```

## MORE OPTIONS

You can use method chaining...
```php
$client = app('onesignal');

$client->addTag(['user_id', 15)->addTag('notifiable', 1)->setTitle("Test Notif")->sendToAll("New Message");
```


<a name="license" />

## License

Laravel OneSignal is free software distributed under the terms of the MIT license.

Feel free to send pull requests.