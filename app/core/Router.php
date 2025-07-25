<?php

namespace App\Core;

use App\Core\Abstract\Singleton;

class Router extends Singleton
{
    private static array $routes = [];
    private static bool $routesLoaded = false;

    public static function get(string $uri, string $controller, string $action, array $middlewares = []): void
    {
        self::$routes['GET'][$uri] = [
            'controller' => $controller,
            'action' => $action,
            'middlewares' => $middlewares
        ];
    }

    public static function post(string $uri, string $controller, string $action, array $middlewares = []): void
    {
        self::$routes['POST'][$uri] = [
            'controller' => $controller,
            'action' => $action,
            'middlewares' => $middlewares
        ];
    }

    public static function resolve(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // Charger dynamiquement les routes à chaque requête
        $routes = require dirname(__DIR__, 2) . '/routes/route.web.php';
        foreach ($routes as $route) {
            if (strtoupper($route['method']) !== $method) continue;

            // Convertir le path en regex si paramètre {xxx}
            $pattern = preg_replace('#\{([^/]+)\}#', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                // Si action est une closure
                if (isset($route['action']) && is_callable($route['action'])) {
                    $route['action']();
                    return;
                }
                // Sinon, appel contrôleur via App::get
                if (isset($route['controller']) && isset($route['action'])) {
                    $controller = \App\Core\App::get($route['controller']);
                    $params = [];
                    // Extraire les paramètres nommés
                    foreach ($matches as $key => $value) {
                        if (!is_int($key)) {
                            $params[] = $value;
                        }
                    }
                    call_user_func_array([$controller, $route['action']], $params);
                    return;
                }
            }
        }
        // 404 - Route non trouvée (message en français)
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'data' => null,
            'statut' => 'erreur',
            'code' => 404,
            'message' => 'Point d\'accès non trouvé'
        ]);
    }

    /**
     * ✨ Exécuter les middlewares avec chargement manuel
     */
    private static function runMiddlewares(array $middlewares): void
    {
        foreach ($middlewares as $middlewareName) {
            switch ($middlewareName) {
                case 'auth':
                    self::runAuthMiddleware();
                    break;
                case 'guest':
                    self::runGuestMiddleware();
                    break;
                default:
                    throw new \Exception("Middleware '$middlewareName' non supporté.");
            }
        }
    }

    /**
     * Middleware d'authentification
     */
    private static function runAuthMiddleware(): void
    {
        // Démarrer la session si pas encore fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /');
            exit();
        }

        // Vérifier que les données utilisateur sont complètes
        if (!isset($_SESSION['user']['id']) || empty($_SESSION['user']['id'])) {
            session_destroy();
            header('Location: /');
            exit();
        }

        // Vérifier le statut du compte
        if (isset($_SESSION['user']['statut_compte']) && 
            $_SESSION['user']['statut_compte'] !== 'ACTIF') {
            session_destroy();
            header('Location: /?error=compte_inactif');
            exit();
        }
    }

    /**
     * Middleware pour les invités (non connectés)
     */
    private static function runGuestMiddleware(): void
    {
        // Démarrer la session si pas encore fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Si l'utilisateur est déjà connecté, rediriger vers le dashboard
        if (isset($_SESSION['user'])) {
            header('Location: /dashboard');
            exit();
        }
    }

    // Suppression de loadRoutes car les routes sont chargées dynamiquement
}