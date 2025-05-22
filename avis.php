<?php
session_start();
require_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn
include_once 'header.php';

// Définir les IDs de rôle selon votre table 'role' (ASSUREZ-VOUS QUE CES IDS CORRESPONDENT À VOTRE BASE DE DONNÉES)
$ID_ROLE_ADMIN = 1;     // Exemple d'ID pour le rôle d'administrateur
$ID_ROLE_EMPLOYE = 3;   // Exemple d'ID pour le rôle d'employé (si vous en avez un distinct)

// Vérifier si l'utilisateur est un administrateur ou un employé pour accéder à cette page
if (!isset($_SESSION['utilisateur_id']) || !isset($_SESSION['utilisateur_role_id']) ||
    ($_SESSION['utilisateur_role_id'] != $ID_ROLE_ADMIN && $_SESSION['utilisateur_role_id'] != $ID_ROLE_EMPLOYE)) {
    $_SESSION['error_notification'] = "Accès non autorisé. Vous devez être administrateur ou employé.";
    header("Location: index.php"); // Rediriger vers la page d'accueil ou de connexion
    exit();
}

$avis_list = [];
$notification = '';
$error_notification = '';
$filtre_statut = $_GET['statut'] ?? 'tous'; // Par défaut, afficher tous les statuts

try {
    $sql = "
        SELECT
            a.id AS avis_id,
            a.commentaire,
            a.note,
            a.statut AS avis_statut,
            DATE_FORMAT(a.date_avis, '%d/%m/%Y %H:%i') AS date_avis_formattee,
            u.pseudo AS passager_pseudo,
            u.email AS passager_email,
            c.adresse_depart,
            c.adresse_arrivee,
            DATE_FORMAT(c.date_depart, '%d/%m/%Y') AS covoiturage_date_depart,
            c.heure_depart,
            ch.pseudo AS chauffeur_pseudo,
            ch.email AS chauffeur_email,
            ch.id AS chauffeur_id -- Important pour potentiellement contacter le chauffeur
        FROM
            avis a
        JOIN
            utilisateur u ON a.utilisateur_id = u.id
        JOIN
            covoiturage c ON a.covoiturage_id = c.id
        JOIN
            utilisateur ch ON c.utilisateur_id = ch.id -- Joindre pour obtenir les infos du chauffeur
    ";

    $params = [];
    if ($filtre_statut !== 'tous' && in_array($filtre_statut, ['en_attente', 'approuve', 'rejete'])) {
        $sql .= " WHERE a.statut = :statut";
        $params[':statut'] = $filtre_statut;
    }

    $sql .= " ORDER BY a.date_avis DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $avis_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_notification = "Erreur lors du chargement des avis : " . $e->getMessage();
    error_log("Error loading avis (avis.php): " . $e->getMessage());
}

