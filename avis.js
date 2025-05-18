const stars = document.querySelectorAll('.star');
const ratingOutput = document.getElementById('rating-output');
// Champ caché pour envoyer la note
const ratingInput = document.getElementById('rating'); 

stars.forEach(star => {
    star.addEventListener('click', () => {
        const rating = star.getAttribute('data-value'); // Récupérer la valeur de l'étoile cliquée

        // Réinitialiser les sélections
        stars.forEach(s => s.classList.remove('selected'));

        // Marquer les étoiles jusqu'à celle cliquée
        for (let i = 0; i < rating; i++) {
            stars[i].classList.add('selected');
        }

        // Mettre à jour le champ caché et l'affichage
        ratingInput.value = rating;
        ratingOutput.textContent = `Note sélectionnée : ${rating}`;
    });
});