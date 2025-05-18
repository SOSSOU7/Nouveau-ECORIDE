<?php
include_once 'db.php';
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ACCEUIL</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="SCSS/main.css">
    <link rel="stylesheet" href="SCSS/main.scss">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Madurai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  </head>
  <body style="background-image: url('img/gente.png'); background-size: cover; background-repeat: no-repeat; background-position: center; min-height: 100vh;">
  <!--style="background-image: url('img/gente.png'); background-size: cover; background-repeat: no-repeat; background-position: center; min-height: 100vh;"-->
<header> 
    <nav class="navbar navbar-expand-lg bg-dark py-4"  data-bs-theme="dark">
        <div class="container-fluid">
            <!--RETOUR A LA PAGE D'ACCEUIL-->
            <img src="img/OIP 1.jpg" alt="logo" class="logo me-3" style="width: 80px; height: 50px; border-radius: 20px;">
            <a class="navbar-brand me-3 fw-bold" href="/">EcoRide</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <form class="d-flex justify-content-center" role="search">
                <input class="form-control me-2 fst-italic" type="search" placeholder="trouver un itinéraire" aria-label="Search">
                <button class="btn btn-outline-success" type="submit">Chercher</button>
            </form>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <!--RETOUR A LA PAGE D'ACCEUIL-->
                <a class="nav-link active fw-bold" href="/">ACCEUIL</a>
                </li>
                <li class="nav-item">
                <a class="nav-link fw-bold"  href="formulaire_covoiturage.php">ACCES AUX COVOITURAGES</a>
                </li>
                <li class="nav-item">
                <a class="nav-link fw-bold" href="#">CONTACTS</a>
                </li>
                <li class="nav-item">
                <a class="nav-link fw-bold" href="#" aria-disabled="true">CONNEXION/DECONNEXION</a>
                </li>
            </ul>
            </div>
        </div>
        </nav>
  </header>
  
  <div class="col-md-6 justify-content-start">
        <h1>EcoRide</h1>
        <p class="fw-bold"  id="p1">Roulez vert, partagez mieux et économisez dès votre inscription !</p>
    </div>