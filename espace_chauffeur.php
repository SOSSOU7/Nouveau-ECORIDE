<?php
session_start();
require_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn
include_once 'header.php'; // Assurez-vous que header.php inclut la balise <head> et le début de <body>

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour accéder à votre espace.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Définition des IDs de rôle selon votre table 'role' (récupérés ou définis comme constants)
// Ces constantes DOIVENT correspondre aux IDs réels dans votre table 'role'
$ID_ROLE_UTILISATEUR = 2; // Exemple: ID pour le rôle 'passager'
$ID_ROLE_CHAUFFEUR = 4; // Exemple: ID pour le rôle 'chauffeur'
$ID_ROLE_CHAUFFEUR_PASSAGER = 5; // Exemple: ID pour le rôle 'chauffeur_passager'

// Initialiser les variables de session pour éviter les "null" si elles ne sont pas définies
$_SESSION['utilisateur_pseudo'] = $_SESSION['utilisateur_pseudo'] ?? 'Non défini';
$_SESSION['utilisateur_email'] = $_SESSION['utilisateur_email'] ?? 'non.defini@example.com';
$_SESSION['utilisateur_nom'] = $_SESSION['utilisateur_nom'] ?? 'Nom';
$_SESSION['utilisateur_prenom'] = $_SESSION['utilisateur_prenom'] ?? 'Prénom';
$_SESSION['utilisateur_role_id'] = $_SESSION['utilisateur_role_id'] ?? null;
$_SESSION['utilisateur_role_nom'] = $_SESSION['utilisateur_role_nom'] ?? 'Chargement...';


