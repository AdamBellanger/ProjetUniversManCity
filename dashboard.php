<?php
session_start();
require_once 'db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'fan';

// Récupérer les infos de l'utilisateur
$requete = $pdo->prepare('SELECT nom, email, role FROM users WHERE id = ?');
$requete->execute([$userId]);
$utilisateur = $requete->fetch(PDO::FETCH_ASSOC);

if (!$utilisateur) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Mettre à jour la dernière activité
$requete = $pdo->prepare('UPDATE users SET derniere_activite = NOW() WHERE id = ?');
$requete->execute([$userId]);
?>

<?php require_once 'includes/header.php'; ?>
<?php
// Phrases de bienvenue aléatoires
$prenoms = explode(' ', $utilisateur['nom']);
$prenom = $prenoms[0];

$messages = [
    "Bon retour parmi nous, <strong>" . htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8') . "</strong> ! Prêt pour une nouvelle journée ?",
    "Bienvenue sur le Dashboard, <strong>" . htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8') . "</strong> ! Que la victoire soit avec vous.",
    "Content de vous revoir, <strong>" . htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8') . "</strong> ! Les Citizens comptent sur vous.",
    "Bonjour <strong>" . htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8') . "</strong> ! Manchester City vous attend.",
    "Ravi de vous voir, <strong>" . htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8') . "</strong> ! Let's go City !",
];

$message_aleatoire = $messages[array_rand($messages)];
?>

<div style="text-align:center; padding: 3rem 0 2rem 0;">
    <p style="
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--blanc);
        margin-bottom: 0.8rem;
        letter-spacing: 1px;">
        <?php echo $message_aleatoire; ?>
    </p>
    <h1 style="
        font-size: 1rem;
        font-weight: 400;
        color: var(--gris-fonce);
        letter-spacing: 3px;
        text-transform: uppercase;
        margin-top: 0.5rem;">
        Tableau de bord — <?php echo htmlspecialchars($utilisateur['role'], ENT_QUOTES, 'UTF-8'); ?>
    </h1>
</div>

