<?php

require_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn
include_once 'header.php';

// Définir les IDs de rôle selon votre table 'role' (ASSUREZ-VOUS QUE CES IDS CORRESPONDENT À VOTRE BASE DE DONNÉES)
$ID_ROLE_ADMIN = 1;     // ID pour le rôle d'administrateur
$ID_ROLE_EMPLOYE = 3;   // ID pour le rôle d'employé

// Vérifier si l'utilisateur est connecté et a le rôle d'administrateur ou d'employé
if (!isset($_SESSION['utilisateur_id']) || !isset($_SESSION['utilisateur_role_id']) ||
    ($_SESSION['utilisateur_role_id'] != $ID_ROLE_ADMIN && $_SESSION['utilisateur_role_id'] != $ID_ROLE_EMPLOYE)) {
    $_SESSION['error_notification'] = "Accès non autorisé. Vous devez être administrateur ou employé pour accéder à cet espace.";
    header("Location: index.php"); // Rediriger vers la page d'accueil ou de connexion
    exit();
}

$notification = '';
$error_notification = '';
$covoiturages_probleme = [];

// Récupérer les covoiturages signalés comme "mal passés"
// Ces covoiturages sont ceux pour lesquels un avis avec le statut 'en_attente' existe.
try {
    $stmtProblemes = $conn->prepare("
        SELECT
            c.id AS covoiturage_id,
            c.adresse_depart,
            c.adresse_arrivee,
            c.date_depart,
            c.heure_depart,
            c.heure_arrivee,
            p.pseudo AS passager_pseudo,
            p.email AS passager_email,
            ch.pseudo AS chauffeur_pseudo,
            ch.email AS chauffeur_email,
            a.commentaire AS avis_commentaire_probleme,
            a.note AS avis_note_probleme,
            a.date_avis
        FROM
            avis a
        JOIN
            covoiturage c ON a.covoiturage_id = c.id
        JOIN
            utilisateur p ON a.utilisateur_id = p.id -- Passager qui a laissé l'avis
        JOIN
            utilisateur ch ON c.utilisateur_id = ch.id -- Chauffeur du covoiturage
        WHERE
            a.statut = 'en_attente' AND a.note < 3 -- Filtrer les avis négatifs (note < 3) en attente
        ORDER BY
            a.date_avis DESC
    ");
    $stmtProblemes->execute();
    $covoiturages_probleme = $stmtProblemes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_notification = "Erreur lors du chargement des covoiturages problématiques : " . $e->getMessage();
    error_log("Error loading problematic carpools (espace_employe.php): " . $e->getMessage());
}

?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-9">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Espace Employé</h2>

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

                    <div class="list-group mt-3">
                        <h5 class="mb-3 text-center">Actions de l'Employé</h5>
                        <a href="avis.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>Gérer les avis des utilisateurs (Validation/Refus)</span>
                            <i class="fas fa-comments"></i>
                        </a>
                        </div>

                    <hr class="my-4">

                    <h3 class="text-center mb-4 fw-bold text-danger">Covoiturages ayant eu un problème (Avis en attente)</h3>

                    <?php if (empty($covoiturages_probleme)): ?>
                        <div class="alert alert-success text-center" role="alert">
                            Aucun covoiturage signalé comme problématique pour le moment.
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="accordionProblemes">
                            <?php foreach ($covoiturages_probleme as $probleme): ?>
                                <div class="accordion-item mb-3 border border-danger">
                                    <h2 class="accordion-header" id="headingProbleme<?= htmlspecialchars($probleme['covoiturage_id']) ?>">
                                        <button class="accordion-button collapsed bg-danger text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProbleme<?= htmlspecialchars($probleme['covoiturage_id']) ?>" aria-expanded="false" aria-controls="collapseProbleme<?= htmlspecialchars($probleme['covoiturage_id']) ?>">
                                            Problème sur Covoiturage #<?= htmlspecialchars($probleme['covoiturage_id']) ?>
                                            (Départ: <?= htmlspecialchars($probleme['adresse_depart']) ?> le <?= htmlspecialchars(date('d/m/Y', strtotime($probleme['date_depart']))) ?>)
                                            <span class="ms-auto badge bg-light text-danger">Avis en attente</span>
                                        </button>
                                    </h2>
                                    <div id="collapseProbleme<?= htmlspecialchars($probleme['covoiturage_id']) ?>" class="accordion-collapse collapse" aria-labelledby="headingProbleme<?= htmlspecialchars($probleme['covoiturage_id']) ?>" data-bs-parent="#accordionProblemes">
                                        <div class="accordion-body">
                                            <h5>Détails du Trajet :</h5>
                                            <p><strong>Numéro Covoiturage:</strong> <?= htmlspecialchars($probleme['covoiturage_id']) ?></p>
                                            <p><strong>Départ:</strong> <?= htmlspecialchars($probleme['adresse_depart']) ?> le <?= htmlspecialchars(date('d/m/Y', strtotime($probleme['date_depart']))) ?> à <?= htmlspecialchars(substr($probleme['heure_depart'], 0, 5)) ?></p>
                                            <p><strong>Arrivée:</strong> <?= htmlspecialchars($probleme['adresse_arrivee']) ?> (heure d'arrivée estimée: <?= htmlspecialchars(substr($probleme['heure_arrivee'], 0, 5)) ?>)</p>

                                            <h5 class="mt-4">Informations des Intéressés :</h5>
                                            <p><strong>Passager ayant signalé le problème:</strong></p>
                                            <ul>
                                                <li>Pseudo: <?= htmlspecialchars($probleme['passager_pseudo']) ?></li>
                                                <li>Email: <a href="mailto:<?= htmlspecialchars($probleme['passager_email']) ?>"><?= htmlspecialchars($probleme['passager_email']) ?></a></li>
                                            </ul>
                                            <p><strong>Chauffeur concerné:</strong></p>
                                            <ul>
                                                <li>Pseudo: <?= htmlspecialchars($probleme['chauffeur_pseudo']) ?></li>
                                                <li>Email: <a href="mailto:<?= htmlspecialchars($probleme['chauffeur_email']) ?>"><?= htmlspecialchars($probleme['chauffeur_email']) ?></a></li>
                                            </ul>

                                            <h5 class="mt-4">Avis du Passager (Problème signalé) :</h5>
                                            <p><strong>Commentaire:</strong> "<?= !empty($probleme['avis_commentaire_probleme']) ? htmlspecialchars($probleme['avis_commentaire_probleme']) : '<i>(Aucun commentaire détaillé)</i>' ?>"</p>
                                            <p><strong>Note attribuée:</strong> <span class="text-warning"><?= str_repeat('★', $probleme['avis_note_probleme']) ?><span class="text-muted"><?= str_repeat('★', 5 - $probleme['avis_note_probleme']) ?></span></span> (Déposé le <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($probleme['date_avis']))) ?>)</p>

                                            <hr>
                                            <div class="d-flex justify-content-end">
                                                <a href="avis.php?statut=en_attente" class="btn btn-primary btn-sm me-2">Gérer cet avis (Approuver/Rejeter)</a>
                                                <a href="mailto:<?= htmlspecialchars($probleme['chauffeur_email']) ?>?subject=Problème%20signalé%20sur%20votre%20covoiturage%20#<?= htmlspecialchars($probleme['covoiturage_id']) ?>" class="btn btn-warning btn-sm">Contacter le chauffeur</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <a href="espace_passager.php" class="btn btn-secondary">Retour à l'espace personnel (si applicable)</a>
                        </div>

                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'footer.php'; ?>