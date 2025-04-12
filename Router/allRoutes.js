import Route from "route.js";

//Définir ici vos routes
export const allRoutes = [
    new Route("/", "Accueil", "/pages/home.html"),
    new Route("/Formulaire", "Formulaire", "/pages/form.php"),];

//Le titre s'affiche comme ceci : Route.titre - websitename
export const websiteName = "EcoRide";