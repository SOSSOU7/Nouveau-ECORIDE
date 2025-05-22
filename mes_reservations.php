<?php

require_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn
include_once 'header.php'; // Assurez-vous que header.php inclut les balises HTML de base et les liens CSS/JS (Bootstrap, Font Awesome)

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour voir vos réservations.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Initialiser les notifications
$notification = '';
$error_notification = '';
$current_reservations = [];

try {
    // Récupérer les réservations actuelles du passager pour l'utilisateur
    // Une 'actuelle' réservation signifie que la date/heure de départ du covoiturage est future
    // et que le statut du covoiturage est 'actif'.
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
            ch.telephone AS chauffeur_telephone,
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
            AND c.statut = 'actif'
            AND (
                c.date_depart > CURDATE()
                OR (c.date_depart = CURDATE() AND c.heure_depart > CURTIME())
            )
        ORDER BY
            c.date_depart ASC, c.heure_depart ASC
    ");
    $stmt->execute([':utilisateur_id' => $utilisateur_id]);
    $current_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_notification = "Erreur lors du chargement de vos réservations : " . $e->getMessage();
    error_log("Current reservations load error (mes_reservations.php): " . $e->getMessage());
}
?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-9">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Mes réservations actuelles</h2>

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

                    <?php if (empty($current_reservations)): ?>
                        <div class="alert alert-info text-center" role="alert">
                            Vous n'avez aucune réservation de covoiturage en cours.
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="accordionCurrentReservations">
                            <?php foreach ($current_reservations as $reservation): ?>
                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header" id="heading<?= htmlspecialchars($reservation['participation_id']) ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= htmlspecialchars($reservation['participation_id']) ?>" aria-expanded="false" aria-controls="collapse<?= htmlspecialchars($reservation['participation_id']) ?>">
                                            Départ le <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($reservation['date_depart'] . ' ' . $reservation['heure_depart']))) ?>
                                            de <?= htmlspecialchars($reservation['adresse_depart']) ?> à <?= htmlspecialchars($reservation['adresse_arrivee']) ?>
                                            (Chauffeur: <?= htmlspecialchars($reservation['chauffeur_pseudo']) ?>)
                                        </button>
                                    </h2>
                                    <div id="collapse<?= htmlspecialchars($reservation['participation_id']) ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= htmlspecialchars($reservation['participation_id']) ?>" data-bs-parent="#accordionCurrentReservations">
                                        <div class="accordion-body">
                                            <p><strong>Départ:</strong> <?= htmlspecialchars($reservation['adresse_depart']) ?> le <?= htmlspecialchars(date('d/m/Y', strtotime($reservation['date_depart']))) ?> à <?= htmlspecialchars(substr($reservation['heure_depart'], 0, 5)) ?></p>
                                            <p><strong>Arrivée:</strong> <?= htmlspecialchars($reservation['adresse_arrivee']) ?> à <?= htmlspecialchars(substr($reservation['heure_arrivee'], 0, 5)) ?></p>
                                            <p><strong>Chauffeur:</strong> <?= htmlspecialchars($reservation['chauffeur_pseudo']) ?>
                                                <?php if (!empty($reservation['chauffeur_telephone'])): ?>
                                                    (<a href="tel:<?= htmlspecialchars($reservation['chauffeur_telephone']) ?>"><?= htmlspecialchars($reservation['chauffeur_telephone']) ?></a>)
                                                <?php endif; ?>
                                            </p>
                                            <p><strong>Prix par personne:</strong> <?= htmlspecialchars(number_format($reservation['prix_personne'], 2, ',', ' ')) ?> €</p>
                                            <p><strong>Places offertes:</strong> <?= htmlspecialchars($reservation['nb_place']) ?></p>
                                            <p><strong>Statut du covoiturage:</strong>
                                                <span class="text-success"><?= htmlspecialchars(ucfirst($reservation['covoiturage_statut'])) ?></span>
                                            </p>
                                            <?php if (!empty($reservation['marque'])): ?>
                                                <p><strong>Véhicule:</strong> <?= htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele'] . ' (' . $reservation['couleur'] . ' - ' . $reservation['immatriculation'] . ')') ?></p>
                                            <?php else: ?>
                                                <p class="text-muted">Informations véhicule non disponibles.</p>
                                            <?php endif; ?>

                                            <hr>
                                            <div class="d-flex justify-content-end">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmCancelReservation(<?= htmlspecialchars($reservation['participation_id']) ?>, <?= htmlspecialchars($reservation['covoiturage_id']) ?>)">Annuler la réservation</button>
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
function confirmCancelReservation(participationId, covoiturageId) {
    if (confirm("Êtes-vous sûr de vouloir annuler cette réservation ? Cette action est irréversible.")) {
        // Rediriger vers un script qui gère l'annulation
        window.location.href = 'annuler_reservation.php?participation_id=' + participationId + '&covoiturage_id=' + covoiturageId;
    }
}
</script>