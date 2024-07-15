<?php

define("APPLICATION_START", microtime(true));

define("ROOT_PATH", __DIR__ . "/../");
define("APPLICATION_PATH", ROOT_PATH . "/app");

use Illuminate\Http\Request;

require_once ROOT_PATH ."/vendor/autoload.php";

(require_once ROOT_PATH . "/bootstrap/app.php")
    ->handleRequest(Request::capture());
