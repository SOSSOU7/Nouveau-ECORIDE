<?php
session_start(); // Démarre la session
include_once 'header.php';
?>

<?php   
 if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['account_created'])){
    $utilisateur_id = 1;
    $fumeur = $_POST['fumeur'];
    $animaux_compagnie = $_POST['animaux_compagnie'];
    $sql = "INSERT INTO preferences (utilisateur_id, fumeur, animaux_compagnie) VALUES (?, ?, ?)";
    $req = $conn->prepare($sql);

    if($req->execute([$utilisateur_id, $fumeur, $animaux_compagnie])){
        $_SESSION['notification'] = "Votre compte en tant que chauffeur ou chauffeur/Passager a été créé avec succès.";
        header("location: espace_chauffeur.php");  // REDIRECTION VERS LA PAGE SUIVANTE 
        exit(); // ARRET DE L'EXECUTION DU SCRIPT apres la redirection
    } 
}
?>
<!-- Page principale -->
<main class="main-page">
<div class="container mt-5 account-form " style="background: rgb(237, 215, 15, 0.9); border-radius: 10px; padding: 20px;"> 
    
    <form action="infos3.php" method="post"> 
        <p class="text-center rounded m-2 p-2" style="margin:10px; font-weight:bold; background-color:black; color: white">
            Vous poursuivez votre inscription en tant que Chauffeur ou  Chauffeur/Passager, veuillez enrégistrer 
            vos préférences ci-dessous</p>


        <!--PREFERENCES DU CHAUFFEUR-->
    <div class="col-sm-6 col-md-8  pt-5 d-flex flex-column align-items-center">
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
    <center><button type="submit"  id="liveToastBtn" class="btn btn-primary btn-block account-btn" name="account_created">ENVOYER</button></center>
    </form> 
  </div>
</div>
</main>

<!-- Footer -->
    <?php
include_once 'footer.php';
?>
