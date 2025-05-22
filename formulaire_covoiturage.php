<?php

// Assurez-vous que 'auth.php' gère la connexion à la base de données ($conn) et démarre la session (session_start()).
// Il est crucial que $conn soit un objet PDO valide et que session_start() soit appelé au tout début du script.
require_once 'auth.php'; 
include_once 'header.php'; 

// --- DEBUGGING: Display all errors for development ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); // Affiche toutes les erreurs, y compris les notices et les dépréciations
// --- END DEBUGGING ---

// --- PHP LOGIC FOR US 3: VIEW COVOITURAGES AND US 4: FILTERS ---

$covoiturages = []; // Tableau pour stocker les résultats de la recherche
$proche_covoiturage_date = null; // Pour la suggestion de date si aucun résultat
$search_performed = false; // Indicateur pour savoir si une recherche a été tentée ou si des filtres sont appliqués

// Initialisation de toutes les variables de recherche et de filtre pour éviter les "Undefined variable"
$adresse_depart_recherche = trim($_GET['adresse_depart'] ?? '');
$adresse_arrivee_recherche = trim($_GET['adresse_arrivee'] ?? '');
$date_recherche = trim($_GET['date_depart'] ?? '');

$tarif_maximum = trim($_GET['tarif_maximum'] ?? '');
$duree_maximale = trim($_GET['duree_maximale'] ?? '');
$note_minimale = trim($_GET['note_minimale'] ?? '');
$voiture_eco = trim($_GET['eco'] ?? ''); // 'oui' ou 'non'

// Construction de la requête SQL de base
$base_sql = "SELECT 
                c.id AS covoiturage_id,
                c.adresse_depart,
                c.adresse_arrivee,
                c.date_depart,
                c.heure_depart,
                c.heure_arrivee,
                c.prix_personne,
                c.nb_place,
                u.nom AS chauffeur_nom,
                u.prenom AS chauffeur_prenom,
                -- u.photo, -- Retiré car c'est un BLOB et nécessite un traitement spécial pour l'affichage direct
                v.energie, -- Assurez-vous que cette colonne existe dans 'voiture'
                v.marque AS voiture_marque,
                v.modele AS voiture_modele,
                COALESCE(AVG(a.note), 0) AS chauffeur_note -- Calcul de la note moyenne
            FROM covoiturage c
            JOIN utilisateur u ON c.utilisateur_id = u.id
            JOIN voiture v ON c.voiture_id = v.id
            LEFT JOIN avis a ON a.utilisateur_id = u.id -- Jointure pour les avis
            WHERE c.nb_place > 0 "; // Condition de base: au moins une place disponible

$params = [];
$filter_conditions = [];

// --- Conditions de Recherche Principale ---
// La recherche est considérée comme "lancée" si au moins un des champs de recherche principaux est rempli
if (!empty($adresse_depart_recherche) || !empty($adresse_arrivee_recherche) || !empty($date_recherche)) {
    $search_performed = true;
}

// Ajouter les conditions de recherche si elles sont présentes
if (!empty($adresse_depart_recherche)) {
    $filter_conditions[] = "c.adresse_depart LIKE :adresse_depart";
    $params[':adresse_depart'] = '%' . $adresse_depart_recherche . '%';
}
if (!empty($adresse_arrivee_recherche)) {
    $filter_conditions[] = "c.adresse_arrivee LIKE :adresse_arrivee";
    $params[':adresse_arrivee'] = '%' . $adresse_arrivee_recherche . '%';
}
if (!empty($date_recherche)) {
    $filter_conditions[] = "c.date_depart = :date_recherche";
    $params[':date_recherche'] = $date_recherche;
}

