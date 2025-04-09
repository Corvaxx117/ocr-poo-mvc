<?php

namespace App\Controller;

use App\Model\ArticleModel;
use App\Model\CommentModel;
use App\Services\ViewRenderer;


/**
 * Contrôleur de la partie article
 */
class ArticleController
{
    private ArticleModel $articleModel;
    private CommentModel $commentModel;

    public function __construct(private ViewRenderer $viewRenderer)
    {
        $this->articleModel = new ArticleModel();
        $this->commentModel = new CommentModel();
    }
    /**
     * Affiche la page d'accueil.
     * @return void
     */
    public function listAllArticles(): void
    {
        $articles = $this->articleModel->getAllArticles();

        $data = [
            'title' => 'Liste des articles',
            'articles' => $articles
        ];
        $this->viewRenderer->render('../views/articles/all_articles.phtml', $data);
    }

    /**
     * Affiche le détail d'un article.
     * @return void
     */
    public function showArticleDetails(int $id): void
    {
        // Récupération de l'id de l'article demandé.
        $article = $this->articleModel->getArticleById($id);
        if (!$article) {
            $this->viewRenderer->redirectWithFlash('/articles', 'error', "L'article demandé n'existe pas.");
        }

        // Incrémente le nombre de vues
        $this->articleModel->incrementViews($id);

        $comments = $this->commentModel->getAllCommentsByArticleId($id);

        $data = [
            'title' => 'Détails de l\'article',
            'article' => $article,
            'comments' => $comments
        ];
        $this->viewRenderer->render('../views/articles/show_details.phtml', $data);
    }


    /**
     * Affiche la page "à propos".
     * @return void
     */
    public function showApropos(): void
    {
        $this->viewRenderer->render("../views/a_propos.phtml");
    }
}
