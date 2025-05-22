<?php

require_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn
include_once 'header.php';

if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour accéder à vos covoiturages.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

$ID_ROLE_CHAUFFEUR = 4;
$ID_ROLE_CHAUFFEUR_PASSAGER = 5;

if (!isset($_SESSION['utilisateur_role_id']) ||
    ($_SESSION['utilisateur_role_id'] != $ID_ROLE_CHAUFFEUR && $_SESSION['utilisateur_role_id'] != $ID_ROLE_CHAUFFEUR_PASSAGER)) {
    $_SESSION['error_notification'] = "Vous devez être chauffeur pour accéder à cette section.";
    header("Location: espace_passager.php");
    exit();
}

$notification = '';
$error_notification = '';
$covoiturages_chauffeur = [];

try {
    // Récupérer les covoiturages proposés par l'utilisateur (en tant que chauffeur)
    // qui sont 'actifs' ou 'en_cours' et dont la date/heure de départ est future ou actuelle.
    $stmt = $conn->prepare("
        SELECT
            c.id AS covoiturage_id,
            c.adresse_depart,
            c.adresse_arrivee,
            c.date_depart,
            c.heure_depart,
            c.heure_arrivee,
            c.prix_personne,
            c.nb_place,
            c.statut AS covoiturage_statut,
            v.marque,
            v.modele,
            v.couleur,
            v.immatriculation,
            (SELECT COUNT(p.id) FROM participation p WHERE p.covoiturage_id = c.id) AS nb_participants
        FROM
            covoiturage c
        LEFT JOIN
            voiture v ON c.voiture_id = v.id
        WHERE
            c.utilisateur_id = :utilisateur_id
            AND c.statut IN ('actif', 'en_cours')
            AND (
                c.date_depart >= CURDATE()
            )
        ORDER BY
            c.date_depart ASC, c.heure_depart ASC
    ");
    $stmt->execute([':utilisateur_id' => $utilisateur_id]);
    $covoiturages_chauffeur = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_notification = "Erreur lors du chargement de vos covoiturages : " . $e->getMessage();
    error_log("Erreur de chargement des covoiturages chauffeur (mes_covoiturages_chauffeur.php) : " . $e->getMessage());
}
?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-9">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Mes covoiturages en cours</h2>

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

                    <?php if (empty($covoiturages_chauffeur)): ?>
                        <div class="alert alert-info text-center" role="alert">
                            Vous n'avez aucun covoiturage actif en tant que chauffeur. <a href="saisir_voyage.php">Proposer un covoiturage</a>.
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="accordionCovoituragesChauffeur">
                            <?php foreach ($covoiturages_chauffeur as $covoiturage): ?>
                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header" id="heading<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>" aria-expanded="false" aria-controls="collapse<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>">
                                            Départ le <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($covoiturage['date_depart'] . ' ' . $covoiturage['heure_depart']))) ?>
                                            de <?= htmlspecialchars($covoiturage['adresse_depart']) ?> à <?= htmlspecialchars($covoiturage['adresse_arrivee']) ?>
                                            (<?= htmlspecialchars($covoiturage['nb_participants']) ?> participant(s) | Statut: <span class="
                                                <?php
                                                    $statut_class = '';
                                                    switch ($covoiturage['covoiturage_statut']) {
                                                        case 'actif': $statut_class = 'text-success'; break;
                                                        case 'en_cours': $statut_class = 'text-primary'; break; // Nouvelle couleur pour "en_cours"
                                                        case 'termine': $statut_class = 'text-muted'; break;
                                                        case 'annule': $statut_class = 'text-danger'; break;
                                                        default: $statut_class = 'text-info'; break;
                                                    }
                                                ?>
                                            <?= $statut_class ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $covoiturage['covoiturage_statut']))) ?></span>)
                                        </button>
                                    </h2>
                                    <div id="collapse<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>" data-bs-parent="#accordionCovoituragesChauffeur">
                                        <div class="accordion-body">
                                            <p><strong>Départ:</strong> <?= htmlspecialchars($covoiturage['adresse_depart']) ?> le <?= htmlspecialchars(date('d/m/Y', strtotime($covoiturage['date_depart']))) ?> à <?= htmlspecialchars(substr($covoiturage['heure_depart'], 0, 5)) ?></p>
                                            <p><strong>Arrivée:</strong> <?= htmlspecialchars($covoiturage['adresse_arrivee']) ?> à <?= htmlspecialchars(substr($covoiturage['heure_arrivee'], 0, 5)) ?></p>
                                            <p><strong>Prix par personne:</strong> <?= htmlspecialchars(number_format($covoiturage['prix_personne'], 2, ',', ' ')) ?> €</p>
                                            <p><strong>Nombre de places (initial):</strong> <?= htmlspecialchars($covoiturage['nb_place']) ?> places</p>
                                            <p><strong>Participants:</strong> <?= htmlspecialchars($covoiturage['nb_participants']) ?></p>
                                            <p><strong>Statut du covoiturage:</strong>
                                                <span class="<?= $statut_class ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $covoiturage['covoiturage_statut']))) ?></span>
                                            </p>
                                            <?php if (!empty($covoiturage['marque'])): ?>
                                                <p><strong>Véhicule:</strong> <?= htmlspecialchars($covoiturage['marque'] . ' ' . $covoiturage['modele'] . ' (' . $covoiturage['couleur'] . ' - ' . $covoiturage['immatriculation'] . ')') ?></p>
                                            <?php else: ?>
                                                <p class="text-muted">Informations véhicule non disponibles.</p>
                                            <?php endif; ?>

                                            <hr>
                                            <div class="d-flex justify-content-end">
                                                <?php if ($covoiturage['covoiturage_statut'] == 'actif'): ?>
                                                    <a href="modifier_covoiturage.php?id=<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>" class="btn btn-primary btn-sm me-2">Modifier</a>
                                                    <button type="button" class="btn btn-success btn-sm me-2" onclick="confirmStartCovoiturage(<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>)">Démarrer le covoiturage</button>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmCancelCovoiturage(<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>)">Annuler le covoiturage</button>
                                                <?php elseif ($covoiturage['covoiturage_statut'] == 'en_cours'): ?>
                                                    <button type="button" class="btn btn-warning btn-sm" onclick="confirmEndCovoiturage(<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>)">Arrivée à destination</button>
                                                <?php endif; ?>
                                                <a href="voir_participants.php?covoiturage_id=<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>" class="btn btn-info btn-sm ms-2">Voir les participants</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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

<script>
function confirmCancelCovoiturage(covoiturageId) {
    if (confirm("Êtes-vous sûr de vouloir annuler ce covoiturage ? Cela annulera également toutes les réservations des passagers.")) {
        window.location.href = 'annuler_covoiturage_chauffeur.php?covoiturage_id=' + covoiturageId;
    }
}

function confirmStartCovoiturage(covoiturageId) {
    if (confirm("Confirmer le démarrage de ce covoiturage ?")) {
        window.location.href = 'demarrer_covoiturage.php?covoiturage_id=' + covoiturageId;
    }
}

function confirmEndCovoiturage(covoiturageId) {
    if (confirm("Confirmer l'arrivée à destination pour ce covoiturage ? Les passagers recevront un mail pour valider le trajet.")) {
        window.location.href = 'terminer_covoiturage.php?covoiturage_id=' + covoiturageId;
    }
}
</script>