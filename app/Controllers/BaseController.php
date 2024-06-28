<?php

namespace App\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\RouterException;

class BaseController
{
    const HTTP_RESPONSE = [
        400 => "Bad Request",
        401 => "Unauthorized",
        404 => "Not Found",
        500 => "Internal Server Error"
    ];

    protected Request $request;
    private static $template;
    private static $title;
    private $view;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function render($path, $params = [])
    {
        $this->view($path, $params);
        $this->template();

        echo $this->view;
    }

    public function renderJson($mixed)
    {
        header("Content-Type: application/json");

        $response = [
            "status" => 200,
            "d" => [],
            "redirect" => false
        ];

        if (is_array($mixed)) {
            $response = array_merge($response, $mixed);
        } elseif (is_int($mixed)) {
            $response["status"] = $mixed;
        } else {
            $response["status"] = 400;
        }

        if (
            empty($response["d"]) &&
            array_key_exists($response["status"], self::HTTP_RESPONSE) &&
            !in_array($response["status"], [200, 201])
        ) {
            $response["d"] = [
                "message" => self::HTTP_RESPONSE[$response["status"]]
            ];
        }

        http_response_code($response["status"]);

        exit(json_encode($response["d"], JSON_UNESCAPED_UNICODE));
    }

    public function partial($path, $params = [])
    {
        $viewPath = $this->getPath($path);

        if ($viewPath) {
            $this->initParams($params, true);

            include $viewPath;
        }
    }

    public static function setTemplate($path)
    {
        $viewPath = self::getPath($path);

        if ($viewPath !== false) {
            self::$template = $path;
        }
    }

    public static function setTitle($title)
    {
        self::$title = $title;
    }

    private function template()
    {
        if (!empty(self::$template)) {
            ob_start();
            include self::getPath(self::$template);
            $template = ob_get_clean();

            $title = self::$title ?? "{{title}}";

            $template = str_replace("{{title}}", $title, $template);
            $this->view = str_replace("{{body}}", $this->view, $template);
        }
    }

    private function view($path, $params = [])
    {
        $viewPath = self::getPath($path);

        if ($viewPath === false) {
            throw new RouterException("View {$path} not found", 404);
        }

        self::$template = null;

        $this->initParams($params);

        ob_start();
        include $viewPath;
        $this->view = ob_get_clean();
    }

    private static function getPath($path)
    {
        if (!is_string($path)) {
            return false;
        }

        $path = rtrim($path, "/");

        $viewPath = APPLICATION_PATH . "/Views/{$path}.phtml";

        return file_exists($viewPath) ? $viewPath : false;
    }

    private function initParams($params = [], $onlyParams = false)
    {
        $params = array_merge([
            "template" => null,
            "title" => null,
            "params" => []
        ], $params);

        if (!$onlyParams) {
            self::setTemplate($params["template"]);
            self::setTitle($params["title"]);
        }

        foreach ($params["params"] as $key => $value) {
            $this->$key = $value;
        }
    }
}
