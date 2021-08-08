<?php
namespace App\Core;

class Database
{
    const PDO_OPTIONS = [
        \PDO::ATTR_TIMEOUT => 5,
        \PDO::ATTR_ERRMODE => true,
        \PDO::ERRMODE_EXCEPTION  => true,
    ];

    private static $instance;

    public static function getInstance()
    {
        $config = Config::getInstance()->get("database");

        if (is_null(Database::$instance)) {
            Database::$instance = new \PDO(
                "mysql:host={$config["host"]};dbname={$config["database"]};charset=utf8",
                $config["username"],
                $config["password"],
                Database::PDO_OPTIONS
            );
        }

        return Database::$instance;
    }
}
