<?php
session_start();




// --- Configuration de la base de données ---
$db_host = 'localhost'; // Généralement 'localhost'
$db_name = 'ecoride'; // REMPLACEZ PAR LE NOM DE VOTRE BASE DE DONNÉES
$db_user = 'root'; // REMPLACEZ PAR VOTRE NOM D'UTILISATEUR MYSQL
$db_pass = 'root'; // REMPLACEZ PAR VOTRE MOT DE PASSE MYSQL

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Récupère les résultats sous forme de tableau associatif par défaut
    // echo "Connexion à la base de données réussie!"; // Ligne de débogage, à commenter ou supprimer en production
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// --- Définition des constantes de rôle ---
// Assurez-vous que ces ID correspondent à ceux de votre table 'role'
if (!defined('ID_ROLE_ADMIN')) {
    define('ID_ROLE_ADMIN', 1); // ID pour le rôle 'administrateur'
}
if (!defined('ID_ROLE_PASSAGER')) {
    define('ID_ROLE_PASSAGER', 2); // ID pour le rôle 'passager' (si applicable)
}
if (!defined('ID_ROLE_EMPLOYE')) {
    define('ID_ROLE_EMPLOYE', 3); // ID pour le rôle 'employe'
}
if (!defined('ID_ROLE_CHAUFFEUR')) {
    define('ID_ROLE_CHAUFFEUR', 4); // ID pour le rôle 'chauffeur' (si applicable)
}
if (!defined('ID_ROLE_CHAUFFEUR_PASSAGER')) {
    define('ID_ROLE_CHAUFFEUR_PASSAGER', 5); // ID pour le rôle 'chauffeur_passager' (si applicable)
}

// --- Gestion des sessions ---
// S'assure que la session est démarrée une seule fois et au tout début
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Optionnel: Gestion de l'inactivité de session (décommentez pour activer)
/*
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) { // 3600 secondes = 1 heure
    session_unset();
    session_destroy();
    header("Location: connexion.php?session_expiree=true");
    exit();
}
$_SESSION['last_activity'] = time();
*/

// --- Fonction de redirection sécurisée ---
function redirect_to($location) {
    header("Location: " . $location);
    exit();
}
