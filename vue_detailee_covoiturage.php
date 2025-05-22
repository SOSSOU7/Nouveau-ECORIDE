<?php

require_once 'auth.php'; // Pour la connexion DB ($conn) et potentiellement l'authentification si nécessaire
include_once 'header.php'; 

// --- DEBUGGING: Display all errors for development ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- END DEBUGGING ---

$covoiturage_id = filter_input(INPUT_GET, 'covoiturage_id', FILTER_VALIDATE_INT);
$covoiturage = null;
$avis_conducteur = [];
$preferences_conducteur = null; // Initialisation
$utilisateur_connecte_id = $_SESSION['utilisateur_id'] ?? null; // Récupérer l'ID de l'utilisateur connecté

// Récupération de tous les paramètres de recherche/filtre passés depuis la page précédente
// Ces variables seront utilisées pour reconstruire le lien de retour.
$adresse_depart_recherche = trim($_GET['adresse_depart'] ?? '');
$adresse_arrivee_recherche = trim($_GET['adresse_arrivee'] ?? '');
$date_recherche = trim($_GET['date_depart'] ?? '');
$tarif_maximum = trim($_GET['tarif_maximum'] ?? '');
$duree_maximale = trim($_GET['duree_maximale'] ?? '');
$note_minimale = trim($_GET['note_minimale'] ?? '');
$voiture_eco = trim($_GET['eco'] ?? '');


if (!$covoiturage_id) {
    // Si l'ID est manquant ou invalide, rediriger ou afficher une erreur
    $_SESSION['error_notification'] = "Covoiturage non spécifié ou ID invalide.";
    header('Location: formulaire_covoiturage.php'); // Rediriger vers la page de recherche
    exit();
}

