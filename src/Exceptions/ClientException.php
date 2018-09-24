<?php
namespace KCE\OneSignal\Exceptions;

class ClientException extends \Exception
{
    /**
     * ClientException constructor.
     */
    public function __construct($message)
    {
        parent::__construct($message, 400);
    }
}
