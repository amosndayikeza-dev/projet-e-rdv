<?php

/**
 * Fichier de configuration de la base de données
 * 
 * Ce fichier retourne un tableau associatif contenant les paramètres
 * de connexion à MySQL. Il est chargé par la classe Database (Singleton)
 * lors du premier appel.
 * 
 * @package Config
 * @version 1.0
 */

return [
    /** @var string Hôte du serveur MySQL (généralement localhost en développement) */
    'host' => 'localhost',
    
    /** @var string Nom de la base de données créée via le script SQL */
    'dbname' => 'e_rdv_burundi',
    
    /** @var string Utilisateur MySQL (par défaut 'root' sous XAMPP) */
    'user' => 'root',
    
    /** @var string Mot de passe MySQL (laisser vide sous XAMPP) */
    'password' => ''
];
