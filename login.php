<?php
session_start();
require_once 'db.php';

$email = '';
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['password'] ?? '';

    if ($email === '') {
        $erreurs['email'] = 'L\'email est obligatoire.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = 'Format d\'email invalide.';
    }

    if ($mot_de_passe === '') {
        $erreurs['mot_de_passe'] = 'Le mot de passe est obligatoire.';
    }

    if (empty($erreurs)) {
        $requete = $pdo->prepare(
            'SELECT id, email, password_hash, role FROM users WHERE email = ?'
        );
        $requete->execute([$email]);
        $utilisateur = $requete->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur && password_verify($mot_de_passe, $utilisateur['password_hash'])) {
            $_SESSION['user_id'] = $utilisateur['id'];
            $_SESSION['role']    = $utilisateur['role'];
            $_SESSION['toast'] = ['message' => 'Connexion réussie ! Heureux de vous revoir.', 'type' => 'success'];
            header('Location: dashboard.php');
            exit;
        } else {
            $erreurs['global'] = 'Email ou mot de passe incorrect.';
        }
    }
}

require_once 'includes/header.php';
?>

<div style="
    max-width: 460px;
    margin: 3rem auto;
    background: rgba(26, 43, 74, 0.6);
    border: 1px solid rgba(108, 171, 221, 0.2);
    border-radius: 16px;
    padding: 2.5rem;
    backdrop-filter: blur(10px);">

    <h1 class="titre-page" style="font-size:1.6rem; margin-bottom:0.3rem;">
        Connexion
    </h1>
    <p class="sous-titre-page">Manchester City</p>

    <?php if (!empty($erreurs['global'])): ?>
        <div class="erreur-globale">
            <?php echo htmlspecialchars($erreurs['global'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="post" class="formulaire-groupe">

        <div class="formulaire-champ">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                placeholder="votre@email.com"
                value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
            <?php if (!empty($erreurs['email'])): ?>
                <span class="erreur-champ">
                    <?php echo htmlspecialchars($erreurs['email'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="formulaire-champ">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password"
                placeholder="••••••••">
            <?php if (!empty($erreurs['mot_de_passe'])): ?>
                <span class="erreur-champ">
                    <?php echo htmlspecialchars($erreurs['mot_de_passe'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
            <?php endif; ?>
        </div>

        <button type="submit" class="bouton bouton-principal" style="width:100%; margin-top:0.5rem;">
            Se connecter
        </button>

    </form>

    <p style="text-align:center; margin-top:1.5rem; color:var(--gris-fonce); font-size:0.9rem;">
        Pas encore de compte ?
        <a href="register.php" style="color:var(--bleu-city); text-decoration:none; font-weight:600;">
            S'inscrire
        </a>
    </p>

    <div style="display: flex; align-items: center; margin: 2rem 0 1.5rem;">
        <hr style="flex: 1; border: none; border-top: 1px solid rgba(108, 171, 221, 0.2);">
        <span style="padding: 0 1rem; color: var(--gris-fonce); font-size: 0.85rem;">ou continuer avec</span>
        <hr style="flex: 1; border: none; border-top: 1px solid rgba(108, 171, 221, 0.2);">
    </div>

    <div style="display: flex; gap: 1rem; flex-direction: column;">
        <a href="login_google.php" onclick="openGooglePopup(); return false;" class="bouton" style="background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(108, 171, 221, 0.3); color: white; display: flex; align-items: center; justify-content: center; gap: 0.75rem; text-decoration: none; transition: background 0.2s;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="#EA4335" d="M23.49 12.275c0-.814-.067-1.615-.199-2.392H12v4.614h6.582a5.578 5.578 0 01-2.42 3.65v3.012h3.911c2.288-2.107 3.607-5.21 3.607-8.884z"/><path fill="#4285F4" d="M12 24c3.24 0 5.955-1.08 7.94-2.914l-3.911-3.013c-1.076.721-2.454 1.147-4.029 1.147-3.097 0-5.717-2.094-6.65-4.918H1.306v3.136C3.291 21.393 7.33 24 12 24z"/><path fill="#FBBC05" d="M5.35 14.298a7.18 7.18 0 010-4.596V6.566H1.306a11.967 11.967 0 000 10.868l4.044-3.136z"/><path fill="#34A853" d="M12 4.783c1.762 0 3.344.607 4.59 1.791l3.447-3.447C17.95 1.192 15.236 0 12 0 7.33 0 3.291 2.607 1.306 6.566l4.044 3.136c.933-2.824 3.553-4.919 6.65-4.919z"/></svg>
            Google
        </a>
    </div>

</div>

<script>
function openGooglePopup() {
    const width = 500;
    const height = 600;
    const left = (window.innerWidth / 2) - (width / 2) + window.screenX;
    const top = (window.innerHeight / 2) - (height / 2) + window.screenY;
    
    // Ouvre la fenêtre popup au centre de l'écran
    window.open('login_google.php', 'GoogleLogin', `width=${width},height=${height},left=${left},top=${top},toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes`);
}

// Permet de rediriger la page principale quand la popup confirme la connexion réussie
window.addEventListener('message', function(event) {
    if (event.origin === window.location.origin && event.data === 'google_login_success') {
        window.location.href = 'dashboard.php';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>