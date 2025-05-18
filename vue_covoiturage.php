<?php
session_start();
include_once 'header.php';
?>


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

<?php
  include_once 'footer.php';
?>



