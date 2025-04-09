<?php

namespace App\Controller;

use App\Model\ArticleModel;
use App\Services\ViewRenderer;
use App\Services\AuthService;
use App\Services\FlashMessage;

class AdminController
{
    private ArticleModel $articleModel;
    private FlashMessage $flashMessage;

    public function __construct(private ViewRenderer $viewRenderer)
    {
        $this->articleModel = new ArticleModel();
        $this->flashMessage = new FlashMessage();
    }

    /**
     * Vérifie que l'utilisateur est administrateur avant d'accéder à une page admin.
     * return void
     */
    private function ensureAdminAccess(): void
    {
        if (!AuthService::isAdmin()) {
            $this->flashMessage->addFlash('error', "Accès refusé : vous devez être administrateur.");
            $this->viewRenderer->render('auth/connection_form.phtml');
            exit;
        }
    }

    /**
     * Affichage de la page admin.
     * @throws \Exception
     * return void
     */
    public function showAdmin(): void
    {
        $this->ensureAdminAccess();

        // Tableau des messages flash associés aux paramètres d'URL
        $flashMessages = [
            'createSuccess' => "Article ajouté avec succès.",
            'updateSuccess' => "Article modifié avec succès.",
            'deleteSuccess' => "Article supprimé avec succès."
        ];

        // Ajout des messages flash en fonction des paramètres GET
        foreach ($flashMessages as $param => $message) {
            if (isset($_GET[$param]) && $_GET[$param] == 1) {
                $this->flashMessage->addFlash('success', $message);
            }
        }
        // Affichage des articles avec leurs statistiques
        $sort = $_GET['sort'] ?? 'date_creation';
        $direction = $_GET['dir'] ?? 'DESC';
        $articles = $this->articleModel->getArticlesWithStats($sort, $direction);

        $this->viewRenderer->render('admin/admin.phtml', [
            'articles' => $articles
        ]);
    }

    /**
     * Ajoute un article.
     * return void
     */
    public function addArticle(?int $id = null): void
    {
        $this->ensureAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');

            $this->articleModel->addArticle([
                'title' => $title,
                'content' => $content,
                'id_user' => $_SESSION['user']['id'],
                'date_update' => null
            ]);

            // Redirection avec le paramètre dans l'URL
            header('Location: ' . $this->viewRenderer->url('/admin') . '?createSuccess=1');
            exit;
        }
        $article = $id ? $this->articleModel->getArticleById($id) : null;

        $this->viewRenderer->render('admin/article_form.phtml', [
            'article' => $article,
            'title' => "Créer un article"
        ]);
    }

    /**
     * Modifie un article existant.
     * @param int $id L'id de l'article à modifier
     * @throws \Exception
     * return void
     */
    public function editArticle(?int $id = null): void
    {
        $this->ensureAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');

            try {
                $this->articleModel->updateArticle($id, [
                    'title' => $title,
                    'content' => $content,
                    'date_update' => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                $this->flashMessage->addFlash('error', "Erreur lors de la modification de l'article.", $e->getMessage());
                $this->viewRenderer->render('admin/article_form.phtml', [
                    'title' => 'Modifier un article'
                ]);
                exit;
            }
            // Redirection avec le paramètre dans l'URL
            header('Location: ' . $this->viewRenderer->url('/admin') . '?updateSuccess=1');
            exit;
        }
        $article = $id ? $this->articleModel->getArticleById($id) : null;

        $this->viewRenderer->render('admin/article_form.phtml', [
            'article' => $article,
            'title' => "Modifier l'article"
        ]);
    }

    /**
     * Supprime un article.
     * @param int $id L'id de l'article à supprimer
     * return void
     */
    public function deleteArticle(int $id): void
    {
        $this->ensureAdminAccess();

        try {
            if ($this->articleModel->deleteArticle($id)) {
                header('Location: ' . $this->viewRenderer->url('/admin?deleteSuccess=1'));
                exit;
            } else {
                throw new \Exception("Erreur lors de la suppression de l'article.");
            }
        } catch (\Exception $e) {
            $this->flashMessage->addFlash('error', $e->getMessage());
            $this->viewRenderer->render('admin/admin.phtml');
            exit;
        }
    }
}
