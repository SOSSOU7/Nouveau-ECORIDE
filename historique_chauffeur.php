<?php
session_start();
require_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn
include_once 'header.php'; // Assurez-vous que header.php inclut les balises HTML de base et les liens CSS/JS (Bootstrap, Font Awesome)

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour accéder à votre historique de covoiturage.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Définition des IDs de rôle (DOIVENT correspondre à votre BDD)
$ID_ROLE_CHAUFFEUR = 4; // Exemple: ID pour le rôle 'chauffeur'
$ID_ROLE_CHAUFFEUR_PASSAGER = 5; // Exemple: ID pour le rôle 'chauffeur_passager'

// Vérifier si l'utilisateur a le rôle de chauffeur ou chauffeur_passager
if (!isset($_SESSION['utilisateur_role_id']) ||
    ($_SESSION['utilisateur_role_id'] != $ID_ROLE_CHAUFFEUR && $_SESSION['utilisateur_role_id'] != $ID_ROLE_CHAUFFEUR_PASSAGER)) {
    $_SESSION['error_notification'] = "Vous devez être chauffeur pour accéder à cette section de l'historique.";
    header("Location: espace_passager.php"); // Rediriger vers l'espace passager si pas chauffeur
    exit();
}

// Initialiser les notifications
$notification = '';
$error_notification = '';
$historique_chauffeur = [];

try {
    // Récupérer l'historique des covoiturages proposés par l'utilisateur (en tant que chauffeur)
    // C'est-à-dire les covoiturages dont le statut est 'terminé' ou 'annulé',
    // OU dont la date/heure de départ est passée (même si le statut est toujours 'actif' par erreur).
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
            (SELECT COUNT(p.id) FROM participation p WHERE p.covoiturage_id = c.id) AS nb_participants_enregistres
        FROM
            covoiturage c
        LEFT JOIN
            voiture v ON c.voiture_id = v.id
        WHERE
            c.utilisateur_id = :utilisateur_id
            AND (
                c.statut = 'termine' OR c.statut = 'annule' OR (c.date_depart < CURDATE() OR (c.date_depart = CURDATE() AND c.heure_depart < CURTIME()))
            )
        ORDER BY
            c.date_depart DESC, c.heure_depart DESC
    ");
    $stmt->execute([':utilisateur_id' => $utilisateur_id]);
    $historique_chauffeur = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_notification = "Erreur lors du chargement de l'historique de vos covoiturages : " . $e->getMessage();
    error_log("Erreur de chargement de l'historique chauffeur (historique_chauffeur.php) : " . $e->getMessage());
}
?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-9">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Historique de mes covoiturages en tant que chauffeur</h2>

                    <?php if ($notification): ?>
                        <div class="alert alert-success text-center" role="alert">
                            <?php echo htmlspecialchars($notification); ?>
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

                    <?php if (empty($historique_chauffeur)): ?>
                        <div class="alert alert-info text-center" role="alert">
                            Vous n'avez pas encore d'historique de covoiturages en tant que chauffeur.
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="accordionHistoriqueChauffeur">
                            <?php foreach ($historique_chauffeur as $covoiturage): ?>
                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header" id="heading<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>" aria-expanded="false" aria-controls="collapse<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>">
                                            Du <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($covoiturage['date_depart'] . ' ' . $covoiturage['heure_depart']))) ?>
                                            de <?= htmlspecialchars($covoiturage['adresse_depart']) ?> à <?= htmlspecialchars($covoiturage['adresse_arrivee']) ?>
                                            (Statut: <span class="
                                                <?php
                                                switch ($covoiturage['covoiturage_statut']) {
                                                    case 'termine': echo 'text-muted'; break;
                                                    case 'annule': echo 'text-danger'; break;
                                                    default: echo 'text-warning'; // Pour les covoiturages passés mais statut encore "actif"
                                                }
                                                ?>
                                            "><?= htmlspecialchars(ucfirst($covoiturage['covoiturage_statut'])) ?></span>)
                                        </button>
                                    </h2>
                                    <div id="collapse<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>" data-bs-parent="#accordionHistoriqueChauffeur">
                                        <div class="accordion-body">
                                            <p><strong>Départ:</strong> <?= htmlspecialchars($covoiturage['adresse_depart']) ?> le <?= htmlspecialchars(date('d/m/Y', strtotime($covoiturage['date_depart']))) ?> à <?= htmlspecialchars(substr($covoiturage['heure_depart'], 0, 5)) ?></p>
                                            <p><strong>Arrivée:</strong> <?= htmlspecialchars($covoiturage['adresse_arrivee']) ?> à <?= htmlspecialchars(substr($covoiturage['heure_arrivee'], 0, 5)) ?></p>
                                            <p><strong>Prix par personne:</strong> <?= htmlspecialchars(number_format($covoiturage['prix_personne'], 2, ',', ' ')) ?> €</p>
                                            <p><strong>Places offertes (initial):</strong> <?= htmlspecialchars($covoiturage['nb_place']) ?></p>
                                            <p><strong>Nombre de participants enregistrés:</strong> <?= htmlspecialchars($covoiturage['nb_participants_enregistres']) ?></p>
                                            <p><strong>Statut du covoiturage:</strong>
                                                <span class="
                                                    <?php
                                                    switch ($covoiturage['covoiturage_statut']) {
                                                        case 'termine': echo 'text-muted'; break;
                                                        case 'annule': echo 'text-danger'; break;
                                                        default: echo 'text-warning';
                                                    }
                                                    ?>
                                                "><?= htmlspecialchars(ucfirst($covoiturage['covoiturage_statut'])) ?></span>
                                            </p>
                                            <?php if (!empty($covoiturage['marque'])): ?>
                                                <p><strong>Véhicule:</strong> <?= htmlspecialchars($covoiturage['marque'] . ' ' . $covoiturage['modele'] . ' (' . $covoiturage['couleur'] . ' - ' . $covoiturage['immatriculation'] . ')') ?></p>
                                            <?php else: ?>
                                                <p class="text-muted">Informations véhicule non disponibles.</p>
                                            <?php endif; ?>

                                            <hr>
                                            <div class="d-flex justify-content-end">
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