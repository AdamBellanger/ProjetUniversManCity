<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$page_courante = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manchester City Universe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Barlow+Condensed:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProjetUnivers/includes/style.css">
    <script>(function(){var t=localStorage.getItem('theme')||'dark';document.documentElement.setAttribute('data-theme',t);})()</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <header class="header-principal">
        <div class="header-contenu">
            <a href="/ProjetUnivers/dashboard.php" style="text-decoration:none;">
                <div class="logo">
                    <span class="logo-texte">Manchester City</span>
                </div>
            </a>

            <nav class="navigation">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/ProjetUnivers/dashboard.php"
                        class="<?php echo $page_courante === 'dashboard.php' ? 'actif' : ''; ?>">
                        Tableau de bord
                    </a>
                    <a href="/ProjetUnivers/players.php"
                        class="<?php echo $page_courante === 'players.php' ? 'actif' : ''; ?>">
                        Joueurs
                    </a>
                    <a href="/ProjetUnivers/matchs.php"
                        class="<?php echo $page_courante === 'matchs.php' ? 'actif' : ''; ?>">
                        Matchs
                    </a>
                    <a href="/ProjetUnivers/profil.php"
                        class="<?php echo $page_courante === 'profil.php' ? 'actif' : ''; ?>">
                        Profil
                    </a>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff'): ?>
                        <a href="/ProjetUnivers/includes/admin/players.php" class="nav-admin">
                            Admin
                        </a>
                    <?php endif; ?>
                    <a href="/ProjetUnivers/logout.php" class="nav-deconnexion">
                        Déconnexion
                    </a>
                <?php else: ?>
                    <a href="/ProjetUnivers/login.php"
                        class="<?php echo $page_courante === 'login.php' ? 'actif' : ''; ?>">
                        Connexion
                    </a>
                    <a href="/ProjetUnivers/register.php"
                        class="<?php echo $page_courante === 'register.php' ? 'actif' : ''; ?>">
                        Inscription
                    </a>
                <?php endif; ?>

                <!-- Toggle Thème -->
                <div class="theme-toggle-container">
                    <button id="theme-btn" class="theme-btn" title="Changer le mode">
                        <span id="theme-icon">🌙</span>
                    </button>
                </div>
            </nav>
        </div>
    </header>
    <main class="contenu-principal">
