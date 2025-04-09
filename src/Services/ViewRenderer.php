<?php

namespace App\Services;

use App\Services\UrlGenerator;
use App\Services\TextHandler;
use App\Services\FormatToFrenchDate;
use App\Services\FlashMessage;
use App\Services\AuthService;

/**
 * Class ViewRenderer
 * Gestionnaire de vue
 * Charge la plupart des services et permet de passer des données à la vue
 * @package App\Services
 */
class ViewRenderer
{

    private UrlGenerator $url;
    private TextHandler $textHandler;
    private FormatToFrenchDate $formatDate;
    private FlashMessage $flashMessage;
    private AuthService $auth;
    public function __construct()
    {
        $this->url = new UrlGenerator();
        $this->textHandler = new TextHandler();
        $this->formatDate = new FormatToFrenchDate();
        $this->flashMessage = new FlashMessage();
        $this->auth = new AuthService();
    }

    // methode appelée automatiquement dès lors qu'on appelle une méthode inexistante dans l'objet ou  dans l'instance de la classe
    // Parcours toutes les propriétés de l'objet
    // Teste si la méthode que l'on tente d'appeler existe
    // si elle existe, on appelle la methode
    // Sinon par defaut, on appelle la  méthode invoque
    public function __call($name, $arguments)
    {
        // retourne la liste des propriétés accessibles de l'objet en argument
        // ici $url et $textHandler sous forme de tableau
        // voir doc __invoque, ..., get_object_vars, is_callable
        $properties = get_object_vars($this);
        foreach ($properties as $propertyValue) {
            if (is_callable([$propertyValue, $name])) {
                return $propertyValue->$name(...$arguments);
            }
        }
        return ($this->$name)(...$arguments);
    }
    public function render(string $view, array $data = [], int $statusCode = 200): void
    {
        // Défini le code HTTP
        http_response_code($statusCode);

        // Vérifie si le fichier de vue existe
        $viewPath = __DIR__ . "/../../views/{$view}";
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("Le fichier '{$view}' est introuvable.");
        }
        // Chemin du fichier de mise en page (layout)
        $layoutPath = __DIR__ . "/../../views/layout.phtml";
        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Le fichier de mise en page est introuvable.");
        }
        // Extrait les données à inclure dans la vue
        extract($data);

        // Définir $template pour inclure la vue dans le layout
        $template = $viewPath;

        // Inclure le fichier layout
        require $layoutPath;
    }

    /**
     * @param string $field Champ sur lequel on veut trier
     * @return string
     * Méthode permettant de changer le sens de tri
     */
    public function toggleSort(string $field): string
    {
        $currentSort = $_GET['sort'] ?? 'date_creation';
        $currentOrder = $_GET['dir'] ?? 'DESC';

        // Si on trie déjà sur le même champ, on inverse l'ordre
        $newOrder = ($currentSort === $field && $currentOrder === 'ASC') ? 'DESC' : 'ASC';

        return $newOrder;
    }

    /**
     * Redirige avec un message flash stocké dans l'URL.
     * @param string $url L'URL de redirection.
     * @param string $type Le type de message flash (success, error, warning).
     * @param string $message Le message flash.
     * @return void
     */
    public function redirectWithFlash(string $url, string $type, string $message): void
    {
        $this->flashMessage->addFlash($type, $message);

        // Rendre une page de transition avec redirection via JavaScript
        $this->render('flashMessages/redirect.phtml', [
            'redirectUrl' => $this->url($url)
        ]);
        exit;
    }
}
