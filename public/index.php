<?php

// Debug: Afficher les informations de l'environnement
if (isset($_GET['debug'])) {
    echo "<h2>Debug Information</h2>";
    echo "<p><strong>Current directory:</strong> " . getcwd() . "</p>";
    echo "<p><strong>__DIR__:</strong> " . __DIR__ . "</p>";
    echo "<p><strong>Expected autoload path:</strong> " . __DIR__ . '/../vendor/autoload.php' . "</p>";
    echo "<p><strong>Autoload exists:</strong> " . (file_exists(__DIR__ . '/../vendor/autoload.php') ? 'YES' : 'NO') . "</p>";
    
    if (is_dir(__DIR__ . '/../vendor')) {
        echo "<p><strong>Vendor directory contents:</strong></p><ul>";
        foreach (scandir(__DIR__ . '/../vendor') as $file) {
            if ($file != '.' && $file != '..') {
                echo "<li>$file</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p><strong>Vendor directory:</strong> NOT FOUND</p>";
    }
    
    echo "<p><strong>Root directory contents:</strong></p><ul>";
    foreach (scandir(__DIR__ . '/..') as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
    exit;
}

// Vérifier si autoload.php existe avant de l'inclure
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("❌ Error: Composer autoload file not found at: $autoloadPath\n" .
        "Please run 'composer install' to install dependencies.\n" .
        "Add ?debug=1 to the URL to see debug information.");
}

require_once $autoloadPath;

// Charger les variables d'environnement
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Exception $e) {
    // Si le fichier .env n'existe pas, utiliser les valeurs par défaut
}

// Définir les headers CORS pour l'API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Initialiser le conteneur IoC
    \App\Core\App::initialize();
    
    // Lancer le système de routage
    \App\Core\Router::resolve();
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'data' => null,
        'statut' => 'error',
        'code' => 500,
        'message' => 'Erreur interne du serveur: ' . $e->getMessage()
    ]);
}