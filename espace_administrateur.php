<?php
// espace_administrateur.php

// 1. Inclure auth.php EN PREMIER pour démarrer la session et définir les constantes de rôle
require_once 'auth.php';

// 2. Vérifier si l'utilisateur est connecté et a le rôle d'administrateur
// CORRECTION ICI : Utilisation de $_SESSION['role_id'] au lieu de $_SESSION['utilisateur_role_id']
if (!isset($_SESSION['utilisateur_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != ID_ROLE_ADMIN) {
    $_SESSION['error_notification'] = "Accès non autorisé. Vous devez être administrateur pour accéder à cet espace.";
    redirect_to("signin.php"); // Redirection vers la page de connexion
}

// 3. Inclure le header APRÈS la vérification de redirection
require_once 'header.php';

$total_credits_gagnes = 0;
$liste_employes = [];
$liste_utilisateurs = []; // Pour la suspension de comptes

try {
    // 1. Nombre total de crédits gagnés par la plateforme
    // Cela correspond au total des prix des participations payées en crédits.
    // Cette requête suppose que `prix_personne` est dans `covoiturage`
    // et que chaque participation implique un paiement de `prix_personne` crédit.
    $stmtCredits = $conn->query("
        SELECT SUM(c.prix_personne) AS total_credits
        FROM participation p
        JOIN covoiturage c ON p.covoiturage_id = c.id
        -- WHERE p.statut_paiement = 'paye_credits' -- Décommentez si vous avez un statut de paiement explicite pour les crédits
    ");
    $resultCredits = $stmtCredits->fetch(PDO::FETCH_ASSOC);
    $total_credits_gagnes = $resultCredits['total_credits'] ?? 0; // Utilisation de l'opérateur null coalesce pour une valeur par défaut

    // 2. Récupérer les employés (pour la création et la suspension)
    $stmtEmployes = $conn->prepare("SELECT id, pseudo, email, statut_compte FROM utilisateur WHERE role_id = :role_employe ORDER BY pseudo ASC");
    $stmtEmployes->execute([':role_employe' => ID_ROLE_EMPLOYE]);
    $liste_employes = $stmtEmployes->fetchAll(PDO::FETCH_ASSOC);

    // 3. Récupérer les utilisateurs (pour la suspension)
    // Exclure les administrateurs et les employés ici pour la gestion des "utilisateurs standards"
    $stmtUtilisateurs = $conn->prepare("SELECT id, pseudo, email, statut_compte FROM utilisateur WHERE role_id NOT IN (:role_admin, :role_employe) ORDER BY pseudo ASC");
    $stmtUtilisateurs->execute([':role_admin' => ID_ROLE_ADMIN, ':role_employe' => ID_ROLE_EMPLOYE]);
    $liste_utilisateurs = $stmtUtilisateurs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error_notification'] = "Erreur lors du chargement des données de l'administrateur : " . $e->getMessage();
    error_log("Error loading admin dashboard data (espace_administrateur.php): " . $e->getMessage());
    // Pas de redirection ici pour permettre à l'utilisateur de voir l'erreur sur la page
    // Mais on pourrait décommenter si on veut une redirection plus stricte :
    // redirect_to("index.php"); // Rediriger en cas d'erreur de chargement
}

// Traitement de la création d'employé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer_employe'])) {
    // Nettoyage et validation des entrées
    $pseudo = trim($_POST['pseudo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? ''; // N'utilisez pas trim() pour les mots de passe avant hachage

    if (empty($pseudo) || empty($email) || empty($mot_de_passe)) {
        $_SESSION['error_notification'] = "Tous les champs sont requis pour créer un employé.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_notification'] = "Format d'email invalide.";
    } else {
        try {
            // Vérifier si l'email ou le pseudo existe déjà
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email OR pseudo = :pseudo");
            $stmtCheck->execute([':email' => $email, ':pseudo' => $pseudo]);
            if ($stmtCheck->fetchColumn() > 0) {
                $_SESSION['error_notification'] = "Un utilisateur avec cet email ou ce pseudo existe déjà.";
            } else {
                $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                // Préparer l'insertion
                $stmtInsert = $conn->prepare("INSERT INTO utilisateur (pseudo, email, mot_de_passe, role_id, statut_compte, code_postal, description) VALUES (:pseudo, :email, :mot_de_passe, :role_id, 'actif', :code_postal_default, :description_default)");
                
                // Exécution de la requête avec les valeurs par défaut pour code_postal et description
                if ($stmtInsert->execute([
                    ':pseudo' => $pseudo,
                    ':email' => $email,
                    ':mot_de_passe' => $mot_de_passe_hache,
                    ':role_id' => ID_ROLE_EMPLOYE,
                    ':code_postal_default' => '00000', // Valeur par défaut
                    ':description_default' => 'Compte employé créé par l\'administrateur.' // Valeur par défaut
                ])) {
                    $_SESSION['notification'] = "Le compte employé '" . htmlspecialchars($pseudo) . "' a été créé avec succès.";
                } else {
                    $_SESSION['error_notification'] = "Erreur lors de la création du compte employé.";
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error_notification'] = "Erreur BD lors de la création de l'employé : " . $e->getMessage();
            error_log("Error creating employee (espace_administrateur.php): " . $e->getMessage());
        }
    }
    redirect_to("espace_administrateur.php"); // Toujours rediriger après un POST pour éviter la soumission multiple
}

// Traitement de la suspension/réactivation de compte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_compte'])) {
    $user_id = $_POST['user_id'] ?? null;
    $action = $_POST['action_compte'] ?? null; // 'suspendre' ou 'activer'

    if ($user_id && ($action == 'suspendre' || $action == 'activer')) {
        try {
            // Sécurité : Ne pas permettre à un admin de se suspendre lui-même
            if ($user_id == $_SESSION['utilisateur_id'] && $action == 'suspendre') {
                $_SESSION['error_notification'] = "Vous ne pouvez pas suspendre votre propre compte administrateur.";
                redirect_to("espace_administrateur.php");
            }

            // Vérifier que l'utilisateur existe et n'est PAS un autre administrateur
            $stmtGetUserRole = $conn->prepare("SELECT role_id FROM utilisateur WHERE id = :user_id");
            $stmtGetUserRole->execute([':user_id' => $user_id]);
            $user_role_data = $stmtGetUserRole->fetch(PDO::FETCH_ASSOC);

            if ($user_role_data && $user_role_data['role_id'] != ID_ROLE_ADMIN) { // Empêcher la suspension/activation d'autres administrateurs
                $new_statut = ($action == 'suspendre') ? 'suspendu' : 'actif';
                $stmtUpdateStatut = $conn->prepare("UPDATE utilisateur SET statut_compte = :statut WHERE id = :user_id");
                if ($stmtUpdateStatut->execute([':statut' => $new_statut, ':user_id' => $user_id])) {
                    $_SESSION['notification'] = "Le compte a été " . ($action == 'suspendre' ? "suspendu" : "réactivé") . " avec succès.";
                } else {
                    $_SESSION['error_notification'] = "Erreur lors de la mise à jour du statut du compte.";
                }
            } else {
                $_SESSION['error_notification'] = "Utilisateur introuvable ou vous n'êtes pas autorisé à modifier ce compte (ce pourrait être un autre administrateur).";
            }
        } catch (PDOException $e) {
            $_SESSION['error_notification'] = "Erreur BD lors de la gestion du compte : " . $e->getMessage();
            error_log("Error managing user account status (espace_administrateur.php): " . $e->getMessage());
        }
    } else {
        $_SESSION['error_notification'] = "Action ou identifiant d'utilisateur invalide.";
    }
    redirect_to("espace_administrateur.php"); // Toujours rediriger après un POST
}
?>

<main class="main-page">
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-10">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Espace Administrateur</h2>

                    <?php if (isset($_SESSION['notification'])): ?>
                        <div class="alert alert-success text-center"><?= htmlspecialchars($_SESSION['notification']); unset($_SESSION['notification']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error_notification'])): ?>
                        <div class="alert alert-danger text-center"><?= htmlspecialchars($_SESSION['error_notification']); unset($_SESSION['error_notification']); ?></div>
                    <?php endif; ?>

                    <div class="row text-center mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h3>Total des crédits gagnés par la plateforme : <br> **<?= number_format($total_credits_gagnes, 0, ',', ' ') ?> Crédits**</h3>
                                <p class="text-muted small">_Cette valeur représente la somme des crédits collectés via les participations aux covoiturages._</p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h3 class="text-center mb-3">Créer un compte Employé</h3>
                    <div class="card p-3 mb-4">
                        <form action="espace_administrateur.php" method="POST">
                            <div class="mb-3">
                                <label for="pseudo_employe" class="form-label">Pseudo :</label>
                                <input type="text" class="form-control" id="pseudo_employe" name="pseudo" required>
                            </div>
                            <div class="mb-3">
                                <label for="email_employe" class="form-label">Email :</label>
                                <input type="email" class="form-control" id="email_employe" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="mdp_employe" class="form-label">Mot de passe :</label>
                                <input type="password" class="form-control" id="mdp_employe" name="mot_de_passe" required>
                            </div>
                            <button type="submit" name="creer_employe" class="btn btn-primary w-100">Créer l'employé</button>
                        </form>
                    </div>

                    <hr class="my-4">

                    <h3 class="text-center mb-3">Gestion des comptes (Employés & Utilisateurs)</h3>
                    <div class="accordion" id="accordionGestionComptes">
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header" id="headingEmployes">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmployes" aria-expanded="false" aria-controls="collapseEmployes">
                                    Gérer les comptes Employés (<?= count($liste_employes) ?>)
                                </button>
                            </h2>
                            <div id="collapseEmployes" class="accordion-collapse collapse" aria-labelledby="headingEmployes" data-bs-parent="#accordionGestionComptes">
                                <div class="accordion-body">
                                    <?php if (empty($liste_employes)): ?>
                                        <div class="alert alert-info text-center">Aucun employé enregistré.</div>
                                    <?php else: ?>
                                        <ul class="list-group">
                                            <?php foreach ($liste_employes as $employe): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?= htmlspecialchars($employe['pseudo']) ?></strong> (<?= htmlspecialchars($employe['email']) ?>)
                                                        <span class="ms-2 badge <?= ($employe['statut_compte'] == 'actif') ? 'bg-success' : 'bg-danger'; ?>">
                                                            <?= htmlspecialchars(ucfirst($employe['statut_compte'])) ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <form action="espace_administrateur.php" method="POST" class="d-inline-block">
                                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($employe['id']) ?>">
                                                            <?php if ($employe['statut_compte'] == 'actif'): ?>
                                                                <button type="submit" name="action_compte" value="suspendre" class="btn btn-warning btn-sm">Suspendre</button>
                                                            <?php else: ?>
                                                                <button type="submit" name="action_compte" value="activer" class="btn btn-success btn-sm">Activer</button>
                                                            <?php endif; ?>
                                                        </form>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingUtilisateurs">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUtilisateurs" aria-expanded="false" aria-controls="collapseUtilisateurs">
                                    Gérer les comptes Utilisateurs (Autres rôles) (<?= count($liste_utilisateurs) ?>)
                                </button>
                            </h2>
                            <div id="collapseUtilisateurs" class="accordion-collapse collapse" aria-labelledby="headingUtilisateurs" data-bs-parent="#accordionGestionComptes">
                                <div class="accordion-body">
                                    <?php if (empty($liste_utilisateurs)): ?>
                                        <div class="alert alert-info text-center">Aucun autre utilisateur enregistré.</div>
                                    <?php else: ?>
                                        <ul class="list-group">
                                            <?php foreach ($liste_utilisateurs as $user): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?= htmlspecialchars($user['pseudo']) ?></strong> (<?= htmlspecialchars($user['email']) ?>)
                                                        <span class="ms-2 badge <?= ($user['statut_compte'] == 'actif') ? 'bg-success' : 'bg-danger'; ?>">
                                                            <?= htmlspecialchars(ucfirst($user['statut_compte'])) ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <form action="espace_administrateur.php" method="POST" class="d-inline-block">
                                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                                            <?php if ($user['statut_compte'] == 'actif'): ?>
                                                                <button type="submit" name="action_compte" value="suspendre" class="btn btn-warning btn-sm">Suspendre</button>
                                                            <?php else: ?>
                                                                <button type="submit" name="action_compte" value="activer" class="btn btn-success btn-sm">Activer</button>
                                                            <?php endif; ?>
                                                        </form>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h3 class="text-center mb-3">Statistiques de la plateforme</h3>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card p-3">
                                <h5 class="text-center">Nombre de covoiturages par jour</h5>
                                <canvas id="covoituragesParJourChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card p-3">
                                <h5 class="text-center">Crédits gagnés par jour</h5>
                                <canvas id="creditsGagnesParJourChart"></canvas>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Fonction pour charger les données des graphiques via AJAX
    async function loadAdminStats() {
        try {
            const response = await fetch('get_admin_stats.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            // Graphique de covoiturages par jour
            const ctxCovoiturages = document.getElementById('covoituragesParJourChart').getContext('2d');
            new Chart(ctxCovoiturages, {
                type: 'bar',
                data: {
                    labels: data.covoiturages.labels,
                    datasets: [{
                        label: 'Nombre de covoiturages',
                        data: data.covoiturages.data,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nombre de covoiturages'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });

            // Graphique de crédits gagnés par jour
            const ctxCredits = document.getElementById('creditsGagnesParJourChart').getContext('2d');
            new Chart(ctxCredits, {
                type: 'line',
                data: {
                    labels: data.credits.labels,
                    datasets: [{
                        label: 'Crédits gagnés',
                        data: data.credits.data,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        fill: false // Pour un graphique en ligne sans remplissage
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Crédits gagnés'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('Erreur lors du chargement des statistiques:', error);
            // Afficher un message d'erreur à l'utilisateur si les graphiques ne se chargent pas
            const statsContainer = document.getElementById('covoituragesParJourChart').closest('.row');
            if (statsContainer) {
                statsContainer.innerHTML = '<div class="col-12"><div class="alert alert-warning text-center" role="alert">Impossible de charger les statistiques. Veuillez réessayer plus tard.</div></div>';
            }
        }
    }

    // Charger les statistiques au chargement de la page
    document.addEventListener('DOMContentLoaded', loadAdminStats);
</script>

<?php include_once 'footer.php'; ?>