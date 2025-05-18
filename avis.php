<?php
include_once 'header.php';
?>

<?php
//  AVIS
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $utilisateur_id = 2;
    $covoiturage_id = 7;
    $commentaire = $_POST['commentaire'];
    $note = $_POST['note'];
    $statut = $_POST['statut'];
    $date_avis = $_POST['date_avis'];

    $sql = "INSERT INTO avis (utilisateur_id, covoiturage_id, commentaire, note, statut, date_avis) VALUES (?, ?, ?, ?, ? ,?)";
    $req = $conn->prepare($sql);

    if($req->execute([$utilisateur_id, $covoiturage_id, $commentaire, $note, $statut, $date_avis])){
        $_SESSION['notification'] = "Votre avis a été enregistré avec succès.";
        header("location: espace_passager.php");  // REDIRECTION VERS LA PAGE SUIVANTE 
        exit(); // ARRET DE L'EXECUTION DU SCRIPT apres la redirection
    } 
}
?>
<main id="main-page">
    <!-- FORMULAIRE D'AVIS -->
<div class="container mt-4 views-form col-sm-12 col-md-8 col-lg-6"> 
    <h5 class="text-center creation-title">Partagez votre expérience sur ce covoiturage.</h5> 
    <form action="avis.php" method="post"  id="rating-form" class="mx-4"> 
        <div class="views-group pt-3 row">
            <label for="comment" class="fw-bold text-center">Commentaire</label> 
            <input  class="input-account rounded border border-dark border-2" style="height: 80px" name= "commentaire" type="text" class="form-views" id="comment" placeholder="saisir votre commentaire" > 
        </div> 
        <div class="views-group pt-1"> 
                <label for="star" class="fw-bold">Note du chauffeur(sur 5 étoiles)</label> 
            <div class="container mt-3 text-center bg-light rounded border border-dark border-2">
                <div id="star-rating">
                    <!-- Étoiles -->
                    <span class="star" data-value="1">&#9733;</span>
                    <span class="star" data-value="2">&#9733;</span>
                    <span class="star" data-value="3">&#9733;</span>
                    <span class="star" data-value="4">&#9733;</span>
                    <span class="star" data-value="5">&#9733;</span>
                </div>
                <input type="hidden" id="rating" name="note" value="0">
                   <p id="rating-output" class="mt-2">Note sélectionnée : 0</p>
                </div>
        </div>
        <div class="views-group pt-3"> 
            <label for="status" class="fw-bold">Statut du covoiturage</label> 
            <select  name= "statut" class="form-views" id="status"> 
                <option value="1">En cours</option> 
                <option value="2">Achevé</option> 
                <option value="2">Bien achevé</option> 
                <option value="3">Mal achevé</option>
            </select>
        </div> 
        <div class="views-group pt-3"> 
            <label for="date" class="fw-bold">Date du covoiturage</label> 
            <input class="input-account"  name="date_avis" type="date" class="form-views" id="date"> 
        </div> 
        <center><button type= "submit" class="btn btn-primary btn-block views-btn my-3">ENVOYER</button></center>
    </form> 
</div>
</main>


  <!-- Footer -->      
<?php
  include_once 'footer.php';
?>