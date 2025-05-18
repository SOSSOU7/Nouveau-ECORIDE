<?php
include_once 'header.php';
?>
<main class="main-page">
   


<!--ESPACE ADMINISTRATEUR-->

<div class="container rounded">
    <div class="card shadow"  style=" background-color: rgba(26, 99, 106, 0.83);">
        <div class="card-header text-center">
            <h4 class="user-space text-white"> MON ESPACE ADMINISTRATEUR</h4>
        </div>
        <div class="card-body">
            <div class="row ">
                <div class="col-md-12 d-flex flex-column align-items-center">
                    <img src="img/jd.jpeg" alt="administrateur" class="admin-pic  ">               
                    <h5 class="mt-3  administrator-name text-white fw-bold">Alfredo EL-GONZALEZ</h5>    
                </div>
            </div>
 
<!--CREATION DE COMPTE DES EMPLOYES-->
<h5 class="text-center historique rounded m-2 p-2 " style="background-color:black; color:white;" >CI-DESSOUS CRTEATION DE COMPTE DES EMPLOYERS</h5>
<div class="container col-md-6 rounded align-items-center">
  <form class="form-horizontal px-4 rounded " action="/action_page.php" style="position:center;  background-color: rgb(237, 215, 15, 0.7);">
        <p class="form-title text-center fw-bold">Veuillez remplir les champs suivants</p>
        <div class="form-group">
          <label class="control-label col-sm-2 emp-create-formt" for="worker-name">Nom:</label>
          <div class="col-sm-10">          
              <input type="text" class="form-control" id="worker-name" placeholder=" ex: DUPONT">
          </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2 emp-create-formt" for="worker-firstname">Prénom(s):</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="worker-firstname" placeholder=" ex: Dominique">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2 emp-create-formt" for="worker-desk">Poste à occuper:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="worker-desk" placeholder=" ex: Responsable RH">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2 emp-create-formt" for="worker-mail">Adresse mail:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="worker-mail" placeholder=" ex: dominique@ecoride.fr">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2 emp-create-formt" for="worker-password">Mot de passe:</label>
            <div class="col-sm-10">
              <input type="password" class="form-control" id="worker-password" placeholder=" ex: Dd@2021Ecomoni?fr33#">
            </div>
        </div>
        <center> <button type="submit" class="btn create-emp-account btn btn-primary m-3">CREER</button></center>
        </form>
 </div>

 <!--LISTE DES COMPTES DES EMPLOYES-->
            <h5 class="text-center rounded m-2  p-2" style="background-color:black; color:white;" >LISTE DES COMPTES DES EMPLOYES</h5>
            <div class="list-group user-list col-md-6 col-sm-12">
                <div class="list-group-item user-list " style="background-color: rgb(237, 215, 15, 0.7);">
                    <div class="d-flex flex-column align-items-center  col-sm-12">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <h6 class="fw-bold">Nom et Prénom: MENDI Gaillard</h6>
                        <h6 class="fw-bold">Poste occupé: Comptable</h6>
                        <div>
                            <center> <button type="submit" class="btn btn-danger">SUSPENDRE</button></center>
                        </div>
                    </div>
                </div>
                <div class="list-group-item user-list" style="background-color: rgb(237, 215, 15, 0.7);">
                    <div class="d-flex justify-content-around ">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <h6 class="fw-bold m-4">Nom et Prénom: ROUSSEAUX JeanJacques</h6>
                        <h6 class="fw-bold m-4">Poste occupé: Assistant de direction</h6>
                        <div> 
                            <center> <button type="submit" class="btn btn-danger m-4">SUSPENDRE</button></center>
                        </div>
                    </div>
                </div>
                <div class="list-group-item user-list" style="background-color: rgb(237, 215, 15, 0.7);">
                    <div class="d-flex justify-content-around ">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <h6 class="fw-bold m-4">Nom et Prénom: EIFEL Diane</h6>
                        <h6 class="fw-bold m-4">Poste occupé: Secrétaire</h6>
                        <div> 
                            <center> <button type="submit" class="btn btn-danger m-4">SUSPENDRE</button></center>
                        </div>
                    </div>
                </div>
            </div>
            <h5 class="text-center historique rounded m-2  p-2" style="background-color:black; color:white;" >LISTE DES COMPTES DES UTILISATEURS</h5>
            <div class="list-group user-list">
                <div class="list-group-item"  style="background-color: rgb(237, 215, 15, 0.7);">
                      <div class="d-flex justify-content-around ">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <h6 class="fw-bold m-4">Nom et Prénom: EIFEL Diane</h6>
                        <div> 
                            <center> <button type="submit" class="btn btn-danger m-4">SUSPENDRE</button></center>
                        </div>
                    </div>
                </div>
                
                <div class="list-group-item user-list"  style="background-color: rgb(237, 215, 15, 0.7);">
                    <div class="d-flex justify-content-around ">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <h6 class="fw-bold m-4">Nom et Prénom: EIFEL Diane</h6>
                        <div> 
                            <center> <button type="submit" class="btn btn-danger m-4">SUSPENDRE</button></center>
                        </div>
                    </div>
                </div>
                <div class="list-group-item user-list"  style="background-color: rgb(237, 215, 15, 0.7);">
                     <div class="d-flex justify-content-around ">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <h6 class="fw-bold m-4">Nom et Prénom: EIFEL Diane</h6>
                        <div> 
                            <center> <button type="submit" class="btn btn-danger m-4">SUSPENDRE</button></center>
                        </div>
                    </div>
                </div>
            </div>
        </div>   

