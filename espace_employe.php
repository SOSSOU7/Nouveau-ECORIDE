<?php
include_once 'header.php';
?>

<main id="main-page">
    <!--GESTION DES AVIS-->
    <div class="container ">
<div class="container views-section">
    <div class="card shadow" style=" background-color: rgba(26, 99, 106, 0.83);">
        <div class="card-header text-center">
            <h4 class="employee-title text-white fw-bold">MON ESPACE EMPLOYE</h4>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-12 d-flex flex-column align-items-center">
                    <img src="img/OIPh.jpeg" alt="Photo d'une femme employée" class="profile-pic employee-photo">
                    <h5 class="mt-2 employee-name text-white fw-bold">Antoinette DOUGLAS</h5> 
                </div>
            </div>
        </div>
        <h5 class="text-center rounded m-2  p-2" style="background-color:black; color:white;" >GESTION DES AVIS UTILISATEURS</h5>
            <div class="list-group views-judgement">
                <div class="list-group-item"  style="background-color: rgb(237, 215, 15, 0.7);">
                    <div class="d-flex justify-content-center">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <div>
                            <h4>Suzie CASTORAMA</h4>
                                <p class="mb-0">Super voyage</p>
                                <p class="text-muted small">Une expérience inoubliable, merci beaucoup !</p>
                        </div>
                    </div>
                    <div class="validate-btn d-flex justify-content-around">
                        <button class="btn views-btn  validate-btn btn-primary">VALIDER</button>
                        <button class="btn  views-btn  reject-btn btn-danger">REFUSER</button>
                    </div>
                </div>
                <div class="list-group-item rounded"  style="background-color: rgb(237, 215, 15, 0.7);">
                    <div class="d-flex  justify-content-center"">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <div>
                            <h4>Suzie CASTORAMA</h4>
                            <p class="mb-0">Désagréable</p>
                            <p class="text-muted small">Bavard et curieux sur ma vie.</p>
                        </div>
                    </div>
                    <div class="validate-btn d-flex justify-content-around">
                        <button class="btn views-btn  validate-btn  btn-primary">VALIDER</button>
                        <button class="btn  views-btn  reject-btn btn-danger">REFUSER</button>
                    </div>
                </div>
                <div class="list-group-item"  style="background-color: rgb(237, 215, 15, 0.7);">
                    <div class="d-flex justify-content-center">
                        <img src="img/oipg.jpg" alt="Avatar" class="profile-pic me-3">
                        <div>
                            <h4>Suzie CASTORAMA</h4>
                            <p class="mb-0">Nice travel</p>
                            <p class="text-muted small">Super expérience, je recommande vivement !</p>
                        </div>
                    </div>
                    <div class="validate-btn d-flex justify-content-around">
                        <button class="btn views-btn  validate-btn  btn-primary">VALIDER</button>
                        <button class="btn  views-btn  reject-btn btn-danger">REFUSER</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--VISIONAGE DES COVOITURAGES MAL PASSES-->

<div class="container bad-covoit">
    <div class="card shadow"  style=" background-color: rgba(26, 99, 106, 0.83);">
        <div class="card-header">
         
        <h5 class="text-center rounded m-2  p-2" style="background-color:black; color:white;" >VISONAGE DES COVOITURAGES MAL PASSES</h5>

        <form class="view-covoit rounded "  style="background-color: rgb(237, 215, 15, 0.7);" method="post" action="employe.php">
            <div class=" list-group-covoit d-flex flex-column align-items-center">
                <label for="bad-covoit" class="covoit-font"> Numero du covoiturage:</label> 
                <input type="text" class=" covoit-input" id="bad-covoit" placeholder="Entrez le numero du covoiturage" >
                <br>
                <label for="date1" class="covoit-font">Date de départ:</label>
                <input type="date" class=" covoit-input" id="date1" placeholder="Entrez la date de départ" >
                <br>
                <label for="date2" class="covoit-font">Date d'arrivée:</label>
                <input type="date" class=" covoit-input" id="date2" placeholder="Entrez la date de départ" >
                <br>
                <label for="text" class="covoit-font">Adresse de départ:</label>
                <input type="text" class="covoit-input" id="adress" placeholder="ex: 6 rue de la paix 102000 saint christophe" >
                <br>
                <label for="text" class="covoit-font">Adresse d'arrivée:</label>
                <input type="text" class="covoit-input covoit-font" id="date3" placeholder="12 rue de la force 102000 montréal">
            </div>

            <h5 class="text-center m-2  p-2 fw-bold" style="background-color:black; color:white;">PSEUDO ET E-MAIL DU CHAUFFEUR</h5>
                <div class="d  m-3  p-1 rounded d-flex justify-content-around">
                    <div class="driver-pseudo covoit-input" >
                        <label for="bad-covoit" class="covoit-font">Pseudo:</label> 
                        <input type="text" class="rounded" id="bad-covoit" placeholder="John" >
                        <label for="mail">Adresse mail:</label> 
                        <input type="text" class="rounded" id="mail" placeholder="ex: john@gmail.com">
                    </div>
                </div>
                <h5 class="text-center m-3  p-1 fw-bold" style="background-color:black; color:white;"> PSEUDO ET E-MAIL DU PASSAGER</h5>
                <div class="d  m-2  p-2 rounded d-flex justify-content-around">
                    <div class="passenger-pseudo covoit-input m-2  p-2">
                        <label for="bad-covoit" class="covoit-font">Pseudo:</label> 
                        <input type="text" class="rounded" id="bad-covoit" placeholder="Diego" >
                        <label for="mail" class="covoit-font">Adresse mail:</label> 
                        <input type="text" class="rounded" id="mail" placeholder="ex: diego@gmail.com">
                    </div>
                 </div>
            </div>
                <CENTER> <button class="btn  btn-primary m-4">VALIDER</button></CENTER>  
        </form>
        </div>
    </div>
</div>
</main>

  <!-- Footer -->      
<?php
  include_once 'footer.php';
?>