<?php

namespace App\Core;

use App\Core\Session;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;

class Request implements \App\Interfaces\Request
{
    public $_get;
    public $_post;
    public $_header;
    public $_cookie;

    public function __construct()
    {
        $this->_get = new ParameterBag($_GET);
        $this->_post = new ParameterBag($_POST);
        $this->_header = new HeaderBag(apache_request_headers());
        $this->_cookie = new ParameterBag($_COOKIE);
    }

    public function redirect($url)
    {
        exit(header("Location: {$url}"));
    }

    public function session()
    {
        return Session::getInstance();
    }

    public function getHttpProtocol()
    {
        return isset($_SERVER["HTTPS"]) ? "https" : "http";
    }

    public function getHttpHost()
    {
        return strtolower($_SERVER["SERVER_NAME"]);
    }

    public function getHttpPath()
    {
        return $_SERVER["REQUEST_URI"];
    }
}
