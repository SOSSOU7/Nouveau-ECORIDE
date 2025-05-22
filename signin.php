<?php
session_start();
include_once 'header.php';

$erreur = '';
$_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_input(INPUT_POST, 'connexion1', FILTER_VALIDATE_EMAIL);
    $password = $_POST['mot_de_passe'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!$email || empty($password)) {
        $erreur = "Veuillez remplir tous les champs correctement.";
    } elseif ($csrf !== $_SESSION['csrf_token']) {
        $erreur = "Jeton CSRF invalide. Veuillez recharger la page.";
    } else {
        $req = $conn->prepare("SELECT * FROM utilisateur WHERE email = :email");
        $req->execute(['email' => $email]);
        $utilisateur = $req->fetch();

        if ($utilisateur && password_verify($password, $utilisateur['mot_de_passe'])) {
            session_regenerate_id(true);
            $token = bin2hex(random_bytes(32));
            $_SESSION['token'] = $token;
            $_SESSION['utilisateur_id'] = $utilisateur['id'];
            $_SESSION['role_id'] = $utilisateur['role_id'];

            $sql = $conn->prepare('INSERT INTO session (token, utilisateur_id, adresse_ip, user_agent) VALUES (?, ?, ?, ?)');
            if ($sql->execute([$token, $utilisateur['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']])) {
                header('Location: espace_passager.php');
                exit();
            } else {
                $erreur = "Erreur lors de la sauvegarde de la session.";
            }
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<main id="main-page">
    <h1 class="text-center pt-3 fw-bold">CONNECTEZ-VOUS !</h1>
    <section>
        <div class="container-fluid h-custom">
            <div class="row d-flex justify-content-center align-items-center">
                <div class="col-sm-6 col-md-6 col-lg-6">

                    <!-- Affichage des erreurs -->
                    <?php if (!empty($erreur)): ?>
                        <div class="alert alert-danger text-center"><?= htmlspecialchars($erreur) ?></div>
                    <?php endif; ?>

                    <form action="signin.php" method="POST" class="border-5 rounded-2 p-4 bg-light" id="loginForm">

                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                        <!-- Email -->
                        <div class="form-outline mb-3">
                            <label class="form-label fw-bold" for="email">Adresse mail</label>
                            <input type="email" id="email" name="connexion1" class="form-control fst-italic" placeholder="Entrez une adresse mail valide" autocomplete="email" required autofocus>
                        </div>

                        <!-- Mot de passe -->
                        <div class="form-outline mb-3">
                            <label class="form-label fw-bold" for="password">Mot de passe</label>
                            <input type="password" id="password" name="mot_de_passe" class="form-control fst-italic" placeholder="Entrez votre mot de passe" autocomplete="current-password" required>
                        </div>

                        <!-- Options -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input me-2" type="checkbox" name="rememberMe" id="remember">
                                <label class="form-check-label" for="remember">Se souvenir de moi</label>
                            </div>
                            <a href="mot_de_passe_oublie.php" class="text-body fw-bold">Mot de passe oublié ?</a>
                        </div>

                        <!-- Bouton -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">SE CONNECTER</button>
                        </div>

                        <!-- Lien d'inscription -->
                        <p class="small fw-bold mt-3 mb-0 text-center">Vous n'avez pas de compte ? <a href="signup.php" class="link-danger">Inscrivez-vous !</a></p>
                    </form>

                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = 'Connexion en cours...';
});
</script>

<?php include_once 'footer.php'; ?>
