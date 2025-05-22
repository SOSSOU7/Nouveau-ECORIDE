<?php
session_start();
include_once 'header.php';
?>

    <?php
    
        // Récupération des valeurs des champs
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_SESSION['search_criteres'] ==[
            "adresse_depart" => $_POST['toGo'],
            "adresse_arrivee" => $_POST['arrival'],
            "date_depart" => $_POST['searchCovoitDate']
            ];
        }

        // POUR LE FORMULAIRE DE RECHERCHE DE COVOITURAGE  
        if (isset($_GET['btnSearchCovoit'])) {
           // Récupération des valeurs des champs
           $toGo = isset($_GET['toGo']) ? trim($_GET['toGo']) : '';
           $arrival = isset($_GET['arrival']) ? trim($_GET['arrival']) : '';
           $searchCovoitDate = isset($_GET['searchCovoitDate']) ? trim($_GET['searchCovoitDate']) : '';
        }

        // POUR LE FILTRE DE RECHERCHE
           if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['btnFilterSearchCovoit'])) {
            // Récupération des valeurs des champs
            $filterPrice = isset($_GET['filterPrice']) ? trim($_GET['filterPrice']) : '';
            $filterTimeMax = isset($_GET['filterTimeMax']) ? trim($_GET['filterTimeMax']) : '';
            $filterSRateMin = isset($_GET['filterSRateMin']) ? trim($_GET['filterSRateMin']) : '';
            $eco = isset($_GET['eco']) ? trim($_GET['eco']) : null;
        }
        $eco = $_GET['eco'] ?? null;

        $sql = "SELECT c.*, TIMEDIFF(c.heure_arrivee, c.heure_depart) AS duree_trajet 
        FROM covoiturages c 
        INNER JOIN voiture v ON c.voiture_id = v.id 
        WHERE 1=1"; // Condition par défaut pour construire dynamiquement

        $params = [];   
         
        // POUR LE FORMULAIRE DE RECHERCHE DE COVOITURAGE 
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['btnNewFormCovoit'])) {
            // Récupération des valeurs des champs
            $position1NewFormCovoit = isset($_GET['position1NewFormCovoit']) ? trim($_GET['position1NewFormCovoit']) : '';
            $position2NewFormCovoit = isset($_GET['position2NewFormCovoit']) ? trim($_GET['position2NewFormCovoit']) : '';
            $dateNewFormCovoit = isset($_GET['dateNewFormCovoit']) ? trim($_GET['dateNewFormCovoit']) : '';
        }
       
           // Construction de la requête SQL
           $sql = "SELECT c.*, u.nom, u.prenom, u.telephone, u.photo, v.marque,
                 v.modele, v.immatriculation, v.energie, v.couleur, v.date_premiere_immatriculation,
                 COALESCE(AVG(a.note), 0) AS note_moyenne
                FROM
                    covoiturage c
                JOIN
                    utilisateur u ON c.utilisateur_id = u.id
                JOIN
                    voiture v ON c.voiture_id = v.id
                LEFT JOIN
                    avis a ON a.utilisateur_id = u.id
                WHERE
                c.nb_place > 0";
           $params = [];
        
           if (!empty($toGo)) {
               $sql .= " AND c.adresse_depart LIKE ?";
               $params[] = "%$toGo%";
           }
       
           if (!empty($arrival)) {
               $sql .= " AND c.adresse_arrivee LIKE ?";
               $params[] = "%$arrival%";
           }

        // FILTRE DE RECHERCHE

        if (!empty($filterPrice )) {
            $sql .= " AND c.prix_personne <= ?";
            $params[] = $filterPrice;
        }
        if (!empty($filterTimeMax)) {
        $sql .= " AND TIMEDIFF(c.heure_arrivee, c.heure_depart) <= ?";
        $params[] = $filterTimeMax;
        }
        if (!empty($filterSRateMin)) {
            $sql .= " AND note_moyenne >= ?";
            $params[] = $filterSRateMin;
        }
        if ($eco === 'oui') {
            $sql .= " AND v.energie = 'electrique'";
        } elseif ($eco === 'non') {
            $sql .= " AND v.energie != 'electrique'";
        }

           // FILTRE SUR LA DATE DE DEPART AVEC UNE PLAGE DE 5 JOURS SUR DATE SAISIE
             if(!empty($searchCovoitDate)){
                $sql .= " AND (c.date_depart = ? OR c.date_depart BETWEEN ? AND ?)";
                $dateMin = date('Y-m-d', strtotime($searchCovoitDate . ' -5 days'));
                $dateMax = date('Y-m-d', strtotime($searchCovoitDate . ' +5 days'));
                $params[] = $searchCovoitDate;
                $params[] = $dateMin;
                $params[] = $dateMax;
                 }

             // FILTRE SUR LE NOMBRE DE PLACES DISPONIBLES ET SUPERIEUR A 0
             $sql .= " AND c.nb_place > 0";

             // GROUPER LES RESULTATS CORRECTEMENT
             $sql .=" GROUP BY c.id, c.adresse_depart, c.adresse_arrivee, c.date_depart, c.heure_depart, c.prix_personne,
             c.nb_place, u.nom, u.prenom, u.telephone, v.modele, v.immatriculation, v.energie, v.couleur,
             v.date_premiere_immatriculation";
             

    // Préparation et exécution de la requête
  $conn = new PDO("mysql:host=$servername; dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['search_results'] = $results;



    
    // Récupération des résultats
     if($results){
      
    // Affichage des résultats dans une structure HTML
    foreach ($results as $covoiturage) {
        ?>
<main class="main-page">
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
        <!-- Affichage de la note sous forme d'étoiles -->
        <?php
                    $note = isset($covoiturage['note_moyenne']) ? round($covoiturage['note_moyenne']) : 0;

                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $note) {
                            echo '★';
                        } else {
                            echo '☆';
                        }
                    }
                    ?>
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
            <p class="p-driver"><?php 
                            if($covoiturage['energie'] == 'electrique'){
                                echo 'Oui';
                            }else{
                                echo 'Non';
                            }
                            ?></p>
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
        <img src="img/<?php echo htmlspecialchars($covoiturage['photo']); ?>" alt="chauffeur" class="m-2 rounded-circle driver-photo">
      </div>
     <div class=" col-sm-6 text-center text-sm-start flex-column mt-2 mt-sm-0">
        <h5 class="fst-italic text-white" aria-label="Driver Name"><?php echo htmlspecialchars($covoiturage['nom']); ?> <?php echo htmlspecialchars($covoiturage['prenom']); ?></h5>
        <p class="stars" style="font-size: 20px; color: gold;">★ ★ ★ ★ ★</p>
      
        <!-- Affichage de la note sous forme d'étoiles -->
       <?php
                    $note = isset($covoiturage['note_moyenne']) ? round($covoiturage['note_moyenne']) : 0;

                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $note) {
                            echo '★';
                        } else {
                            echo '☆';
                        }
                    }
                    ?>


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
            <p class="p-driver"><?php 
              if($covoiturage['energie'] == 'electrique'){
                  echo 'Oui';
              }else{
                  echo 'Non';
              }
              ?></p>
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
    }
