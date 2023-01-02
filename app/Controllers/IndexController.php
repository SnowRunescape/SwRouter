<?php

namespace App\Controllers;

use App\Core\Request;

class IndexController extends BaseController
{
    public function index()
    {
        $this->render("index", [
            "params" => [
                "teste" => $this->request->_get->get("teste")
            ]
        ]);
    }
}
