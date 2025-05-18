<?php
include_once 'header.php';
?>

<main class="main-page">
<!--FILTRE DE RECHERCHE DE COVOITURAGES-->
<div class="filter">
  <h5 class="text-center">FILTREZ VOS COVOITURAGES</h5>
  <form id ="filter-form" class="form-horizontal" action="/espace_chauffeur.php", method="POST">
    <div class="form-group row justify-content-between">
      <label class="control-label col-sm-3" for="tarif">Tarif maximum:</label>
      <div class="col-sm-7">
        <input type="number" class="form-control filter-input" id="tarif" placeholder="ex 35€" name="tarif-maximum">
      </div>
    </div>
    <div class="form-group row">
      <label class="control-label col-sm-3 " for="time">Durée maximale:</label>
      <div class="col-sm-7">          
        <input type="time" class="form-control filter-input" id="time" placeholder="45 min" name="durée-maximale">
      </div>
    </div>
    <div class="form-group row">
      <label class="control-label col-sm-3" for="grade">Note minimale:</label>
      <div class="col-sm-7">          
        <input type="number" class="form-control filter-input" id="grade" placeholder=" ex: 4 étoiles">
      </div>
    </div>
    <div class="form-group row">        
      <div class="col-sm-offset-2 col-sm-12 row ">
            <label class="control-label col-sm-4" for="eco">Voiture écologique:</label>
            <div class="col-sm-7">
                <input type="checkbox" id="eco-oui" name="eco" value="oui">
                <label for="eco-oui">Oui</label>
                <input type="checkbox" id="eco-non" name="eco" value="non">
                <label for="eco-non">Non</label>
            </div>
      </div>
    </div>
    <div class="form-group">        
      <div class="col-sm-offset-2 col-sm-10">
       <center><button type="submit" class="btn btn-primary">RECHERCHER</button></center>
      </div>
    </div>
  </form>
</div>

<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
       


<!-- SECTION COVOITURAGES -->

<div class="container-fluid text-center">
  <div class="row ">
    <div class="col">
      
<div class=" driver ">
  <div class="border-2 border-dark rounded-4 m-1 " id="covoitCard" style="background-color: rgba(26, 99, 106, 0.83);">
    <!-- Ligne supérieure avec l'image et les informations principales -->
    <div class="row align-items-center mt-2">
      <div class="col-sm-3">
        <img src="img/OIP 13.jpeg" alt="chauffeur" class="m-2 rounded-circle driver-photo">
      </div>
      <div class=" col-sm-6 text-center text-sm-start flex-column mt-2 mt-sm-0">
        <h5 class="fst-italic text-white" aria-label="Driver Name">Corneille</h5>
        <p class="stars" style="font-size: 20px; color: gold;">★ ★ ★ ★ ★</p>
      </div>
      <div class=" col-sm-3 text-center text-sm-end mt-3 mt-sm-0">
        <form action="vue_detailee_covoiturage.php" method="GET">
          <input type="hidden" name="covoiturage_id">
          <button type="submit" class="detailBtn fw-bold btn btn-primary">DETAILS</button>
        </form>
      </div>
    </div>

    <!-- Ligne inférieure avec les informations supplémentaires -->
    <div class="row  justify-content-between mt-4 mb-1">
      <div class="col-md-2  text-center">
        <div class="yellow-opaque p-2  m-2">
          <p class="fw-bold p-driver">Places :</p>
          <div class="yellow p-2">
            <h4 class="p-driver"><?php echo htmlspecialchars($covoiturage['nb_place'] ?? '0'); ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-2  text-center">
        <div class="yellow-opaque p-2 m-2">
          <p class="fw-bold p-driver">Prix (€) :</p>
          <div class="yellow p-2">
            <h4 class="p-driver"><?php echo htmlspecialchars($covoiturage['prix_personne'] ?? '0'); ?></h4>
          </div>
        </div>
      </div>
      <div class=" col-md-2  text-center">
        <div class="yellow-opaque p-2  m-2">
          <p class="fw-bold p-driver">Date : <?php echo htmlspecialchars($covoiturage['date_depart'] ?? 'Inconnue'); ?></p>
          <div class="yellow p-2">
            <p class="fw-bold arrival p-driver">Départ : <?php echo htmlspecialchars($covoiturage['heure_depart'] ?? '--:--'); ?></p>
            <p class="fw-bold arrival p-driver">Arrivée : <?php echo htmlspecialchars($covoiturage['heure_arrivee'] ?? '--:--'); ?></p>
          </div>
        </div>
      </div>
      <div class=" col-md-3 text-center ">
        <div class="yellow-opaque p-2  m-2">
          <p class="fw-bold p-driver">Voiture écologique :</p>
          <div class="yellow p-2">
            <p class="p-driver">Oui/Non</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
    </div>
   
    <div class="col">
     