?>

 <?php
 }
 else{

 ?>
<script>
    alert("Aucun covoiturage trouvé pour cette recherche.");
 </script>
<!-- FORMULAIRE MODIFICATION DE DATE -->

<div class="container col-md-6 search-form mb-5 mt-5">
  <form class="form-horizontal " action="espace_chauffeur.php" method="post" style="position:center;">
        <p class="fw-bold text-center">Si vous n'êtes pas satisfaire, veuillez modifier votre date de départ.</p>
        <div class="edit-covoit-group">
          <label class="edit-covoit-label col-sm-2 edit-covoit-form" for="adress1">votre position actuelle:</label>
          <div class="col-sm-10">          
              <input type="adress" class="form-control" name="position1NewFormCovoit" id="adress1" placeholder=" ex: 91 rue de la paix, 95300 Paris">
          </div>
        </div>
        <div class="edit-covoit-group">
            <label class="edit-covoit-label col-sm-2 edit-covoit-form" for="adress2">Adresse d'arrivée:</label>
            <div class="col-sm-10">
              <input type="adress" class="form-control"  name="position2NewFormCovoit" id="adress2" placeholder=" ex: 81 rue de la joie, 95300 Paris">
            </div>
        </div>
        <div class="edit-covoit-group">
            <label class="edit-covoit-label col-sm-2 edit-covoit-form" for="date">Nouvelle date:</label>
            <div class="col-sm-10">
              <input type="date" class="form-control" name="dateNewFormCovoit" id="date" placeholder=" ex: 12/12/2021">
            </div>
        </div>
        <center> <button type="submit" class="btn btn-primary m-4" name= "btnNewFormCovoit" >RECHERCHER</button></center>
        </form>
 </div>
 <?php
}
?>

