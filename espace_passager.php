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



<!--ESPACE DU PASSAGER-->

<div class="container" style="background-color: rgba(26, 99, 106, 0.83);">
    <div class="card shadow pt-2 pb-3" style="background-color: rgba(26, 99, 106, 0.83);"  >
        <div class="card-header text-center ">
            <h4 class="user-space text-white">MON ESPACE PASSAGER</h4>
        </div>
        <div class="card-body">
            <!-- <div class="row text-center">
                <div class="col-md-3 driver-star">
                    <img src="img/OIP 13.jpeg" alt="Photo du conducteur" class="profile-pic driver-photo">
                    
                    <div class="mb-3 driver1 ">
                    <h5 class="mt-2 driver1 driver-name2">BRUNO DACOSTA</h5> 
                            <span class="text-warning">&#9733;</span>
                            <span class="text-warning">&#9733;</span>
                            <span class="text-warning">&#9733;</span>
                            <span class="text-warning">&#9734;</span>
                            <span class="text-warning">&#9734;</span>
                    </div>
                </div>
                 <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Places restantes :</strong> 2</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Animaux de compagnie :</strong> Oui</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Fumeurs :</strong> Non</p>
                        </div>
                    </div> -->
                    <div class="row  text-white justify-content-center">
                        <div class="col-md-4">
                            <p><strong>CREDITS RESTANT : 20</p></strong>
                        </div>
                        <div class="col-md-4">
                            <p><strong>SOLDE EN EURO : 50</p></strong>
                        </div>
                        <!--<div class="col-md-4">
                            <p><strong>ÉNERGIE UTILISÉE :</strong><br> Électrique</p>
                        </div> -->
                    </div> 
                </div>

                <?php
                        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['saveModal'])) {
                            try {
                                $conn->beginTransaction();

                                // INSERTION DES INFOS DE LA VOITURE
                                $utilisateur_id = 1;
                                $marque = trim($_POST['marque']);
                                $modele = trim($_POST['modele']);
                                $immatriculation = trim($_POST['immatriculation']);
                                $energie = $_POST['energie'];
                                $couleur = trim($_POST['couleur']);
                                $date_premiere_immatriculation = $_POST['date_premiere_immatriculation'];
                                $places_dispo = (int)$_POST['places_dispo'];

                                $sql_voiture = "INSERT INTO voiture (utilisateur_id, marque, modele, immatriculation, energie, couleur, date_premiere_immatriculation, places_dispo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                                $req_voiture = $conn->prepare($sql_voiture);
                                $req_voiture->execute([$utilisateur_id, $marque, $modele, $immatriculation, $energie, $couleur, $date_premiere_immatriculation, $places_dispo]);

                                // INSERTION DES PREFERENCES
                                $fumeur = $_POST['fumeur'];
                                $animaux_compagnie = $_POST['animaux_compagnie'];

                                $sql_preferences = "INSERT INTO preferences (utilisateur_id, fumeur, animaux_compagnie) VALUES (?, ?, ?)";
                                $req_preferences = $conn->prepare($sql_preferences);
                                $req_preferences->execute([$utilisateur_id, $fumeur, $animaux_compagnie]);

                                $conn->commit();
                                $_SESSION['notification'] = "Votre compte en tant que chauffeur ou chauffeur/passager a été créé avec succès.";
                                header("Location: espace_chauffeur.php");
                                exit();
                            } catch (Exception $e) {
                                $conn->rollBack();
                                echo "Erreur : " . $e->getMessage();
                            }
                        }
                        ?>

                <h5 class="text-center rounded m-2 p-2" style="background-color:black; color:white;" >SI VOUS SOUHAITEZ DEVENIR CHAUFFEUR CLIQUEZ SUR LE BOUTON CI-DESSOUS</h5>

               <center> <button  type="submit" class="btn btn-primary btn-block account-btn" data-bs-toggle="modal" data-bs-target="#exampleModal">DEVENIR CHAUFFEUR</button></center>
               <!-- Structure du modal -->
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style=" background-color: rgb(244, 244, 29, 0.3);">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header" style="background-color:  rgba(26, 99, 106, 0.83);">
                        <h5 class="modal-title text-center" id="exampleModalLabel">FORMULAIRE  A REMPLIR POUR DEVENIR CHAUFFEUR</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="background-color:  rgba(26, 99, 106, 0.83);">
                                    <form action="espace_passager.php" method="post" class=""> 
                        <p class="text-center" style="margin:10px; font-weight:bold; background-color:black; color: white">Remplissez les champs ci-dessous puis sauvegarder</p>
                        
                        <div class="car-registration">
                            <div class="form-group m-2"> 
                                <label for="car1" class=" text-white">Marque:</label> 
                                <input class="input-account rounded"  type="text" name ="marque" class="form-control" id="carMark" placeholder="ex: TESLA"> 
                            </div> 
                        <div class="form-group m-2"> 
                            <label for="model1" class=" text-white">Modèle:</label> 
                            <input class="input-account"  type="text" name="modele" class="form-control" id="carModel" placeholder="ex: Model S"> 
                        </div> 
                        <div class="form-group m-2"> 
                            <label for="immatriculation" class=" text-white"> N° Plaque d'immatriculation:</label> 
                            <input class="input-account" type="text" name="immatriculation" class="form-control" id="immatriculation" placeholder="ex: GZ-XXX-AB"> 
                        </div> 
                        <div class="form-group m-2"> 
                            <label for="immatriculation" class=" text-white"> Date de première immatriculation</label> 
                            <input class="input-account" type="date"  name="date_premiere_immatriculation" class="form-control" id="immatriculation"> 
                        </div>
                        <div class="form-group m-2"> 
                                <label for="model1" class=" text-white">Couleur:</label> 
                                <input class="input-account"  type="text" name="couleur" class="form-control" id="carColor" placeholder="ex: Noir"> 
                        </div>  
                        <div class="form-group m-2"> 
                            <label for="model1" class=" text-white">Places disponibles:</label> 
                            <input class="input-account"  type="number" name="places_dispo" class="form-control" id="availablePlace" placeholder="ex: 4 places"> 
                        </div>
                        <div class="form-group m-2"> 
                            <label for="model1" class=" text-white">Energie utilisée:</label> 
                            <div class="form-check m-2">
                            <select class="form-select"name="energie" id= "carEnergy">
                                <option selected>Sélectionner dans le menu</option>
                                <option value="electrique">Electrique</option>
                                <option value="thermique">Thermique</option>
                            </select>
                            </div>
                        </div>
                </div>

                        <!--PREFERENCES DU CHAUFFEUR POUR LE PASSAGER QUI DEVIENT CHAUFFEUR-->
                        <div class="driver-choice pt-5 justify-content-center">
                            <label class="control-label col-sm-6 fw-bold" for="preference">Fumeur(s):</label>
                            <div class="col-sm-6 pb-5">
                                <label for="eco-oui" class="fw-bold">Oui</label>
                                <input type="radio" id="smokingYes" name="fumeur" value="oui">
                                <label for="eco-non" class="fw-bold">Non</label>
                                <input type="radio" id="smokingNo" name="fumeur" value="non">
                            </div>
                            <label class="control-label col-sm-6 fw-bold" for="tarif">Animaux de compagnie:</label>
                            <div class="col-sm-6 pb-5">
                                <label for="eco-oui" class="fw-bold" >Oui</label>
                                <input type="radio" id="animalYes" name="animaux_compagnie" value="oui">
                                <label for="eco-non" class="fw-bold">Non</label>
                                <input type="radio" id="animalNo" name="animaux_compagnie" value="non">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary" name ="saveModal" >Sauvegarder</button>
                    </div>
                </form> 
                    </div>
                    
                    </div>
                </div>
             </div>
        </div>
 
            <hr>
 
            <!-- <h5 class="text-center" style="background-color:black; color:white;" >AVIS SUR LE CHAUFFEUR</h5>
            <div class="list-group">
                <div class="list-group-item">
                    <div class="d-flex">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <div>
                            <h6>Suzie CASTORAMA</h6>
                            <p class="mb-0">Super voyage</p>
                            <p class="text-muted small">Une expérience inoubliable, merci beaucoup !</p>
                        </div>
                    </div>
                </div>
                <div class="list-group-item">
                    <div class="d-flex">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <div>
                            <h6>Suzie CASTORAMA</h6>
                            <p class="mb-0">Désagréable</p>
                            <p class="text-muted small">Bavard et curieux sur ma vie.</p>
                        </div>
                    </div>
                </div>
                <div class="list-group-item">
                    <div class="d-flex">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <div>
                            <h6>Suzie CASTORAMA</h6>
                            <p class="mb-0">Nice travel</p>
                            <p class="text-muted small">Super expérience, je recommande vivement !</p>
                        </div>
                    </div>
                </div>
            </div> -->

            <div>    
            <h5 class="text-center historique rounded m-2 p-2" style="background-color:black; color:white;" >HISTORIQUE DE MES COVOITURAGES</h5>
            <div class="list-group-item">
                <h6 class="text-center pt-2 pb-2 text-white fw-bold" >COVOITIURAGE ACTUELLEMENT EN COURS</h6>
                    <div class=" d-flex justify-content-center pt-2 pb-3">
                        <button type="submit" class="btn btn-primary btn-covoit me-2">VALIDER COVOITURAGE</button>
                        <button type="submit" class="btn btn-primary btn-covoit me-2"><a href="avis.php">SOUMETTRE UN AVIS</a></button>
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
</div>


 </main>
<?php
include_once 'footer.php';
?>
