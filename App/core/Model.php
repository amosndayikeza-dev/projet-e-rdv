<?php
declare(strict_types=1);

/**
 * =====================================================================
 * Classe Model (Classe mère abstraite)
 * =====================================================================
 * 
 * Tous les modèles métier (UserModel, FamilyGroupModel, etc.) doivent
 * hériter de cette classe.
 * 
 * Elle fournit l'accès à la base de données via la propriété protégée
 * `$this->db` et offre des méthodes génériques de lecture (findAll, findById).
 * 
 * @package Core
 * @version 1.0
 */

namespace Core;

abstract class Model
{
    /** @var \PDO Instance de la connexion PDO (provenant du Singleton Database) */
    protected \PDO $db;

    /** @var string Nom de la table associée au modèle (à définir dans l'enfant) */
    protected string $table;

    /**
     * Constructeur du modèle.
     * 
     * Récupère automatiquement la connexion PDO via le Singleton Database.
     * Cela évite d'instancier Database dans chaque modèle.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Récupère tous les enregistrements de la table.
     * 
     * Utilise la propriété `$this->table` définie dans l'enfant.
     * 
     * @return array Tableau associatif de tous les enregistrements.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Récupère un enregistrement par son identifiant primaire.
     * 
     * @param int $id L'identifiant de l'enregistrement.
     * @return array|false Tableau associatif ou false si non trouvé.
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}