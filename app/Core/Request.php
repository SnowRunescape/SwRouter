<?php
namespace App\Core;

class Request implements \App\Interfaces\Request
{
    public function redirect($url)
    {
        exit(header("Location: {$url}"));
    }

    public function session()
    {
        return Session::getInstance();
    }
}
