<?php

namespace App\Controller;

/**
 * Contrôleur de la partie utilisateur.
 */

use App\Model\UserModel;
use App\Services\ViewRenderer;
use App\Services\AuthService;
use App\Services\FlashMessage;

class UserController
{

    private UserModel $userModel;
    private FlashMessage $flashMessage;

    public function __construct(private ViewRenderer $viewRenderer)
    {
        $this->userModel = new UserModel();
        $this->flashMessage = new FlashMessage();
    }

    /**
     * Vérifie si un utilisateur est connecté et retourne son ID
     * @return int|null
     */
    private function getCurrentUserId(): ?int
    {
        $user = AuthService::getUser();

        return $user['id'] ?? null;
    }

    /**
     * Redirige vers la page de connexion si l'utilisateur n'est pas connecté.
     * @return void
     */
    private function ensureAuthenticated(): void
    {
        if (!$this->getCurrentUserId()) {
            $this->viewRenderer->redirectWithFlash('/auth/connection', 'error', "Vous devez être connecté pour accéder à cette page.");
        }
    }


    /**
     * Affiche le profil de l'utilisateur connecté.
     * @return void
     */
    public function profile(): void
    {
        $this->ensureAuthenticated();
        $userId = $this->getCurrentUserId();
        $user = $this->userModel->findUserById($userId);

        if (!$user) {
            $this->viewRenderer->redirectWithFlash('/auth/connection', 'error', "Utilisateur introuvable.");
        }

        $this->viewRenderer->render('../views/users/profile.phtml', [
            'user' => $user,
            'title' => 'Mon profil'
        ]);
    }

    /**
     * Affiche le formulaire pour modifier ses informations.
     * @return void
     */
    public function editProfile(): void
    {
        $this->ensureAuthenticated();
        $userId = $this->getCurrentUserId();
        $user = $this->userModel->findUserById($userId);

        if (!$user) {
            $this->viewRenderer->redirectWithFlash('/auth/connection', 'error', "Utilisateur introuvable.");
        }

        $this->viewRenderer->render('../views/users/edit.phtml', [
            'user' => $user,
            'title' => 'Modifier mon profil'
        ]);
    }

    /**
     * Met à jour les informations de l'utilisateur connecté.
     * @return void
     */
    public function updateProfile(): void
    {
        $this->ensureAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $this->getCurrentUserId();
            $nickname = trim($_POST['nickname']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);

            if (empty($nickname || $password || $email)) {
                $this->viewRenderer->addFlash('error', "Tous les champs sont requis.");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->viewRenderer->addFlash('error', "Veuillez saisir un email valide.");
            }

            if (!($password === $confirmPassword)) {
                $this->viewRenderer->addFlash('error', "Les mots de passe ne correspondent pas.");
            }

            if ($this->viewRenderer->hasFlash('error')) {
                $this->flashMessage->addFlash('error', implode(' / ', $this->flashMessage->getFlash('error')));
                $this->viewRenderer->render('/users/edit.phtml');
                exit;
            }

            $this->userModel->updateUser($userId, [
                'nickname' => $nickname,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            $this->viewRenderer->addFlash('success', "Profil mis à jour avec succès.");
            $this->profile();
        }
    }

    /**
     * Supprime le compte de l'utilisateur connecté.
     * @return void
     */
    public function deleteAccount(): void
    {
        $this->ensureAuthenticated();
        $userId = $this->getCurrentUserId();
        $user = $this->userModel->findUserById($userId);

        if (!$user) {
            $this->viewRenderer->addFlash('error', "Utilisateur introuvable.");
            $this->viewRenderer->render('/auth/connection_form.phtml');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->userModel->deleteUser($userId)) {
                // Déconnecte l'utilisateur après suppression
                AuthService::logout();
                $this->viewRenderer->redirectWithFlash('/articles', 'success', "Votre compte a été supprimé.");
            }
        }

        $this->viewRenderer->addFlash('error', "Impossible de supprimer votre compte.");
        $this->viewRenderer->render('/users/profile.phtml');
    }
}
