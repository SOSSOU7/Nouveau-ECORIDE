<?php
session_start();
include_once 'header.php';
?>

<!--information sur le chauffeur-->
<section class="container grid mobile">

<?php
    // Inclusion de la base de données
    include_once 'db.php';

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
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$_SESSION['search_results'] = $results;




// Récupération des résultats
 if($results){
  
// Affichage des résultats dans une structure HTML
foreach ($results as $covoiturage) {
    ?>
    <div class="container mt-4 box">
        <div class="row driver form-block">
            <div class="col-md-2 driver-identity"> 
                <img src="img/<?php echo htmlspecialchars($covoiturage['photo']); ?>" alt="chauffeur" class="driver-photo"> 
            </div>
            <div class="col-md-3 driver-identity1"> 
                <p class="driver-name stars"><?php echo htmlspecialchars($covoiturage['nom']); ?> <?php echo htmlspecialchars($covoiturage['prenom']); ?></p> 
                <p class="stars" style="color:gold; font-size:15px"><?php
                $note = isset($covoiturage['note_moyenne']) ? round($covoiturage['note_moyenne']) : 0;

                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $note) {
                        echo '★';
                    } else {
                        echo '☆';
                    }
                }
                ?></p> 
            </div>
            <div class="bottom mobile-bottom">
                <div class="col-md-1 yellow-opaque">
                    <p class="bold">Places :</p>
                    <div class="yellow">
                        <h4><?php echo htmlspecialchars($covoiturage['nb_place'] ?? '0'); ?></h4>
                    </div>
                </div>
                <div class="col-md-1 yellow-opaque">
                    <p class="bold">Prix (€) :</p>
                    <div class="yellow">
                        <h4><?php echo htmlspecialchars( $covoiturage['prix_personne'] ?? 'N/A'); ?></h4>
                    </div>
                </div> 
                <div class="col-md-2 yellow-opaque"> 
                    <p class="bold">Date : <?php echo htmlspecialchars($covoiturage['date_depart'] ?? 'Inconnue'); ?></p> 
                    <div class="yellow">
                        <p class="bold arrival">Départ : <?php echo htmlspecialchars($covoiturage['heure_depart'] ?? '--:--'); ?></p> 
                        <p class="bold arrival">Arrivée : <?php echo htmlspecialchars($covoiturage['heure_arrivee'] ?? '--:--'); ?></p> 
                    </div>
                </div>
                <div class="col-md-2 yellow-opaque">
                    <p class="bold">Voiture écologique :</p>
                    <div class="yellow">
                        <p><?php 
                        if($covoiturage['energie'] == 'electrique'){
                            echo 'Oui';
                        }else{
                            echo 'Non';
                        }
                        ?></p>
                    </div>
                </div>
                <div class="top-right-button">
                    <form action="vue_detailee_covoiturage.php" method="GET">
                        <input type="hidden" name="covoiturage_id" value="<?php echo $covoiturage['id']; ?>">
                        <button type="submit" class="detailBtn">DETAILS</button>
                    </form>
                </div>
            </div>     
        </div>
    </div>
    <?php
    }
}
?>

<?php
  include_once 'footer.php';
?>