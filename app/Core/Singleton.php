<?php

namespace App\Core;

abstract class Singleton
{
    final public static function getInstance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new static();
        }

        return $instance;
    }

    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
}
