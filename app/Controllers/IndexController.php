<?php

namespace App\Controllers;

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
