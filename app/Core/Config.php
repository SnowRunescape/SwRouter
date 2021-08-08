<?php
namespace App\Core;

class Config
{
    const SECRETS_FILE = __DIR__ . '/../../config/secrets.json';

    private static $attributes;

    public static function getInstance()
    {
        if (is_null(Config::$attributes)) {
            Config::$attributes = [];

            if (file_exists(Config::SECRETS_FILE)) {
                $content = file_get_contents(Config::SECRETS_FILE);
                
                Config::$attributes = json_decode($content, true);
            }
        }

        return new static;
    }

    public function get($key)
    {
		return Config::$attributes[$key] ?? null;
    }
}