<?php
// ENVOI DU FORMULAIRE DE RECHERCHE DE COVOITURAGE SI RESULTATS TROUVES
// Affichage du formulaire de recherche de covoiturages
if ($results) {
?>

<br><br><br><br><br><br><br><br><br><br><br>

<!--FILTRE DE RECHERCHE DE COVOITURAGES-->
<div class="filter" >
  <h5 class="text-center">FILTREZ VOS COVOITURAGES</h5>
  <form id ="filter-form" class="form-horizontal" action="/espace_chauffeur.php", method="POST">
    <div class="form-group row justify-content-between">
      <label class="control-label col-sm-3" for="tarif">Tarif maximum:</label>
      <div class="col-sm-7">
        <input type="number" value="<?php echo $toGo;?>" class="form-control filter-input" id="tarif" placeholder="ex 35€" name="tarif-maximum">
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



<!-- FILTRE DE RECHERCHE -->
<div class="container col-md-5" style="background-color: rgba(26, 99, 106, 0.83);">
    
 <form id ="filter-form" class="col-md-6" action="vue_covoiturage.php" method="GET">
        <h5 class="filter-title">FILTREZ VOS RECHERCES</h5>

        <!--Valeurs cachées pour les champs de recherche-->
        <div class="col-sm-10">
            <input type="hidden"  value="<?php echo $toGo;?>" name="toGo" class="filter-input" id="tarif">
        </div>

        <div class="col-sm-10">
            <input type="hidden" value="<?php echo $arrival;?>" name="arrival" class="filter-input" id="time">
        </div>
        
        <div class="col-sm-10">
            <input type="hidden" value="<?php echo $searchCovoitDate;?>" name="searchCovoitDate" class="filter-input" id="tarif">
        </div>

        <div class="form-group">
            <label class="control-label col-sm-3" for="time">Durée maximale:</label>
            <div class="col-sm-10">
              <input type="number" name="filterTimeMax" class="filter-input" id="time" placeholder=" ex: 50 minutes">
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-3" for="grade">Note minimale:</label>
            <div class="col-sm-10">
              <input type="number" name="filterSRateMin" class="filter-input" id="grade" placeholder=" ex: 4 étoiles">
            </div>
        </div>

        <div class="form-group">
          <label class="control-label col-sm-3" for="tarif">Tarif maximum:</label>
          <div class="col-sm-10">
            <input type="number" name="filterPrice" class="filter-input" id="tarif" placeholder=" ex: 20€">
          </div>
        </div>

        <div class="form-group">
          <label class="control-label col-sm-6" for="tarif">Voiture écologique(électrique):</label>
          <div class="col-sm-6">
            <select name="eco" id="eco">
              <option value="oui">Oui</option>
              <option value="non">Non</option>
            </select>
        </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-12 text-center">
            <button type="submit" name="btnfilterSearchCovoit" class="btn btn-default btnForm">RECHERCHER</button>
          </div>
        </div>        
      </form>
</div>


      
<?php
}
?>

 </main>
<?php
  include_once 'footer.php';
?>



