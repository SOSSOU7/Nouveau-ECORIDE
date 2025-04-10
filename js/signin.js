let email = document.getElementById('email');
let password = document.getElementById('password');
let confirmPassword = document.getElementById('confirmPassword');

// Ajout d'un gestionnaire d'événements pour chaque champ
email.addEventListener("keyup", validateForm);
password.addEventListener("keyup", validateForm);
confirmPassword.addEventListener("keyup", validateForm);

function validateForm() {
    validateRequired(email);
    validateRequired(password);
    validateRequired(confirmPassword);

    // Vérifier si les deux mots de passe correspondent
    validatePasswordsMatch(password, confirmPassword);
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

function validatePasswordsMatch(password, confirmPassword) {
    if (password.value !== '' && confirmPassword.value !== '' && password.value === confirmPassword.value) {
        confirmPassword.classList.add("is-valid");
        confirmPassword.classList.remove("is-invalid");
    } else {
        confirmPassword.classList.remove("is-valid");
        confirmPassword.classList.add("is-invalid");
        // alert ('mot de passe non identique');
    }
}


// METHODE DE CHRISTIAN
// let loginForm = document.getElementById('loginForm');
// let email = document.getElementById('email');
// let password = document.getElementById('password');
// let confirmPassword = document.getElementById('confirmPassword');
// // let submitButton = document.getElementById('submitButton');

// loginForm.addEventListener('submit', function(event) {
//     event.preventDefault();
//     if(email.value.trim() === '' && password.value.trim() === '' && confirmPassword.value.trim() ===''){
//         email.style.border = '2px solid red';
//         password.style.border = '2px solid red';
//         confirmPassword.style.border = '2px solid red';
//         return;
//     }
//     else if(email.value !== '' && password.value !== '' && confirmPassword.value !== ''){
//         email.style.border = '2px solid green';
//         password.style.border= '2px solid green';
//         confirmPassword.style.border = '2px solid green';
//         // alert('Merci pour votre message, nous vous recontacterons dès que possible.');
//         loginForm.reset();
//     }
// });
