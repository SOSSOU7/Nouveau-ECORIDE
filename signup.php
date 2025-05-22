<?php
session_start();
// Assurez-vous que 'auth.php' est inclus si c'est là que votre connexion $conn est définie.
// Si $conn n'est pas accessible ici, vous devez inclure le fichier qui l'initialise.
// Exemple: require_once 'auth.php'; // Décommentez cette ligne si nécessaire
include_once 'header.php'; // Inclut le header (qui peut afficher les notifications)

// --- DEBUGGING: Afficher toutes les erreurs pour le développement ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN DEBUGGING ---

// Générer un jeton CSRF si ce n'est pas déjà fait
$_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));

// Valeur par défaut pour le rôle d'un nouvel utilisateur (par exemple, "passager" ou "utilisateur standard")
// IMPORTANT : VÉRIFIEZ CETTE VALEUR DANS VOTRE BASE DE DONNÉES (TABLE 'role')
// D'après votre ecoride.sql, l'ID 1 est un choix courant pour le rôle d'utilisateur par défaut.
$default_role_id = 2; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Validation du jeton CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_notification'] = "Erreur de sécurité : Jeton CSRF invalide. Veuillez réessayer.";
        // Rediriger pour rafraîchir le jeton et éviter la soumission multiple
        header("Location: signup.php"); 
        exit();
    }

    $pseudo = trim($_POST['pseudo'] ?? '');
    $email = filter_var(trim($_POST['mail'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['mot_de_passe'] ?? '';
    $confirm_password = $_POST['confirmer'] ?? '';

    // 2. Validation des entrées du formulaire
    if (empty($pseudo) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error_notification'] = "Veuillez remplir tous les champs.";
    } elseif (!$email) {
        $_SESSION['error_notification'] = "Adresse email invalide.";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error_notification'] = "Les mots de passe ne correspondent pas.";
    } 
    // Optionnel : Ajouter des règles de complexité du mot de passe (min 8 caractères, majuscule, chiffre...)
    // elseif (strlen($password) < 8 || !preg_match("#[0-9]+#", $password) || !preg_match("#[A-Z]+#", $password)) {
    //     $_SESSION['error_notification'] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.";
    // }
    else {
        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $credits_offerts = 20; // US 7: L'utilisateur bénéficie de 20 crédits

            // 3. Vérifier si l'email existe déjà dans la base de données
            $sql_check_email = "SELECT COUNT(*) FROM utilisateur WHERE email = :email";
            $req_check_email = $conn->prepare($sql_check_email);
            $req_check_email->execute([':email' => $email]);
            $email_exists = $req_check_email->fetchColumn();

            // 4. Vérifier si le pseudo existe déjà dans la base de données
            $sql_check_pseudo = "SELECT COUNT(*) FROM utilisateur WHERE pseudo = :pseudo";
            $req_check_pseudo = $conn->prepare($sql_check_pseudo);
            $req_check_pseudo->execute([':pseudo' => $pseudo]);
            $pseudo_exists = $req_check_pseudo->fetchColumn();

            if ($email_exists > 0) {
                $_SESSION['error_notification'] = "Cet email est déjà utilisé. Veuillez en choisir un autre ou vous connecter.";
            } elseif ($pseudo_exists > 0) { // Nouvelle condition pour le pseudo
                $_SESSION['error_notification'] = "Ce pseudo est déjà pris. Veuillez en choisir un autre.";
            } else {
                // 5. Insérer le nouvel utilisateur si l'email et le pseudo sont uniques
                $sql_insert_user = "INSERT INTO utilisateur (pseudo, email, mot_de_passe, credit, role_id) VALUES (:pseudo, :email, :mot_de_passe, :credit, :role_id)";
                $req_insert_user = $conn->prepare($sql_insert_user);

                if ($req_insert_user->execute([
                    ':pseudo' => $pseudo,
                    ':email' => $email,
                    ':mot_de_passe' => $passwordHash,
                    ':credit' => $credits_offerts, // Attribution des 20 crédits
                    ':role_id' => $default_role_id // Attribution du rôle par défaut
                ])) {
                    // Si l'insertion est réussie, rediriger vers la page de connexion
                    $_SESSION['notification'] = "Votre compte a été créé avec succès ! Vous avez 20 crédits offerts.";
                    header("Location: signin.php"); 
                    exit();
                } else {
                    $_SESSION['error_notification'] = "Erreur lors de la création de votre compte. Veuillez réessayer.";
                }
            }
        } catch (PDOException $e) {
            // Gérer les erreurs de base de données (ex: champ manquant, problème de connexion)
            $_SESSION['error_notification'] = "Une erreur de base de données est survenue. Code : " . $e->getCode() . " Message : " . $e->getMessage();
            error_log("Signup error: " . $e->getMessage()); // Pour le débogage côté serveur
        }
    }
    // Rediriger en cas d'erreur ou de validation échouée pour afficher la notification
    // et éviter la re-soumission du formulaire si l'utilisateur rafraîchit la page
    header("Location: signup.php");
    exit();
}
?>

<main id="main-page" class="container-fluid h-custom">
    <div class="container account-form" style="background: rgb(237, 215, 15, 0.9); border-radius: 10px; padding: 20px;">
        <h2 class="text-center">Créer un compte</h2>
        <h4 class="text-center creation-title">Remplissez le formulaire ci-dessous pour créer un compte et bénéficier de 20 crédits offerts gratuitement.</h4>

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

        <form class="form-horizontal m-5" action="signup.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group mb-3">
                <label class="control-label col-sm-2 fw-bold" for="username">Votre Pseudo:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="username" name="pseudo" placeholder="Entrez votre pseudo" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="control-label col-sm-2 fw-bold" for="email">Votre adresse mail:</label>
                <div class="col-sm-10">
                    <input type="email" class="form-control" id="email" name="mail" placeholder="Entrez votre email" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="control-label col-sm-2 fw-bold" for="password">Mot de passe:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" name="mot_de_passe" id="password" placeholder="Entrez votre mot de passe" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="control-label col-sm-2 fw-bold" for="confirm_password">Confirmez votre mot de passe:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" name="confirmer" id="confirm_password" placeholder="Confirmez votre mot de passe" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                        <label><input type="checkbox"> Se souvenir de moi</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <center><button type="submit" class="btn btn-primary">ENVOYER</button></center>
                </div>
            </div>
        </form>
    </div>
</main>

<?php
include_once 'footer.php';
?>