<?php

require_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn
include_once 'header.php'; // Assurez-vous que header.php inclut les balises HTML de base et les liens CSS/JS (Bootstrap, Font Awesome)

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour modifier votre profil.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Initialiser les variables pour afficher les données actuelles de l'utilisateur
$pseudo = '';
$nom = '';
$prenom = '';
$email = '';
$telephone = '';
$date_naissance = '';
$adresse = '';
$ville = '';
$code_postal = '';
$description = '';

// Initialiser les notifications
$notification = '';
$error_notification = '';

// 1. Récupérer les données actuelles de l'utilisateur
try {
    $stmt = $conn->prepare("SELECT pseudo, nom, prenom, email, telephone, date_naissance, adresse, ville, code_postal, description FROM utilisateur WHERE id = :id");
    $stmt->execute([':id' => $utilisateur_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $pseudo = htmlspecialchars($userData['pseudo'] ?? '');
        $nom = htmlspecialchars($userData['nom'] ?? '');
        $prenom = htmlspecialchars($userData['prenom'] ?? '');
        $email = htmlspecialchars($userData['email'] ?? '');
        $telephone = htmlspecialchars($userData['telephone'] ?? '');
        $date_naissance = htmlspecialchars($userData['date_naissance'] ?? '');
        $adresse = htmlspecialchars($userData['adresse'] ?? '');
        $ville = htmlspecialchars($userData['ville'] ?? '');
        $code_postal = htmlspecialchars($userData['code_postal'] ?? '');
        $description = htmlspecialchars($userData['description'] ?? '');
    } else {
        $error_notification = "Erreur: Impossible de charger les informations de votre profil.";
        // Si l'utilisateur n'est pas trouvé, le déconnecter pour plus de sécurité
        header("Location: designin.php");
        exit();
    }
} catch (PDOException $e) {
    $error_notification = "Erreur de base de données lors du chargement du profil : " . $e->getMessage();
    error_log("Profile load error (modifier_profil.php): " . $e->getMessage());
}

// 2. Traitement du formulaire POST (quand l'utilisateur soumet les modifications)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $new_pseudo = trim($_POST['pseudo'] ?? '');
    $new_nom = trim($_POST['nom'] ?? '');
    $new_prenom = trim($_POST['prenom'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_telephone = trim($_POST['telephone'] ?? '');
    $new_date_naissance = trim($_POST['date_naissance'] ?? '');
    $new_adresse = trim($_POST['adresse'] ?? '');
    $new_ville = trim($_POST['ville'] ?? '');
    $new_code_postal = trim($_POST['code_postal'] ?? '');
    $new_description = trim($_POST['description'] ?? '');

    try {
        // Validation basique (vous pouvez ajouter des validations plus complexes ici)
        if (empty($new_pseudo) || empty($new_nom) || empty($new_prenom) || empty($new_email)) {
            throw new Exception("Le pseudo, nom, prénom et email sont obligatoires.");
        }
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("L'adresse email n'est pas valide.");
        }

        // Vérifier si le nouvel email ou pseudo existe déjà pour un AUTRE utilisateur
        $stmtCheck = $conn->prepare("SELECT id FROM utilisateur WHERE (email = :email OR pseudo = :pseudo) AND id != :current_id");
        $stmtCheck->execute([
            ':email' => $new_email,
            ':pseudo' => $new_pseudo,
            ':current_id' => $utilisateur_id
        ]);
        if ($stmtCheck->fetch()) {
            throw new Exception("L'email ou le pseudo est déjà utilisé par un autre utilisateur.");
        }

        // Mettre à jour les informations dans la base de données
        $stmtUpdate = $conn->prepare("UPDATE utilisateur SET 
            pseudo = :pseudo,
            nom = :nom,
            prenom = :prenom,
            email = :email,
            telephone = :telephone,
            date_naissance = :date_naissance,
            adresse = :adresse,
            ville = :ville,
            code_postal = :code_postal,
            description = :description
            WHERE id = :id");

        $stmtUpdate->execute([
            ':pseudo' => $new_pseudo,
            ':nom' => $new_nom,
            ':prenom' => $new_prenom,
            ':email' => $new_email,
            ':telephone' => ($new_telephone !== '') ? $new_telephone : null, // Mettre à NULL si vide
            ':date_naissance' => ($new_date_naissance !== '') ? $new_date_naissance : null,
            ':adresse' => ($new_adresse !== '') ? $new_adresse : null,
            ':ville' => ($new_ville !== '') ? $new_ville : null,
            ':code_postal' => ($new_code_postal !== '') ? $new_code_postal : null,
            ':description' => ($new_description !== '') ? $new_description : null,
            ':id' => $utilisateur_id
        ]);

        $notification = "Votre profil a été mis à jour avec succès !";

        // Mettre à jour les variables de session pour refléter les changements immédiatement
        $_SESSION['utilisateur_pseudo'] = $new_pseudo;
        $_SESSION['utilisateur_email'] = $new_email;
        $_SESSION['utilisateur_nom'] = $new_nom;
        $_SESSION['utilisateur_prenom'] = $new_prenom;
        // Recharger la page pour afficher les nouvelles données
        header("Location: modifier_profil.php");
        exit();

    } catch (Exception $e) {
        $error_notification = "Erreur lors de la mise à jour du profil : " . $e->getMessage();
        // Garder les valeurs saisies par l'utilisateur pour qu'il puisse corriger
        $pseudo = htmlspecialchars($new_pseudo);
        $nom = htmlspecialchars($new_nom);
        $prenom = htmlspecialchars($new_prenom);
        $email = htmlspecialchars($new_email);
        $telephone = htmlspecialchars($new_telephone);
        $date_naissance = htmlspecialchars($new_date_naissance);
        $adresse = htmlspecialchars($new_adresse);
        $ville = htmlspecialchars($new_ville);
        $code_postal = htmlspecialchars($new_code_postal);
        $description = htmlspecialchars($new_description);
    }
}
?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-8">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Modifier mon profil</h2>

                    <?php if ($notification): ?>
                        <div class="alert alert-success text-center" role="alert">
                            <?php echo htmlspecialchars($notification); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_notification): ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <?php echo htmlspecialchars($error_notification); ?>
                        </div>
                    <?php endif; ?>

                    <form action="modifier_profil.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom:</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" value="<?= $prenom ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom:</label>
                                <input type="text" class="form-control" id="nom" name="nom" value="<?= $nom ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="pseudo" class="form-label">Pseudo:</label>
                            <input type="text" class="form-control" id="pseudo" name="pseudo" value="<?= $pseudo ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= $email ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone (facultatif):</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" value="<?= $telephone ?>" placeholder="Ex: 0612345678">
                        </div>
                        <div class="mb-3">
                            <label for="date_naissance" class="form-label">Date de naissance (facultatif):</label>
                            <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?= $date_naissance ?>">
                        </div>
                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse (facultatif):</label>
                            <input type="text" class="form-control" id="adresse" name="adresse" value="<?= $adresse ?>" placeholder="Ex: 12 Rue de la Paix">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ville" class="form-label">Ville (facultatif):</label>
                                <input type="text" class="form-control" id="ville" name="ville" value="<?= $ville ?>" placeholder="Ex: Paris">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="code_postal" class="form-label">Code postal (facultatif):</label>
                                <input type="text" class="form-control" id="code_postal" name="code_postal" value="<?= $code_postal ?>" placeholder="Ex: 75001">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description (facultatif):</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Parlez un peu de vous..."><?= $description ?></textarea>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'footer.php'; ?>