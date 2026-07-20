<?php
declare(strict_types=1);

/**
 * =====================================================================
 * Classe Database
 * =====================================================================
 * 
 * Implémente le design pattern Singleton pour garantir une connexion
 * unique à la base de données MySQL pendant toute la durée de la requête.
 * 
 * Pourquoi Singleton ?
 * - Évite la multiplication des connexions (optimisation des performances).
 * - Centralise la configuration (modification unique dans un seul fichier).
 * - Fournit un accès global à l'objet PDO dans tous les Models.
 * 
 * @package Core
 * @version 1.0
 * @author Lead Developer
 */

namespace Core;

class Database
{
    /** @var self|null Instance unique de la classe (pattern Singleton) */
    private static ?self $instance = null;

    /** @var \PDO Instance de l'objet PDO pour les requêtes SQL */
    private \PDO $pdo;

    /**
     * Constructeur privé (empêche l'instanciation directe).
     * 
     * Charge la configuration depuis 'config/database.php' et initialise
     * la connexion PDO avec les attributs suivants :
     * - ERRMODE_EXCEPTION : Les erreurs SQL lèvent des exceptions.
     * - DEFAULT_FETCH_MODE : Les résultats sont retournés en tableaux associatifs.
     */
    private function __construct()
    {
        // Chargement du fichier de configuration
        $config = require_once __DIR__ . '/../../config/database.php';
        
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        $this->pdo = new \PDO($dsn, $config['user'], $config['password']);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    /**
     * Récupère l'instance unique de la connexion (Singleton).
     * 
     * Si l'instance n'existe pas, elle est créée pour la première fois.
     * Sinon, l'instance existante est retournée.
     * 
     * @return self L'instance unique de la classe Database.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retourne l'objet PDO pour exécuter des requêtes SQL.
     * 
     * Cette méthode est utilisée par tous les Models pour interagir
     * avec la base de données.
     * 
     * @return \PDO L'objet PDO actif.
     */
    public function getConnection(): \PDO
    {
        return $this->pdo;
    }
}