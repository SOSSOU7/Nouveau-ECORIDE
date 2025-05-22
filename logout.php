<?php
session_start();

// Supprimer toutes les données de session
$_SESSION = [];
session_destroy();

// Supprimer le cookie s’il existe (ex : remember me)
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

header("Location: signin.php"); // Redirection vers la page de connexion
exit();
