<?php

require_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn
include_once 'header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour accéder à cette page.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];
$current_role_id = $_SESSION['utilisateur_role_id'] ?? null;

// Définition des IDs de rôle pour la vérification
$ID_ROLE_CHAUFFEUR = 4;
$ID_ROLE_CHAUFFEUR_PASSAGER = 5;

// Rediriger si l'utilisateur n'est pas chauffeur ou chauffeur_passager
if ($current_role_id != $ID_ROLE_CHAUFFEUR && $current_role_id != $ID_ROLE_CHAUFFEUR_PASSAGER) {
    $_SESSION['error_notification'] = "Vous n'avez pas les autorisations nécessaires pour saisir un voyage. Veuillez devenir chauffeur.";
    header("Location: espace_passager.php"); // Ou une autre page de redirection
    exit();
}

// Récupérer les voitures de l'utilisateur pour la sélection
$userCars = [];
try {
    $stmtCars = $conn->prepare("SELECT id, marque, modele, immatriculation, couleur FROM voiture WHERE utilisateur_id = :utilisateur_id ORDER BY marque, modele");
    $stmtCars->execute([':utilisateur_id' => $utilisateur_id]);
    $userCars = $stmtCars->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_notification'] = "Erreur lors du chargement de vos véhicules : " . $e->getMessage();
    error_log("Error loading user cars in saisir_voyage.php: " . $e->getMessage());
}

