<?php

namespace App\Core;

use App\Core\Router;
use App\Core\ErrorHandler;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Classe Launcher
 * Classe responsable de lancement de l'application
 * @package App\Core
 */
class Launcher
{
    private Router $router;
    private ErrorHandler $errorHandler;

    public function __construct(string $routesFile)
    {
        $this->initializeEnvironment();
        $this->errorHandler = new ErrorHandler();
        $this->router = new Router($routesFile);

        $this->errorHandler = new ErrorHandler();
        // Configurer un gestionnaire global pour les exceptions non capturées
        set_exception_handler([$this->errorHandler, 'handle']);
    }

    /**
     * Initialise les variables d'environnement
     */
    private function initializeEnvironment(): void
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../../.env');
        if (file_exists(__DIR__ . '/../../.envlocal')) {
            $dotenv->load(__DIR__ . '/../../.envlocal');
        }
    }

    /**
     * Démarre l'application en fonction de la route et de la requête
     */
    public function run(): void
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];

            // Astuce php qui permet de traiter la partie de l'url qui nous interresse 
            // On detecte le fichier index.php et on en extrait le chemin
            // Si l'URL est http://localhost/public/index.php/news, $basePath devient /public/
            $basePath = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
            // dans $basePath on va avoir le chemin vers le fichier index.php
            // On utilise ce basePath pour le retirer 
            // Il ne restera que ce qui suit le public/ Exemple : /news.
            $requestUri = '/' . trim(substr($_SERVER['REQUEST_URI'], strlen($basePath)), '/');
            // Extrait le chemin sans les paramètres Exemple : /news si l'URL est /news?id=123
            $uri = parse_url($requestUri, PHP_URL_PATH);

            // Résoudre la route
            $route = $this->router->match($uri, $method);

            call_user_func_array($route['callable'], $route['params']);
        } catch (\Throwable $e) {
            $this->errorHandler->handle($e);
        }
    }
}