try {
    // 1. Récupérer les détails du covoiturage, du conducteur et de la voiture
    // La note moyenne du conducteur est calculée sur tous les avis qu'il a reçus
    // en étant le conducteur d'un covoiturage.
    $sql_covoiturage_detail = "
        SELECT 
            c.id AS covoiturage_id,
            c.adresse_depart,
            c.adresse_arrivee,
            c.date_depart,
            c.heure_depart,
            c.heure_arrivee,
            c.prix_personne,
            c.nb_place,
            u.id AS conducteur_id,
            u.nom AS chauffeur_nom,
            u.prenom AS chauffeur_prenom,
            u.photo_profil,
            v.marque AS voiture_marque,
            v.modele AS voiture_modele,
            v.energie,
            COALESCE(AVG(a_note.note), 0) AS chauffeur_note -- Calcul de la note moyenne du conducteur
        FROM 
            covoiturage c
        JOIN 
            utilisateur u ON c.utilisateur_id = u.id
        JOIN 
            voiture v ON c.voiture_id = v.id
        LEFT JOIN 
            avis a_note ON a_note.covoiturage_id IN (SELECT id FROM covoiturage WHERE utilisateur_id = u.id) -- Avis liés à tous les covoiturages de ce conducteur
        WHERE 
            c.id = :covoiturage_id
        GROUP BY 
            c.id, c.adresse_depart, c.adresse_arrivee, c.date_depart, c.heure_depart, 
            c.heure_arrivee, c.prix_personne, c.nb_place, u.id, u.nom, u.prenom, 
            u.photo_profil, v.marque, v.modele, v.energie
    ";
    $stmt_covoiturage = $conn->prepare($sql_covoiturage_detail);
    $stmt_covoiturage->execute([':covoiturage_id' => $covoiturage_id]);
    $covoiturage = $stmt_covoiturage->fetch(PDO::FETCH_ASSOC);

    if (!$covoiturage) {
        $_SESSION['error_notification'] = "Covoiturage introuvable.";
        header('Location: formulaire_covoiturage.php');
        exit();
    }



    // 2. Récupérer les avis spécifiques au conducteur
    // Nous recherchons tous les avis laissés sur les covoiturages conduits par ce conducteur.
    $sql_avis = "
        SELECT 
            a.commentaire, 
            a.note, 
            a.date_avis,
            u_auteur.pseudo AS auteur_avis_pseudo 
        FROM 
            avis a
        JOIN 
            utilisateur u_auteur ON a.utilisateur_id = u_auteur.id -- L'auteur de l'avis
        JOIN
            covoiturage c_avis ON a.covoiturage_id = c_avis.id -- Le covoiturage associé à cet avis
        WHERE 
            c_avis.utilisateur_id = :conducteur_id -- Le conducteur de ce covoiturage (cible de l'avis)
        ORDER BY 
            a.date_avis DESC
    ";

    $stmt_avis = $conn->prepare($sql_avis);
    $stmt_avis->execute([':conducteur_id' => $covoiturage['conducteur_id']]);
    $avis_conducteur = $stmt_avis->fetchAll(PDO::FETCH_ASSOC);

    // 3. Récupérer les préférences du conducteur (correction: 'animaux_compagnie', 'musique' absent)
    $sql_preferences = "
        SELECT 
            fumeur, 
            animaux_compagnie
        FROM 
            preferences
        WHERE 
            utilisateur_id = :conducteur_id
    ";
    $stmt_preferences = $conn->prepare($sql_preferences);
    $stmt_preferences->execute([':conducteur_id' => $covoiturage['conducteur_id']]);
    $preferences_conducteur = $stmt_preferences->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error fetching covoiturage details: " . $e->getMessage()); 
    $_SESSION['error_notification'] = "Une erreur est survenue lors du chargement des détails du covoiturage. Veuillez réessayer."; 
    header('Location: formulaire_covoiturage.php');
    exit();
}
?>

<main class="main-page">
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="alert alert-success text-center mt-3" role="alert">
            <?php echo htmlspecialchars($_SESSION['notification']); unset($_SESSION['notification']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_notification'])): ?>
        <div class="alert alert-danger text-center mt-3" role="alert">
            <?php echo htmlspecialchars($_SESSION['error_notification']); unset($_SESSION['error_notification']); ?>
        </div>
    <?php endif; ?>

    <section class="container mt-5">
        <h1 class="text-center mb-4 fw-bold text-primary">Détails du covoiturage</h1>

        <?php if ($covoiturage): ?>
            <div class="card shadow-lg p-4 mb-5 bg-white rounded">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4"><?= htmlspecialchars($covoiturage['adresse_depart']) ?> <i class="bi bi-arrow-right-short"></i> <?= htmlspecialchars($covoiturage['adresse_arrivee']) ?></h2>
                    
                    <div class="row align-items-center mb-4 border-bottom pb-3">
                        <div class="col-md-3 text-center">
                            <?php if (!empty($covoiturage['photo_profil'])): ?>
                                <img src="<?php echo htmlspecialchars($covoiturage['photo_profil']); ?>" alt="Photo de profil du chauffeur" class="rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                            <?php else: ?>
                                <img src="img/default_driver.jpeg" alt="Chauffeur par défaut" class="rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                            <?php endif; ?>
                            <h5 class="fw-bold"><?= htmlspecialchars($covoiturage['chauffeur_prenom'] . ' ' . $covoiturage['chauffeur_nom']) ?></h5>
                            <p class="stars" style="font-size: 20px; color: gold;">
                                <?php
                                $note = (float)($covoiturage['chauffeur_note'] ?? 0); 
                                for ($i = 0; $i < 5; $i++) {
                                    echo ($i < round($note)) ? '★' : '☆'; 
                                }
                                ?> (<?= number_format($note, 1) ?>/5)
                            </p>
                        </div>
                        <div class="col-md-9">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($covoiturage['date_depart']))) ?></li>
                                <li class="list-group-item"><strong>Heure de départ :</strong> <?= htmlspecialchars(substr($covoiturage['heure_depart'], 0, 5)) ?></li>
                                <li class="list-group-item"><strong>Heure d'arrivée estimée :</strong> <?= htmlspecialchars(substr($covoiturage['heure_arrivee'], 0, 5)) ?></li>
                                <li class="list-group-item"><strong>Places disponibles :</strong> <?= htmlspecialchars($covoiturage['nb_place']) ?></li>
                                <li class="list-group-item"><strong>Prix par personne :</strong> <?= htmlspecialchars(number_format($covoiturage['prix_personne'], 2, ',', ' ')) ?> €</li>
                                <li class="list-group-item">
                                    <strong>Voyage écologique :</strong> 
                                    <?php if (($covoiturage['energie'] ?? '') == 'electrique'): ?>
                                        <span class="badge bg-success">Oui <i class="bi bi-leaf-fill"></i></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non</span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-4 border-bottom pb-3">
                        <h4 class="fw-bold mb-3">Détails du véhicule</h4>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Marque :</strong> <?= htmlspecialchars($covoiturage['voiture_marque'] ?? 'N/A') ?></li>
                            <li class="list-group-item"><strong>Modèle :</strong> <?= htmlspecialchars($covoiturage['voiture_modele'] ?? 'N/A') ?></li>
                            <li class="list-group-item"><strong>Énergie :</strong> <?= htmlspecialchars($covoiturage['energie'] ?? 'N/A') ?></li>
                        </ul>
                    </div>

                    <div class="mb-4 border-bottom pb-3">
                        <h4 class="fw-bold mb-3">Préférences du conducteur</h4>
                        <?php if ($preferences_conducteur): ?>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <strong>Fumeur :</strong> 
                                    <?= ($preferences_conducteur['fumeur'] == 1) ? '<span class="badge bg-danger">Oui</span>' : '<span class="badge bg-success">Non</span>' ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Animaux de compagnie :</strong> 
                                    <?= ($preferences_conducteur['animaux_compagnie'] == 1) ? '<span class="badge bg-warning text-dark">Autorisés</span>' : '<span class="badge bg-dark">Non autorisés</span>' ?>
                                </li>
                            </ul>
                        <?php else: ?>
                            <p class="alert alert-info">Aucune préférence définie par le conducteur.</p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <h4 class="fw-bold mb-3">Avis sur ce conducteur</h4>
                        <?php if (!empty($avis_conducteur)): ?>
                            <?php foreach ($avis_conducteur as $avis): ?>
                                <div class="card mb-3 p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">De: <?= htmlspecialchars($avis['auteur_avis_pseudo'] ?? 'Anonyme') ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars(date('d/m/Y', strtotime($avis['date_avis']))) ?></small>
                                    </div>
                                    <p class="mb-1">Note: 
                                        <?php 
                                        $avis_note = (float)($avis['note'] ?? 0);
                                        for ($i = 0; $i < 5; $i++): 
                                            echo ($i < round($avis_note)) ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-star text-muted"></i>'; 
                                        endfor; 
                                        ?>
                                    </p>
                                    <?php if (!empty($avis['commentaire'])): ?>
                                        <p class="mb-0 text-muted fst-italic">"<?= htmlspecialchars($avis['commentaire']) ?>"</p>
                                    <?php else: ?>
                                        <p class="mb-0 text-muted fst-italic">Pas de commentaire.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="alert alert-info">Ce conducteur n'a pas encore reçu d'avis.</p>
                        <?php endif; ?>
                    </div>

                    <div class="text-center mt-4">
                        <?php
                        // Reconstruire l'URL avec tous les paramètres de recherche et filtres
                        $queryParams = [];
                        if (!empty($adresse_depart_recherche)) {
                            $queryParams['adresse_depart'] = urlencode($adresse_depart_recherche);
                        }
                        if (!empty($adresse_arrivee_recherche)) {
                            $queryParams['adresse_arrivee'] = urlencode($adresse_arrivee_recherche);
                        }
                        if (!empty($date_recherche)) {
                            $queryParams['date_depart'] = urlencode($date_recherche);
                        }
                        if (!empty($tarif_maximum)) {
                            $queryParams['tarif_maximum'] = urlencode($tarif_maximum);
                        }
                        if (!empty($duree_maximale)) {
                            $queryParams['duree_maximale'] = urlencode($duree_maximale);
                        }
                        if (!empty($note_minimale)) {
                            $queryParams['note_minimale'] = urlencode($note_minimale);
                        }
                        if (!empty($voiture_eco)) {
                            $queryParams['eco'] = urlencode($voiture_eco);
                        }
                        
                        // Ajoutez le paramètre de déclenchement de la recherche pour que la page de recherche affiche les résultats
                        $queryParams['rechercher_covoiturage'] = 1;

                        $backLink = 'formulaire_covoiturage.php?' . http_build_query($queryParams);
                        ?>
                        <a href="<?= htmlspecialchars($backLink) ?>" class="btn btn-secondary me-2">Retour à la recherche</a>
                        
                        <?php 
                        $est_conducteur_connecte="";
                        // 1. Si l'utilisateur connecté EST le conducteur de ce covoiturage
                        if ($est_conducteur_connecte): 
                        ?>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#arreterCovoiturageModal">
                                Arrêter le covoiturage
                            </button>
                        <?php 
                        // 2. Si l'utilisateur n'est PAS le conducteur de ce covoiturage
                        else: 
                        ?>
                            <?php if (isset($_SESSION['utilisateur_id'])): // Si l'utilisateur est connecté (mais n'est pas le conducteur) ?>
                                <?php if ($covoiturage['nb_place'] > 0): ?>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmParticipationModal">
                                        Participer (<?= htmlspecialchars(number_format($covoiturage['prix_personne'], 2, ',', ' ')) ?> crédits)
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-warning" disabled>Plus de places disponibles</button>
                                <?php endif; ?>
                            <?php else: // Si l'utilisateur n'est PAS connecté du tout ?>
                                <a href="signin.php?redirect=vue_detailee_covoiturage.php?covoiturage_id=<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>" class="btn btn-success">
                                    Connectez-vous pour participer
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger text-center">Impossible de charger les détails du covoiturage.</div>
        <?php endif; ?>
    </section>

    <div class="modal fade" id="confirmParticipationModal" tabindex="-1" aria-labelledby="confirmParticipationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmParticipationModalLabel">Confirmer votre participation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Vous êtes sur le point de participer à ce covoiturage.<br>
                    Le coût est de <strong><?= htmlspecialchars(number_format($covoiturage['prix_personne'], 2, ',', ' ')) ?> crédits</strong>.
                    <br><br>
                    Votre solde actuel est de <strong><?= htmlspecialchars($_SESSION['utilisateur_credit'] ?? 'N/A') ?> crédits</strong>.
                    <br><br>
                    Êtes-vous sûr de vouloir continuer ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="participer_covoiturage.php" method="POST" style="display:inline;">
                        <input type="hidden" name="covoiturage_id" value="<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>">
                        <input type="hidden" name="adresse_depart" value="<?= htmlspecialchars($adresse_depart_recherche); ?>">
                        <input type="hidden" name="adresse_arrivee" value="<?= htmlspecialchars($adresse_arrivee_recherche); ?>">
                        <input type="hidden" name="date_depart" value="<?= htmlspecialchars($date_recherche); ?>">
                        <input type="hidden" name="tarif_maximum" value="<?= htmlspecialchars($tarif_maximum); ?>">
                        <input type="hidden" name="duree_maximale" value="<?= htmlspecialchars($duree_maximale); ?>">
                        <input type="hidden" name="note_minimale" value="<?= htmlspecialchars($note_minimale); ?>">
                        <input type="hidden" name="eco" value="<?= htmlspecialchars($voiture_eco); ?>">
                        <button type="submit" class="btn btn-success">Confirmer et Participer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="arreterCovoiturageModal" tabindex="-1" aria-labelledby="arreterCovoiturageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="arreterCovoiturageModalLabel">Arrêter ce covoiturage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Êtes-vous sûr de vouloir arrêter ce covoiturage ?<br>
                    Cela annulera le trajet pour tous les passagers qui y ont participé et les remboursera.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="arreter_covoiturage.php" method="POST" style="display:inline;">
                        <input type="hidden" name="covoiturage_id" value="<?= htmlspecialchars($covoiturage['covoiturage_id']) ?>">
                        <input type="hidden" name="adresse_depart" value="<?= htmlspecialchars($adresse_depart_recherche); ?>">
                        <input type="hidden" name="adresse_arrivee" value="<?= htmlspecialchars($adresse_arrivee_recherche); ?>">
                        <input type="hidden" name="date_depart" value="<?= htmlspecialchars($date_recherche); ?>">
                        <input type="hidden" name="tarif_maximum" value="<?= htmlspecialchars($tarif_maximum); ?>">
                        <input type="hidden" name="duree_maximale" value="<?= htmlspecialchars($duree_maximale); ?>">
                        <input type="hidden" name="note_minimale" value="<?= htmlspecialchars($note_minimale); ?>">
                        <input type="hidden" name="eco" value="<?= htmlspecialchars($voiture_eco); ?>">
                        <button type="submit" class="btn btn-danger">Confirmer l'arrêt</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</main>

<?php include_once 'footer.php'; ?>