<?php
require_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn
include_once 'header.php';

if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour voir les participants.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$covoiturage_id = $_GET['covoiturage_id'] ?? null;

$ID_ROLE_CHAUFFEUR = 4;
$ID_ROLE_CHAUFFEUR_PASSAGER = 5;

// Vérifier si l'utilisateur est bien un chauffeur ou un chauffeur-passager
if (!isset($_SESSION['utilisateur_role_id']) ||
    ($_SESSION['utilisateur_role_id'] != $ID_ROLE_CHAUFFEUR && $_SESSION['utilisateur_role_id'] != $ID_ROLE_CHAUFFEUR_PASSAGER)) {
    $_SESSION['error_notification'] = "Vous n'êtes pas autorisé à voir cette page.";
    header("Location: espace_passager.php");
    exit();
}

$participants = [];
$covoiturage_details = null;
$error_notification = '';

if ($covoiturage_id) {
    try {
        // 1. Vérifier que le covoiturage appartient bien au chauffeur connecté
        $stmtCovoit = $conn->prepare("
            SELECT
                id, adresse_depart, adresse_arrivee, date_depart, heure_depart,
                nb_place, prix_personne, statut
            FROM
                covoiturage
            WHERE
                id = :covoiturage_id AND utilisateur_id = :utilisateur_id
        ");
        $stmtCovoit->execute([
            ':covoiturage_id' => $covoiturage_id,
            ':utilisateur_id' => $utilisateur_id
        ]);
        $covoiturage_details = $stmtCovoit->fetch(PDO::FETCH_ASSOC);

        if (!$covoiturage_details) {
            $error_notification = "Ce covoiturage est introuvable ou ne vous appartient pas.";
        } else {
            // 2. Récupérer la liste des participants pour ce covoiturage
            $stmtParticipants = $conn->prepare("
                SELECT
                    u.pseudo,
                    u.email,
                    u.telephone,
                    DATE_FORMAT(p.date_participation, '%d/%m/%Y à %H:%i') AS date_participation_formattee
                FROM
                    participation p
                JOIN
                    utilisateur u ON p.utilisateur_id = u.id
                WHERE
                    p.covoiturage_id = :covoiturage_id
                ORDER BY
                    u.pseudo ASC
            ");
            $stmtParticipants->execute([':covoiturage_id' => $covoiturage_id]);
            $participants = $stmtParticipants->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (PDOException $e) {
        $error_notification = "Erreur lors du chargement des participants : " . $e->getMessage();
        error_log("Error loading participants (voir_participants.php): " . $e->getMessage());
    }
} else {
    $error_notification = "Identifiant du covoiturage manquant.";
}
?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-8">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Participants au covoiturage</h2>

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
                            Covoiturage de <strong><?= htmlspecialchars($covoiturage_details['adresse_depart']) ?></strong> à <strong><?= htmlspecialchars($covoiturage_details['adresse_arrivee']) ?></strong> le <strong><?= htmlspecialchars(date('d/m/Y', strtotime($covoiturage_details['date_depart']))) ?></strong> à <strong><?= htmlspecialchars(substr($covoiturage_details['heure_depart'], 0, 5)) ?></strong>
                            <br>Places disponibles : <?= htmlspecialchars($covoiturage_details['nb_place'] - count($participants)) ?> / <?= htmlspecialchars($covoiturage_details['nb_place']) ?> (initial)
                            <br>Statut : <span class="
                                <?php
                                    $statut_class = '';
                                    switch ($covoiturage_details['statut']) {
                                        case 'actif': $statut_class = 'text-success'; break;
                                        case 'en_cours': $statut_class = 'text-primary'; break;
                                        case 'termine': $statut_class = 'text-muted'; break;
                                        case 'annule': $statut_class = 'text-danger'; break;
                                        default: $statut_class = 'text-info'; break;
                                    }
                                ?>
                            <?= $statut_class ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $covoiturage_details['statut']))) ?></span>
                        </div>

                        <?php if (empty($participants)): ?>
                            <div class="alert alert-warning text-center" role="alert">
                                Il n'y a pas encore de participants pour ce covoiturage.
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($participants as $participant): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($participant['pseudo']) ?></strong><br>
                                            <small>Email: <?= htmlspecialchars($participant['email']) ?></small><br>
                                            <?php if (!empty($participant['telephone'])): ?>
                                                <small>Tél: <?= htmlspecialchars($participant['telephone']) ?></small><br>
                                            <?php endif; ?>
                                            <small class="text-muted">Inscrit le: <?= htmlspecialchars($participant['date_participation_formattee']) ?></small>
                                        </div>
                                        <a href="mailto:<?= htmlspecialchars($participant['email']) ?>" class="btn btn-sm btn-outline-primary">Contacter</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <p class="text-center mt-3">Nombre de participants : <strong><?= count($participants) ?></strong></p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <a href="mes_covoiturages_chauffeur.php" class="btn btn-secondary">Retour à mes covoiturages</a>
                    </div>

                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'footer.php'; ?>