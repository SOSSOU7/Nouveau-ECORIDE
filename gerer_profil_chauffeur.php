<?php

require_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn
include_once 'header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour accéder à cette page.";
    header("Location: connexion.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Définition des IDs de rôle selon votre table 'role'
$ID_ROLE_UTILISATEUR = 2; // Rôle par défaut pour un passager
$ID_ROLE_CHAUFFEUR = 4;
$ID_ROLE_CHAUFFEUR_PASSAGER = 5;

// Initialisation des données
$userData = [];
$userCars = []; // Pour stocker toutes les voitures de l'utilisateur
$preferencesData = [];

try {
    // Récupérer les infos utilisateur
    $stmtUser = $conn->prepare("SELECT u.role_id, r.nom_role FROM utilisateur u JOIN role r ON u.role_id = r.id WHERE u.id = :id");
    $stmtUser->execute([':id' => $utilisateur_id]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if ($userData) {
        $current_role_id = $userData['role_id'];
        $_SESSION['utilisateur_role_id'] = $current_role_id; // Mettre à jour la session
        $_SESSION['utilisateur_role_nom'] = $userData['nom_role'];
    } else {
        $_SESSION['error_notification'] = "Votre profil n'a pas pu être chargé.";
        header("Location: connexion.php");
        exit();
    }

    // Récupérer TOUTES les voitures de l'utilisateur
    $stmtUserCars = $conn->prepare("SELECT * FROM voiture WHERE utilisateur_id = :utilisateur_id ORDER BY id DESC");
    $stmtUserCars->execute([':utilisateur_id' => $utilisateur_id]);
    $userCars = $stmtUserCars->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les préférences (il n'y en a qu'un jeu par utilisateur)
    $stmtPreferences = $conn->prepare("SELECT * FROM preferences WHERE utilisateur_id = :utilisateur_id");
    $stmtPreferences->execute([':utilisateur_id' => $utilisateur_id]);
    $preferencesData = $stmtPreferences->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error_notification'] = "Erreur lors du chargement de votre profil : " . $e->getMessage();
    error_log("Error loading user profile in gerer_profil_chauffeur.php: " . $e->getMessage());
}

// Traitement du formulaire POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine which form was submitted based on the button name
    if (isset($_POST['submit_role_preferences'])) {
        $new_role_selection = $_POST['role_selection'] ?? null;
        
        $new_role_id_to_set = $current_role_id;
        $requires_chauffeur_info = false; // Indique si les infos véhicule/préf sont requises

        if ($new_role_selection === 'passager') {
            $new_role_id_to_set = $ID_ROLE_UTILISATEUR;
            $requires_chauffeur_info = false;
        } elseif ($new_role_selection === 'chauffeur') {
            $new_role_id_to_set = $ID_ROLE_CHAUFFEUR;
            $requires_chauffeur_info = true;
        } elseif ($new_role_selection === 'chauffeur_passager') {
            $new_role_id_to_set = $ID_ROLE_CHAUFFEUR_PASSAGER;
            $requires_chauffeur_info = true;
        } else {
            $_SESSION['error_notification'] = "Sélection de rôle invalide.";
            header("Location: gerer_profil_chauffeur.php");
            exit();
        }

        try {
            $conn->beginTransaction();

            // 1. Mise à jour du rôle de l'utilisateur
            $stmtUpdateRole = $conn->prepare("UPDATE utilisateur SET role_id = :role_id WHERE id = :utilisateur_id");
            $stmtUpdateRole->execute([
                ':role_id' => $new_role_id_to_set,
                ':utilisateur_id' => $utilisateur_id
            ]);
            $_SESSION['utilisateur_role_id'] = $new_role_id_to_set;

            $stmtRoleName = $conn->prepare("SELECT nom_role FROM role WHERE id = :role_id");
            $stmtRoleName->execute([':role_id' => $new_role_id_to_set]);
            $_SESSION['utilisateur_role_nom'] = $stmtRoleName->fetchColumn();

            // 2. Traitement des préférences uniquement si le rôle le requiert
            if ($requires_chauffeur_info) {
                $fumeur_pref = $_POST['fumeur_pref'] ?? 'non';
                $animaux_pref = $_POST['animaux_pref'] ?? 'non';
                $autres_preferences = trim($_POST['autres_preferences'] ?? '');

                if (!in_array($fumeur_pref, ['oui', 'non'])) {
                    throw new Exception("Veuillez choisir votre préférence concernant les fumeurs.");
                }
                if (!in_array($animaux_pref, ['oui', 'non'])) {
                    throw new Exception("Veuillez choisir votre préférence concernant les animaux.");
                }

                if ($preferencesData) {
                    $stmtUpdatePreferences = $conn->prepare("UPDATE preferences SET fumeur = :fumeur, animaux_compagnie = :animaux, autres_preferences = :autres_preferences WHERE utilisateur_id = :utilisateur_id");
                    $stmtUpdatePreferences->execute([
                        ':fumeur' => $fumeur_pref,
                        ':animaux' => $animaux_pref,
                        ':autres_preferences' => $autres_preferences,
                        ':utilisateur_id' => $utilisateur_id
                    ]);
                } else {
                    $stmtInsertPreferences = $conn->prepare("INSERT INTO preferences (utilisateur_id, fumeur, animaux_compagnie, autres_preferences) VALUES (:utilisateur_id, :fumeur, :animaux, :autres_preferences)");
                    $stmtInsertPreferences->execute([
                        ':utilisateur_id' => $utilisateur_id,
                        ':fumeur' => $fumeur_pref,
                        ':animaux' => $animaux_pref,
                        ':autres_preferences' => $autres_preferences
                    ]);
                }
            }
            $conn->commit();
            $_SESSION['notification'] = "Votre rôle et vos préférences ont été mis à jour avec succès !";
            header("Location: gerer_profil_chauffeur.php"); 
            exit();

        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['error_notification'] = "Erreur lors de la mise à jour du rôle/préférences : " . $e->getMessage();
            error_log("Role/preferences update error (gerer_profil_chauffeur.php): " . $e->getMessage());
            header("Location: gerer_profil_chauffeur.php");
            exit();
        }
    } elseif (isset($_POST['add_car'])) {
        // Traitement de l'ajout d'une nouvelle voiture
        $plaque_immatriculation = trim($_POST['plaque_immatriculation'] ?? '');
        $date_immatriculation = trim($_POST['date_premiere_immatriculation'] ?? '');
        $modele = trim($_POST['modele'] ?? '');
        $couleur = trim($_POST['couleur'] ?? '');
        $marque = trim($_POST['marque'] ?? '');
        $places_dispo = intval($_POST['places_dispo'] ?? 0);
        $energie = trim($_POST['energie'] ?? '');

        try {
            if (empty($plaque_immatriculation) || empty($modele) || empty($couleur) || empty($marque) || $places_dispo <= 0 || empty($date_immatriculation) || empty($energie)) {
                throw new Exception("Veuillez remplir tous les champs obligatoires du véhicule (Plaque, Date immat., Modèle, Couleur, Marque, Places, Énergie).");
            }

            $stmtInsertVoiture = $conn->prepare("INSERT INTO voiture (utilisateur_id, marque, modele, immatriculation, couleur, date_premiere_immatriculation, places_dispo, energie) VALUES (:utilisateur_id, :marque, :modele, :immatriculation, :couleur, :date_premiere_immatriculation, :places_dispo, :energie)");
            $stmtInsertVoiture->execute([
                ':utilisateur_id' => $utilisateur_id,
                ':marque' => $marque,
                ':modele' => $modele,
                ':immatriculation' => $plaque_immatriculation,
                ':couleur' => $couleur,
                ':date_premiere_immatriculation' => $date_immatriculation,
                ':places_dispo' => $places_dispo,
                ':energie' => $energie
            ]);
            $_SESSION['notification'] = "Nouvelle voiture ajoutée avec succès !";
            header("Location: gerer_profil_chauffeur.php"); 
            exit();

        } catch (Exception $e) {
            $_SESSION['error_notification'] = "Erreur lors de l'ajout de la voiture : " . $e->getMessage();
            error_log("Add car error (gerer_profil_chauffeur.php): " . $e->getMessage());
            header("Location: gerer_profil_chauffeur.php");
            exit();
        }
    }
}
?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-9">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Gérer votre Profil Chauffeur/Passager</h2>

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

                    <form action="gerer_profil_chauffeur.php" method="POST">
                        <h4 class="mb-3">Sélection de votre rôle</h4>
                        <div class="form-group mb-4">
                            <label for="role_selection" class="form-label">Je suis :</label>
                            <select class="form-select" id="role_selection" name="role_selection" required>
                                <option value="passager" <?= ($current_role_id == $ID_ROLE_UTILISATEUR) ? 'selected' : '' ?>>Passager</option>
                                <option value="chauffeur" <?= ($current_role_id == $ID_ROLE_CHAUFFEUR) ? 'selected' : '' ?>>Chauffeur</option>
                                <option value="chauffeur_passager" <?= ($current_role_id == $ID_ROLE_CHAUFFEUR_PASSAGER) ? 'selected' : '' ?>>Chauffeur et Passager</option>
                            </select>
                            <small class="form-text text-muted">Sélectionnez "Chauffeur" ou "Chauffeur et Passager" pour ajouter ou modifier vos informations de véhicule et préférences.</small>
                        </div>

                        <div id="preferences_section" style="display: <?= ($current_role_id == $ID_ROLE_CHAUFFEUR || $current_role_id == $ID_ROLE_CHAUFFEUR_PASSAGER) ? 'block' : 'none' ?>;">
                            <h5 class="mb-3 mt-4">Préférences</h5>
                            <div class="mb-3">
                                <label class="form-label">Acceptez-vous les fumeurs ?</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="fumeur_pref" id="fumeur_oui" value="oui" <?= (isset($preferencesData['fumeur']) && $preferencesData['fumeur'] == 'oui') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="fumeur_oui">Oui</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="fumeur_pref" id="fumeur_non" value="non" <?= (isset($preferencesData['fumeur']) && $preferencesData['fumeur'] == 'non') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="fumeur_non">Non</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Acceptez-vous les animaux de compagnie ?</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="animaux_pref" id="animaux_oui" value="oui" <?= (isset($preferencesData['animaux_compagnie']) && $preferencesData['animaux_compagnie'] == 'oui') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="animaux_oui">Oui</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="animaux_pref" id="animaux_non" value="non" <?= (isset($preferencesData['animaux_compagnie']) && $preferencesData['animaux_compagnie'] == 'non') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="animaux_non">Non</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="autres_preferences" class="form-label">Autres préférences (facultatif):</label>
                                <textarea class="form-control" id="autres_preferences" name="autres_preferences" rows="3" placeholder="Ex: Musique classique uniquement, pas de nourriture salée..."><?= htmlspecialchars($preferencesData['autres_preferences'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="submit_role_preferences" class="btn btn-primary btn-lg px-5">Enregistrer Rôle et Préférences</button>
                        </div>
                    </form>

                    <hr class="my-5">

                    <?php if ($current_role_id == $ID_ROLE_CHAUFFEUR || $current_role_id == $ID_ROLE_CHAUFFEUR_PASSAGER): ?>
                        <h4 class="mb-3 mt-4">Vos Véhicules Enregistrés</h4>
                        <?php if (empty($userCars)): ?>
                            <div class="alert alert-info text-center" role="alert">
                                Vous n'avez pas encore de véhicule enregistré.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Marque</th>
                                            <th>Modèle</th>
                                            <th>Immatriculation</th>
                                            <th>Couleur</th>
                                            <th>Énergie</th>
                                            <th>Places Dispo</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($userCars as $car): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($car['marque']) ?></td>
                                                <td><?= htmlspecialchars($car['modele']) ?></td>
                                                <td><?= htmlspecialchars($car['immatriculation']) ?></td>
                                                <td><?= htmlspecialchars($car['couleur']) ?></td>
                                                <td><?= htmlspecialchars($car['energie']) ?></td>
                                                <td><?= htmlspecialchars($car['places_dispo']) ?></td>
                                                <td>
                                                    <a href="edit_car.php?id=<?= $car['id'] ?>" class="btn btn-sm btn-info mb-1">Modifier</a>
                                                    <form action="delete_car.php" method="POST" style="display:inline-block;">
                                                        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ?');">Supprimer</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <h4 class="mb-3 mt-5">
                            Ajouter un nouveau véhicule 
                            <button type="button" id="toggle_add_car_form" class="btn btn-success btn-sm ms-2" title="Ajouter une nouvelle voiture">
                                <i class="fas fa-plus-circle"></i> </button>
                        </h4>

                        <div id="add_car_form_container" style="display: none;">
                            <form action="gerer_profil_chauffeur.php" method="POST">
                                <div class="mb-3">
                                    <label for="new_plaque_immatriculation" class="form-label">Plaque d'immatriculation:</label>
                                    <input type="text" class="form-control" id="new_plaque_immatriculation" name="plaque_immatriculation" placeholder="Ex: AB-123-CD">
                                </div>
                                <div class="mb-3">
                                    <label for="new_date_premiere_immatriculation" class="form-label">Date de première immatriculation:</label>
                                    <input type="date" class="form-control" id="new_date_premiere_immatriculation" name="date_premiere_immatriculation">
                                </div>
                                <div class="mb-3">
                                    <label for="new_marque" class="form-label">Marque du véhicule:</label>
                                    <input type="text" class="form-control" id="new_marque" name="marque" placeholder="Ex: Renault">
                                </div>
                                <div class="mb-3">
                                    <label for="new_modele" class="form-label">Modèle du véhicule:</label>
                                    <input type="text" class="form-control" id="new_modele" name="modele" placeholder="Ex: Clio">
                                </div>
                                <div class="mb-3">
                                    <label for="new_couleur" class="form-label">Couleur du véhicule:</label>
                                    <input type="text" class="form-control" id="new_couleur" name="couleur" placeholder="Ex: Noir">
                                </div>
                                <div class="mb-3">
                                    <label for="new_energie" class="form-label">Energie utilisée:</label>
                                    <select class="form-select" name="energie" id="new_energie">
                                        <option value="">Sélectionner dans le menu</option>
                                        <option value="electrique">Electrique</option>
                                        <option value="thermique">Thermique</option>
                                        <option value="hybride">Hybride</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="new_places_dispo" class="form-label">Nombre de places disponibles (hors conducteur):</label>
                                    <input type="number" class="form-control" id="new_places_dispo" name="places_dispo" min="1" placeholder="Ex: 4">
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" name="add_car" class="btn btn-success btn-lg px-5">Ajouter ce véhicule</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role_selection');
    const preferencesSection = document.getElementById('preferences_section');
    const toggleAddCarFormBtn = document.getElementById('toggle_add_car_form');
    const addCarFormContainer = document.getElementById('add_car_form_container');
    const addCarFormFields = addCarFormContainer.querySelectorAll('input, select'); // Get all fields in the add car form

    // Passer les IDs des rôles de PHP à JavaScript
    const ID_ROLE_UTILISATEUR = <?= json_encode($ID_ROLE_UTILISATEUR); ?>;
    const ID_ROLE_CHAUFFEUR = <?= json_encode($ID_ROLE_CHAUFFEUR); ?>;
    const ID_ROLE_CHAUFFEUR_PASSAGER = <?= json_encode($ID_ROLE_CHAUFFEUR_PASSAGER); ?>;

    function togglePreferencesSection() {
        const selectedRoleValue = roleSelect.value;
        const isChauffeurRelatedRole = (selectedRoleValue === 'chauffeur' || selectedRoleValue === 'chauffeur_passager');

        if (isChauffeurRelatedRole) {
            preferencesSection.style.display = 'block';
            // Rendre les groupes de radios requis pour les préférences
            document.querySelectorAll('#preferences_section input[name="fumeur_pref"]').forEach(radio => radio.required = true);
            document.querySelectorAll('#preferences_section input[name="animaux_pref"]').forEach(radio => radio.required = true);
        } else {
            preferencesSection.style.display = 'none';
            // Rendre les champs non obligatoires si la section est masquée
            document.querySelectorAll('#preferences_section input, #preferences_section select, #preferences_section textarea').forEach(function(field) {
                field.required = false;
            });
            document.querySelectorAll('#preferences_section input[name="fumeur_pref"]').forEach(radio => radio.required = false);
            document.querySelectorAll('#preferences_section input[name="animaux_pref"]').forEach(radio => radio.required = false);
        }
    }

    // Fonction pour réinitialiser les champs du formulaire d'ajout de voiture
    function resetAddCarForm() {
        addCarFormFields.forEach(function(field) {
            if (field.type === 'text' || field.type === 'date' || field.type === 'number') {
                field.value = '';
            } else if (field.tagName === 'SELECT') {
                field.selectedIndex = 0; // Réinitialise la sélection à la première option
            }
            // Rendre les champs requis (important pour quand le formulaire est masqué/affiché)
            field.required = false; // Initialement non requis, devient requis quand le formulaire est montré
        });
    }

    // Gestion de la visibilité du formulaire d'ajout de voiture
    if (toggleAddCarFormBtn) { // S'assurer que le bouton existe (il n'est visible que pour les chauffeurs)
        toggleAddCarFormBtn.addEventListener('click', function() {
            if (addCarFormContainer.style.display === 'none') {
                addCarFormContainer.style.display = 'block';
                // Rendre les champs obligatoires lorsque le formulaire est affiché
                addCarFormFields.forEach(field => field.required = true);
                resetAddCarForm(); // Réinitialiser le formulaire quand il est affiché
            } else {
                addCarFormContainer.style.display = 'none';
                // Rendre les champs non obligatoires lorsque le formulaire est masqué
                addCarFormFields.forEach(field => field.required = false);
            }
        });
    }

    // Appeler la fonction au chargement de la page pour gérer l'état initial des préférences
    togglePreferencesSection();

    // Ajouter un écouteur d'événements pour les changements de sélection de rôle
    roleSelect.addEventListener('change', togglePreferencesSection);
});
</script>