<?php

require_once 'auth.php';
include_once 'header.php';

if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour valider un trajet.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$covoiturage_id = $_GET['covoiturage_id'] ?? null;

$notification = '';
$error_notification = '';
$trajet_valide = false; // Pour savoir si le trajet a déjà été validé par ce passager

$covoiturage_details = null;
$chauffeur_id = null;

if (!$covoiturage_id) {
    $_SESSION['error_notification'] = "Identifiant du covoiturage manquant.";
    header("Location: espace_passager.php");
    exit();
}

try {
    // 1. Vérifier que l'utilisateur a participé à ce covoiturage et qu'il est "termine"
    $stmtCheckParticipation = $conn->prepare("
        SELECT c.id, c.adresse_depart, c.adresse_arrivee, c.date_depart, c.heure_depart, c.utilisateur_id AS chauffeur_id, c.prix_personne
        FROM participation p
        JOIN covoiturage c ON p.covoiturage_id = c.id
        WHERE p.utilisateur_id = :utilisateur_id
        AND c.id = :covoiturage_id
        AND c.statut = 'termine'
    ");
    $stmtCheckParticipation->execute([
        ':utilisateur_id' => $utilisateur_id,
        ':covoiturage_id' => $covoiturage_id
    ]);
    $covoiturage_details = $stmtCheckParticipation->fetch(PDO::FETCH_ASSOC);

    if (!$covoiturage_details) {
        throw new Exception("Ce covoiturage est introuvable, n'est pas terminé, ou vous n'y avez pas participé.");
    }

    $chauffeur_id = $covoiturage_details['chauffeur_id'];

    // 2. Vérifier si l'utilisateur a déjà validé ce trajet (par exemple, en ayant laissé un avis lié à la validation)
    $stmtCheckAvis = $conn->prepare("SELECT id FROM avis WHERE utilisateur_id = :utilisateur_id AND covoiturage_id = :covoiturage_id");
    $stmtCheckAvis->execute([
        ':utilisateur_id' => $utilisateur_id,
        ':covoiturage_id' => $covoiturage_id
    ]);
    if ($stmtCheckAvis->fetch()) {
        $trajet_valide = true;
        $notification = "Vous avez déjà validé ce trajet et/ou laissé un avis.";
    }

} catch (PDOException $e) {
    $error_notification = "Erreur de base de données : " . $e->getMessage();
    error_log("Error loading trip validation (valider_trajet_passager.php): " . $e->getMessage());
} catch (Exception $e) {
    $_SESSION['error_notification'] = $e->getMessage();
    header("Location: espace_passager.php");
    exit();
}


// Traitement du formulaire de validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$trajet_valide) {
    $avis_commentaire = trim($_POST['commentaire'] ?? '');
    $avis_note = $_POST['note'] ?? null;
    $trajet_ok = isset($_POST['trajet_ok']) && $_POST['trajet_ok'] === 'oui';

    try {
        if ($avis_note === null || !is_numeric($avis_note) || $avis_note < 1 || $avis_note > 5) {
            throw new Exception("Veuillez donner une note de 1 à 5.");
        }

        $conn->beginTransaction();

        if ($trajet_ok) {
            // Le trajet s'est bien passé : mettre à jour le crédit du chauffeur
            // (Ici, j'utilise le prix_personne du covoiturage comme base pour le crédit. Adaptez si votre logique de crédit est différente)
            $credit_a_ajouter = $covoiturage_details['prix_personne']; // Ou une autre logique pour le crédit
            $stmtUpdateChauffeurCredit = $conn->prepare("UPDATE utilisateur SET credit = credit + :credit WHERE id = :chauffeur_id");
            $stmtUpdateChauffeurCredit->execute([
                ':credit' => $credit_a_ajouter,
                ':chauffeur_id' => $chauffeur_id
            ]);
            $notification .= "Le crédit du chauffeur a été mis à jour de {$credit_a_ajouter} ! ";

            // Insérer l'avis avec statut 'approuve' par défaut si tout s'est bien passé
            $avis_statut = 'approuve'; // Marque l'avis comme approuvé si le trajet s'est bien passé
            $notification .= "Votre validation a été enregistrée.";
            
        } else {
            // Le trajet s'est mal passé : enregistrer l'avis pour validation par un employé
            if (empty($avis_commentaire)) {
                throw new Exception("Veuillez fournir un commentaire si le trajet s'est mal passé.");
            }
            $avis_statut = 'en_attente'; // Marque l'avis comme en attente de validation
            $_SESSION['notification_pour_admin'] = "Un problème a été signalé pour le covoiturage ID {$covoiturage_id} par l'utilisateur ID {$utilisateur_id}. Un employé doit intervenir.";
            $notification .= "Votre signalement a été enregistré. Un employé va prendre contact avec le chauffeur.";
        }

        // Enregistrer l'avis dans la table 'avis'
        $stmtInsertAvis = $conn->prepare("
            INSERT INTO avis (utilisateur_id, covoiturage_id, commentaire, note, statut, date_avis)
            VALUES (:utilisateur_id, :covoiturage_id, :commentaire, :note, :statut, NOW())
        ");
        $stmtInsertAvis->execute([
            ':utilisateur_id' => $utilisateur_id,
            ':covoiturage_id' => $covoiturage_id,
            ':commentaire' => $avis_commentaire,
            ':note' => $avis_note,
            ':statut' => $avis_statut
        ]);

        $conn->commit();
        $trajet_valide = true; // Marque le trajet comme validé pour ne plus afficher le formulaire
        $_SESSION['notification'] = $notification; // Stocker la notification pour l'affichage après redirection
        header("Location: valider_trajet_passager.php?covoiturage_id=" . $covoiturage_id);
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        $error_notification = "Erreur lors de la validation : " . $e->getMessage();
        error_log("Error during trip validation (valider_trajet_passager.php): " . $e->getMessage());
    }
}
?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Valider votre trajet et laisser un avis</h2>

                    <?php if (isset($_SESSION['notification'])): ?>
                        <div class="alert alert-success text-center" role="alert">
                            <?php echo htmlspecialchars($_SESSION['notification']); unset($_SESSION['notification']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error_notification'])): ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <?php echo htmlspecialchars($_SESSION['error_notification']); unset($_SESSION['error_notification']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_notification): ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <?php echo htmlspecialchars($error_notification); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($covoiturage_details): ?>
                        <div class="alert alert-info text-center mb-4">
                            Covoiturage de <strong><?= htmlspecialchars($covoiturage_details['adresse_depart']) ?></strong> à <strong><?= htmlspecialchars($covoiturage_details['adresse_arrivee']) ?></strong> le <strong><?= htmlspecialchars(date('d/m/Y à H:i', strtotime($covoiturage_details['date_depart'] . ' ' . $covoiturage['heure_depart']))) ?></strong>
                        </div>

                        <?php if ($trajet_valide): ?>
                            <div class="alert alert-success text-center" role="alert">
                                Merci d'avoir validé ce trajet ! Votre avis a été enregistré.
                                <br><a href="espace_passager.php" class="btn btn-primary btn-sm mt-3">Retour à mon espace</a>
                            </div>
                        <?php else: ?>
                            <form action="valider_trajet_passager.php?covoiturage_id=<?= htmlspecialchars($covoiturage_id) ?>" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Le trajet s'est-il bien passé ?</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="trajet_ok" id="trajet_oui" value="oui" required checked>
                                            <label class="form-check-label" for="trajet_oui">Oui, tout s'est bien passé</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="trajet_ok" id="trajet_non" value="non" required>
                                            <label class="form-check-label" for="trajet_non">Non, il y a eu un problème</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="note" class="form-label">Notez votre expérience (1 à 5 étoiles):</label>
                                    <select class="form-select" id="note" name="note" required>
                                        <option value="">-- Sélectionnez une note --</option>
                                        <option value="5">5 étoiles (Excellent)</option>
                                        <option value="4">4 étoiles (Très bien)</option>
                                        <option value="3">3 étoiles (Moyen)</option>
                                        <option value="2">2 étoiles (Insatisfaisant)</option>
                                        <option value="1">1 étoile (Très mauvais)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="commentaire" class="form-label">Commentaire (facultatif, obligatoire si problème):</label>
                                    <textarea class="form-control" id="commentaire" name="commentaire" rows="4"></textarea>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">Valider et soumettre l'avis</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning text-center" role="alert">
                            Aucun covoiturage à valider pour le moment.
                        </div>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <a href="espace_passager.php" class="btn btn-secondary">Retour à l'espace personnel</a>
                    </div>

                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'footer.php'; ?>