<?php

/**
 * =====================================================================
 * FamilyGroupModel
 * =====================================================================
 * 
 * Gère les opérations sur la table `groupes_familiaux`.
 * 
 * C'est le modèle le plus critique du système car il garantit
 * l'UNICITÉ des numéros de carte d'assurance/mutuelle.
 * 
 * Règles métier implémentées :
 * - Un numéro de carte ne peut être enregistré qu'une seule fois.
 * - Le premier utilisateur d'une carte devient le Chef de famille.
 * - La création d'un groupe et du Chef est atomique (transaction).
 * 
 * @package Models
 * @version 1.0
 */

namespace Models;

use Core\Model;

class FamilyGroupModel extends Model
{
    /** @var string Nom de la table associée */
    protected string $table = 'groupes_familiaux';

    /**
     * Vérifie si un numéro de carte existe déjà dans le système.
     * 
     * Si la carte existe, retourne également le nom du Chef de famille
     * pour afficher un message personnalisé à l'utilisateur.
     * 
     * @param string $numero_carte Le numéro de carte à vérifier.
     * @return array|false Tableau avec les infos du groupe et du chef,
     *                     ou false si la carte n'existe pas.
     */
    public function findUniqueCard(string $numero_carte): array|false
    {
        $stmt = $this->db->prepare("
            SELECT g.*, p.nom as chef_nom, p.prenom as chef_prenom 
            FROM groupes_familiaux g
            LEFT JOIN patients p ON g.chef_de_famille_id = p.id
            WHERE g.numero_carte = :carte
        ");
        $stmt->execute(['carte' => $numero_carte]);
        return $stmt->fetch();
    }

    /**
     * Crée un groupe familial et désigne le patient comme Chef.
     * 
     * ⚠️ MÉTHODE CRITIQUE - TRANSACTION SQL ⚠️
     * 
     * Cette méthode doit être exécutée dans une transaction pour garantir
     * l'intégrité référentielle. Elle effectue 3 opérations :
     * 1. Insertion du groupe avec la carte unique.
     * 2. Mise à jour du patient (le lier au groupe et définir 'est_chef' = TRUE).
     * 3. Mise à jour du groupe (renseigner 'chef_de_famille_id').
     * 
     * Si une opération échoue (ex: violation de la contrainte UNIQUE),
     * la transaction est annulée (rollback) et aucune donnée n'est persistée.
     * 
     * @param int $chef_patient_id Identifiant du patient qui devient Chef.
     * @param string $type_document Type de couverture (MUTUELLE_PUBLIC, ASSURANCE_PRIVEE, etc.).
     * @param string $numero_carte Le numéro de carte (doit être unique).
     * @return int L'identifiant du groupe familial créé.
     * @throws \Exception En cas d'erreur SQL (ex: doublon de carte).
     */
    public function createGroupForChef(
        int $chef_patient_id,
        string $type_document,
        string $numero_carte
    ): int {
        // Démarrage de la transaction
        $this->db->beginTransaction();

        try {
            // Étape 1 : Insérer le groupe familial (la carte UNIQUE)
            $stmt = $this->db->prepare("
                INSERT INTO groupes_familiaux (type_document, numero_carte) 
                VALUES (:type, :carte)
            ");
            $stmt->execute(['type' => $type_document, 'carte' => $numero_carte]);
            $groupId = (int) $this->db->lastInsertId();

            // Étape 2 : Mettre à jour le patient pour le lier au groupe et en faire le Chef
            $stmt2 = $this->db->prepare("
                UPDATE patients 
                SET groupe_familial_id = :gid, est_chef = 1 
                WHERE id = :pid
            ");
            $stmt2->execute(['gid' => $groupId, 'pid' => $chef_patient_id]);

            // Étape 3 : Mettre à jour la table groupe pour indiquer le chef
            $stmt3 = $this->db->prepare("
                UPDATE groupes_familiaux SET chef_de_famille_id = :pid WHERE id = :gid
            ");
            $stmt3->execute(['pid' => $chef_patient_id, 'gid' => $groupId]);

            // Toutes les opérations ont réussi : validation de la transaction
            $this->db->commit();
            return $groupId;

        } catch (\Exception $e) {
            // Une erreur est survenue : annulation de toutes les modifications
            $this->db->rollBack();
            throw $e; // Relancer l'exception pour la gérer dans le contrôleur
        }
    }
}
