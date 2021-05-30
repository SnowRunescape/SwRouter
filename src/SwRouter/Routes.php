<?php
namespace SwRouter;

class Routes {
    const APP_ROUTES = [
        '/' => 'IndexController@index',
        '/api/query/{server}' => 'IndexController@server'
    ];
}