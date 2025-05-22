<?php

require_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn
include_once 'header.php'; // Assurez-vous que header.php inclut les balises HTML de base et les liens CSS/JS (Bootstrap, Font Awesome)

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour accéder à votre historique de passager.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Initialiser les notifications
$notification = '';
$error_notification = '';
$voyages_passager = [];

try {
    // Récupérer l'historique des participations de l'utilisateur en tant que passager
    // Sont considérés comme 'historiques' les covoiturages dont la date de départ est passée,
    // ou dont le statut est 'terminé' ou 'annulé'.
    $stmt = $conn->prepare("
        SELECT
            p.id AS participation_id,
            c.id AS covoiturage_id,
            c.adresse_depart,
            c.adresse_arrivee,
            c.date_depart,
            c.heure_depart,
            c.heure_arrivee,
            c.prix_personne,
            c.nb_place,
            c.statut AS covoiturage_statut,
            ch.pseudo AS chauffeur_pseudo,
            v.marque,
            v.modele,
            v.couleur,
            v.immatriculation
        FROM
            participation p
        JOIN
            covoiturage c ON p.covoiturage_id = c.id
        JOIN
            utilisateur ch ON c.utilisateur_id = ch.id -- Alias 'ch' pour le chauffeur
        LEFT JOIN
            voiture v ON c.voiture_id = v.id
        WHERE
            p.utilisateur_id = :utilisateur_id
            AND (
                c.statut = 'termine' OR c.statut = 'annule' OR (c.date_depart < CURDATE() OR (c.date_depart = CURDATE() AND c.heure_depart < CURTIME()))
            )
        ORDER BY
            c.date_depart DESC, c.heure_depart DESC
    ");
    $stmt->execute([':utilisateur_id' => $utilisateur_id]);
    $voyages_passager = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_notification = "Erreur lors du chargement de l'historique des voyages : " . $e->getMessage();
    error_log("Passenger history load error (historique_passager.php): " . $e->getMessage());
}
?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-9">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Historique de mes voyages en tant que passager</h2>

                    <?php if ($notification): ?>
                        <div class="alert alert-success text-center" role="alert">
                            <?php echo htmlspecialchars($notification); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error_notification'])): // Utilisez la notification de session ici ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <?php echo htmlspecialchars($_SESSION['error_notification']); unset($_SESSION['error_notification']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_notification): ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <?php echo htmlspecialchars($error_notification); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($voyages_passager)): ?>
                        <div class="alert alert-info text-center" role="alert">
                            Vous n'avez pas encore de voyages enregistrés en tant que passager.
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="accordionVoyagesPassager">
                            <?php foreach ($voyages_passager as $voyage): ?>
                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header" id="heading<?= htmlspecialchars($voyage['participation_id']) ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= htmlspecialchars($voyage['participation_id']) ?>" aria-expanded="false" aria-controls="collapse<?= htmlspecialchars($voyage['participation_id']) ?>">
                                            Du <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($voyage['date_depart'] . ' ' . $voyage['heure_depart']))) ?>
                                            de <?= htmlspecialchars($voyage['adresse_depart']) ?> à <?= htmlspecialchars($voyage['adresse_arrivee']) ?>
                                            (Chauffeur: <?= htmlspecialchars($voyage['chauffeur_pseudo']) ?>)
                                        </button>
                                    </h2>
                                    <div id="collapse<?= htmlspecialchars($voyage['participation_id']) ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= htmlspecialchars($voyage['participation_id']) ?>" data-bs-parent="#accordionVoyagesPassager">
                                        <div class="accordion-body">
                                            <p><strong>Départ:</strong> <?= htmlspecialchars($voyage['adresse_depart']) ?> le <?= htmlspecialchars(date('d/m/Y', strtotime($voyage['date_depart']))) ?> à <?= htmlspecialchars(substr($voyage['heure_depart'], 0, 5)) ?></p>
                                            <p><strong>Arrivée:</strong> <?= htmlspecialchars($voyage['adresse_arrivee']) ?> à <?= htmlspecialchars(substr($voyage['heure_arrivee'], 0, 5)) ?></p>
                                            <p><strong>Chauffeur:</strong> <?= htmlspecialchars($voyage['chauffeur_pseudo']) ?></p>
                                            <p><strong>Prix par personne:</strong> <?= htmlspecialchars(number_format($voyage['prix_personne'], 2, ',', ' ')) ?> €</p>
                                            <p><strong>Nombre de places offertes:</strong> <?= htmlspecialchars($voyage['nb_place']) ?></p>
                                            <p><strong>Statut du covoiturage:</strong>
                                                <?php
                                                    $statut_class = '';
                                                    switch ($voyage['covoiturage_statut']) {
                                                        case 'actif': $statut_class = 'text-success'; break;
                                                        case 'termine': $statut_class = 'text-muted'; break;
                                                        case 'annule': $statut_class = 'text-danger'; break;
                                                        default: $statut_class = 'text-info'; break;
                                                    }
                                                ?>
                                                <span class="<?= $statut_class ?>"><?= htmlspecialchars(ucfirst($voyage['covoiturage_statut'])) ?></span>
                                            </p>
                                            <?php if (!empty($voyage['marque'])): ?>
                                                <p><strong>Véhicule:</strong> <?= htmlspecialchars($voyage['marque'] . ' ' . $voyage['modele'] . ' (' . $voyage['couleur'] . ' - ' . $voyage['immatriculation'] . ')') ?></p>
                                            <?php else: ?>
                                                <p class="text-muted">Informations véhicule non disponibles.</p>
                                            <?php endif; ?>

                                            <hr>
                                            <div class="d-flex justify-content-end">
                                                <?php if ($voyage['covoiturage_statut'] == 'termine'): ?>
                                                    <a href="laisser_avis.php?covoiturage_id=<?= htmlspecialchars($voyage['covoiturage_id']) ?>" class="btn btn-sm btn-outline-info me-2">Laisser un avis</a>
                                                <?php endif; ?>
                                                <?php
                                                // Pour cet historique, on ne mettra pas d'option d'annulation car ce sont des voyages passés ou annulés.
                                                // L'annulation se ferait depuis "Mes réservations actuelles".
                                                ?>
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