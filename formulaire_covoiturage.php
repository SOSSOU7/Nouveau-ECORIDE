<?php
include_once 'header.php';
?>
 <!-- FORMULAIRE DE COVOITURAGE-->
    <section class=" container mt-5 mb-2" id="">
      <h2 class="text-center ">FORMULAIRE DE COVOITURAGES</h2>
        <div class="row justify-content-center">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header bg-primary text-white">
                  <h3 class="text-center">Rechercher un covoiturage</h3>
                </div>
                  <div class="card-body">
                    <form method="GET" id="covoitForm" action="vue_covoiturage.php" method="POST">
                      <div class="mb-3">
                        <label for="adress1" class="form-label">Adresse de départ</label>
                        <input type="text" class="form-control" name="toGo"  id="adress1" placeholder="Entrez votre adresse de départ">
                      </div>
                      <div class="mb-3">
                        <label for="adress2" class="form-label">Adresse d'arrivée</label>
                        <input type="text" class="form-control" name= "arrival" id="adress2">
                      </div>
                      <div class="mb-3">
                        <label for="date" class="form-label">Date de départ</label>
                        <input type="date" class="form-control" name="searchCovoitDate" id="date" placeholder=" ex: 12/12/2021">
                      </div>
                      <div id="formFeedback" class="mb-3"></div>
                        <button type="submit" class="btn btn-primary w-100">RECHERCHER</button>
                      </form>
                  </div>
              </div>
            </div>
        </div>
     </section>


<script src="js/formulaire_covoiturage.js"></script>
<?php
  include_once 'footer.php';
?>