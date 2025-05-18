<?php
include_once 'header.php';
?>

<?php
// CONNEXION
  
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['connexion1'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['mot_de_passe'];
      
    $req = $conn->prepare("SELECT * FROM utilisateur WHERE email = :email");
    $req->execute(['email' => $email]);
    $utilisateur = $req->fetch();
  
    if ($utilisateur && password_verify($password, $utilisateur['mot_de_passe'])) {
        session_regenerate_id(true); // Regenerate session ID
        $token = bin2hex(random_bytes(32));
        $_SESSION['token'] = $token;  // Correction effectuée ici
        $_SESSION['utilisateur_id'] = $utilisateur['id'];
        $_SESSION['role_id'] = $utilisateur['role_id'];
        
        $sql = $conn->prepare('INSERT INTO session (token, utilisateur_id, adresse_ip, user_agent) VALUES (?, ?, ?, ?)');
        if ($sql->execute([$token, $utilisateur['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']])) {
          header('location:espace_passager.php');
          exit();
        } else {
            echo "Erreur lors de la sauvegarde de la session.";
        }
    } else {
        echo "Email ou mot de passe incorrect";
    }
} else {
    echo "Le champ mot de passe est manquant.";
}
?>

 <main  id="main-page" >
    <!-- Le menu de la page sera injecté ici-->
    <h1 class="text-center pt-3 fw-bold">CONNECTEZ-VOUS!</h1>
    <section >
        <div class="container-fluid h-custom">
          <div class="row d-flex justify-content-center align-items-center ">
            <div class="col-sm-6 col-md-6 col-lg-6">
              <form action="signin.php" method="POST" class="border-5 rounded-2 p-0 mb-2 bg-light" id="loginForm">      
                <!-- Email input -->
                <div data-mdb-input-init class="form-outline mb-2">
                  <label class="form-label fw-bold" for="email">Adresse mail</label>
                  <input type="email" id="email"  name="connexion1" class="form-control fst-italic"
                    placeholder="Entrez une adresse mail valide" />
                </div>
      
                <!-- Password input -->
                <div data-mdb-input-init class="form-outline mb-3">
                  <label class="form-label fw-bold" for="password">Mot de passe</label>
                  <input type="password" id="password"  name="mot_de_passe" class="form-control fst-italic"
                  placeholder="Entrez mot de passe" />
                </div>

                 <!-- Password input confirm -->
                 <div data-mdb-input-init class="form-outline mb-3">
                    <label class="form-label fw-bold" for="password">Confirmez mot de passe</label>
                    <input type="password" id="confirmPassword" name="passwordInput" class="form-control fst-italic"
                    placeholder="Confirmez mot de passe" />
                  </div>
                <div class="d-flex justify-content-between align-items-center">
                  <!-- Checkbox -->
                  <div class="form-check mb-0">
                    <label class="form-check-label" for="remember">
                    <input class="form-check-input me-2" type="checkbox" value=""  name="rememberMe" id="remember" />
                      Se souvenir de moi
                    </label>
                  </div>
                  <a href="#!" class="text-body fw-bold">Mot de passe oublié ?</a>
                </div>
      
                <div class="text-center text-lg-start mt-4 pt-2">
                 <center> <button  type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                    style="padding-left: 2.5rem; padding-right: 2.5rem;">SE CONNECTER</button></center>
                  <p class="small fw-bold mt-2 pt-1 mb-0 text-center">Vous n'avez pas un compte ? <a href="#!"
                      class="link-danger ">Inscrivez-vous!</a></p>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>
   </main>

<script src="js/signin.js"></script>
     
  <!-- Footer -->      
<?php
  include_once 'footer.php';
?>