<div class=" driver ">
  <div class="border-2 border-dark rounded-4 m-1 " id="covoitCard" style="background-color: rgba(26, 99, 106, 0.83);">
    <!-- Ligne supérieure avec l'image et les informations principales -->
    <div class="row align-items-center mt-2">
      <div class="col-sm-3 text-center">
        <img src="img/OIP 13.jpeg" alt="chauffeur" class="m-2 rounded-circle driver-photo">
      </div>
      <div class=" col-sm-6 text-center text-sm-start flex-column mt-2 mt-sm-0">
        <h5 class="fst-italic text-white" aria-label="Driver Name">Driver Name</h5>
        <p class="stars" style="font-size: 20px; color: gold;">★ ★ ★ ★ ★</p>
      </div>
      <div class=" col-sm-3 text-center text-sm-end mt-3 mt-sm-0">
        <form action="vue_detailee_covoiturage.php" method="GET">
          <input type="hidden" name="covoiturage_id">
          <button type="submit" class="detailBtn fw-bold btn btn-primary">DETAILS</button>
        </form>
      </div>
    </div>

    <!-- Ligne inférieure avec les informations supplémentaires -->
    <div class="row  justify-content-between mt-4 mb-1">
      <div class="col-md-2  text-center">
        <div class="yellow-opaque p-2  m-2">
          <p class="fw-bold p-driver">Places :</p>
          <div class="yellow p-2">
            <h4 class="p-driver"><?php echo htmlspecialchars($covoiturage['nb_place'] ?? '0'); ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-2  text-center">
        <div class="yellow-opaque p-2 m-2">
          <p class="fw-bold p-driver">Prix (€) :</p>
          <div class="yellow p-2">
            <h4 class="p-driver"><?php echo htmlspecialchars($covoiturage['prix_personne'] ?? '0'); ?></h4>
          </div>
        </div>
      </div>
      <div class=" col-md-2  text-center">
        <div class="yellow-opaque p-2  m-2">
          <p class="fw-bold p-driver">Date : <?php echo htmlspecialchars($covoiturage['date_depart'] ?? 'Inconnue'); ?></p>
          <div class="yellow p-2">
            <p class="fw-bold arrival p-driver">Départ : <?php echo htmlspecialchars($covoiturage['heure_depart'] ?? '--:--'); ?></p>
            <p class="fw-bold arrival p-driver">Arrivée : <?php echo htmlspecialchars($covoiturage['heure_arrivee'] ?? '--:--'); ?></p>
          </div>
        </div>
      </div>
      <div class=" col-md-3 text-center ">
        <div class="yellow-opaque p-2  m-2">
          <p class="fw-bold p-driver">Voiture écologique :</p>
          <div class="yellow p-2">
            <p class="p-driver">Oui/Non</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
    </div>
  </div>
</div>




<!-- FORMULAIRE MODIFICATION DE DATE -->


<div class="container col-md-6 search-form mb-5 mt-5">
  <form class="form-horizontal " action="espace_chauffeur.php" method="post" style="position:center;">
        <p class="fw-bold text-center">Si vous n'êtes pas satisfaire, veuillez modifier votre date de départ.</p>
        <div class="edit-covoit-group">
          <label class="edit-covoit-label col-sm-2 edit-covoit-form" for="adress1">votre position actuelle:</label>
          <div class="col-sm-10">          
              <input type="adress" class="form-control" name="real_position" id="adress1" placeholder=" ex: 91 rue de la paix, 95300 Paris">
          </div>
        </div>
        <div class="edit-covoit-group">
            <label class="edit-covoit-label col-sm-2 edit-covoit-form" for="adress2">Adresse d'arrivée:</label>
            <div class="col-sm-10">
              <input type="adress" class="form-control"  name= "destination" id="adress2" placeholder=" ex: 81 rue de la joie, 95300 Paris">
            </div>
        </div>
        <div class="edit-covoit-group">
            <label class="edit-covoit-label col-sm-2 edit-covoit-form" for="date">Nouvelle date:</label>
            <div class="col-sm-10">
              <input type="date" class="form-control" name="new_date" id="date" placeholder=" ex: 12/12/2021">
            </div>
        </div>
        <center> <button type="submit" class="btn btn-primary m-4" name="search_covoit" >RECHERCHER</button></center>
        </form>
 </div>


