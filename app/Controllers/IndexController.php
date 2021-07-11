<?php
namespace App\Controllers;

use App\Core\Request;

class IndexController extends BaseController
{
    public function index(Request $request)
    {
        $this->render("index");
    }
}
