<?php

namespace App\Controller;

use App\Model\UserModel;
use App\Services\ViewRenderer;
use App\Services\AuthService;
use App\Services\FlashMessage;

class AuthController
{
    private UserModel $userModel;
    private FlashMessage $flashMessage;
    public function __construct(private ViewRenderer $viewRenderer)
    {
        $this->userModel = new UserModel();
        $this->flashMessage = new FlashMessage();
    }

    /**
     * Affichage du formulaire d'inscription. (GET)
     * Inscription de l'utilisateur. (POST)
     */
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->verifyFormRegister();

            if (!empty($result['errors'])) {
                // Rediriger avec les erreurs si elles existent
                $this->flashMessage->addFlash('error', implode(' / ', $result['errors']));
                $this->viewRenderer->render('auth/registration_form.phtml');
                return;
            }

            // Création de l'utilisateur
            $this->userModel->createUser($result['data']);

            // Ajouter un message de succès
            $this->flashMessage->addFlash('success', "Inscription réussie.");
            $this->viewRenderer->render('auth/connection_form.phtml');
        } else {
            $this->viewRenderer->render('auth/registration_form.phtml');
        }
    }

    /**
     * Verifie le formulaire d'inscription
     * @return array Retourne les erreurs et les données validées
     */
    private function verifyFormRegister(): array
    {
        $nickname = trim($_POST['nickname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if (empty($nickname) || empty($email) || empty($password) || empty($confirmPassword)) {
            $this->flashMessage->addFlash('error', "Tous les champs sont obligatoires.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashMessage->addFlash('error', "Veuillez saisir un email valide.");
        }

        if ($password !== $confirmPassword) {
            $this->flashMessage->addFlash('error', "Les mots de passe ne correspondent pas.");
        }

        // Vérification utilisateur
        $user = $this->userModel->findUserByEmail($email);
        if ($user) {
            $this->flashMessage->addFlash('error', "Un utilisateur avec cet email existe deja.");
        }

        return [
            'errors' => $this->flashMessage->getFlash('error'),
            'data' => empty($this->flashMessage->getFlash('error')) ? [ // Seulement si aucune erreur
                'nickname' => $nickname,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ] : null
        ];
    }

    /**
     * Affichage du formulaire de connexion administrateur. (GET)
     * Connexion de l'utilisateur en tant qu'administrateur. (POST)
     * @return void
     */
    public function connect(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                $this->flashMessage->addFlash('error', "Tous les champs sont obligatoires.");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flashMessage->addFlash('error', "Veuillez saisir un email valide.");
            }

            // Vérification utilisateur
            $user = $this->userModel->findUserByEmail($email);
            if (!$user || !password_verify($password, $user['password'])) {
                $this->flashMessage->addFlash('error', "Email ou mot de passe incorrect.");
            }

            if ($this->viewRenderer->hasFlash('error')) {
                // Rediriger avec les erreurs si elles existent
                $this->flashMessage->addFlash('error', implode(' / ', $this->flashMessage->getFlash('error')));
                $this->viewRenderer->render('auth/connection_form.phtml');
                return;
            }
            // Connexion de l'utilisateur
            AuthService::login($user);

            header('Location: ' . $this->viewRenderer->url('/articles'));
            exit;
        } else {
            $this->viewRenderer->render('auth/connection_form.phtml');
        }
    }

    /**
     * Déconnexion de l'utilisateur.
     */
    public function disconnect(): void
    {
        AuthService::logout();
        header('Location: ' . $this->viewRenderer->url('/articles'));
        exit;
    }
}
