<?php
if (session_status() === PHP_SESSION_NONE) session_start();
http_response_code(404);
$est_connecte = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page introuvable · Manchester City Universe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Barlow+Condensed:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProjetUnivers/includes/style.css">
    <script>(function(){var t=localStorage.getItem('theme')||'dark';document.documentElement.setAttribute('data-theme',t);})()</script>
    <style>
        .page-404 {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            gap: 0;
        }
        .err-code {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: clamp(7rem, 20vw, 14rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: 4px;
            background: linear-gradient(135deg, var(--bleu-city) 0%, rgba(108,171,221,0.35) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.25rem;
            animation: pulseNum 3s ease-in-out infinite;
        }
        @keyframes pulseNum {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.7; }
        }
        .err-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-main);
            margin-bottom: 0.6rem;
        }
        .err-sub {
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            max-width: 400px;
        }
        .err-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        .err-line {
            width: 60px;
            height: 2px;
            background: var(--bleu-city);
            border-radius: 2px;
            margin: 0 auto 1.5rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="page-404">
        <div class="err-code">404</div>
        <div class="err-line"></div>
        <p class="err-title">Page introuvable</p>
        <p class="err-sub">
            Cette page n'existe pas ou a été déplacée.
            Retourne sur le terrain.
        </p>
        <div class="err-actions">
            <?php if ($est_connecte): ?>
                <a href="/ProjetUnivers/dashboard.php" class="bouton bouton-principal">
                    Tableau de bord
                </a>
                <a href="/ProjetUnivers/players.php" class="bouton bouton-secondaire">
                    Effectif
                </a>
            <?php else: ?>
                <a href="/ProjetUnivers/login.php" class="bouton bouton-principal">
                    Se connecter
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