// Traitement de l'approbation/rejet d'avis (si un employé gère cela)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_avis'])) {
    $avis_id = $_POST['avis_id'] ?? null;
    $action = $_POST['action_avis'] ?? null; // 'approuver' ou 'rejeter'

    if ($avis_id && ($action == 'approuver' || $action == 'rejeter')) {
        try {
            $conn->beginTransaction();

            $new_statut = ($action == 'approuver') ? 'approuve' : 'rejete';
            $stmtUpdateAvis = $conn->prepare("UPDATE avis SET statut = :statut WHERE id = :avis_id");
            $stmtUpdateAvis->execute([':statut' => $new_statut, ':avis_id' => $avis_id]);

            // Si l'avis est approuvé et qu'il était en attente (et potentiellement négatif),
            // ou si la logique de crédit dépend de l'approbation de l'avis en cas de problème,
            // vous pourriez avoir besoin d'une logique ici.
            // Pour l'US11, le crédit est mis à jour sur `valider_trajet_passager.php` si "tout s'est bien passé".
            // Si le trajet s'est "mal passé", le crédit n'est PAS mis à jour avant résolution.
            // Donc, si un avis négatif est REJETÉ, le crédit ne doit pas être mis à jour.
            // Si un avis négatif est finalement APPROUVÉ après résolution, le crédit DOIT être mis à jour.
            // Cela nécessiterait une logique plus complexe ici, liée à la résolution du "mal passé".
            // Pour l'instant, je m'en tiens à la US11 : crédit mis à jour SEULEMENT si "tout s'est bien passé" initialement.

            $conn->commit();
            $_SESSION['notification'] = "L'avis a été " . ($action == 'approuver' ? "approuvé" : "rejeté") . " avec succès.";
            header("Location: avis.php?statut=" . $filtre_statut); // Rediriger pour rafraîchir la page
            exit();

        } catch (PDOException $e) {
            $conn->rollBack();
            $_SESSION['error_notification'] = "Erreur lors de la mise à jour de l'avis : " . $e->getMessage();
            error_log("Error updating avis status (avis.php): " . $e->getMessage());
            header("Location: avis.php?statut=" . $filtre_statut);
            exit();
        }
    } else {
        $_SESSION['error_notification'] = "Action ou identifiant d'avis invalide.";
        header("Location: avis.php?statut=" . $filtre_statut);
        exit();
    }
}
?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-10">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Gestion des Avis Clients</h2>

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

                    <div class="mb-4 d-flex justify-content-center">
                        <div class="btn-group" role="group" aria-label="Filtre par statut">
                            <a href="avis.php?statut=tous" class="btn btn-<?php echo ($filtre_statut == 'tous') ? 'primary' : 'outline-primary'; ?>">Tous les avis</a>
                            <a href="avis.php?statut=en_attente" class="btn btn-<?php echo ($filtre_statut == 'en_attente') ? 'warning' : 'outline-warning'; ?>">En attente</a>
                            <a href="avis.php?statut=approuve" class="btn btn-<?php echo ($filtre_statut == 'approuve') ? 'success' : 'outline-success'; ?>">Approuvés</a>
                            <a href="avis.php?statut=rejete" class="btn btn-<?php echo ($filtre_statut == 'rejete') ? 'danger' : 'outline-danger'; ?>">Rejetés</a>
                        </div>
                    </div>

                    <?php if (empty($avis_list)): ?>
                        <div class="alert alert-info text-center" role="alert">
                            Aucun avis à afficher pour le moment avec ce filtre.
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="accordionAvis">
                            <?php foreach ($avis_list as $avis): ?>
                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header" id="headingAvis<?= htmlspecialchars($avis['avis_id']) ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAvis<?= htmlspecialchars($avis['avis_id']) ?>" aria-expanded="false" aria-controls="collapseAvis<?= htmlspecialchars($avis['avis_id']) ?>">
                                            Avis du <?= htmlspecialchars($avis['date_avis_formattee']) ?> par <strong><?= htmlspecialchars($avis['passager_pseudo']) ?></strong>
                                            (Note: <?= htmlspecialchars($avis['note']) ?>/5)
                                            <span class="ms-auto badge 
                                                <?php
                                                    switch ($avis['avis_statut']) {
                                                        case 'en_attente': echo 'bg-warning text-dark'; break;
                                                        case 'approuve': echo 'bg-success'; break;
                                                        case 'rejete': echo 'bg-danger'; break;
                                                        default: echo 'bg-secondary'; break;
                                                    }
                                                ?>
                                            "><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $avis['avis_statut']))) ?></span>
                                        </button>
                                    </h2>
                                    <div id="collapseAvis<?= htmlspecialchars($avis['avis_id']) ?>" class="accordion-collapse collapse" aria-labelledby="headingAvis<?= htmlspecialchars($avis['avis_id']) ?>" data-bs-parent="#accordionAvis">
                                        <div class="accordion-body">
                                            <p><strong>Covoiturage:</strong> de <?= htmlspecialchars($avis['adresse_depart']) ?> à <?= htmlspecialchars($avis['adresse_arrivee']) ?>, le <?= htmlspecialchars($avis['covoiturage_date_depart']) ?> à <?= htmlspecialchars(substr($avis['heure_depart'], 0, 5)) ?></p>
                                            <p><strong>Passager:</strong> <?= htmlspecialchars($avis['passager_pseudo']) ?> (<?= htmlspecialchars($avis['passager_email']) ?>)</p>
                                            <p><strong>Chauffeur:</strong> <?= htmlspecialchars($avis['chauffeur_pseudo']) ?> (<?= htmlspecialchars($avis['chauffeur_email']) ?>)</p>
                                            <p><strong>Commentaire:</strong> "<?= !empty($avis['commentaire']) ? htmlspecialchars($avis['commentaire']) : '<i>(Aucun commentaire)</i>' ?>"</p>
                                            <p><strong>Note:</strong> <span class="text-warning"><?= str_repeat('★', $avis['note']) ?><span class="text-muted"><?= str_repeat('★', 5 - $avis['note']) ?></span></span></p>
                                            
                                            <?php if ($avis['avis_statut'] === 'en_attente'): ?>
                                                <hr>
                                                <div class="d-flex justify-content-end">
                                                    <form action="avis.php?statut=<?= htmlspecialchars($filtre_statut) ?>" method="POST" class="me-2">
                                                        <input type="hidden" name="avis_id" value="<?= htmlspecialchars($avis['avis_id']) ?>">
                                                        <button type="submit" name="action_avis" value="approuver" class="btn btn-success btn-sm">Approuver l'avis</button>
                                                    </form>
                                                    <form action="avis.php?statut=<?= htmlspecialchars($filtre_statut) ?>" method="POST">
                                                        <input type="hidden" name="avis_id" value="<?= htmlspecialchars($avis['avis_id']) ?>">
                                                        <button type="submit" name="action_avis" value="rejeter" class="btn btn-danger btn-sm">Rejeter l'avis</button>
                                                    </form>
                                                    <?php if ($avis['note'] < 3): ?>
                                                         <a href="mailto:<?= htmlspecialchars($avis['chauffeur_email']) ?>?subject=Problème%20concernant%20un%20covoiturage%20(%C3%A0%20partir%20de%20<?= urlencode($avis['adresse_depart']) ?>)" class="btn btn-warning btn-sm ms-2">Contacter le chauffeur</a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'footer.php'; ?>