<?php
include_once 'header.php'; 
require_once 'auth.php'; // Pour la connexion DB ($conn)

// --- DEBUGGING ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- END DEBUGGING ---

// Assurez-vous que la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_notification'] = "Méthode de requête non autorisée.";
    header('Location: formulaire_covoiturage.php');
    exit();
}

$covoiturage_id = filter_input(INPUT_POST, 'covoiturage_id', FILTER_VALIDATE_INT);
$utilisateur_id_connecte = $_SESSION['utilisateur_id'] ?? null;

// Récupérer les paramètres de recherche pour le retour
// Ces paramètres ne sont pas strictement nécessaires pour l'arrêt,
// mais utiles si on veut rediriger vers la page de détails en cas d'erreur.
$adresse_depart_recherche = trim($_POST['adresse_depart'] ?? '');
$adresse_arrivee_recherche = trim($_POST['adresse_arrivee'] ?? '');
$date_recherche = trim($_POST['date_depart'] ?? '');
$tarif_maximum = trim($_POST['tarif_maximum'] ?? '');
$duree_maximale = trim($_POST['duree_maximale'] ?? '');
$note_minimale = trim($_POST['note_minimale'] ?? '');
$voiture_eco = trim($_POST['eco'] ?? '');

// Construire l'URL de redirection (vers la page de détail si erreur)
$redirectParams = [
    'covoiturage_id' => $covoiturage_id,
    'adresse_depart' => urlencode($adresse_depart_recherche),
    'adresse_arrivee' => urlencode($adresse_arrivee_recherche),
    'date_depart' => urlencode($date_recherche),
    'tarif_maximum' => urlencode($tarif_maximum),
    'duree_maximale' => urlencode($duree_maximale),
    'note_minimale' => urlencode($note_minimale),
    'eco' => urlencode($voiture_eco)
];
$redirectUrl = 'vue_detailee_covoiturage.php?' . http_build_query($redirectParams);

if (!$utilisateur_id_connecte) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour effectuer cette action.";
    header('Location: signin.php?redirect=' . urlencode($redirectUrl));
    exit();
}

if (!$covoiturage_id) {
    $_SESSION['error_notification'] = "ID de covoiturage invalide.";
    header('Location: ' . $redirectUrl);
    exit();
}

try {
    $conn->beginTransaction();

    // 1. Vérifier si l'utilisateur connecté est bien le conducteur de ce covoiturage
    // Et récupérer le prix pour les remboursements
    $stmt = $conn->prepare("SELECT utilisateur_id, prix_personne FROM covoiturage WHERE id = :covoiturage_id FOR UPDATE");
    $stmt->execute([':covoiturage_id' => $covoiturage_id]);
    $covoiturage_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$covoiturage_info) {
        throw new Exception("Covoiturage introuvable.");
    }

    if ($covoiturage_info['utilisateur_id'] != $utilisateur_id_connecte) {
        throw new Exception("Vous n'êtes pas autorisé à arrêter ce covoiturage.");
    }

    // 2. Récupérer les ID des passagers qui ont participé à ce covoiturage
    $stmt = $conn->prepare("SELECT utilisateur_id FROM participation WHERE covoiturage_id = :covoiturage_id FOR UPDATE");
    $stmt->execute([':covoiturage_id' => $covoiturage_id]);
    $participants_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. Rembourser les participants (s'il y en a)
    $prix_personne = $covoiturage_info['prix_personne'];
    if (!empty($participants_ids)) {
        foreach ($participants_ids as $passager_id) {
            $stmt = $conn->prepare("UPDATE utilisateur SET credit = credit + :prix WHERE id = :passager_id");
            $stmt->execute([
                ':prix' => $prix_personne,
                ':passager_id' => $passager_id
            ]);
            // Si le passager remboursé est l'utilisateur actuellement connecté (ce qui ne devrait pas arriver ici,
            // car le conducteur ne peut pas participer à son propre covoiturage, mais bonne pratique)
            if ($passager_id == $_SESSION['utilisateur_id']) { 
                 $_SESSION['utilisateur_credit'] += $prix_personne;
            }
        }
        // 4. Supprimer les entrées de participation pour ce covoiturage
        $stmt = $conn->prepare("DELETE FROM participation WHERE covoiturage_id = :covoiturage_id");
        $stmt->execute([':covoiturage_id' => $covoiturage_id]);
    }

    // 5. Mettre à jour le statut du covoiturage à "annulé" et remettre nb_place à 0
    // Nécessite que votre table `covoiturage` ait une colonne `statut` (ex: VARCHAR(50) DEFAULT 'actif').
    // Si la colonne n'existe pas, vous devrez l'ajouter à votre DB.
    $stmt = $conn->prepare("UPDATE covoiturage SET statut = 'annule', nb_place = 0 WHERE id = :covoiturage_id");
    $stmt->execute([':covoiturage_id' => $covoiturage_id]);

    $conn->commit();
    $_SESSION['notification'] = "Le covoiturage de " . htmlspecialchars($covoiturage_info['adresse_depart']) . " à " . htmlspecialchars($covoiturage_info['adresse_arrivee']) . " a été arrêté avec succès. " . count($participants_ids) . " participant(s) ont été remboursés.";
    
    // Rediriger vers une page plus pertinente, par exemple la liste des covoiturages du conducteur ou le tableau de bord
    // Pour cet exemple, je redirige vers la page de recherche pour le moment.
    header('Location: formulaire_covoiturage.php'); 
    exit();

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['error_notification'] = "Erreur lors de l'arrêt du covoiturage : " . $e->getMessage();
    header('Location: ' . $redirectUrl); // Rediriger vers la page de détail avec l'erreur
    exit();
}

?>