<?php

/**
 * =====================================================================
 * InvitationModel
 * =====================================================================
 * 
 * Gère les opérations sur la table `invitations`.
 * 
 * Responsabilités :
 * - Génération d'un token d'invitation pour un membre.
 * - Vérification de la validité d'un token (existence + non expiré).
 * - Marquage d'une invitation comme "UTILISÉE" une fois le compte créé.
 * 
 * @package Models
 * @version 1.0
 */

namespace Models;

use Core\Model;

class InvitationModel extends Model
{
    /** @var string Nom de la table associée */
    protected string $table = 'invitations';

    /**
     * Crée une nouvelle invitation dans la base de données.
     * 
     * @param int $groupe_id L'ID du groupe familial.
     * @param string $email L'email du destinataire de l'invitation.
     * @param string $token Le token unique (généré par bin2hex(random_bytes(32))).
     * @param string $expiration Date d'expiration (format 'Y-m-d H:i:s').
     * @return int L'identifiant de l'invitation créée.
     */
    public function create(int $groupe_id, string $email, string $token, string $expiration): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO invitations (groupe_familial_id, email_destinataire, token, date_expiration) 
            VALUES (:gid, :email, :token, :exp)
        ");
        $stmt->execute([
            'gid' => $groupe_id,
            'email' => $email,
            'token' => $token,
            'exp' => $expiration
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Recherche une invitation par son token.
     * 
     * @param string $token Le token unique.
     * @return array|false Les données de l'invitation ou false si non trouvée.
     */
    public function findByToken(string $token): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM invitations WHERE token = :token");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch();
    }

    /**
     * Marque une invitation comme "UTILISÉE" après la création du compte.
     * 
     * @param string $token Le token unique de l'invitation.
     */
    public function markAsUsed(string $token): void
    {
        $stmt = $this->db->prepare("UPDATE invitations SET statut = 'UTILISE' WHERE token = :token");
        $stmt->execute(['token' => $token]);
    }
}
