<?php
    /**
     * Routes Web (Pages HTML)
     * 
     * Ce fichier définit la correspondance entre les URLs accessibles
     * dans le navigateur et les contrôleurs/actions à exécuter.
     * 
     * Le préfixe des routes est géré automatiquement par le Router.
     * Structure d'une route :
     *   'nom_url' => [
     *       'controller' => 'NomDuControleur',
     *       'action' => 'nomDeLaMethode',
     *       'middlewares' => ['Liste', 'Des', 'Middlewares']
     *   ]
     * 
     * @package Config\Routes
     * @version 1.0
     */

    return [
        // Page d'accueil : redirige vers le formulaire de connexion
        'home' => [
            'controller' => 'Web\\AuthController', 
            'action' => 'showLogin'
        ],
        
        // Formulaire de connexion (protégé par GuestMiddleware)
        'login' => [
            'controller' => 'Web\\AuthController', 
            'action' => 'showLogin', 
            'middlewares' => ['GuestMiddleware']
        ],
        
        // Étape 1 de l'inscription (infos personnelles)
        'registerStep1' => [
            'controller' => 'Web\\AuthController', 
            'action' => 'showRegisterStep1', 
            'middlewares' => ['GuestMiddleware']
        ],
        
        // Étape 2 de l'inscription (saisie de la carte d'assurance)
        'registerStep2' => [
            'controller' => 'Web\\AuthController', 
            'action' => 'showRegisterStep2', 
            'middlewares' => ['GuestMiddleware']
        ],
        
        // Page d'acceptation d'invitation (le membre crée son mot de passe)
        'accept-invitation' => [
            'controller' => 'Web\\AuthController', 
            'action' => 'showAcceptInvitation'
        ],
        
        // Tableau de bord (protégé par AuthMiddleware)
        'dashboard' => [
            'controller' => 'Web\\DashboardController', 
            'action' => 'showDashboard', 
            'middlewares' => ['AuthMiddleware']
        ],
        
        // Déconnexion (détruit la session)
        'logout' => [
            'controller' => 'Web\\AuthController', 
            'action' => 'logout', 
            'middlewares' => ['AuthMiddleware']
        ],
    ];
