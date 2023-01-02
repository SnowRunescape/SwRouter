<?php

namespace App\Core;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    public static function init()
    {
        $capsule = new Capsule;
        $capsule->addConnection([
           "driver" => "mysql",
           "host" => getenv("DATABASE_HOST"),
           "database" => getenv("DATABASE_DB"),
           "username" => getenv("DATABASE_USERNAME"),
           "password" => getenv("DATABASE_PASSWORD")
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
