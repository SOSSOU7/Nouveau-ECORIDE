<?php
session_start();
require_once 'auth.php'; // Assure la connexion à la base de données et l'authentification

if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour annuler une réservation.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$participation_id = $_GET['participation_id'] ?? null;
$covoiturage_id = $_GET['covoiturage_id'] ?? null;

if ($participation_id && $covoiturage_id) {
    try {
        $conn->beginTransaction();

        // 1. Vérifier que cette participation appartient à l'utilisateur actuel
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM participation WHERE id = :participation_id AND utilisateur_id = :utilisateur_id");
        $stmtCheck->execute([':participation_id' => $participation_id, ':utilisateur_id' => $utilisateur_id]);
        if ($stmtCheck->fetchColumn() == 0) {
            throw new Exception("Réservation introuvable ou vous n'êtes pas autorisé à l'annuler.");
        }

        // 2. Supprimer l'entrée de la participation
        $stmtDelete = $conn->prepare("DELETE FROM participation WHERE id = :participation_id");
        $stmtDelete->execute([':participation_id' => $participation_id]);

        // 3. Incrémenter le nombre de places disponibles dans le covoiturage
        $stmtUpdateCovoiturage = $conn->prepare("UPDATE covoiturage SET nb_place = nb_place + 1 WHERE id = :covoiturage_id");
        $stmtUpdateCovoiturage->execute([':covoiturage_id' => $covoiturage_id]);

        $conn->commit();
        $_SESSION['notification'] = "Votre réservation a été annulée avec succès.";

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_notification'] = "Erreur lors de l'annulation de la réservation : " . $e->getMessage();
        error_log("Reservation cancellation error (annuler_reservation.php): " . $e->getMessage());
    }
} else {
    $_SESSION['error_notification'] = "Paramètres d'annulation manquants.";
}

header("Location: mes_reservations.php"); // Rediriger vers la page des réservations
exit();
?>