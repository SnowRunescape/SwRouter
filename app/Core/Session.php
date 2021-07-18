<?php
namespace App\Core;

class Session
{
    private static $instance;

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public final static function getInstance()
    {
        if (is_null(Session::$instance)) {
            session_start();

            Session::$instance = new static;
        }

        return Session::$instance;
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
