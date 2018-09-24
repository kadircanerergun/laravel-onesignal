<?php
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