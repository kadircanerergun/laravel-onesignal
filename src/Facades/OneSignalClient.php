<?php
namespace KCE\OneSignal\Facades;

use Illuminate\Support\Facades\Facade;

class OneSignalClient extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'onesignal';
    }
}
