<?php
include_once 'header.php';
?>
 <main  id="main-page" style="background-image: url('img/cecodrive.webp'); background-size: cover; background-repeat: no-repeat; background-position: center; min-height: 100vh;">
    <!-- Le menu de la page sera injecté ici-->
    <h1 class="text-center pt-3">CONNECTEZ-VOUS!</h1>
    <section >
        <div class="container-fluid h-custom">
          <div class="row d-flex justify-content-center align-items-center ">
            <div class="col-sm-6 col-md-6 col-lg-6">
              <form action="form.html" method="POST" class="border-5 rounded-2 p-0 mb-2 bg-light" id="loginForm">      
                <!-- Email input -->
                <div data-mdb-input-init class="form-outline mb-2">
                  <label class="form-label" for="email">Adresse mail</label>
                  <input type="email" id="email" name="emailInput" class="form-control form-control-lg fst-italic"
                    placeholder="Entrez une adresse mail valide" />
                </div>
      
                <!-- Password input -->
                <div data-mdb-input-init class="form-outline mb-3">
                  <label class="form-label" for="password">Mot de passe</label>
                  <input type="password" id="password" name="passwordInput" class="form-control form-control-lg fst-italic"
                  placeholder="Entrez mot de passe" />
                </div>

                 <!-- Password input confirm -->
                 <div data-mdb-input-init class="form-outline mb-3">
                    <label class="form-label" for="password">Confirmez mot de passe</label>
                    <input type="password" id="confirmPassword" name="passwordInput" class="form-control form-control-lg fst-italic"
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
                  <a href="#!" class="text-body">Mot de passe oublié ?</a>
                </div>
      
                <div class="text-center text-lg-start mt-4 pt-2">
                 <center> <button  type="button" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
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