<!--INFORMATIONS SUR LE CHAUFFEUR-->
<div class="container ">
  <div class="card driverGlobalInfo"  style="background-color: rgba(26, 99, 106, 0.83);">
    <div class="card-header text-center">
        <h4 class=" text-white"> MON ESPACE CHAUFFEUR</h4>
    </div>
    <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3 driver-star">
                    <img src="img/OIP 13.jpeg" alt="Photo du conducteur" class="profile-pic driver-photo">
                    
                    <div class="mb-3 driver1 ">
                    <h5 class="mt-2 driver1 driver-name2 text-white">BRUNO DACOSTA</h5> 
                            <span class="text-warning">&#9733;</span>
                            <span class="text-warning">&#9733;</span>
                            <span class="text-warning">&#9733;</span>
                            <span class="text-warning">&#9734;</span>
                            <span class="text-warning">&#9734;</span>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row text-white">
                        <div class="col-md-4">
                            <p><strong>Places restantes :</strong> 2</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Animaux de compagnie :</strong> Oui</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Fumeurs :</strong> Non</p>
                        </div>
                    </div>
                    <div class="row text-white">
                        <div class="col-md-4">
                            <p><strong>MARQUE DE LA VOITURE :</strong><br> TESLA</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>MODÈLE DE LA VOITURE :</strong><br> Modèle 3</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>ÉNERGIE UTILISÉE :</strong><br> Électrique</p>
                        </div>
                    </div>
                </div>
            </div>
 
            <hr><!-- Ligne de séparation -->
 
            <h5 class="text-center" style="background-color:black; color:white;" >AVIS SUR LE CHAUFFEUR</h5>
            <div class="list-group">
                <div class="clientView">
                    <div class="d-flex">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <div>
                            <h6>Suzie CASTORAMA</h6>
                            <p class="mb-0">Super voyage</p>
                            <p class="text-muted small">Une expérience inoubliable, merci beaucoup !</p>
                        </div>
                    </div>
                </div>
                
                <div class="clientView">
                    <div class="d-flex">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <div>
                            <h6>Suzie CASTORAMA</h6>
                            <p class="mb-0">Désagréable</p>
                            <p class="text-muted small">Bavard et curieux sur ma vie.</p>
                        </div>
                    </div>
                </div>
                <div class="clientView">
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
            <h5 class="text-center historique mt-3 mb-3" style="background-color:black; color:white;" >HISTORIQUE DE MES COVOITURAGES</h5>
            <div class="clientView  ">
                <div class="start-covoit text-center justify-content-center pt-3">
                    <h6>COVOITIURAGE ACTUELLEMENT EN COURS</h6>
                    <button type="submit" class="btn btn-primary  fw-bold mb-2">DEMARRER</button>
                    <button type="submit" class="btn btn-primary fw-bold mb-2">ARRIVER A DESTINATION</button>
                </div>
                    <div class="d-flex">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <div>
                            <h6>Suzie CASTORAMA</h6>
                            <p class="mb-0">Super voyage</p>
                            <p class="text-muted small fw-bold">Une expérience inoubliable, merci beaucoup !</p>
                        </div>
                    </div>
                </div>
            <div class="list-group">
                <div class=" style="background-color: rgba(26, 99, 106, 0.83);"">
                    <div class="d-flex">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <div>
                            <h6>Suzie CASTORAMA</h6>
                            <p class="mb-0">Super voyage</p>
                            <p class="text-muted small fw-bold">Une expérience inoubliable, merci beaucoup !</p>
                        </div>
                    </div>
                </div>
                
                <div class="clientView">
                    <div class="d-flex">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <div>
                            <h6>Suzie CASTORAMA</h6>
                            <p class="mb-0">Désagréable</p>
                            <p class="text-muted small fw-bold">Bavard et curieux sur ma vie.</p>
                        </div>
                    </div>
                </div>
                <div class="clientView">
                    <div class="d-flex">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <div>
                            <h6>Suzie CASTORAMA</h6>
                            <p class="mb-0">Nice travel</p>
                            <p class="text-muted small fw-bold">Super expérience, je recommande vivement !</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

           
