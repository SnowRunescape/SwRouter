<?php

namespace App\Core;

class Session extends Singleton
{
    protected function __construct()
    {
        session_start();
    }

    public function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function put($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function del($key)
    {
        unset($_SESSION[$key]);
    }
}
