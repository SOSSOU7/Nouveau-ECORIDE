<?php
include_once 'header.php';
?>


<div class="container " style="background: rgb(237, 215, 15, 0.9); border-radius: 10px; padding: 20px; margin-top: 20px;">
    <h4 class="text-center creation-title m-4">Je m'inscris en tant que:</h4>
<form>
  <div class=" text-center p-1 m-2 infos1-size" >
    <label for="username">Passager:</label>
    <input type="radio" id="username"  name="contact" value="email" />
    <label for="username">Chauffeur:</label>
    <input type="radio" id="username"  name="contact" value="telephone" />
    <label for="username">Chauffeur/Passager</label>
    <input type="radio" id="username" name="contact" value="courrier" />
  </div>
  <div>
  <center><a href="infos2.php" class="btn btn-primary btn-block account-btn">ENVOYER</a></center>
  </div>
</form>
</div>

<?php
include_once 'footer.php';
?>
