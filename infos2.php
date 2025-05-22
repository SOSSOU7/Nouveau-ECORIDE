<?php
include_once 'header.php';
?>

<?php   
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_car'])){
    $utilisateur_id = 1;
    $marque = $_POST['marque'];
    $modele = $_POST['modele'];
    $immatriculation = $_POST['immatriculation'];
    $energie = $_POST['energie'];
    $couleur = $_POST['couleur'];
    $date_premiere_immatriculation = $_POST['date_premiere_immatriculation'];
    $places_dispo = $_POST['places_dispo'];

    $sql = "INSERT INTO voiture (utilisateur_id, marque, modele, immatriculation, energie, couleur, date_premiere_immatriculation, places_dispo) VALUES (?, ?, ?, ?, ?, ?, ?,?)";
    $req = $conn->prepare($sql);

    if($req->execute([$utilisateur_id, $marque, $modele, $immatriculation, $energie, $couleur, $date_premiere_immatriculation, $places_dispo])){
        header("location: infos3.php");  // REDIRECTION VERS LA PAGE SUIVANTE 
        exit(); // ARRET DE L'EXECUTION DU SCRIPT apres la redirection
    } 
}  
?>
<main>
<div class="container account-form mb-3" style="background: rgb(237, 215, 15, 0.9); border-radius: 10px; padding: 20px; margin-top: 20px;">
    <p class="text-center creation-title fw-bold">Vous poursuivez votre inscription en tant que Chauffeur ou  Chauffeur/Passager, veuillez enrégistrer votre voiture ci-dessous</p>
    
    <!-- Formulaire d'inscription -->
<form class="form-horizontal mb-3" action="infos2.php" method="post">
  <div class="form-group">
    <label class="control-label col-sm-2 fw-bold" for="car1">Marque de votre voiture:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  name="marque" id="carMark" placeholder="ex: TESLA">
    </div>
  </div>

  <div class="form-group">
    <label class="control-label col-sm-2 fw-bold" for="model1">Modèle de la voiture:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control"   name="modele" id="carModel" placeholder="ex: Model S">
    </div>
  </div>
  <div class="form-group">
    <label class="control-label col-sm-2 fw-bold" for="immatriculation">N° Plaque d'immatriculation</label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  name="immatriculation" id="immatriculation" placeholder="ex: XX-XXX-XX">
    </div>
  </div>
  <div class="form-group">
    <label class="control-label col-sm-2 fw-bold" for="carColor">Date de première immatriculation</label>
    <div class="col-sm-10">
      <input type="date"  name="date_premiere_immatriculation" class="form-control" id="immatriculation" placeholder="ex: 12/12/2021">
    </div>
  </div>
   <div class="form-group">
    <label class="control-label col-sm-2 fw-bold" for="carColor">Couleur</label>
    <div class="col-sm-10">
      <input  type="text" name="couleur" class="form-control" id="carColor" placeholder="ex: Noir">
    </div>
  </div>
  <div class="form-group">
    <label class="control-label col-sm-2 fw-bold" for="carColor">Places disponibles</label>
    <div class="col-sm-10">
      <input type="number" name="places_dispo" class="form-control" id="availablePlace" placeholder="ex: 4">
    </div>
  </div> 
  <div class="form-group">
    <label class="control-label col-sm-2 fw-bold"for="availablePlace">Energie</label>
    <div class="col-sm-10">
      <select name="energie" id= "carEnergy" class="form-select">
        <option value="essence">Thermique</option>
        <option value="electrique">Electrique</option>
      </select>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
    <center><button type="submit" name="register_car" class="btn btn-primary m-4">ENVOYER</button></center>
    </div>
  </div>
</form>
</div>
</main>

<!-- Footer -->
    <?php
include_once 'footer.php';
?>
