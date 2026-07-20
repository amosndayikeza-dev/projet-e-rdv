<?php


/**
 * =====================================================================
 * Classe Controller (Classe mère abstraite)
 * =====================================================================
 * 
 * Tous les contrôleurs (Web et API) héritent de cette classe.
 * Elle fournit deux méthodes essentielles :
 * - renderView() : Affiche une vue HTML (pour le Web).
 * - sendJson() : Retourne une réponse JSON (pour l'API).
 * 
 * @package Core
 * @version 1.0
 */

namespace Core;

abstract class Controller
{
    /**
     * Affiche une vue (template HTML) avec des données.
     * 
     * Extrait le tableau $data en variables individuelles (extract)
     * pour les rendre directement utilisables dans la vue.
     * 
     * @param string $viewPath Chemin de la vue (relatif à 'views/'), sans extension.
     *                         Exemple : 'auth/login' -> views/auth/login.php
     * @param array $data Tableau associatif de variables à passer à la vue.
     * 
     * @throws \Exception Si le fichier de vue n'existe pas.
     */
    protected function renderView(string $viewPath, array $data = []): void
    {
        extract($data);
        $fullPath = __DIR__ . "/../../views/" . $viewPath . ".php";
        if (file_exists($fullPath)) {
            require_once $fullPath;
        } else {
            throw new \Exception("Vue introuvable : " . $viewPath);
        }
    }

    /**
     * Envoie une réponse JSON standardisée au client.
     * 
     * Configure automatiquement le code HTTP et le type de contenu.
     * 
     * @param array $data Tableau de données à encoder en JSON.
     * @param int $statusCode Code HTTP (200, 400, 401, 403, 409, 500, etc.).
     */
    protected function sendJson(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}