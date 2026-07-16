# Classe mère des modèles (CRUD basique)
<?php

namespace App\Core;

use PDO;
abstract class Model
{
    
    protected PDO $db;

    /**
     * Le constructeur est appelé automatiquement à chaque fois qu'on
     * fait `new UserModel()`, `new FamilyGroupModel()`, etc.
     * Il va chercher la connexion PDO unique auprès du Singleton
     * Database, et la range dans $this->db pour que les méthodes
     * filles (findByEmail, create, etc.) puissent l'utiliser.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }

    protected function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    protected function commit(): bool
    {
        return $this->db->commit();
    }

    protected function rollBack(): bool
    {
        return $this->db->rollBack();
    }
}