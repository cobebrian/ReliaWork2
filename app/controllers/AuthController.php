<?php
/**
 * ReliaWork2 AuthController
 */

class AuthController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // GET / (public landing page)
    public function showLanding(): void
    {
        // If logged in, go to dashboard instead of landing
        if (isLoggedIn()) {
            redirect(roleDashboardUrl(currentUser()['role']));
        }
        // Render public landing page
        BedoController::landingPage();
    }

    // GET /login
    public function showLogin(): void
    {
        if (isLoggedIn()) {
            redirect(roleDashboardUrl(currentUser()['role']));
        }
        $pageTitle = 'Login';
        $error     = getFlash('error');
        $success   = getFlash('success');
        include VIEW_PATH . '/auth/login.php';
    }

    // POST /login
    public function login(): void
    {
        verifyCsrf();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            flash('error', 'Email and password are required.');
            redirect(APP_URL . '/login');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            flash('error', 'Invalid email or password.');
            redirect(APP_URL . '/login');
        }

        if (!verifyPassword($password, $user['password'])) {
            flash('error', 'Invalid email or password.');
            redirect(APP_URL . '/login');
        }

        if ($user['status'] === 'pending') {
            flash('error', 'Your account is awaiting admin approval. Please check back later.');
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            redirect(str_contains($referer, APP_URL . '/login') ? APP_URL . '/login' : APP_URL . '/');
        }

        if ($user['status'] === 'rejected') {
            flash('error', 'Your registration has been rejected. Please contact the administrator.');
            redirect(APP_URL . '/login');
        }

        if ($user['status'] !== 'approved') {
            flash('error', 'Your account is not active. Please contact the administrator.');
            redirect(APP_URL . '/login');
        }

        // Regenerate session ID on login
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user']    = [
            'id'         => $user['id'],
            'name'       => trim(($user['lastname'] ?? $user['name']) . ', ' . ($user['firstname'] ?? '')),
            'lastname'   => $user['lastname']   ?? '',
            'firstname'  => $user['firstname']  ?? '',
            'middlename' => $user['middlename'] ?? '',
            'email'      => $user['email'],
            'role'       => $user['role'],
            'status'     => $user['status'],
        ];

        auditLog('login', 'auth', "User {$user['email']} logged in.");

        redirect(roleDashboardUrl($user['role']));
    }

    // GET /register
    public function showRegister(): void
    {
        if (isLoggedIn()) {
            redirect(roleDashboardUrl(currentUser()['role']));
        }
        $pageTitle = 'Register';
        $error     = getFlash('error');
        $success   = getFlash('success');
        $old       = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        include VIEW_PATH . '/auth/register.php';
    }

    // POST /register
    public function register(): void
    {
        verifyCsrf();

        $lastname   = trim($_POST['lastname']   ?? '');
        $firstname  = trim($_POST['firstname']  ?? '');
        $middlename = trim($_POST['middlename'] ?? '');
        $email      = trim($_POST['email']      ?? '');
        $password   = $_POST['password']        ?? '';
        $confirm    = $_POST['confirm_password'] ?? '';

        // Validation
        $errors = [];
        if (empty($lastname))  $errors[] = 'Last name is required.';
        if (empty($firstname)) $errors[] = 'First name is required.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }
        if (strlen($password) < 8)              $errors[] = 'Password must be at least 8 characters.';
        if (!preg_match('/[A-Z]/', $password))  $errors[] = 'Password must contain at least one uppercase letter.';
        if (!preg_match('/[0-9]/', $password))  $errors[] = 'Password must contain at least one number.';
        if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = 'Password must contain at least one special character.';
        if ($password !== $confirm)             $errors[] = 'Passwords do not match.';

        if (!empty($errors)) {
            $_SESSION['old_input'] = [
                'lastname'   => $lastname,
                'firstname'  => $firstname,
                'middlename' => $middlename,
                'email'      => $email,
            ];
            flash('error', implode(' ', $errors));
            redirect(APP_URL . '/register');
        }

        if ($this->userModel->findByEmail($email)) {
            $_SESSION['old_input'] = [
                'lastname'   => $lastname,
                'firstname'  => $firstname,
                'middlename' => $middlename,
                'email'      => $email,
            ];
            flash('error', 'An account with that email already exists.');
            redirect(APP_URL . '/register');
        }

        $userId = $this->userModel->create([
            'lastname'   => $lastname,
            'firstname'  => $firstname,
            'middlename' => $middlename ?: null,
            'email'      => $email,
            'password'   => hashPassword($password),
            'role'       => null,
            'status'     => 'pending',
        ]);

        auditLog('register', 'auth', "New registration: {$email} ({$lastname}, {$firstname})");
        flash('success', 'Registration submitted successfully. Your account is awaiting admin approval.');
        redirect(APP_URL . '/login');
    }

    // GET /logout
    public function logout(): void
    {
        $email = currentUser()['email'] ?? 'unknown';
        auditLog('logout', 'auth', "User {$email} logged out.");

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();

        redirect(APP_URL . '/dashboard');
    }
}
