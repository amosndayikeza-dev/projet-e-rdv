<?php

/**
 * =====================================================================
 * UserModel
 * =====================================================================
 * 
 * Gère toutes les opérations liées à la table `patients`.
 * 
 * Responsabilités :
 * - Création d'un nouveau patient (inscription).
 * - Recherche par email, téléphone ou combinaison des deux.
 * - Vérification de l'existence d'un utilisateur pour la connexion.
 * - Création d'un compte depuis une invitation.
 * 
 * @package Models
 * @version 1.0
 */

namespace Models;

use Core\Model;

class UserModel extends Model
{
    /** @var string Nom de la table associée */
    protected string $table = 'patients';

    /**
     * Insère un nouveau patient dans la base de données.
     * 
     * @param string $nom Nom de famille du patient.
     * @param string $prenom Prénom du patient.
     * @param string $telephone Numéro de téléphone (format +257).
     * @param string $email Adresse email unique.
     * @param string $password_hash Hash bcrypt du mot de passe.
     * @param string $date_naissance Date de naissance (format YYYY-MM-DD).
     * @return int L'identifiant auto-incrémenté du patient créé.
     */
    public function create(
        string $nom,
        string $prenom,
        string $telephone,
        string $email,
        string $password_hash,
        string $date_naissance
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO patients (nom, prenom, telephone, email, password_hash, date_naissance) 
            VALUES (:nom, :prenom, :telephone, :email, :hash, :date_naiss)
        ");
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
            'email' => $email,
            'hash' => $password_hash,
            'date_naiss' => $date_naissance
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Recherche un patient par son adresse email.
     * 
     * Utilisé pour vérifier la disponibilité de l'email lors de l'inscription.
     * 
     * @param string $email L'adresse email à rechercher.
     * @return array|false Tableau des données du patient ou false si non trouvé.
     */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Recherche un patient par son numéro de téléphone.
     * 
     * Utilisé pour vérifier la disponibilité du téléphone lors de l'inscription.
     * 
     * @param string $phone Le numéro de téléphone à rechercher.
     * @return array|false Tableau des données du patient ou false si non trouvé.
     */
    public function findByPhone(string $phone): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE telephone = :phone");
        $stmt->execute(['phone' => $phone]);
        return $stmt->fetch();
    }

    /**
     * Recherche un patient par email OU par téléphone.
     * 
     * Méthode utilisée pour la connexion : l'utilisateur peut saisir
     * indifféremment son email ou son téléphone dans le champ 'login'.
     * 
     * @param string $login L'email ou le téléphone saisi par l'utilisateur.
     * @return array|false Tableau des données du patient ou false si non trouvé.
     */
    public function findByEmailOrPhone(string $login): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE email = :login OR telephone = :login");
        $stmt->execute(['login' => $login]);
        return $stmt->fetch();
    }

    /**
     * Crée un nouveau patient à partir d'une invitation (sans ressaisir la carte).
     * 
     * Cette méthode est appelée lorsque le membre accepte son invitation.
     * Le numéro de téléphone est temporaire (à mettre à jour plus tard).
     * 
     * @param string $nom Nom du membre.
     * @param string $prenom Prénom du membre.
     * @param string $email Email d'invitation (servira de login).
     * @param string $password_hash Hash du mot de passe choisi par le membre.
     * @param int $groupe_familial_id ID du groupe auquel le rattacher.
     * @return int L'identifiant du patient créé.
     */
    public function createFromInvitation(
        string $nom,
        string $prenom,
        string $email,
        string $password_hash,
        int $groupe_familial_id
    ): int {
        // Le téléphone est provisoire. L'utilisateur devra le mettre à jour dans son profil.
        $telephone_provisoire = 'TEMP_' . uniqid();
        
        $stmt = $this->db->prepare("
            INSERT INTO patients (nom, prenom, email, telephone, password_hash, date_naissance, groupe_familial_id, est_chef) 
            VALUES (:nom, :prenom, :email, :telephone, :hash, CURDATE(), :groupe_id, 0)
        ");
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone_provisoire,
            'hash' => $password_hash,
            'groupe_id' => $groupe_familial_id
        ]);
        return (int) $this->db->lastInsertId();
    }
}
