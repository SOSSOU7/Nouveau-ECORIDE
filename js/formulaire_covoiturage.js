let adress1 = document.getElementById('adress1');
let adress2 = document.getElementById('adress2');
let date = document.getElementById('date');
let covoitForm = document.getElementById('covoitForm');

// Ajout d'un gestionnaire d'événements pour chaque champ
adress1.addEventListener("keyup", validateForm);
adress2.addEventListener("keyup", validateForm);
date.addEventListener("keyup", validateForm);
covoitForm.addEventListener("submit", validateForm);

// Fonction de validation du formulaire
function validateForm(event) {
    event.preventDefault(); // Empêche l'envoi du formulaire pour la démonstration
    validateRequired(adress1);
    validateRequired(adress2);
    validateRequired(date);
}
function validateRequired(field) {
    if (field.value !== '') {
        field.classList.add("is-valid");
        field.classList.remove("is-invalid");
        
    } else {
        field.classList.remove("is-valid");
        field.classList.add("is-invalid");
    }
}


