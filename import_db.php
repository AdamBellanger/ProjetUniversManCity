<?php
// Script d'import temporaire — À SUPPRIMER APRÈS UTILISATION
require_once 'db.php';

$sql = file_get_contents(__DIR__ . '/includes/admin/database.sql');

// Découpe les requêtes
$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
$requetes = array_filter(
    array_map('trim', explode(';', $sql)),
    fn($r) => $r !== ''
);

$ok = 0;
$erreurs = [];

foreach ($requetes as $requete) {
    try {
        $pdo->exec($requete);
        $ok++;
    } catch (PDOException $e) {
        $erreurs[] = $e->getMessage();
    }
}

echo "<pre>";
echo "Requêtes exécutées : $ok\n";
if ($erreurs) {
    echo "Erreurs :\n";
    foreach ($erreurs as $e) echo "  - $e\n";
} else {
    echo "Import terminé sans erreur.\n";
}
echo "</pre>";
