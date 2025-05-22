<?php
require_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn
include_once 'header.php'; // Assurez-vous que header.php inclut les balises HTML de base et les liens CSS/JS (Bootstrap, Font Awesome)

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour modifier votre mot de passe.";
    header("Location: signin.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Initialiser les notifications
$notification = '';
$error_notification = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancien_mot_de_passe = $_POST['ancien_mot_de_passe'] ?? '';
    $nouveau_mot_de_passe = $_POST['nouveau_mot_de_passe'] ?? '';
    $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';

    // 1. Récupérer le mot de passe haché actuel de l'utilisateur depuis la base de données
    try {
        $stmt = $conn->prepare("SELECT mot_de_passe FROM utilisateur WHERE id = :id");
        $stmt->execute([':id' => $utilisateur_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error_notification = "Erreur: Utilisateur non trouvé. Veuillez vous reconnecter.";
        } else {
            $mot_de_passe_hache_actuel = $user['mot_de_passe'];

            // 2. Vérifier si l'ancien mot de passe fourni correspond au hachage actuel
            if (!password_verify($ancien_mot_de_passe, $mot_de_passe_hache_actuel)) {
                $error_notification = "L'ancien mot de passe est incorrect.";
            } 
            // 3. Vérifier que le nouveau mot de passe et sa confirmation correspondent
            else if ($nouveau_mot_de_passe !== $confirmer_mot_de_passe) {
                $error_notification = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
            }
            // 4. Appliquer des règles de complexité pour le nouveau mot de passe
            else if (strlen($nouveau_mot_de_passe) < 8) {
                $error_notification = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
            }
            // Exemple de règle de complexité plus avancée (décommenter si nécessaire)
            /*
            else if (!preg_match('/[A-Z]/', $nouveau_mot_de_passe) ||
                     !preg_match('/[a-z]/', $nouveau_mot_de_passe) ||
                     !preg_match('/[0-9]/', $nouveau_mot_de_passe) ||
                     !preg_match('/[^A-Za-z0-9]/', $nouveau_mot_de_passe)) {
                $error_notification = "Le nouveau mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.";
            }
            */
            else {
                // 5. Hacher le nouveau mot de passe
                $nouveau_mot_de_passe_hache = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);

                // 6. Mettre à jour le mot de passe dans la base de données
                $stmtUpdate = $conn->prepare("UPDATE utilisateur SET mot_de_passe = :nouveau_mot_de_passe WHERE id = :id");
                $stmtUpdate->execute([
                    ':nouveau_mot_de_passe' => $nouveau_mot_de_passe_hache,
                    ':id' => $utilisateur_id
                ]);

                $notification = "Votre mot de passe a été modifié avec succès !";
                // Optionnel : Déconnecter l'utilisateur pour le forcer à se reconnecter avec le nouveau mot de passe
                // header("Location: designin.php");
                // exit();
            }
        }
    } catch (PDOException $e) {
        $error_notification = "Erreur lors de la mise à jour du mot de passe : " . $e->getMessage();
        error_log("Password update error (modifier_mot_de_passe.php): " . $e->getMessage());
    }
}
?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Modifier mon mot de passe</h2>

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

                    <form action="modifier_mot_de_passe.php" method="POST">
                        <div class="mb-3">
                            <label for="ancien_mot_de_passe" class="form-label">Ancien mot de passe:</label>
                            <input type="password" class="form-control" id="ancien_mot_de_passe" name="ancien_mot_de_passe" required>
                        </div>
                        <div class="mb-3">
                            <label for="nouveau_mot_de_passe" class="form-label">Nouveau mot de passe:</label>
                            <input type="password" class="form-control" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" required minlength="8">
                            <small class="form-text text-muted">Minimum 8 caractères.</small>
                            </div>
                        <div class="mb-3">
                            <label for="confirmer_mot_de_passe" class="form-label">Confirmer le nouveau mot de passe:</label>
                            <input type="password" class="form-control" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" required>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5">Mettre à jour le mot de passe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'footer.php'; ?>