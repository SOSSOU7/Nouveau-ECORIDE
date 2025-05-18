<?php  
session_start();  // On démarre la session
session_unset();  // On efface toutes les variables de session
session_destroy(); // On détruit la session
header("Location: index.php");
exit();
?>