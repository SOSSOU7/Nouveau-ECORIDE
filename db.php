<?php
// CONNEXION A LA BASE DE DONNEES
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "ecoride";


try{  
$conn =new PDO("mysql:host=$servername; dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

catch(PDOException $e){
echo"Erreur de connexion". $e->getMessage();
}
?>