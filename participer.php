<?php
session_start();
require 'db.php'; // Fichier de connexion à la BDD
 
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: connexion.php");
    exit;
}
 
$utilisateur_id = $_SESSION['utilisateur_id'];
$trajet_id = $_POST['trajet_id'] ?? null;
 
if (!$trajet_id) {
    header("Location: index.php?error=invalid_request");
    exit;
}
 
try {
    $conn->beginTransaction();
 
    // Récupérer le trajet et l'utilisateur avec verrouillage
    $sql_trajet = "SELECT nb_place, creditRequis, utilisateur_id AS utilisateur_id FROM covoiturage WHERE id = :trajet_id FOR UPDATE";
    $stmt = $conn->prepare($sql_trajet);
    $stmt->execute(['trajet_id' => $trajet_id]);
    $trajet = $stmt->fetch(PDO::FETCH_ASSOC);
 
    $sql_utilisateur = "SELECT credit FROM utilisateur WHERE id = :id FOR UPDATE";
    $stmt = $conn->prepare($sql_utilisateur);
    $stmt->execute(['id' => $utilisateur_id]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if (!$trajet || !$utilisateur) {
        throw new Exception("Données introuvables.");
    }
 
    // Vérifier les conditions
    if ($trajet['nb_place'] <= 0) {
        throw new Exception("Pas de places disponibles.");
    }
    if ($utilisateur['credit'] < $trajet['creditRequis']) {
        throw new Exception("Crédits insuffisants.");
    }
 
    // Déduire les crédits et mettre à jour le trajet
    $sql_update_credit = "UPDATE utilisateur SET credit = credit - :credit WHERE id = :id";
    $stmt = $conn->prepare($sql_update_credit);
    $stmt->execute(['credit' => $trajet['creditRequis'], 'id' => $utilisateur_id]);
 
    $sql_update_trajet = "UPDATE covoiturage SET nb_place = nb_place - 1 WHERE id = :trajet_id";
    $stmt = $conn->prepare($sql_update_trajet);
    $stmt->execute(['trajet_id' => $trajet_id]);
 
    // Enregistrer la participation
    $sql_participation = "INSERT INTO participation (utilisateur_id, covoiturage_id, date_participation) VALUES (:utilisateur_id, :trajet_id, NOW())";
    $stmt = $conn->prepare($sql_participation);
    $stmt->execute(['utilisateur_id' => $utilisateur_id, 'trajet_id' => $trajet_id]);
 
    $conn->commit();
    header("Location: espace_passager.php?trajet_id=$trajet_id&success=1");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    header("Location: espace_passager.php?trajet_id=$trajet_id&error=" . urlencode($e->getMessage()));
    echo "Erreur: " . $e->getMessage();
    exit;
}

?>