<?php
require_once 'header.php';
require_once 'auth.php'; // Assure la connexion à la base de données et l'authentification

// Fonction pour envoyer un email simple
// ATTENTION : Cette fonction utilise la fonction mail() de PHP qui nécessite une configuration SMTP sur le serveur.
// Pour la production, l'utilisation d'une bibliothèque comme PHPMailer est FORTEMENT recommandée.
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: <noreply@ecoride.com>' . "\r\n"; // Remplacez par votre adresse d'expéditeur
    $headers .= 'Reply-To: <noreply@ecoride.com>' . "\r\n"; // Adresse de réponse si le destinataire répond

    // Tente d'envoyer l'email
    $mail_sent = mail($to, $subject, $message, $headers);

    if (!$mail_sent) {
        // Enregistrez l'erreur dans les logs si l'envoi échoue
        error_log("Failed to send email to " . $to . " - Subject: " . $subject);
        // Sur certains systèmes (ex: Windows avec XAMPP/WAMP), mail() peut ne pas fonctionner sans configuration supplémentaire
        // Pour les tests, vous pourriez afficher une notification pour le développeur.
    }
    return $mail_sent;
}


if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour annuler un covoiturage.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$covoiturage_id = $_GET['covoiturage_id'] ?? null;

// Définition des IDs de rôle (DOIVENT correspondre à votre BDD)
$ID_ROLE_CHAUFFEUR = 4;
$ID_ROLE_CHAUFFEUR_PASSAGER = 5;

// Vérifier si l'utilisateur a le rôle de chauffeur ou chauffeur_passager
if (!isset($_SESSION['utilisateur_role_id']) ||
    ($_SESSION['utilisateur_role_id'] != $ID_ROLE_CHAUFFEUR && $_SESSION['utilisateur_role_id'] != $ID_ROLE_CHAUFFEUR_PASSAGER)) {
    $_SESSION['error_notification'] = "Vous n'êtes pas autorisé à annuler ce covoiturage.";
    header("Location: espace_passager.php");
    exit();
}

if ($covoiturage_id) {
    try {
        $conn->beginTransaction();

        // 1. Récupérer les détails du covoiturage avant de l'annuler
        // Vérifier aussi que c'est bien le covoiturage de l'utilisateur connecté et qu'il est actif
        $stmtCovoitDetails = $conn->prepare("SELECT adresse_depart, adresse_arrivee, date_depart, heure_depart, utilisateur_id FROM covoiturage WHERE id = :covoiturage_id AND utilisateur_id = :utilisateur_id AND statut = 'actif'");
        $stmtCovoitDetails->execute([':covoiturage_id' => $covoiturage_id, ':utilisateur_id' => $utilisateur_id]);
        $covoiturageDetails = $stmtCovoitDetails->fetch(PDO::FETCH_ASSOC);

        if (!$covoiturageDetails) {
            throw new Exception("Covoiturage introuvable, non actif, ou vous n'êtes pas le chauffeur.");
        }

        // 2. Récupérer les adresses email des participants AVANT de supprimer les participations
        $stmtGetParticipants = $conn->prepare("SELECT u.email FROM participation p JOIN utilisateur u ON p.utilisateur_id = u.id WHERE p.covoiturage_id = :covoiturage_id");
        $stmtGetParticipants->execute([':covoiturage_id' => $covoiturage_id]);
        $participants = $stmtGetParticipants->fetchAll(PDO::FETCH_ASSOC);

        // 3. Mettre à jour le statut du covoiturage à 'annule'
        $stmtUpdateCovoiturage = $conn->prepare("UPDATE covoiturage SET statut = 'annule' WHERE id = :covoiturage_id");
        $stmtUpdateCovoiturage->execute([':covoiturage_id' => $covoiturage_id]);

        // 4. Supprimer les participations liées
        // Alternative : Mettre à jour le statut des participations à 'annule' si vous gardez un historique détaillé
        $stmtDeleteParticipations = $conn->prepare("DELETE FROM participation WHERE covoiturage_id = :covoiturage_id");
        $stmtDeleteParticipations->execute([':covoiturage_id' => $covoiturage_id]);

        $conn->commit(); // Confirmer la transaction

        // 5. Envoyer les emails aux participants
        $depart = htmlspecialchars($covoiturageDetails['adresse_depart']);
        $arrivee = htmlspecialchars($covoiturageDetails['adresse_arrivee']);
        $date = htmlspecialchars(date('d/m/Y', strtotime($covoiturageDetails['date_depart'])));
        $heure = htmlspecialchars(substr($covoiturageDetails['heure_depart'], 0, 5));

        $email_subject = "Annulation de votre covoiturage EcoRide";
        $email_message_base = "
            <html>
            <head>
                <title>Annulation de covoiturage</title>
            </head>
            <body>
                <p>Bonjour,</p>
                <p>Nous vous informons que le covoiturage de <strong>{$depart}</strong> à <strong>{$arrivee}</strong> prévu le <strong>{$date}</strong> à <strong>{$heure}</strong> a été annulé par le chauffeur.</p>
                <p>Nous sommes désolés pour tout inconvénient que cela pourrait occasionner.</p>
                <p>N'hésitez pas à rechercher d'autres covoiturages disponibles sur EcoRide.</p>
                <p>Cordialement,</p>
                <p>L'équipe EcoRide</p>
            </body>
            </html>
        ";

        foreach ($participants as $participant) {
            $to = $participant['email'];
            sendEmail($to, $email_subject, $email_message_base);
        }

        $_SESSION['notification'] = "Le covoiturage a été annulé avec succès et les participants ont été informés.";

    } catch (Exception $e) {
        $conn->rollBack(); // Annuler la transaction en cas d'erreur
        $_SESSION['error_notification'] = "Erreur lors de l'annulation du covoiturage : " . $e->getMessage();
        error_log("Erreur d'annulation du covoiturage chauffeur (annuler_covoiturage_chauffeur.php) : " . $e->getMessage());
    }
} else {
    $_SESSION['error_notification'] = "Paramètres d'annulation manquants.";
}

header("Location: mes_covoiturages_chauffeur.php"); // Rediriger vers la page des covoiturages chauffeur
exit();
?>