<?php if ($role === 'staff'): ?>

    <?php
    $requete = $pdo->query('SELECT COUNT(*) FROM players');
    $total_joueurs = $requete->fetchColumn();

    $requete = $pdo->query('SELECT COUNT(*) FROM matchs');
    $total_matchs = $requete->fetchColumn();

    $requete = $pdo->query('SELECT COUNT(*) FROM matchs WHERE goals_city > goals_opponent');
    $total_victoires = $requete->fetchColumn();

    $requete = $pdo->query('SELECT COUNT(*) FROM matchs WHERE goals_city < goals_opponent');
    $total_defaites = $requete->fetchColumn();

    $requete = $pdo->query('SELECT COUNT(*) FROM matchs WHERE goals_city = goals_opponent');
    $total_nuls = $requete->fetchColumn();

    $requete = $pdo->query('SELECT SUM(goals_city) FROM matchs');
    $total_buts_city = $requete->fetchColumn() ?? 0;

    $requete = $pdo->query(
        'SELECT p.full_name, SUM(s.buts) AS total_buts
             FROM player_match_stats s
             JOIN players p ON s.player_id = p.id
             GROUP BY s.player_id
             ORDER BY total_buts DESC
             LIMIT 1'
    );
    $meilleur_buteur = $requete->fetch(PDO::FETCH_ASSOC);

    $requete = $pdo->query(
        'SELECT p.full_name, SUM(s.passes_decisives) AS total_passes
             FROM player_match_stats s
             JOIN players p ON s.player_id = p.id
             GROUP BY s.player_id
             ORDER BY total_passes DESC
             LIMIT 1'
    );
    $meilleur_passeur = $requete->fetch(PDO::FETCH_ASSOC);

    $requete = $pdo->query(
        'SELECT p.full_name, ROUND(AVG(s.note), 2) AS note_moyenne
             FROM player_match_stats s
             JOIN players p ON s.player_id = p.id
             WHERE s.note IS NOT NULL
             GROUP BY s.player_id
             HAVING COUNT(s.id) >= 1
             ORDER BY note_moyenne DESC
             LIMIT 1'
    );
    $meilleure_note = $requete->fetch(PDO::FETCH_ASSOC);
    ?>

    <h2 class="titre-page" style="font-size:1.4rem; margin-bottom:1rem;">
        Admin / Coach
    </h2>

    <h3 style="color:var(--bleu-city); margin-bottom:1rem;">Résumé de la saison</h3>

    <div class="grille-stats">
        <div class="stat-card">
            <span class="stat-valeur"><?php echo (int)$total_joueurs; ?></span>
            <span class="stat-label">Joueurs</span>
        </div>
        <div class="stat-card">
            <span class="stat-valeur"><?php echo (int)$total_matchs; ?></span>
            <span class="stat-label">Matchs joués</span>
        </div>
        <div class="stat-card">
            <span class="stat-valeur" style="color:#2ecc71;">
                <?php echo (int)$total_victoires; ?>
            </span>
            <span class="stat-label">Victoires</span>
        </div>
        <div class="stat-card">
            <span class="stat-valeur" style="color:var(--or);">
                <?php echo (int)$total_nuls; ?>
            </span>
            <span class="stat-label">Nuls</span>
        </div>
        <div class="stat-card">
            <span class="stat-valeur" style="color:#e74c3c;">
                <?php echo (int)$total_defaites; ?>
            </span>
            <span class="stat-label">Défaites</span>
        </div>
        <div class="stat-card">
            <span class="stat-valeur"><?php echo (int)$total_buts_city; ?></span>
            <span class="stat-label">Buts marqués</span>
        </div>
    </div>
    <h3 style="color:var(--bleu-city); margin: 1.5rem 0 1rem;">Statistiques de la saison</h3>

    <?php
    // Top 5 pour buts et passes
    $requete = $pdo->query(
        'SELECT p.full_name,
                    SUM(s.buts) AS total_buts,
                    SUM(s.passes_decisives) AS total_passes
            FROM player_match_stats s
            JOIN players p ON s.player_id = p.id
            GROUP BY s.player_id
            ORDER BY total_buts DESC
            LIMIT 5'
    );
    $stats_top = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Tous les joueurs pour les minutes
    $requete = $pdo->query(
        'SELECT p.full_name,
                    SUM(s.minutes_jouees) AS total_minutes
            FROM player_match_stats s
            JOIN players p ON s.player_id = p.id
            GROUP BY s.player_id
            ORDER BY total_minutes DESC'
    );
    $stats_minutes = $requete->fetchAll(PDO::FETCH_ASSOC);

    $noms_top = [];
    $buts_graphique = [];
    $passes_graphique = [];

    foreach ($stats_top as $sg) {
        $noms_top[] = htmlspecialchars($sg['full_name'], ENT_QUOTES, 'UTF-8');
        $buts_graphique[] = (int)$sg['total_buts'];
        $passes_graphique[] = (int)$sg['total_passes'];
    }

    $noms_minutes = [];
    $minutes_graphique = [];

    foreach ($stats_minutes as $sm) {
        $noms_minutes[] = htmlspecialchars($sm['full_name'], ENT_QUOTES, 'UTF-8');
        $minutes_graphique[] = (int)$sm['total_minutes'];
    }

    $noms_top_json     = json_encode($noms_top);
    $buts_json         = json_encode($buts_graphique);
    $passes_json       = json_encode($passes_graphique);
    $noms_minutes_json = json_encode($noms_minutes);
    $minutes_json      = json_encode($minutes_graphique);
    ?>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem; margin-bottom:2rem;">

        <!-- Graphique buts -->
        <div class="carte">
            <h4 style="color:var(--bleu-city); margin-bottom:1rem; font-size:0.85rem;
                   letter-spacing:1px; text-transform:uppercase;">
                Top buteurs
            </h4>
            <canvas id="graphiqueButs" height="200"></canvas>
        </div>

        <!-- Graphique passes -->
        <div class="carte">
            <h4 style="color:var(--bleu-city); margin-bottom:1rem; font-size:0.85rem;
                   letter-spacing:1px; text-transform:uppercase;">
                Top passeurs
            </h4>
            <canvas id="graphiquePasses" height="200"></canvas>
        </div>

        <!-- Graphique minutes -->
        <div class="carte" style="grid-column: 1 / -1;">
            <h4 style="color:var(--bleu-city); margin-bottom:1rem; font-size:0.85rem;
                   letter-spacing:1px; text-transform:uppercase;">
                Minutes jouées par joueur
            </h4>
            <canvas id="graphiqueMinutes" height="100"></canvas>
        </div>

    </div>

    <script>
        const noms_top = <?php echo $noms_top_json; ?>;
        const buts = <?php echo $buts_json; ?>;
        const passes = <?php echo $passes_json; ?>;
        const noms_minutes = <?php echo $noms_minutes_json; ?>;
        const minutes = <?php echo $minutes_json; ?>;

        const optionsCommunes = {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(13,27,46,0.9)',
                    titleColor: '#6CABDD',
                    bodyColor: '#E8EDF2',
                    borderColor: 'rgba(108,171,221,0.3)',
                    borderWidth: 1,
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#8A9BB0',
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(108,171,221,0.08)'
                    }
                },
                y: {
                    ticks: {
                        color: '#8A9BB0',
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(108,171,221,0.08)'
                    },
                    beginAtZero: true
                }
            }
        };

        // Graphique buts
        new Chart(document.getElementById('graphiqueButs'), {
            type: 'bar',
            data: {
                labels: noms_top,
                datasets: [{
                    data: buts,
                    backgroundColor: 'rgba(108,171,221,0.3)',
                    borderColor: '#6CABDD',
                    borderWidth: 2,
                    borderRadius: 8,
                }]
            },
            options: optionsCommunes
        });

        // Graphique passes
        new Chart(document.getElementById('graphiquePasses'), {
            type: 'bar',
            data: {
                labels: noms_top,
                datasets: [{
                    data: passes,
                    backgroundColor: 'rgba(46,204,113,0.2)',
                    borderColor: '#2ecc71',
                    borderWidth: 2,
                    borderRadius: 8,
                }]
            },
            options: optionsCommunes
        });

        // Graphique minutes TOUS les joueurs
        new Chart(document.getElementById('graphiqueMinutes'), {
            type: 'line',
            data: {
                labels: noms_minutes,
                datasets: [{
                    data: minutes,
                    backgroundColor: 'rgba(201,168,76,0.1)',
                    borderColor: '#C9A84C',
                    borderWidth: 2,
                    pointBackgroundColor: '#C9A84C',
                    pointBorderColor: '#C9A84C',
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(13,27,46,0.9)',
                        titleColor: '#C9A84C',
                        bodyColor: '#E8EDF2',
                        borderColor: 'rgba(201,168,76,0.3)',
                        borderWidth: 1,
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#8A9BB0',
                            font: {
                                size: 10
                            },
                            maxRotation: 45,
                            minRotation: 45
                        },
                        grid: {
                            color: 'rgba(108,171,221,0.08)'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#8A9BB0',
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(108,171,221,0.08)'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <div class="grille-stats" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="stat-card">
            <span class="stat-label">Meilleur buteur</span>
            <span class="stat-valeur" style="font-size:1.1rem; margin-top:0.5rem;">
                <?php if ($meilleur_buteur): ?>
                    <?php echo htmlspecialchars($meilleur_buteur['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                    <small style="color:var(--gris-fonce); font-size:0.85rem; display:block;">
                        <?php echo (int)$meilleur_buteur['total_buts']; ?> buts
                    </small>
                <?php else: ?>
                    <small style="color:var(--gris-fonce);">Aucune donnée</small>
                <?php endif; ?>
            </span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Meilleur passeur</span>
            <span class="stat-valeur" style="font-size:1.1rem; margin-top:0.5rem;">
                <?php if ($meilleur_passeur): ?>
                    <?php echo htmlspecialchars($meilleur_passeur['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                    <small style="color:var(--gris-fonce); font-size:0.85rem; display:block;">
                        <?php echo (int)$meilleur_passeur['total_passes']; ?> passes
                    </small>
                <?php else: ?>
                    <small style="color:var(--gris-fonce);">Aucune donnée</small>
                <?php endif; ?>
            </span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Meilleure note</span>
            <span class="stat-valeur" style="font-size:1.1rem; margin-top:0.5rem;">
                <?php if ($meilleure_note): ?>
                    <?php echo htmlspecialchars($meilleure_note['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                    <small style="color:var(--gris-fonce); font-size:0.85rem; display:block;">
                        <?php echo htmlspecialchars($meilleure_note['note_moyenne'], ENT_QUOTES, 'UTF-8'); ?>/10
                    </small>
                <?php else: ?>
                    <small style="color:var(--gris-fonce);">Aucune donnée</small>
                <?php endif; ?>
            </span>
        </div>
    </div>

    <h3 style="color:var(--bleu-city); margin-bottom:1rem;">Administration</h3>
    <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:2rem;">
        <a href="includes/admin/players.php" class="bouton bouton-secondaire">
            Gérer les joueurs
        </a>
        <a href="includes/admin/matchs.php" class="bouton bouton-secondaire">
            Gérer les matchs
        </a>
        <a href="includes/admin/stats_create.php" class="bouton bouton-secondaire">
            Ajouter des stats
        </a>
    </div>

<?php elseif ($role === 'player'): ?>

    <?php
    $requete = $pdo->prepare(
        'SELECT id, full_name, shirt_number, position, nationality
             FROM players WHERE email = ?'
    );
    $requete->execute([$utilisateur['email']]);
    $joueur = $requete->fetch(PDO::FETCH_ASSOC);
    ?>

    <h2 class="titre-page" style="font-size:1.4rem; margin-bottom:1rem;">
        Zone Joueur
    </h2>

    <?php if (!$joueur): ?>
        <p style="color:var(--gris-fonce);">
            Aucun profil joueur trouvé pour votre compte. Contactez le staff.
        </p>
    <?php else: ?>

        <div class="carte" style="margin-bottom:2rem;">
            <h3 style="color:var(--bleu-city); margin-bottom:0.5rem;">
                <?php echo htmlspecialchars($joueur['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                <span style="color:var(--gris-fonce); font-size:0.9rem;">
                    — n°<?php echo (int)$joueur['shirt_number']; ?>
                </span>
            </h3>
            <p style="color:var(--gris-fonce);">
                Poste : <?php echo htmlspecialchars($joueur['position'], ENT_QUOTES, 'UTF-8'); ?>
                &nbsp;|&nbsp;
                Nationalité : <?php echo htmlspecialchars($joueur['nationality'], ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>

        <?php
        $requete = $pdo->prepare(
            'SELECT m.match_date, m.competition, m.home_away, m.opponent,
                        s.titulaire, s.minutes_jouees, s.buts, s.passes_decisives,
                        s.note, s.cartons_jaunes, s.cartons_rouges
                 FROM player_match_stats s
                 JOIN matchs m ON s.match_id = m.id
                 WHERE s.player_id = ?
                 ORDER BY m.match_date DESC'
        );
        $requete->execute([$joueur['id']]);
        $stats_matchs = $requete->fetchAll(PDO::FETCH_ASSOC);

        $total_buts = $total_passes = $total_minutes = 0;
        $total_jaunes = $total_rouges = $total_notes = $nb_notes = 0;

        foreach ($stats_matchs as $stat) {
            $total_buts    += (int)$stat['buts'];
            $total_passes  += (int)$stat['passes_decisives'];
            $total_minutes += (int)$stat['minutes_jouees'];
            $total_jaunes  += (int)$stat['cartons_jaunes'];
            $total_rouges  += (int)$stat['cartons_rouges'];
            if ($stat['note'] !== null) {
                $total_notes += (float)$stat['note'];
                $nb_notes++;
            }
        }
        $note_moyenne = $nb_notes > 0 ? round($total_notes / $nb_notes, 2) : null;
        ?>

        <h3 style="color:var(--bleu-city); margin-bottom:1rem;">Résumé de saison</h3>

        <div class="grille-stats">
            <div class="stat-card">
                <span class="stat-valeur"><?php echo count($stats_matchs); ?></span>
                <span class="stat-label">Matchs</span>
            </div>
            <div class="stat-card">
                <span class="stat-valeur"><?php echo (int)$total_minutes; ?></span>
                <span class="stat-label">Minutes</span>
            </div>
            <div class="stat-card">
                <span class="stat-valeur"><?php echo (int)$total_buts; ?></span>
                <span class="stat-label">Buts</span>
            </div>
            <div class="stat-card">
                <span class="stat-valeur"><?php echo (int)$total_passes; ?></span>
                <span class="stat-label">Passes déc.</span>
            </div>
            <div class="stat-card">
                <span class="stat-valeur">
                    <?php echo $note_moyenne !== null ? $note_moyenne : '-'; ?>
                </span>
                <span class="stat-label">Note moy.</span>
            </div>
            <div class="stat-card">
                <span class="stat-valeur" style="color:#e74c3c;">
                    <?php echo (int)$total_jaunes; ?>
                </span>
                <span class="stat-label">Cartons jaunes</span>
            </div>
        </div>

        <h3 style="color:var(--bleu-city); margin:1.5rem 0 1rem;">Détail par match</h3>

        <?php if (empty($stats_matchs)): ?>
            <p style="color:var(--gris-fonce);">Aucune statistique enregistrée pour le moment.</p>
        <?php else: ?>
            <div class="tableau-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Compétition</th>
                            <th>Adversaire</th>
                            <th>Lieu</th>
                            <th>Tit.</th>
                            <th>Min.</th>
                            <th>Buts</th>
                            <th>Passes</th>
                            <th>Note</th>
                            <th>J</th>
                            <th>R</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats_matchs as $stat): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($stat['match_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($stat['competition'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($stat['opponent'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $stat['home_away'] === 'HOME' ? 'Domicile' : 'Extérieur'; ?></td>
                                <td><?php echo $stat['titulaire'] ? 'Oui' : 'Non'; ?></td>
                                <td><?php echo (int)$stat['minutes_jouees']; ?></td>
                                <td><?php echo (int)$stat['buts']; ?></td>
                                <td><?php echo (int)$stat['passes_decisives']; ?></td>
                                <td><?php echo $stat['note'] !== null ? htmlspecialchars($stat['note'], ENT_QUOTES, 'UTF-8') . '/10' : '-'; ?></td>
                                <td><?php echo (int)$stat['cartons_jaunes']; ?></td>
                                <td><?php echo (int)$stat['cartons_rouges']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    <?php endif; ?>

<?php else: ?>

    <h2 class="titre-page" style="font-size:1.4rem; margin-bottom:1rem;">
        Zone Supporter
    </h2>

    <div class="carte">
        <p style="color:var(--gris-fonce);">
            Bienvenue ! Consulte l'effectif et les résultats de Manchester City.
        </p>
    </div>

<?php endif; ?>
<div style="margin-top: 20rem;"></div>
<?php require_once 'includes/footer.php'; ?>