<?php
namespace App\Controllers;

use App\Exceptions\RouterException;

class BaseController
{
    const HTTP_RESPONSE = [
        400 => "Bad Request",
        401 => "Unauthorized",
        404 => "Not Found",
        500 => "Internal Server Error"
    ];

    private static $template;
    private static $title;
    private $view;

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
        } else if (is_int($mixed)) {
            $response["status"] = $mixed;
        } else {
            $response["status"] = 400;
        }

        if (
            empty($response["d"]) &&
            array_key_exists($response["status"], BaseController::HTTP_RESPONSE) &&
            !in_array($response["status"], [200, 201])
        ) {
            $response["d"] = [
                "message" => BaseController::HTTP_RESPONSE[$response["status"]]
            ];
        }

        http_response_code($response["status"]);

        exit(json_encode($response["d"], JSON_UNESCAPED_UNICODE));
    }

    public function redirect($url)
    {
        exit(header("Location: {$url}"));
    }

    public function partial($path, $params = [])
    {
        $viewPath = $this->getPath($path);

        if ($viewPath) {
            include $viewPath;
        }
    }

    public static function setTemplate($path)
    {
        $viewPath = BaseController::getPath($path);

        if ($viewPath !== false) {
            BaseController::$template = $path;
        }
    }

    public static function setTitle($title)
    {
        BaseController::$title = $title;
    }

    private function template()
    {
        if (!empty(BaseController::$template)) {
            ob_start();
            include BaseController::getPath(BaseController::$template);
            $template = ob_get_clean();

            $title = BaseController::$title ?? "{{title}}";

            $template = str_replace("{{title}}", $title, $template);
            $this->view = str_replace("{{body}}", $this->view, $template);
        }
    }

    private function view($path, $params = [])
    {
        $viewPath = BaseController::getPath($path);

        if ($viewPath === false) {
            throw new RouterException("View not found", 404);
        }

        BaseController::$template = null;

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

    private function initParams($params = [])
    {
        $params = array_merge([
            "template" => null,
            "title" => null,
            "params" => []
        ], $params);

        BaseController::setTemplate($params["template"]);
        BaseController::setTitle($params["title"]);

        foreach ($params["params"] as $key => $value) {
            $this->$key = $value;
        }
    }
}
