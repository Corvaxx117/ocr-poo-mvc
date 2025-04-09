<?php

// Rôle : Analyse les requêtes (URI et méthode HTTP) et les fait correspondre aux routes définies.
// Fonctionnement :
// Charge les routes depuis routes.yaml.
// Utilise des expressions régulières pour gérer les routes dynamiques (ex. : /news/:id).
// Retourne le contrôleur et les paramètres associés à une route.

namespace App\Core;

use App\Services\ViewRenderer;
use Symfony\Component\Yaml\Yaml;
use App\Exceptions\NotFoundException;
use App\Exceptions\InternalServerErrorException;

use function PHPUnit\Framework\assertIsArray;

class Router
{
    private array $routes;
    // private ErrorHandler $errorHandler;

    public function __construct(string $routesFile)
    {
        $this->routes = Yaml::parseFile($routesFile)['routes'];
        // $this->errorHandler = new ErrorHandler();
        // // Configurer un gestionnaire global pour les exceptions non capturées
        // set_exception_handler([$this->errorHandler, 'handle']);
    }

    public function match(string $uri, string $method)
    {
        foreach ($this->routes as $route => $config) {
            // '/:\w+/' : Une regex qui trouve les paramètres dynamiques (comme :id).
            // '(\w+)' : La chaîne de remplacement. Ici, on utilise une parenthèse capturante
            // $route : La chaîne sur laquelle effectuer le remplacement.
            // '/' est un délimiteur en regexp, on échappe donc '/' par '\/' grace a str_replace
            $pattern = preg_replace('/:\w+/', '(\w+)', str_replace('/', '\/', $route));

            if (is_array($config['method'])) {
                $routeMethods = $config['method'];
            } else {
                $routeMethods = [$config['method']];
            }

            // $matches contient tous les paramètres extrait de l'URI
            // arguments de pregmatch optionnel passé par référence (alimenté directement par cette fonction )
            // le preg_match permet de tester si l'url de la requête correspond bien à une route
            // il alimente $matches avec toutes les valeurs variable de la route par rapport à l'url
            if (in_array($method, $routeMethods) && preg_match('/^' . $pattern . '$/', $uri, $matches)) {
                // $matches[0] contiendra toujours ce que l'expression reguliere valide dans sa totalité, 
                // la ou les index suivant contiendront seulement ce qui est capturé 
                // (par capture j'entends les parenthèse de la regexp)
                array_shift($matches); // Retire le 1er élément
                // Instancier le contrôleur et appeler l'action
                $classDefinition = explode('::', $config['callable']);
                if (count($classDefinition) === 2) {
                    $callable = [new $classDefinition[0](new ViewRenderer()), $classDefinition[1]];
                } else if (count($classDefinition) === 1) {
                    $callable = new $classDefinition[0](new ViewRenderer());
                } else {
                    throw new InternalServerErrorException("Le controller {$route['callable']} n'existe pas");
                }
                // dd($route, $uri, $config, $pattern, $matches);
                // si une route est matchée on retourne donc un array structuré qui va nous être utile pour appeler le bon controller avec les bons arguments
                return ['callable' => $callable, 'params' => $matches];
            }
        }
        // Lancer une exception si aucune route ne correspond
        throw new NotFoundException("Aucune route correspondante pour l'URI: $uri");
    }
}
