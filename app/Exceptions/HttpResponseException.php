<?php

namespace App\Exceptions;

use Exception;

class HttpResponseException extends \Exception
{
    public function __construct($code = 200, $message = "", Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
