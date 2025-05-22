<?php
require_once 'header.php';
require_once 'auth.php'; // Assure la connexion à la base de données et l'authentification

// Fonction pour envoyer un email simple (nécessite une configuration SMTP)
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: <noreply@ecoride.com>' . "\r\n"; // Remplacez par votre adresse d'expéditeur
    $headers .= 'Reply-To: <noreply@ecoride.com>' . "\r\n";

    $mail_sent = mail($to, $subject, $message, $headers);
    if (!$mail_sent) {
        error_log("Failed to send email to " . $to . " - Subject: " . $subject);
    }
    return $mail_sent;
}


if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour terminer un covoiturage.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$covoiturage_id = $_GET['covoiturage_id'] ?? null;

$ID_ROLE_CHAUFFEUR = 4;
$ID_ROLE_CHAUFFEUR_PASSAGER = 5;

if (!isset($_SESSION['utilisateur_role_id']) ||
    ($_SESSION['utilisateur_role_id'] != $ID_ROLE_CHAUFFEUR && $_SESSION['utilisateur_role_id'] != $ID_ROLE_CHAUFFEUR_PASSAGER)) {
    $_SESSION['error_notification'] = "Vous n'êtes pas autorisé à terminer ce covoiturage.";
    header("Location: espace_passager.php");
    exit();
}

if ($covoiturage_id) {
    try {
        $conn->beginTransaction();

        // 1. Vérifier que le covoiturage appartient bien à l'utilisateur et est "en_cours"
        $stmtCheck = $conn->prepare("SELECT adresse_depart, adresse_arrivee, date_depart, heure_depart, statut FROM covoiturage WHERE id = :covoiturage_id AND utilisateur_id = :utilisateur_id AND statut = 'en_cours'");
        $stmtCheck->execute([':covoiturage_id' => $covoiturage_id, ':utilisateur_id' => $utilisateur_id]);
        $covoiturageDetails = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$covoiturageDetails) {
            throw new Exception("Covoiturage introuvable ou déjà terminé.");
        }

        // 2. Mettre à jour le statut du covoiturage à 'termine'
        $stmtUpdate = $conn->prepare("UPDATE covoiturage SET statut = 'termine' WHERE id = :covoiturage_id");
        $stmtUpdate->execute([':covoiturage_id' => $covoiturage_id]);

        $conn->commit();

        // 3. Récupérer les adresses email des participants pour leur envoyer un mail
        $stmtGetParticipants = $conn->prepare("SELECT u.email FROM participation p JOIN utilisateur u ON p.utilisateur_id = u.id WHERE p.covoiturage_id = :covoiturage_id");
        $stmtGetParticipants->execute([':covoiturage_id' => $covoiturage_id]);
        $participants = $stmtGetParticipants->fetchAll(PDO::FETCH_ASSOC);

        $depart = htmlspecialchars($covoiturageDetails['adresse_depart']);
        $arrivee = htmlspecialchars($covoiturageDetails['adresse_arrivee']);
        $date = htmlspecialchars(date('d/m/Y', strtotime($covoiturageDetails['date_depart'])));
        $heure = htmlspecialchars(substr($covoiturageDetails['heure_depart'], 0, 5));

        $email_subject = "Votre covoiturage EcoRide est terminé !";
        $email_message_base = "
            <html>
            <head>
                <title>Covoiturage Terminé</title>
            </head>
            <body>
                <p>Bonjour,</p>
                <p>Le covoiturage de <strong>{$depart}</strong> à <strong>{$arrivee}</strong> qui a eu lieu le <strong>{$date}</strong> à <strong>{$heure}</strong> est maintenant terminé.</p>
                <p>Afin de finaliser ce trajet, veuillez vous rendre sur votre espace personnel EcoRide pour :</p>
                <ul>
                    <li>Confirmer que tout s'est bien passé.</li>
                    <li>Laisser un avis et une note pour le chauffeur.</li>
                </ul>
                <p>Cela permettra au crédit du chauffeur d'être mis à jour.</p>
                <p>Cliquez ici pour valider votre trajet : <a href='" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/valider_trajet_passager.php?covoiturage_id={$covoiturage_id}'>Valider mon trajet</a></p>
                <p>Cordialement,</p>
                <p>L'équipe EcoRide</p>
            </body>
            </html>
        ";

        foreach ($participants as $participant) {
            sendEmail($participant['email'], $email_subject, $email_message_base);
        }

        $_SESSION['notification'] = "Le covoiturage a été marqué comme terminé. Les participants ont été informés par email.";

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_notification'] = "Erreur lors de la finalisation du covoiturage : " . $e->getMessage();
        error_log("Covoiturage end error (terminer_covoiturage.php): " . $e->getMessage());
    }
} else {
    $_SESSION['error_notification'] = "Identifiant du covoiturage manquant.";
}

header("Location: mes_covoiturages_chauffeur.php");
exit();
?>