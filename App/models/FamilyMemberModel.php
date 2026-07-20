<?php

/**
 * =====================================================================
 * FamilyMemberModel
 * =====================================================================
 * 
 * Gère les opérations sur la table `membres_famille`.
 * 
 * Responsabilités :
 * - Ajout d'un membre (conjoint, enfant, parent) à un groupe familial.
 * - Récupération de la liste des membres d'une famille.
 * - Liaison d'un membre avec son compte patient (lorsqu'il accepte l'invitation).
 * 
 * @package Models
 * @version 1.0
 */

namespace Models;

use Core\Model;

class FamilyMemberModel extends Model
{
    /** @var string Nom de la table associée */
    protected string $table = 'membres_famille';

    /**
     * Ajoute un nouveau membre au groupe familial.
     * 
     * @param int $groupe_id L'ID du groupe familial.
     * @param string $nom Nom du membre.
     * @param string $prenom Prénom du membre.
     * @param string $date_naissance Date de naissance (format YYYY-MM-DD).
     * @param string $lien_parente Lien de parenté (conjoint, enfant, parent).
     * @param bool $a_compte_propre TRUE si ce membre aura un compte de connexion.
     * @return int L'identifiant du membre créé.
     */
    public function create(
        int $groupe_id,
        string $nom,
        string $prenom,
        string $date_naissance,
        string $lien_parente,
        bool $a_compte_propre = false
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO membres_famille (groupe_familial_id, nom, prenom, date_naissance, lien_parente, a_compte_propre) 
            VALUES (:gid, :nom, :prenom, :date_naiss, :lien, :compte)
        ");
        $stmt->execute([
            'gid' => $groupe_id,
            'nom' => $nom,
            'prenom' => $prenom,
            'date_naiss' => $date_naissance,
            'lien' => $lien_parente,
            'compte' => $a_compte_propre ? 1 : 0
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Récupère tous les membres d'un groupe familial.
     * 
     * @param int $groupe_id L'ID du groupe familial.
     * @return array Liste des membres (tableau associatif).
     */
    public function findByGroupId(int $groupe_id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM membres_famille WHERE groupe_familial_id = :gid");
        $stmt->execute(['gid' => $groupe_id]);
        return $stmt->fetchAll();
    }

    /**
     * Lie un membre de la famille à son compte patient (après acceptation d'invitation).
     * 
     * @param int $memberId L'ID du membre dans la table `membres_famille`.
     * @param int $patientId L'ID du patient créé dans la table `patients`.
     */
    public function linkToPatient(int $memberId, int $patientId): void
    {
        $stmt = $this->db->prepare("
            UPDATE membres_famille SET patient_id = :pid WHERE id = :mid
        ");
        $stmt->execute(['pid' => $patientId, 'mid' => $memberId]);
    }
}
