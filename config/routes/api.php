<?php
    declare(strict_types=1);

    /**
     * Routes API (Endpoints REST JSON)
     * 
     * Ces routes sont appelées via AJAX par le Frontend (JavaScript)
     * ou par une future application mobile. Elles retournent exclusivement
     * des données au format JSON avec les codes HTTP appropriés.
     * 
     * Le préfixe '/api' est géré automatiquement par le Router.
     * Structure : 'METHODE chemin' => [ ... ]
     * 
     * @package Config\Routes
     * @version 1.0
     */

    return [
        // --- Endpoints publics (sans authentification) ---
        
        /**
         * Vérification de l'unicité de la carte d'assurance.
         * Appelé par le JavaScript lors de l'étape 2 d'inscription.
         * Méthode HTTP : GET
         * Paramètre GET : numero_carte
         * Retour : JSON { success, exists, chef_nom }
         */
        'GET cards/verify' => [
            'controller' => 'Api\\AuthApiController', 
            'action' => 'verifyCard'
        ],
        
        /**
         * Inscription complète du Chef de famille.
         * Reçoit les données des étapes 1 et 2.
         * Méthode HTTP : POST
         * Corps (JSON) : nom, prenom, telephone, email, password, date_naissance, type_document, numero_carte
         */
        'POST auth/register' => [
            'controller' => 'Api\\AuthApiController', 
            'action' => 'register'
        ],
        
        /**
         * Authentification de l'utilisateur.
         * Méthode HTTP : POST
         * Corps (JSON) : login (email ou téléphone), password
         */
        'POST auth/login' => [
            'controller' => 'Api\\AuthApiController', 
            'action' => 'login'
        ],
        
        // --- Endpoints protégés (authentification obligatoire) ---
        
        /**
         * Déconnexion (invalide la session).
         * Méthode HTTP : POST
         * Middleware : AuthMiddleware
         */
        'POST auth/logout' => [
            'controller' => 'Api\\AuthApiController', 
            'action' => 'logout', 
            'middlewares' => ['AuthMiddleware']
        ],
        
        /**
         * Récupère la liste des membres de la famille du Chef.
         * Méthode HTTP : GET
         * Middlewares : AuthMiddleware + ChefMiddleware
         */
        'GET family/members' => [
            'controller' => 'Api\\FamilyApiController', 
            'action' => 'getMembers', 
            'middlewares' => ['AuthMiddleware', 'ChefMiddleware']
        ],
        
        /**
         * Ajoute un nouveau membre (conjoint, enfant) dans la famille.
         * Si 'a_compte_propre' est TRUE, un token d'invitation est généré.
         * Méthode HTTP : POST
         * Middlewares : AuthMiddleware + ChefMiddleware
         */
        'POST family/members' => [
            'controller' => 'Api\\FamilyApiController', 
            'action' => 'addMember', 
            'middlewares' => ['AuthMiddleware', 'ChefMiddleware']
        ],
    ];