// --- Conditions de Filtre Avancées ---
if (!empty($tarif_maximum)) {
    $filter_conditions[] = "c.prix_personne <= :tarif_maximum";
    $params[':tarif_maximum'] = (float)$tarif_maximum;
    $search_performed = true; // Si un filtre est appliqué, on considère qu'une recherche est active
}
if (!empty($duree_maximale)) {
    $filter_conditions[] = "TIMEDIFF(c.heure_arrivee, c.heure_depart) <= :duree_maximale";
    $params[':duree_maximale'] = $duree_maximale . ':00'; 
    $search_performed = true;
}
if (!empty($note_minimale)) {
    $filter_conditions[] = "COALESCE(AVG(a.note), 0) >= :note_minimale"; 
    $params[':note_minimale'] = (int)$note_minimale;
    $search_performed = true;
}
if ($voiture_eco === 'oui') {
    $filter_conditions[] = "v.energie = 'electrique'"; // Basé sur votre colonne 'energie' dans 'voiture'
    $search_performed = true;
} elseif ($voiture_eco === 'non') {
    $filter_conditions[] = "v.energie != 'electrique'";
    $search_performed = true;
}

// Ajout des conditions de filtre à la requête SQL
if (!empty($filter_conditions)) {
    $base_sql .= " AND " . implode(" AND ", $filter_conditions);
}

// Ajout du GROUP BY et ORDER BY nécessaires pour la note moyenne et le tri
// Note: Assurez-vous que toutes les colonnes non agrégées dans le SELECT sont dans le GROUP BY
$base_sql .= " GROUP BY c.id, c.adresse_depart, c.adresse_arrivee, c.date_depart, c.heure_depart, c.heure_arrivee, 
                c.prix_personne, c.nb_place, u.nom, u.prenom, v.energie, v.marque, v.modele
                ORDER BY c.date_depart ASC, c.heure_depart ASC";

