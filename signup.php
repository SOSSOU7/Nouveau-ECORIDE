<?php
include_once 'header.php';
?>
<?php

// CREATION DE COMPTE

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pseudo = $_POST['pseudo'];
    $email = filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['mot_de_passe'];
    $confirm = 1;

    if ($email && !empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
 // VERIFIER SI L'EMAIL EXISTE DEJA
        $sql = "SELECT * FROM utilisateur WHERE email = ?";
        $req = $conn->prepare($sql);
        $req->execute([$email]);
        $utilisateurExist = $req->fetch();

        if ($utilisateurExist) {
            echo "Cet email est déjà utilisé";
            exit();
        } else {
            $sql = "INSERT INTO utilisateur (pseudo, email, mot_de_passe, role_id) VALUES (?, ?, ?, ?,)";
            $req = $conn->prepare($sql);

            if ($req->execute([$pseudo, $email, $passwordHash, $confirm])) {
                header("location: infos1.php");  
                exit(); // 
            } else {
                echo "Erreur lors de la création de votre compte";
            }
        }
    } else {
        echo "Email ou mot de passe invalide";
    }
}
?>
<main id="main-page" class="container-fluid h-custom">
    <!-- Le menu de la page sera injecté ici-->
<div class="container account-form" style="background: rgb(237, 215, 15, 0.9); border-radius: 10px; padding: 20px; ">
    <h2 class="text-center">Créer un compte</h2>
    <h4 class="text-center creation-title">Remplissez le formulaire ci-dessous pour créer un compte et bénéficier de 20 crédits offerts gratuitement.</h4>

    <!-- Formulaire d'inscription -->
<form class="form-horizontal m-5" action="signup.php" method="POST">
  <div class="form-group">
    <label class="control-label col-sm-2 fw-bold" for="username">Votre Pseudo:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="username" name="pseudo" placeholder="Entrez votre pseudo">
    </div>
  </div>

  <div class="form-group">
    <label class="control-label col-sm-2 fw-bold" for="email">Votre adresse mail:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="email" name="mail" placeholder="Entrez votre email">
    </div>
  </div>
  <div class="form-group">
    <label class="control-label col-sm-2 fw-bold" for="pwd">Mot de passe:</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" type="password" name="mot_de_passe" class="form-control" id="password" placeholder="Entrez votre mot de passe">
    </div>
  </div>
  <div class="form-group">
    <label class="control-label col-sm-2 fw-bold" for="pwd">Confirmez votre mot de passe:</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" type="password"  class="form-control" name="confirmer" id="confirm_password" placeholder="confirmez votre mot de passe">
    </div>
  </div>
  <div class="form-group">
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
