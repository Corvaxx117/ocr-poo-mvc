<?php

namespace App\Controller;

/**
 * Controleur de la partie commentaire.
 */

use App\Model\ArticleModel;
use App\Model\CommentModel;
use App\Services\ViewRenderer;
use App\Services\AuthService;
use App\Services\FlashMessage;

class CommentController
{
    private CommentModel $commentModel;
    private ArticleModel $articleModel;
    private FlashMessage $flashMessage;


    public function __construct(private ViewRenderer $viewRenderer)
    {
        $this->commentModel = new CommentModel();
        $this->articleModel = new ArticleModel();
        $this->flashMessage = new FlashMessage();
    }
    /**
     * Ajoute un commentaire.
     * @return void
     */
    public function addComment(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Récupération des données du formulaire.
            $pseudo = trim($_POST['pseudo'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $idArticle = $_POST['idArticle'];

            if (empty($pseudo) || empty($content) || empty($idArticle)) {
                $this->flashMessage->addFlash('error', "Tous les champs sont obligatoires.");
            }

            // On vérifie que l'article existe.
            $article = $this->articleModel->getArticleById($idArticle);
            if (!$article) {
                $this->flashMessage->addFlash('error', "L'article demandé n'existe pas.");
            }
            // On ajoute le commentaire.
            $result = $this->commentModel->addComment([
                'pseudo' => $pseudo,
                'content' => $content,
                'id_article' => $idArticle
            ]);
            // On vérifie que l'ajout a bien fonctionné.
            if (!$result) {
                $this->flashMessage->addFlash('error', "Une erreur est survenue lors de l'ajout du commentaire.");
            }
            // Si des erreurs sont rencontrées, on redirige vers la page d'ajout d'article.
            if ($this->flashMessage->hasFlash('error')) {
                $this->flashMessage->addFlash('error', implode(' / ', $this->flashMessage->getFlash('error')));
                $this->viewRenderer->render('articles/show_details.phtml', ['article' => $article]);
                return;
            }
            // On redirige vers la page de l'article avec un message de succès.
            $this->viewRenderer->redirectWithFlash('/articles/show/' . $idArticle, 'success', "Le commentaire a bien été ajouté.");
        }
    }

    /**
     * Supprime un commentaire.
     * @param integer $id l'id du commentaire à supprimer
     * @return void
     */
    public function delete(int $id): void
    {
        if (!AuthService::isAdmin()) {
            $this->viewRenderer->redirectWithFlash('/auth/connection', 'error', "Vous devez être administrateur pour accéder à cette page.");
            exit;
        }
        // Récupérer l'ID de l'article avant suppression
        $comment = $this->commentModel->getCommentById($id);

        if (!$comment) {
            $this->viewRenderer->redirectWithFlash('/articles/show/' . $comment['id_article'], 'error', "Le commentaire n'a pas été trouvé.");
            exit;
        }

        if ($this->commentModel->deleteComment($id)) {
            $this->viewRenderer->redirectWithFlash('/articles/show/' . $comment['id_article'], 'success', "Le commentaire a été supprimé.");
        } else {
            $this->viewRenderer->redirectWithFlash('/articles/show/' . $comment['id_article'], 'error', "Le commentaire n'a pas pu étre supprimé.");
        }
        exit;
    }
}