// Récupérer le rôle actuel de l'utilisateur depuis la session ou la base de données
if (is_null($_SESSION['utilisateur_role_id'])) { // Utiliser is_null ou !isset pour vérifier explicitement
    try {
        $stmtRole = $conn->prepare("SELECT u.role_id, r.nom_role FROM utilisateur u JOIN role r ON u.role_id = r.id WHERE u.id = :id");
        $stmtRole->execute([':id' => $utilisateur_id]);
        $roleData = $stmtRole->fetch(PDO::FETCH_ASSOC);
        if ($roleData) {
            $_SESSION['utilisateur_role_id'] = $roleData['role_id'];
            $_SESSION['utilisateur_role_nom'] = $roleData['nom_role'];
        } else {
            // Gérer le cas où le rôle ne peut pas être trouvé (utilisateur invalide ?)
            $_SESSION['error_notification'] = "Votre rôle n'a pas pu être chargé. Veuillez réessayer.";
            header("Location: designin.php"); // Déconnecter l'utilisateur
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_notification'] = "Erreur de base de données : " . $e->getMessage();
        error_log("Error fetching user role in espace_passager.php: " . $e->getMessage());
        header("Location: designin.php");
        exit();
    }
}
$current_role_id = $_SESSION['utilisateur_role_id'];

// Récupérer les informations de l'utilisateur (maintenant qu'elles sont garanties non-null)
$username = $_SESSION['utilisateur_pseudo'];
$email = $_SESSION['utilisateur_email'];
$nom = $_SESSION['utilisateur_nom'];
$prenom = $_SESSION['utilisateur_prenom'];

?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Mon Espace Personnel</h2>

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

                    <div class="mb-4 text-center">
                        <img src="img/avatar.png" alt="Avatar" class="profile-avatar mb-3">
                        <h3><?= htmlspecialchars($prenom . ' ' . $nom) ?></h3>
                        <p class="text-muted"><?= htmlspecialchars($email) ?></p>
                        <p class="text-muted">Rôle actuel: <strong><?= htmlspecialchars($_SESSION['utilisateur_role_nom'] ?? 'Non défini') ?></strong></p>
                    </div>

                    <div class="list-group mb-4">
                        <h5 class="text-center rounded m-2 p-2" style="background-color:black; color:white;">MON ESPACE PASSAGER</h5>
                        <a href="modifier_profil.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>Modifier mon profil</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="modifier_mot_de_passe.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>Changer mon mot de passe</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="historique_passager.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>Mes voyages en tant que passager</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="mes_reservations.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>Mes réservations actuelles</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="designin.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-danger">
                            <span>Se déconnecter</span>
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                    
                    <hr class="my-4">

                    <h5 class="text-center rounded m-2 p-2" style="background-color:black; color:white;">MON ESPACE CHAUFFEUR</h5>
                    
                    <?php
                    // Afficher les options de gestion de profil chauffeur et de saisie de voyage
                    // uniquement si l'utilisateur est chauffeur ou chauffeur_passager
                    if ($current_role_id == $ID_ROLE_CHAUFFEUR || $current_role_id == $ID_ROLE_CHAUFFEUR_PASSAGER) {
                    ?>
                        <div class="list-group mb-4">
                            <a href="gerer_profil_chauffeur.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span>Gérer mon profil chauffeur (véhicules et préférences)</span>
                                <i class="fas fa-car"></i>
                            </a>
                        </div>
                        <center> 
                            <a href="saisir_voyage.php" class="btn btn-warning btn-block account-btn mb-4">PROPOSER UN COVOITURAGE</a>
                        </center>
                        <div class="list-group mb-4">
                            <a href="mes_covoiturages_chauffeur.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span>Mes covoiturages en cours</span>
                                <i class="fas fa-route"></i>
                            </a>
                            <a href="historique_chauffeur.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span>Historique de mes covoiturages</span>
                                <i class="fas fa-history"></i>
                            </a>
                        </div>
                    <?php
                    } else {
                        // Si l'utilisateur n'est pas chauffeur, lui proposer de le devenir
                    ?>
                        <div class="alert alert-info text-center" role="alert">
                            Vous n'êtes pas encore enregistré comme chauffeur. Devenez chauffeur pour proposer des covoiturages !
                        </div>
                        <center>
                            <a href="devenir_chauffeur.php" class="btn btn-success btn-lg mb-4">DEVENIR CHAUFFEUR</a>
                        </center>
                    <?php
                    }
                    ?>

                    <hr class="my-4">

                    <h5 class="text-center historique rounded m-2 p-2" style="background-color:black; color:white;">HISTORIQUE DE MES COVOITURAGES</h5>
                    <div class="list-group-item">
                        <h6 class="text-center pt-2 pb-2 text-white fw-bold" style="background-color: #343a40;">COVOITURAGE ACTUELLEMENT EN COURS</h6>
                            <div class=" d-flex justify-content-center pt-2 pb-3">
                                <button type="submit" class="btn btn-primary btn-covoit me-2">VALIDER COVOITURAGE</button>
                                <button type="submit" class="btn btn-primary btn-covoit me-2"><a href="avis.php" style="color:white; text-decoration:none;">SOUMETTRE UN AVIS</a></button>
                            </div>
                    </div>
                    <div class="list-group clientView">
                        <div class="list-group-item" style="background-color:rgba(241, 220, 15, 0.7);">
                            <div class="d-flex">
                                <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                                <div class="">
                                    <h6>Suzie CASTORAMA</h6>
                                    <p class="mb-0">Super voyage</p>
                                    <p class="text-muted small">Une expérience inoubliable, merci beaucoup !</p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item" style="background-color:rgba(241, 220, 15, 0.7);">
                            <div class="d-flex">
                                <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                                <div>
                                    <h6>Suzie CASTORAMA</h6>
                                    <p class="mb-0">Désagréable</p>
                                    <p class="text-muted small">Bavard et curieux sur ma vie.</p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item" style="background-color:rgba(241, 220, 15, 0.7);">
                            <div class="d-flex">
                                <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                                <div>
                                    <h6>Suzie CASTORAMA</h6>
                                    <p class="mb-0">Nice travel</p>
                                    <p class="text-muted small">Super expérience, je recommande vivement !</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'footer.php'; ?>