// Exécution de la requête si une recherche a été lancée
if ($search_performed) {
    try {
        $stmt = $conn->prepare($base_sql);
        $stmt->execute($params);
        $covoiturages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si aucun covoiturage n'est trouvé pour la date actuelle, rechercher la date la plus proche.
        if (empty($covoiturages) && !empty($date_recherche)) {
            $sql_proche_date = "SELECT date_depart 
                                FROM covoiturage 
                                WHERE date_depart > :date_recherche 
                                ORDER BY date_depart ASC 
                                LIMIT 1";
            $stmt_proche_date = $conn->prepare($sql_proche_date);
            $stmt_proche_date->execute([':date_recherche' => $date_recherche]);
            $result_proche_date = $stmt_proche_date->fetch(PDO::FETCH_ASSOC);
            if ($result_proche_date) {
                $proche_covoiturage_date = $result_proche_date['date_depart'];
            }
        }

    } catch (PDOException $e) {
        error_log("Database error fetching covoiturages: " . $e->getMessage()); 
        $_SESSION['error_notification'] = "Une erreur est survenue lors du chargement des covoiturages. Détails: " . $e->getMessage(); // Affiche le message PDO pour le débogage
        $covoiturages = []; 
    }
} else {
    // Si aucune recherche ou filtre n'a été appliqué, le tableau de résultats reste vide par défaut.
    $covoiturages = [];
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

    <section class="container col-md-8 col-lg-6 search-form mb-5 mt-5 p-4 rounded shadow bg-light">
        <h4 class="fw-bold text-center mb-4 text-primary">Trouvez votre covoiturage idéal !</h4>
        <form class="form-horizontal" action="formulaire_covoiturage.php" method="GET">
            <div class="form-group mb-3">
                <label class="control-label col-sm-12" for="adresse_depart">Adresse de départ:</label>
                <div class="col-sm-12">          
                    <input type="text" class="form-control" name="adresse_depart" id="adresse_depart" placeholder="ex: 91 rue de la paix, Paris" value="<?php echo htmlspecialchars($adresse_depart_recherche); ?>">
                </div>
            </div>
            <div class="form-group mb-3">
                <label class="control-label col-sm-12" for="adresse_arrivee">Adresse d'arrivée:</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" name="adresse_arrivee" id="adresse_arrivee" placeholder="ex: 81 rue de la joie, Lyon" value="<?php echo htmlspecialchars($adresse_arrivee_recherche); ?>">
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="control-label col-sm-12" for="date_depart">Date de départ:</label>
                <div class="col-sm-12">
                    <input type="date" class="form-control" name="date_depart" id="date_depart" value="<?php echo htmlspecialchars($date_recherche ?: date('Y-m-d')); ?>">
                </div>
            </div>

            <h5 class="text-center mb-3 filter-title">FILTREZ VOS RÉSULTATS</h5>
            <div class="form-group row mb-3 align-items-center filter-item">
                <label class="control-label col-sm-4 col-md-3" for="tarif_maximum">Tarif maximum:</label>
                <div class="col-sm-8 col-md-7">
                    <input type="number" class="form-control filter-input" id="tarif_maximum" placeholder="ex: 35€" name="tarif_maximum" value="<?php echo htmlspecialchars($tarif_maximum); ?>">
                </div>
            </div>
            <div class="form-group row mb-3 align-items-center filter-item">
                <label class="control-label col-sm-4 col-md-3" for="duree_maximale">Durée maximale (HH:MM):</label>
                <div class="col-sm-8 col-md-7">          
                    <input type="time" class="form-control filter-input" id="duree_maximale" placeholder="01:30" name="duree_maximale" value="<?php echo htmlspecialchars($duree_maximale); ?>">
                </div>
            </div>
            <div class="form-group row mb-3 align-items-center filter-item">
                <label class="control-label col-sm-4 col-md-3" for="note_minimale">Note minimale:</label>
                <div class="col-sm-8 col-md-7">          
                    <input type="number" class="form-control filter-input" id="note_minimale" placeholder=" ex: 4 étoiles" name="note_minimale" min="0" max="5" value="<?php echo htmlspecialchars($note_minimale); ?>">
                </div>
            </div>
            <div class="form-group row mb-3 align-items-center filter-item">        
                <div class="col-12 d-flex flex-column flex-sm-row justify-content-start align-items-sm-center">
                    <label class="control-label col-sm-4 col-md-3 mb-2 mb-sm-0" for="eco">Voiture écologique:</label>
                    <div class="col-sm-8 col-md-7 d-flex align-items-center">
                        <input type="radio" id="eco-oui" name="eco" value="oui" class="form-check-input me-1" <?php echo ($voiture_eco === 'oui') ? 'checked' : ''; ?>>
                        <label for="eco-oui" class="me-3 radio-label">Oui</label>
                        <input type="radio" id="eco-non" name="eco" value="non" class="form-check-input me-1" <?php echo ($voiture_eco === 'non') ? 'checked' : ''; ?>>
                        <label for="eco-non" class="radio-label">Non</label>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4"> 
                <button type="submit" class="btn btn-primary" name="rechercher_covoiturage">RECHERCHER ET APPLIQUER LES FILTRES</button>
            </div>
        </form>
    </section>

    <section class="covoiturages-list container-fluid text-center mt-5">
        <h4 class="text-center mb-4">Covoiturages disponibles</h4>
        <div class="row justify-content-center">
            <?php if (!$search_performed): ?>
                <p class="col-12 alert alert-info text-center">Veuillez utiliser le formulaire de recherche ci-dessus pour trouver des covoiturages.</p>
            <?php elseif (empty($covoiturages)): ?>
                <p class="col-12 alert alert-warning text-center">Aucun covoiturage trouvé pour les critères sélectionnés.</p>
                <?php if ($proche_covoiturage_date): ?>
                    <p class="col-12 alert alert-info text-center">
                        Le prochain covoiturage disponible après cette date est le : 
                        <strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($proche_covoiturage_date))); ?></strong>.
                        <br>
                        <a href="formulaire_covoiturage.php?adresse_depart=<?php echo urlencode($adresse_depart_recherche); ?>&adresse_arrivee=<?php echo urlencode($adresse_arrivee_recherche); ?>&date_depart=<?php echo htmlspecialchars($proche_covoiturage_date); ?>&rechercher_covoiturage=1" class="btn btn-sm btn-info mt-2">
                            Rechercher à cette date
                        </a>
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <?php foreach ($covoiturages as $covoiturage): ?>
                    <div class="col-lg-6 col-md-10 mb-4">
                        <div class="driver border-2 border-dark rounded-4" style="background-color: rgba(26, 99, 106, 0.83);">
                            <div class="row align-items-center mt-2">
                                <div class="col-sm-3 text-center">
                                    <img src="img/default_driver.jpeg" alt="chauffeur" class="m-2 rounded-circle driver-photo" style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <div class="col-sm-6 text-center text-sm-start flex-column mt-2 mt-sm-0">
                                    <h5 class="fst-italic text-white" aria-label="Driver Name"><?php echo htmlspecialchars($covoiturage['chauffeur_prenom'] ?? 'Prénom Inconnu'); ?> <?php echo htmlspecialchars($covoiturage['chauffeur_nom'] ?? 'Nom Inconnu'); ?></h5>
                                    <p class="stars" style="font-size: 20px; color: gold;">
                                        <?php
                                        $note = (float)($covoiturage['chauffeur_note'] ?? 0); // Convertir en float
                                        for ($i = 0; $i < 5; $i++) {
                                            echo ($i < round($note)) ? '★' : '☆'; 
                                        }
                                        ?>
                                    </p>
                                </div>
                                <div class="col-sm-3 text-center text-sm-end mt-3 mt-sm-0">
                                    <form action="vue_detailee_covoiturage.php" method="GET">
                                        <input type="hidden" name="covoiturage_id" value="<?php echo htmlspecialchars($covoiturage['covoiturage_id'] ?? ''); ?>">
                                        <button type="submit" class="detailBtn fw-bold btn btn-primary">DETAILS</button>
                                    </form>
                                </div>
                            </div>

                            <div class="row justify-content-between mt-4 mb-1 p-2">
                                <div class="col-md-3 text-center mb-2">
                                    <div class="yellow-opaque p-2 rounded">
                                        <p class="fw-bold p-driver mb-1">Places :</p>
                                        <div class="yellow p-1 rounded">
                                            <h5 class="p-driver mb-0"><?php echo htmlspecialchars((string)($covoiturage['nb_place'] ?? '0')); ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center mb-2">
                                    <div class="yellow-opaque p-2 rounded">
                                        <p class="fw-bold p-driver mb-1">Prix (€) :</p>
                                        <div class="yellow p-1 rounded">
                                            <h5 class="p-driver mb-0"><?php echo htmlspecialchars((string)($covoiturage['prix_personne'] ?? '0')); ?>€</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center mb-2">
                                    <div class="yellow-opaque p-2 rounded">
                                        <p class="fw-bold p-driver mb-1">Date : <?php echo htmlspecialchars(date('d/m/Y', strtotime($covoiturage['date_depart'] ?? 'now'))); ?></p>
                                        <div class="yellow p-1 rounded">
                                            <p class="fw-bold arrival p-driver mb-0">Départ : <?php echo htmlspecialchars((string)($covoiturage['heure_depart'] ?? '--:--')); ?></p>
                                            <p class="fw-bold arrival p-driver mb-0">Arrivée : <?php echo htmlspecialchars((string)($covoiturage['heure_arrivee'] ?? '--:--')); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center mb-2">
                                    <div class="yellow-opaque p-2 rounded">
                                        <p class="fw-bold p-driver mb-1">Eco :</p>
                                        <div class="yellow p-1 rounded">
                                            <p class="p-driver mb-0"><?php echo (($covoiturage['ecologique'] ?? 0) == 1) ? 'Oui' : 'Non'; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; 
            
            ?>
        </div>
    </section>
</main>


<?php
include_once 'footer.php';
?>