<?php

namespace App\Http;

final class Router
{
    private array $routes;
    private array $factories;
    private Csrf $csrf;
    private string $base;

    public function __construct(array $routes, array $factories, Csrf $csrf, string $base)
    {
        $this->routes = $routes;
        $this->factories = $factories;
        $this->csrf = $csrf;
        $this->base = $base;
    }

    public function dispatch(string $method, string $uri) : void
    {
        $requestPath = parse_url($uri, PHP_URL_PATH);
        $path = '/' . trim(substr($requestPath,  strlen($this->base)), '/');

        foreach ($this->routes as [$routeMethod, $pattern, $class, $action]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';

            if (!preg_match($regex, $path, $matches)) {
                continue;
            }

            if ($method === 'POST' && !$this->csrf->isValid($_POST['csrf_token'] ?? null)) {
                header('Location: ' . $this->base . '/login');
                exit;
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $controller = ($this->factories[$class])();
            $controller->$action(...$params);

            return;
        }

        http_response_code(404);
        echo 'Page not found';
    }
}
