<?php

namespace App\Core;

class Router
{
    private array $routes = [
        'GET'  => [],
        'POST' => [],
        'PUT'  => [],
        'DELETE' => [],
    ];

    public function get(string $uri, string $action, array $middlewares = []): void
    {
        $this->addRoute('GET', $uri, $action, $middlewares);
    }

    public function post(string $uri, string $action, array $middlewares = []): void
    {
        $this->addRoute('POST', $uri, $action, $middlewares);
    }

    public function put(string $uri, string $action, array $middlewares = []): void
    {
        $this->addRoute('PUT', $uri, $action, $middlewares);
    }

    public function delete(string $uri, string $action, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $uri, $action, $middlewares);
    }

    private function addRoute(string $method, string $uri, string $action, array $middlewares): void
    {
        $uri = $this->normalize($uri);

        $this->routes[$method][$uri] = [
            'action'      => $action,
            'middlewares' => $middlewares,
        ];
    }

    private function normalize(string $uri): string
    {
        $trimmed = trim($uri, '/');
        return $trimmed === '' ? '/' : '/' . $trimmed;
    }

    public function dispatch(string $requestMethod, string $requestUri): void
    {
        $method = strtoupper($requestMethod);

        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $path = $this->normalize($path);

        $route = $this->routes[$method][$path] ?? null;

        if ($route === null) {
            $this->respondNotFound();
            return;
        }

        foreach ($route['middlewares'] as $middlewareName) {
            $this->runMiddleware($middlewareName);
        }

        [$controllerClass, $methodName] = $this->parseAction($route['action']);

        if (!class_exists($controllerClass)) {
            $this->respondServerError("Contrôleur introuvable : {$controllerClass}");
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            $this->respondServerError("Méthode introuvable : {$controllerClass}@{$methodName}");
            return;
        }

        $controller->$methodName();
    }

    private function runMiddleware(string $middlewareName): void
    {
        $fqcn = "App\\Middleware\\{$middlewareName}";

        if (!class_exists($fqcn)) {
            $this->respondServerError("Middleware introuvable : {$fqcn}");
            exit;
        }

        $middleware = new $fqcn();

        if (!method_exists($middleware, 'handle')) {
            $this->respondServerError("Le middleware {$fqcn} doit définir une méthode handle().");
            exit;
        }

        $middleware->handle();
    }

    private function parseAction(string $action): array
    {
        if (!str_contains($action, '@')) {
            $this->respondServerError("Format d'action invalide : {$action} (attendu 'Controller@methode')");
            exit;
        }

        return explode('@', $action, 2);
    }

    private function respondNotFound(): void
    {
        http_response_code(404);
        echo '404 - Page non trouvée';
    }

    private function respondServerError(string $message): void
    {
        http_response_code(500);
        echo '500 - Erreur serveur : ' . htmlspecialchars($message);
    }
}