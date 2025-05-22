<?php
require_once 'header.php';
require_once 'auth.php'; // Assure la connexion à la base de données et l'authentification

if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour démarrer un covoiturage.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$covoiturage_id = $_GET['covoiturage_id'] ?? null;

$ID_ROLE_CHAUFFEUR = 4;
$ID_ROLE_CHAUFFEUR_PASSAGER = 5;

if (!isset($_SESSION['utilisateur_role_id']) ||
    ($_SESSION['utilisateur_role_id'] != $ID_ROLE_CHAUFFEUR && $_SESSION['utilisateur_role_id'] != $ID_ROLE_CHAUFFEUR_PASSAGER)) {
    $_SESSION['error_notification'] = "Vous n'êtes pas autorisé à démarrer ce covoiturage.";
    header("Location: espace_passager.php");
    exit();
}

if ($covoiturage_id) {
    try {
        $conn->beginTransaction();

        // Vérifier que le covoiturage appartient bien à l'utilisateur, est actif et que la date de départ est aujourd'hui ou passée (pour ne pas démarrer un covoit futur lointain)
        $stmtCheck = $conn->prepare("
            SELECT COUNT(*)
            FROM covoiturage
            WHERE id = :covoiturage_id
            AND utilisateur_id = :utilisateur_id
            AND statut = 'actif'
            AND (date_depart <= CURDATE())
        ");
        $stmtCheck->execute([':covoiturage_id' => $covoiturage_id, ':utilisateur_id' => $utilisateur_id]);

        if ($stmtCheck->fetchColumn() == 0) {
            throw new Exception("Covoiturage introuvable, non actif, ou vous n'êtes pas le chauffeur, ou la date de départ n'est pas aujourd'hui.");
        }

        // Mettre à jour le statut du covoiturage à 'en_cours'
        $stmtUpdate = $conn->prepare("UPDATE covoiturage SET statut = 'en_cours' WHERE id = :covoiturage_id");
        $stmtUpdate->execute([':covoiturage_id' => $covoiturage_id]);

        $conn->commit();
        $_SESSION['notification'] = "Le covoiturage a été démarré avec succès !";

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_notification'] = "Erreur lors du démarrage du covoiturage : " . $e->getMessage();
        error_log("Covoiturage start error (demarrer_covoiturage.php): " . $e->getMessage());
    }
} else {
    $_SESSION['error_notification'] = "Identifiant du covoiturage manquant.";
}

header("Location: mes_covoiturages_chauffeur.php"); // Rediriger vers la page des covoiturages chauffeur
exit();
?>