<!--FORMULAIRE DE SAISIE DE COVOITURAGE PAR LE CONDUCTEUR-->
<h5 class="text-center historique" style="background-color:black; color:white;" >VOUS ETES CHAUFFEUR, VEUILLEZ SAISIR VOTRE PROCHAIN COVOITURAGE CI-DESSOUS</h5>
<div class="container col-md-6  insertCovoit ">
  <form class="form-horizontal " action="espace_chauffeur.php"  method="POST" >    
        <p class="edit-covoit-title text-center fw-bold">Saisir mon covoiturage en tant que chauffeur.</p>
        <div class="edit-covoit-group row">
          <label class="edit-covoit-label col-sm-3 edit-covoit-form" for="date1">Date de départ:</label>
            <div class="col-sm-8">          
              <input type="date" class="form-control m-1" name="date_depart" id="date1" placeholder=" ex: 91 rue de la paix, 95300 Paris">
            </div>
        </div>

        <div class="edit-covoit-group row">
          <label class="edit-covoit-label col-sm-3 edit-covoit-form " for="date2">Date d'arrivée:</label>
            <div class="col-sm-8">          
              <input type="date" class="form-control m-1" name="date_arrivee" id="date2" placeholder=" ex: 91 rue de la paix, 95300 Paris">
            </div>
        </div>

        <div class="edit-covoit-group row">
          <label class="edit-covoit-label col-sm-3 edit-covoit-form" for="hour1">Heure de départ:</label>
          <div class="col-sm-8">          
              <input type="time" class="form-control m-1" name="heure_depart" id="hour1" placeholder=" ex: 12h 00">
          </div>
        </div>

        <div class="edit-covoit-group row">
          <label class="edit-covoit-label col-sm-3 edit-covoit-form" for="hour2">Heure d'arrivée:</label>
          <div class="col-sm-8">          
              <input type="time" class="form-control m-1" name="heure_arrivee" id="hour2" placeholder=" ex: 15h 00">
          </div>
        </div>

        <div class="edit-covoit-group row">
          <label class="edit-covoit-label col-sm-3 edit-covoit-form" for="adress1">Adresse de départ:</label>
          <div class="col-sm-8">          
              <input type="adress" class="form-control m-1" name="adresse_depart" id="adress1" placeholder=" ex: 91 rue de la paix, 95300 Paris">
          </div>
        </div>
        <div class="edit-covoit-group row">
            <label class="edit-covoit-label col-sm-3 edit-covoit-form" for="adress2">Adresse d'arrivée:</label>
            <div class="col-sm-8">
              <input type="adress" class="form-control m-1" name= "adresse_arrivee" id="adress2" placeholder=" ex: 81 rue de la joie, 95300 Paris">
            </div>
        </div>
        <div class="edit-covoit-group row">
            <label class="edit-covoit-label col-sm-3 edit-covoit-form" for="price">Prix (€):</label>
            <div class="col-sm-8">
              <input type="number" class="form-control m-1" name="prix_personne" id="price" placeholder=" ex: 35 euros">
            </div>
        </div>
        
        <div class="edit-covoit-group row">
            <label class="edit-covoit-label col-sm-8 edit-covoit-form text-center" for="car">Choisir une voiture(déjà inscrite):</label>
            <div class="col-sm-12">
              <select name="voiture_id" id="car" class="text-center">
                <option value="1">VOLVO</option>
                <option value="2">AUDI</option>
                <option value="3">BMW</option>
                <option value="4">TESLA</option>
                </select>
            </div>
        </div>
       
        <div class="edit-covoit-group row">
            <label class="edit-covoit-label col-sm-3 edit-covoit-form" for="place">Places disponibles:</label>
            <div class="col-sm-8">
              <input type="number" class="form-control m-1"  name="nb_place" id="place" placeholder="ex: 4 places">
            </div>
        </div>

        <div class="edit-covoit-group row">
            <label class="edit-covoit-label col-sm-3 edit-covoit-form" for="place">Statut du covoiturage:</label>
            <div class="col-sm-8">
              <input type="text" class="form-control m-1"  name="statut" id="place" placeholder="ex: en cours">
            </div>
        </div>
        <center><button type="submit" class="btn btn-primary m-3 fw-bold" name ="create_covoit">ENVOYER</button></center>

        <p class="fw-bold pt-3 text-center">ENREGISTRER UNE NOUVELLE VOITURE CI-DESSOUS </p>
        <div class="edit-covoit-group">
            
            <div class="col-sm-12">
            <center><a href="nouvelle_voiture.php" class="btn btn-primary m-3 fw-bold">ENREGISTRER UNE NOUVELLE VOITURE</a></center>
            </div>
        </div>
        </form>
 </div>
  </div>
</div>
 </main>
<?php
include_once 'footer.php';
?>
