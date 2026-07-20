<?php
/**
 * =====================================================================
 * Classe Router
 * =====================================================================
 * 
 * Analyse l'URL de la requête et exécute le contrôleur et l'action
 * correspondants. Il différencie les routes Web (pages HTML) des
 * routes API (JSON) selon le préfixe '/api'.
 * 
 * Fonctionnement :
 * 1. Nettoyer l'URI.
 * 2. Détecter le préfixe (api ou non).
 * 3. Charger le fichier de routes correspondant (web.php ou api.php).
 * 4. Parcourir les routes pour trouver une correspondance.
 * 5. Si trouvée, exécuter les middlewares, puis le contrôleur.
 * 
 * @package Core
 * @version 1.0
 */

namespace Core;

class Router
{
    /**
     * Charge un fichier de routes et retourne le tableau de mapping.
     * 
     * @param string $file Chemin absolu vers le fichier PHP de routes.
     * @return array Tableau des routes [ 'GET path' => ['controller' => ..., 'action' => ...] ].
     */
    private function loadRoutes(string $file): array
    {
        return require_once $file;
    }

    /**
     * Point d'entrée principal du routeur.
     * 
     * @param string $uri L'URI de la requête (ex: 'api/cards/verify' ou 'login').
     * @param string $method La méthode HTTP (GET, POST, etc.).
     */
    public function dispatch(string $uri, string $method): void
    {
        // Nettoyer l'URI : supprimer les paramètres GET et les slashs superflus
        $uri = trim(parse_url($uri, PHP_URL_PATH), '/');
        if (empty($uri)) {
            $uri = 'home';
        }

        // ============================================================
        // 1. Détection de l'API (préfixe 'api/')
        // ============================================================
        $isApi = strpos($uri, 'api/') === 0;
        if ($isApi) {
            // Supprimer le préfixe 'api/' pour correspondre aux routes définies dans api.php
            $uri = substr($uri, 4);
            $routes = $this->loadRoutes(__DIR__ . '/../../config/routes/api.php');
        } else {
            $routes = $this->loadRoutes(__DIR__ . '/../../config/routes/web.php');
        }

        // ============================================================
        // 2. Recherche de correspondance
        // ============================================================
        $matched = false;
        foreach ($routes as $route => $config) {
            // Extraire la méthode HTTP et le chemin depuis la clé (ex: 'GET login')
            $routeMethod = explode(' ', $route)[0] ?? 'GET';
            $routePath = explode(' ', $route)[1] ?? $route;

            // Vérifier la correspondance : chemin exact + méthode identique (ou 'ANY')
            if ($routePath === $uri && ($routeMethod === $method || $routeMethod === 'ANY')) {
                $matched = true;
                $controllerClass = '\\Controllers\\' . $config['controller'];
                $action = $config['action'];
                $middlewares = $config['middlewares'] ?? [];

                // ============================================================
                // 3. Exécution des Middlewares (filtres de sécurité)
                // ============================================================
                foreach ($middlewares as $middleware) {
                    $middlewareClass = '\\Middleware\\' . $middleware;
                    if (class_exists($middlewareClass)) {
                        $m = new $middlewareClass();
                        $m->handle(); // La méthode handle() peut rediriger ou bloquer la requête
                    }
                }

                // ============================================================
                // 4. Instanciation du contrôleur et exécution de l'action
                // ============================================================
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $action)) {
                        $controller->$action();
                        return; // Fin de l'exécution
                    }
                }
                break;
            }
        }

        // ============================================================
        // 5. Aucune correspondance trouvée -> 404
        // ============================================================
        if (!$matched) {
            http_response_code(404);
            echo "404 - Page ou endpoint non trouvé";
        }
    }
}