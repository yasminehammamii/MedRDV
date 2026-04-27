<?php
// controllers/authController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Medecin.php';
require_once __DIR__ . '/../models/Patient.php';

class AuthController {
    private User $userModel;
    private Medecin $medecinModel;
    private Patient $patientModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->userModel   = new User();
        $this->medecinModel = new Medecin();
        $this->patientModel = new Patient();
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            include __DIR__ . '/../views/auth/login.php';
            return;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors   = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
        if (empty($password)) $errors[] = "Mot de passe requis.";

        if (empty($errors)) {
            $user = $this->userModel->findByEmail($email);
            if ($user && $this->userModel->verifyPassword($password, $user['mot_de_passe'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_nom']  = $user['prenom'] . ' ' . $user['nom'];

                // Charger l'ID spécifique selon le rôle
                if ($user['role'] === 'medecin') {
                    $medecin = $this->medecinModel->findByUserId($user['id']);
                    $_SESSION['medecin_id'] = $medecin['id'] ?? null;
                } elseif ($user['role'] === 'patient') {
                    $patient = $this->patientModel->findByUserId($user['id']);
                    $_SESSION['patient_id'] = $patient['id'] ?? null;
                }

                header('Location: ' . APP_URL . '/dashboard.php');
                exit;
            } else {
                $errors[] = "Email ou mot de passe incorrect.";
            }
        }

        $data = ['errors' => $errors, 'email' => $email];
        include __DIR__ . '/../views/auth/login.php';
    }

    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            include __DIR__ . '/../views/auth/register.php';
            return;
        }

        $errors = [];
        $nom       = trim($_POST['nom'] ?? '');
        $prenom    = trim($_POST['prenom'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $role      = $_POST['role'] ?? 'patient';
        $telephone = trim($_POST['telephone'] ?? '');

        if (empty($nom))    $errors[] = "Le nom est requis.";
        if (empty($prenom)) $errors[] = "Le prénom est requis.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
        if (strlen($password) < 6) $errors[] = "Mot de passe: minimum 6 caractères.";
        if ($password !== $password2) $errors[] = "Les mots de passe ne correspondent pas.";
        if (!in_array($role, ['patient', 'medecin'])) $errors[] = "Rôle invalide.";
        if ($this->userModel->emailExists($email)) $errors[] = "Cet email est déjà utilisé.";

        if (empty($errors)) {
            try {
                $userId = $this->userModel->create([
                    'nom'          => $nom,
                    'prenom'       => $prenom,
                    'email'        => $email,
                    'mot_de_passe' => $password,
                    'role'         => $role,
                    'telephone'    => $telephone,
                ]);

                if ($role === 'patient') {
                    $this->patientModel->create($userId, [
                        'date_naissance' => $_POST['date_naissance'] ?? null,
                        'sexe'           => $_POST['sexe'] ?? 'M',
                    ]);
                } elseif ($role === 'medecin') {
                    $this->medecinModel->create($userId, [
                        'specialite' => $_POST['specialite'] ?? '',
                        'num_ordre'  => $_POST['num_ordre'] ?? null,
                    ]);
                }

                $_SESSION['flash'] = "Inscription réussie ! Connectez-vous.";
                header('Location: ' . APP_URL . '/login.php');
                exit;

            } catch (Exception $e) {
                $errors[] = "Erreur lors de l'inscription: " . $e->getMessage();
            }
        }

        $data = compact('errors', 'nom', 'prenom', 'email', 'role', 'telephone');
        include __DIR__ . '/../views/auth/register.php';
    }

    public function logout(): void {
        session_destroy();
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }

    public static function requireAuth(string ...$roles): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
        if (!empty($roles) && !in_array($_SESSION['user_role'], $roles)) {
            header('Location: ' . APP_URL . '/dashboard.php');
            exit;
        }
    }
}