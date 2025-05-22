<?php
session_start();
error_reporting(E_ALL); // Affiche toutes les erreurs
ini_set('display_errors', 1); // Affiche les erreurs directement sur la page

echo "<h2>Débogage Session et Rôle</h2>";
echo "<pre>";
echo "Contenu de \$_SESSION:\n";
print_r($_SESSION);
echo "\n";

echo "ID_ROLE_ADMIN défini: " . (defined('ID_ROLE_ADMIN') ? ID_ROLE_ADMIN : 'Non défini') . "\n";
echo "ID_ROLE_EMPLOYE défini: " . (defined('ID_ROLE_EMPLOYE') ? ID_ROLE_EMPLOYE : 'Non défini') . "\n";

if (isset($_SESSION['utilisateur_id'])) {
    echo "utilisateur_id en session: " . $_SESSION['utilisateur_id'] . "\n";
} else {
    echo "utilisateur_id NON défini en session.\n";
}

if (isset($_SESSION['utilisateur_role_id'])) {
    echo "utilisateur_role_id en session: " . $_SESSION['utilisateur_role_id'] . "\n";
} else {
    echo "utilisateur_role_id NON défini en session.\n";
}

echo "</pre>";


include_once 'header.php';
include_once 'auth.php'; // Assurez-vous que ce fichier initialise $conn

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    $_SESSION['error_notification'] = "Vous devez être connecté pour accéder à cette page.";
    header("Location: connexion.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur_id'];

// Définir les IDs de rôle selon votre table 'role' (ASSUREZ-VOUS QUE CES IDS CORRESPONDENT À VOTRE BASE DE DONNÉES)
$ID_ROLE_UTILISATEUR = 2; // Rôle par défaut pour un passager
$ID_ROLE_CHAUFFEUR = 4;
$ID_ROLE_CHAUFFEUR_PASSAGER = 5;

// Récupérer le rôle actuel de l'utilisateur depuis la session (mis à jour par gerer_profil_chauffeur.php ou la connexion)
// Par défaut, si non défini, c'est un utilisateur/passager
$current_role_id = $_SESSION['utilisateur_role_id'] ?? $ID_ROLE_UTILISATEUR; 

// Récupérer les crédits et le solde en euros de l'utilisateur
$credits_restant = 0;
// Pour le solde en euro, si vous avez une colonne 'solde_euro' dans votre table utilisateur, utilisez-la.
// Sinon, '50' comme valeur par défaut était déjà dans votre code.
$solde_euro = 50; // Valeur par défaut comme dans votre code original, à remplacer si vous avez une colonne spécifique

try {
    $stmtUserBalance = $conn->prepare("SELECT credit FROM utilisateur WHERE id = :id");
    $stmtUserBalance->execute([':id' => $utilisateur_id]);
    $userBalanceData = $stmtUserBalance->fetch(PDO::FETCH_ASSOC);
    if ($userBalanceData) {
        $credits_restant = $userBalanceData['credit'];
        // Si vous avez une colonne 'solde_euro', décommentez et ajustez ceci :
        // $solde_euro = $userBalanceData['solde_euro']; 
    }
} catch (PDOException $e) {
    error_log("Erreur lors du chargement des crédits : " . $e->getMessage());
}

?>

<main class="main-page">
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
       
<div class="container-fluid text-center">
  <div class="row ">
    <div class="col">
      
<div class=" driver ">
  <div class="border-2 border-dark rounded-4 m-1 " id="covoitCard" style="background-color: rgba(26, 99, 106, 0.83);">
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

    <div class="row  justify-content-between mt-4 mb-1">
      <div class="col-md-2  text-center">
        <div class="yellow-opaque p-2  m-2">
          <p class="fw-bold p-driver">Places :</p>
          <div class="yellow p-2">
            <h4 class="p-driver"><?php // echo htmlspecialchars($covoiturage['nb_place'] ?? '0'); ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-2  text-center">
        <div class="yellow-opaque p-2 m-2">
          <p class="fw-bold p-driver">Prix (€) :</p>
          <div class="yellow p-2">
            <h4 class="p-driver"><?php // echo htmlspecialchars($covoiturage['prix_personne'] ?? '0'); ?></h4>
          </div>
        </div>
      </div>
      <div class=" col-md-2  text-center">
        <div class="yellow-opaque p-2  m-2">
          <p class="fw-bold p-driver">Date : <?php // echo htmlspecialchars($covoiturage['date_depart'] ?? 'Inconnue'); ?></p>
          <div class="yellow p-2">
            <p class="fw-bold arrival p-driver">Départ : <?php // echo htmlspecialchars($covoiturage['heure_depart'] ?? '--:--'); ?></p>
            <p class="fw-bold arrival p-driver">Arrivée : <?php // echo htmlspecialchars($covoiturage['heure_arrivee'] ?? '--:--'); ?></p>
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

    <div class="row  justify-content-between mt-4 mb-1">
      <div class="col-md-2  text-center">
        <div class="yellow-opaque p-2  m-2">
          <p class="fw-bold p-driver">Places :</p>
          <div class="yellow p-2">
            <h4 class="p-driver"><?php // echo htmlspecialchars($covoiturage['nb_place'] ?? '0'); ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-2  text-center">
        <div class="yellow-opaque p-2 m-2">
          <p class="fw-bold p-driver">Prix (€) :</p>
          <div class="yellow p-2">
            <h4 class="p-driver"><?php // echo htmlspecialchars($covoiturage['prix_personne'] ?? '0'); ?></h4>
          </div>
        </div>
      </div>
      <div class=" col-md-2  text-center">
        <div class="yellow-opaque p-2  m-2">
          <p class="fw-bold p-driver">Date : <?php // echo htmlspecialchars($covoiturage['date_depart'] ?? 'Inconnue'); ?></p>
          <div class="yellow p-2">
            <p class="fw-bold arrival p-driver">Départ : <?php // echo htmlspecialchars($covoiturage['heure_depart'] ?? '--:--'); ?></p>
            <p class="fw-bold arrival p-driver">Arrivée : <?php // echo htmlspecialchars($covoiturage['heure_arrivee'] ?? '--:--'); ?></p>
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




<div class="container col-md-6 search-form mb-5 mt-5">
  <form class="form-horizontal " action="espace_chauffeur.php" method="post" style="position:center;">
        <p class="fw-bold text-center">Si vous n'êtes pas satisfait, veuillez modifier votre date de départ.</p>
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



<div class="container" style="background-color: rgba(26, 99, 106, 0.83);">
    <div class="card shadow pt-2 pb-3" style="background-color: rgba(26, 99, 106, 0.83);"  >
        <div class="card-header text-center ">
            <h4 class="user-space text-white">MON ESPACE PERSONNEL</h4>
        </div>
        <div class="card-body">
                    <div class="row  text-white justify-content-center">
                        <div class="col-md-4">
                            <p><strong>CRÉDITS RESTANTS : <?php echo htmlspecialchars($credits_restant); ?></p></strong>
                        </div>
                        <div class="col-md-4">
                            <p><strong>SOLDE EN EURO : <?php echo htmlspecialchars($solde_euro); ?></p></strong>
                        </div>
                    </div> 
                </div>

                <div class="list-group mt-3">
                    <h5 class="mb-3 text-white text-center">Mes informations et réservations</h5>
                    <a href="modifier_profil.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Modifier mon profil</span>
                        <i class="fas fa-user-edit"></i>
                    </a>
                    <a href="mes_reservations.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Mes réservations actuelles</span>
                        <i class="fas fa-ticket-alt"></i>
                    </a>
                    <a href="historique_passager.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Historique de mes voyages en tant que passager</span>
                        <i class="fas fa-history"></i>
                    </a>

                    <?php 
                    // Afficher le bouton "DEVENIR CHAUFFEUR" uniquement si l'utilisateur n'est pas déjà chauffeur ou chauffeur_passager
                    if ($current_role_id != $ID_ROLE_CHAUFFEUR && $current_role_id != $ID_ROLE_CHAUFFEUR_PASSAGER) { 
                    ?>
                        <h5 class="text-center rounded m-2 p-2 mt-4" style="background-color:black; color:white;" >SI VOUS SOUHAITEZ DEVENIR CHAUFFEUR CLIQUEZ SUR LE BOUTON CI-DESSOUS</h5>
                        <center> 
                            <a href="gerer_profil_chauffeur.php" class="btn btn-primary btn-block account-btn">DEVENIR CHAUFFEUR</a>
                        </center>
                    <?php 
                    } 
                    ?>

                    <?php
                    // Afficher les liens spécifiques au chauffeur si l'utilisateur est un chauffeur ou chauffeur_passager
                    if ($current_role_id == $ID_ROLE_CHAUFFEUR || $current_role_id == $ID_ROLE_CHAUFFEUR_PASSAGER):
                    ?>
                        <hr class="my-3 text-white">
                        <h5 class="mb-3 text-white text-center">Espace Chauffeur</h5>
                        <a href="mes_covoiturages_chauffeur.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>Mes covoiturages en cours</span>
                            <i class="fas fa-route"></i>
                        </a>
                        <a href="historique_chauffeur.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>Historique de mes covoiturages proposés</span>
                            <i class="fas fa-car-side"></i>
                        </a>
                        <a href="gerer_vehicules.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>Gérer mes véhicules</span>
                            <i class="fas fa-car"></i>
                        </a>
                        <a href="saisir_voyage.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>Proposer un nouveau covoiturage</span>
                            <i class="fas fa-plus-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
 
            <hr>

            <?php
            // SECTION POUR LES TRAJETS À VALIDER PAR LE PASSAGER
            $trajets_a_valider = [];
            try {
                $stmtValidation = $conn->prepare("
                    SELECT c.id AS covoiturage_id, c.adresse_depart, c.adresse_arrivee, c.date_depart, c.heure_depart
                    FROM participation p
                    JOIN covoiturage c ON p.covoiturage_id = c.id
                    WHERE p.utilisateur_id = :utilisateur_id
                    AND c.statut = 'termine'
                    AND NOT EXISTS (SELECT 1 FROM avis a WHERE a.covoiturage_id = c.id AND a.utilisateur_id = :utilisateur_id_avis)
                    ORDER BY c.date_depart DESC, c.heure_depart DESC
                ");
                $stmtValidation->execute([':utilisateur_id' => $utilisateur_id, ':utilisateur_id_avis' => $utilisateur_id]);
                $trajets_a_valider = $stmtValidation->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Erreur lors du chargement des trajets à valider dans espace_passager.php : " . $e->getMessage());
            }
            ?>

            <h5 class="text-center historique rounded m-2 p-2" style="background-color:black; color:white;">TRAJETS À VALIDER</h5>
            <?php if (!empty($trajets_a_valider)): ?>
                <div class="list-group mb-4">
                    <?php foreach ($trajets_a_valider as $trajet): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                Trajet de <strong><?= htmlspecialchars($trajet['adresse_depart']) ?></strong> à <strong><?= htmlspecialchars($trajet['adresse_arrivee']) ?></strong> le <strong><?= htmlspecialchars(date('d/m/Y à H:i', strtotime($trajet['date_depart'] . ' ' . $trajet['heure_depart']))) ?></strong>
                            </span>
                            <a href="valider_trajet_passager.php?covoiturage_id=<?= htmlspecialchars($trajet['covoiturage_id']) ?>" class="btn btn-info btn-sm">Valider ce trajet</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">Aucun trajet en attente de validation.</div>
            <?php endif; ?>

            <hr>
 
            <div>    
            <h5 class="text-center historique rounded m-2 p-2" style="background-color:black; color:white;" >HISTORIQUE DE MES COVOITURAGES</h5>
            <div class="list-group-item">
                <h6 class="text-center pt-2 pb-2 text-white fw-bold" >COVOITURAGE ACTUELLEMENT EN COURS</h6>
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