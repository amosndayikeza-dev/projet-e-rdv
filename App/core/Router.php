// Analyse des URLs (ex: /auth/register)
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

    /**
     * Enregistre une route GET.
     *
     * @param string $uri              ex: '/connexion', '/api/family/members'
     * @param string $action           ex: 'App\Controllers\Web\AuthController@showLogin'
     * @param array  $middlewares      ex: ['AuthMiddleware', 'ChefMiddleware']
     */
    public function get(string $uri, string $action, array $middlewares = []): void
    {
        $this->addRoute('GET', $uri, $action, $middlewares);
    }

    /** Enregistre une route POST (formulaires, création de données...). */
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

    /**
     * Méthode interne commune à get()/post()/put()/delete().
     * On normalise l'URI (retire les "/" en trop) pour que '/connexion'
     * et '/connexion/' soient traités comme identiques.
     */
    private function addRoute(string $method, string $uri, string $action, array $middlewares): void
    {
        $uri = $this->normalize($uri);

        $this->routes[$method][$uri] = [
            'action'      => $action,       // ex: "App\Controllers\Web\AuthController@showLogin"
            'middlewares' => $middlewares,   // ex: ["AuthMiddleware"]
        ];
    }

    private function normalize(string $uri): string
    {
        $trimmed = trim($uri, '/');
        return $trimmed === '' ? '/' : '/' . $trimmed;
    }

    /**
     * Le cœur du Router : appelé UNE SEULE FOIS par public/index.php
     * pour chaque requête HTTP entrante.
     *
     * @param string $requestMethod ex: $_SERVER['REQUEST_METHOD']  -> "GET"
     * @param string $requestUri    ex: $_SERVER['REQUEST_URI']     -> "/api/cards/verify?x=1"
     */
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

        // Chaque middleware décide lui-même de laisser passer ou de
        // bloquer (redirect, http_response_code(401)+exit, etc.).
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