// Traitement du formulaire de saisie de voyage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_voyage'])) {
    $adresse_depart = trim($_POST['adresse_depart'] ?? '');
    $date_depart = trim($_POST['date_depart'] ?? '');
    $heure_depart = trim($_POST['heure_depart'] ?? '');
    $adresse_arrivee = trim($_POST['adresse_arrivee'] ?? '');
    $heure_arrivee = trim($_POST['heure_arrivee'] ?? '');
    $prix_personne = floatval($_POST['prix_personne'] ?? 0);
    $nb_place = intval($_POST['nb_place'] ?? 0);
    $voiture_selection_type = $_POST['voiture_selection_type'] ?? ''; // 'existante' ou 'nouvelle'
    $selected_voiture_id = $_POST['selected_voiture_id'] ?? null;

    // Champs pour nouvelle voiture (si applicable)
    $new_plaque_immatriculation = trim($_POST['new_plaque_immatriculation'] ?? '');
    $new_date_immatriculation = trim($_POST['new_date_premiere_immatriculation'] ?? '');
    $new_modele = trim($_POST['new_modele'] ?? '');
    $new_couleur = trim($_POST['new_couleur'] ?? '');
    $new_marque = trim($_POST['new_marque'] ?? '');
    $new_places_dispo = intval($_POST['new_places_dispo'] ?? 0);
    $new_energie = trim($_POST['new_energie'] ?? '');
    
    $voiture_id_to_use = null; // ID de la voiture à utiliser pour le covoiturage

    try {
        // Validation des champs du voyage
        if (empty($adresse_depart) || empty($date_depart) || empty($heure_depart) || 
            empty($adresse_arrivee) || empty($heure_arrivee) || $prix_personne <= 0 || $nb_place <= 0) {
            throw new Exception("Veuillez remplir tous les champs obligatoires du voyage (adresses, dates, heures, prix, places).");
        }
        
        // Calcul des crédits requis (prix du chauffeur + 2 crédits plateforme)
        $credit_requis = $prix_personne + 2;

        $conn->beginTransaction();

        // Gestion de la sélection ou de l'ajout d'une voiture
        if ($voiture_selection_type === 'existante') {
            if (empty($selected_voiture_id)) {
                throw new Exception("Veuillez sélectionner un véhicule existant.");
            }
            // Vérifier que la voiture sélectionnée appartient bien à l'utilisateur
            $stmtCheckCar = $conn->prepare("SELECT id FROM voiture WHERE id = :id AND utilisateur_id = :utilisateur_id");
            $stmtCheckCar->execute([':id' => $selected_voiture_id, ':utilisateur_id' => $utilisateur_id]);
            if (!$stmtCheckCar->fetch()) {
                throw new Exception("Le véhicule sélectionné n'est pas valide ou ne vous appartient pas.");
            }
            $voiture_id_to_use = $selected_voiture_id;
        } elseif ($voiture_selection_type === 'nouvelle') {
            // Validation des champs de la nouvelle voiture
            if (empty($new_plaque_immatriculation) || empty($new_modele) || empty($new_couleur) || 
                empty($new_marque) || $new_places_dispo <= 0 || empty($new_date_immatriculation) || empty($new_energie)) {
                throw new Exception("Veuillez remplir toutes les informations pour le nouveau véhicule.");
            }
            
            // Insérer la nouvelle voiture
            $stmtInsertVoiture = $conn->prepare("INSERT INTO voiture (utilisateur_id, marque, modele, immatriculation, couleur, date_premiere_immatriculation, places_dispo, energie) VALUES (:utilisateur_id, :marque, :modele, :immatriculation, :couleur, :date_premiere_immatriculation, :places_dispo, :energie)");
            $stmtInsertVoiture->execute([
                ':utilisateur_id' => $utilisateur_id,
                ':marque' => $new_marque,
                ':modele' => $new_modele,
                ':immatriculation' => $new_plaque_immatriculation,
                ':couleur' => $new_couleur,
                ':date_premiere_immatriculation' => $new_date_immatriculation,
                ':places_dispo' => $new_places_dispo,
                ':energie' => $new_energie
            ]);
            $voiture_id_to_use = $conn->lastInsertId(); // Récupérer l'ID de la nouvelle voiture
        } else {
            throw new Exception("Veuillez sélectionner un type de véhicule (existant ou nouveau).");
        }

        // Insérer le nouveau covoiturage
        $stmtInsertCovoiturage = $conn->prepare("INSERT INTO covoiturage (utilisateur_id, voiture_id, date_depart, heure_depart, adresse_depart, adresse_arrivee, heure_arrivee, nb_place, prix_personne, credit_requis, statut) VALUES (:utilisateur_id, :voiture_id, :date_depart, :heure_depart, :adresse_depart, :adresse_arrivee, :heure_arrivee, :nb_place, :prix_personne, :credit_requis, 'actif')");
        $stmtInsertCovoiturage->execute([
            ':utilisateur_id' => $utilisateur_id,
            ':voiture_id' => $voiture_id_to_use,
            ':date_depart' => $date_depart,
            ':heure_depart' => $heure_depart,
            ':adresse_depart' => $adresse_depart,
            ':adresse_arrivee' => $adresse_arrivee,
            ':heure_arrivee' => $heure_arrivee,
            ':nb_place' => $nb_place,
            ':prix_personne' => $prix_personne,
            ':credit_requis' => $credit_requis // Le prix total incluant les crédits plateforme
            // Statut 'actif' par défaut
        ]);

        $conn->commit();
        $_SESSION['notification'] = "Votre voyage a été saisi avec succès ! Prix du covoiturage : " . $prix_personne . "€ (Coût total par personne : " . $credit_requis . " crédits).";
        header("Location: espace_chauffeur.php"); // Rediriger vers l'espace chauffeur après la saisie
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_notification'] = "Erreur lors de la saisie du voyage : " . $e->getMessage();
        error_log("Voyage creation error (saisir_voyage.php): " . $e->getMessage());
        // Rester sur la page pour que l'utilisateur puisse corriger
    }
}
?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-8">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Saisir un nouveau voyage</h2>

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

                    <form action="saisir_voyage.php" method="POST">
                        <fieldset class="mb-4 p-3 border rounded">
                            <legend class="float-none w-auto px-2">Informations du voyage</legend>
                            <div class="mb-3">
                                <label for="adresse_depart" class="form-label">Adresse de départ:</label>
                                <input type="text" class="form-control" id="adresse_depart" name="adresse_depart" required placeholder="Ex: 10 Rue de la Paix, Paris">
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="date_depart" class="form-label">Date de départ:</label>
                                    <input type="date" class="form-control" id="date_depart" name="date_depart" required min="<?= date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="heure_depart" class="form-label">Heure de départ:</label>
                                    <input type="time" class="form-control" id="heure_depart" name="heure_depart" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="adresse_arrivee" class="form-label">Adresse d'arrivée:</label>
                                <input type="text" class="form-control" id="adresse_arrivee" name="adresse_arrivee" required placeholder="Ex: 20 Avenue des Champs, Lyon">
                            </div>
                            <div class="mb-3">
                                <label for="heure_arrivee" class="form-label">Heure d'arrivée:</label>
                                <input type="time" class="form-control" id="heure_arrivee" name="heure_arrivee" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="prix_personne" class="form-label">Prix par personne (€):</label>
                                    <input type="number" class="form-control" id="prix_personne" name="prix_personne" step="0.01" min="0.01" required placeholder="Ex: 15.50">
                                    <small class="form-text text-muted">2 crédits seront ajoutés à ce prix pour la plateforme.</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="nb_place" class="form-label">Nombre de places disponibles:</label>
                                    <input type="number" class="form-control" id="nb_place" name="nb_place" min="1" required placeholder="Ex: 3">
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="mb-4 p-3 border rounded">
                            <legend class="float-none w-auto px-2">Sélection du véhicule</legend>
                            <div class="mb-3">
                                <label class="form-label">Utiliser un véhicule :</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="voiture_selection_type" id="select_existing_car" value="existante" checked>
                                    <label class="form-check-label" for="select_existing_car">
                                        Existant
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="voiture_selection_type" id="add_new_car" value="nouvelle">
                                    <label class="form-check-label" for="add_new_car">
                                        Nouveau
                                    </label>
                                </div>
                            </div>

                            <div id="existing_car_section" class="mt-3">
                                <?php if (!empty($userCars)): ?>
                                    <label for="selected_voiture_id" class="form-label">Choisir un véhicule existant:</label>
                                    <select class="form-select" id="selected_voiture_id" name="selected_voiture_id" required>
                                        <option value="">-- Sélectionnez un véhicule --</option>
                                        <?php foreach ($userCars as $car): ?>
                                            <option value="<?= htmlspecialchars($car['id']) ?>">
                                                <?= htmlspecialchars($car['marque'] . ' ' . $car['modele'] . ' (' . $car['immatriculation'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <div class="alert alert-warning">Vous n'avez pas encore de véhicule enregistré. Veuillez en ajouter un nouveau.</div>
                                <?php endif; ?>
                            </div>

                            <div id="new_car_section" style="display: none;">
                                <h5 class="mb-3 mt-4">Informations du nouveau véhicule</h5>
                                <div class="mb-3">
                                    <label for="new_plaque_immatriculation" class="form-label">Plaque d'immatriculation:</label>
                                    <input type="text" class="form-control" id="new_plaque_immatriculation" name="new_plaque_immatriculation" placeholder="Ex: AB-123-CD">
                                </div>
                                <div class="mb-3">
                                    <label for="new_date_premiere_immatriculation" class="form-label">Date de première immatriculation:</label>
                                    <input type="date" class="form-control" id="new_date_premiere_immatriculation" name="new_date_premiere_immatriculation">
                                </div>
                                <div class="mb-3">
                                    <label for="new_marque" class="form-label">Marque du véhicule:</label>
                                    <input type="text" class="form-control" id="new_marque" name="new_marque" placeholder="Ex: Renault">
                                </div>
                                <div class="mb-3">
                                    <label for="new_modele" class="form-label">Modèle du véhicule:</label>
                                    <input type="text" class="form-control" id="new_modele" name="new_modele" placeholder="Ex: Clio">
                                </div>
                                <div class="mb-3">
                                    <label for="new_couleur" class="form-label">Couleur du véhicule:</label>
                                    <input type="text" class="form-control" id="new_couleur" name="new_couleur" placeholder="Ex: Noir">
                                </div>
                                <div class="mb-3">
                                    <label for="new_energie" class="form-label">Energie utilisée:</label>
                                    <select class="form-select" name="new_energie" id="new_energie">
                                        <option value="">Sélectionner dans le menu</option>
                                        <option value="electrique">Electrique</option>
                                        <option value="thermique">Thermique</option>
                                        <option value="hybride">Hybride</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="new_places_dispo" class="form-label">Nombre de places disponibles (hors conducteur):</label>
                                    <input type="number" class="form-control" id="new_places_dispo" name="new_places_dispo" min="1" placeholder="Ex: 4">
                                </div>
                            </div>
                        </fieldset>

                        <div class="text-center mt-4">
                            <button type="submit" name="submit_voyage" class="btn btn-primary btn-lg px-5">Saisir le voyage</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectExistingCarRadio = document.getElementById('select_existing_car');
    const addNewCarRadio = document.getElementById('add_new_car');
    const existingCarSection = document.getElementById('existing_car_section');
    const newCarSection = document.getElementById('new_car_section');
    
    const existingCarSelect = document.getElementById('selected_voiture_id');
    const newCarFields = newCarSection.querySelectorAll('input, select');

    function toggleCarSections() {
        if (selectExistingCarRadio.checked) {
            existingCarSection.style.display = 'block';
            newCarSection.style.display = 'none';
            if (existingCarSelect) existingCarSelect.required = true; // Rendre le select existant requis
            newCarFields.forEach(field => field.required = false); // Rendre les champs de nouvelle voiture non requis
            // Si pas de voiture existante, forcer l'ajout d'une nouvelle
            if (<?= empty($userCars) ? 'true' : 'false' ?> && selectExistingCarRadio.checked) {
                addNewCarRadio.checked = true;
                toggleCarSections(); // Rappeler la fonction pour ajuster la visibilité
            }
        } else if (addNewCarRadio.checked) {
            existingCarSection.style.display = 'none';
            newCarSection.style.display = 'block';
            if (existingCarSelect) existingCarSelect.required = false; // Rendre le select existant non requis
            // Rendre les champs de nouvelle voiture requis
            newCarFields.forEach(field => {
                if (field.name !== 'new_date_premiere_immatriculation') { // Rendre tous les champs requis sauf la date d'immatriculation (car peut être vide au début)
                    field.required = true;
                }
            });
        }
    }

    // Écouteurs d'événements pour les boutons radio
    selectExistingCarRadio.addEventListener('change', toggleCarSections);
    addNewCarRadio.addEventListener('change', toggleCarSections);

    // Initialiser l'état des sections au chargement de la page
    toggleCarSections();

    // Vérifier si aucune voiture existante n'est présente au chargement
    if (<?= empty($userCars) ? 'true' : 'false' ?>) {
        addNewCarRadio.checked = true; // Sélectionner "Nouveau véhicule"
        selectExistingCarRadio.disabled = true; // Désactiver l'option "Existant"
        toggleCarSections(); // Appliquer les changements de visibilité
    }
});
</script>