<!--GRAPHIQUE DES COVOITURAGES-->

<h5 class="text-center historique rounded m-2  p-2" style="background-color:black; color:white;" >GRAPHIQUE DES COVOITURAGES</h5>
<div class="container col-md-6 rounded align-items-center p-3">
  <form class="form-horizontal px-4 rounded " action="/action_page.php" style="position:center;  background-color: white;">
        <canvas id="myChart"></canvas>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
            labels: ['Jan', 'Fev', 'Mar', 'Apr' , 'Mai' , 'Juin' , 'Juil' , 'Aout' , 'Sept' , 'Oct' , 'Nov' , 'Dec'],
            datasets: [{
                label: 'Covoiturages',
                data: [12.000, 19.000, 33.540, 5.842, 24.110, 31.235, 77.023, 8.240, 99.113, 100.111, 11.003, 122.000],
                backgroundColor: 'rgba(26, 99, 106, 0.83)'
            }]
            }
        });
        </script>
    </form> 
</div>

<!--GRAPHIQUE DES CREDITS GAGNES PAR LA PLATEFORME-->

<h5 class="text-center historique rounded m-2  p-2" style="background-color:black; color:white;" >GRAPHIQUE DES CREDITS GAGNES PAR LA PLATEFORME</h5>
<div class="container col-md-6 rounded align-items-center p-3">
  <form class="form-horizontal px-4 rounded " action="/action_page.php" style="position:center;  background-color: white;">
        <canvas id="myChart1"></canvas>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        var ctx = document.getElementById('myChart1').getContext('2d');
        var myChart1 = new Chart(ctx, {
            type: 'bar',
            data: {
            labels: ['Jan', 'Fev', 'Mar', 'Apr' , 'Mai' , 'Juin' , 'Juil' , 'Aout' , 'Sept' , 'Oct' , 'Nov' , 'Dec'],
            datasets: [{
                label: 'CREDITS',
                data: [12.000, 19.000, 33.540, 5.842, 24.110, 31.235, 77.023, 8.240, 99.113, 100.111, 11.003, 122.000],
                backgroundColor: 'rgba(26, 99, 106, 0.83)'
            }]
            }
        });
        </script>
    </form>
</div>
</div>
</main>

<?php
include_once 'footer.php';
?>
