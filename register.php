<?php
session_start();
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

require_once 'vendor/autoload.php';
use Aries\Dbmodel\Models\User;

$registerSuccess = false;
$registerError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name && $email && $password) {
        $user = new User();
        $existing = $user->getUsers();
        $emailExists = false;
        foreach ($existing as $u) {
            if (strtolower($u['email']) === strtolower($email)) {
                $emailExists = true;
                break;
            }
        }
        if ($emailExists) {
            $registerError = 'Email already registered.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $user->createUser([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword
            ]);
            $registerSuccess = true;
            header('Location: login.php');
            exit();
        }
    } else {
        $registerError = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Solitary</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .register-container {
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            box-shadow: none;
            border-radius: 0;
            margin: 2rem auto;
            max-width: 420px;
        }
        .register-form {
            background: #fff;
            padding: 2rem 2rem 1.5rem 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 350px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .register-form h2 {
            color: #222;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .form-group {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.3rem;
            margin-bottom: 0.7rem;
        }
        .form-group label {
            width: 100%;
            text-align: left;
            margin-bottom: 0.2rem;
            color: #333;
        }
        .form-group input {
            width: 100%;
            box-sizing: border-box;
            padding: 0.6rem 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #fafafa;
            color: #222;
            font-size: 1rem;
        }
        .form-group input:focus {
            border: 1.5px solid #222;
            outline: none;
        }
        .register-btn {
            background: #222;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 0.7rem 0;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: background 0.2s;
        }
        .register-btn:hover {
            background: #444;
        }
        .register-login-link {
            text-align: center;
            font-size: 0.98rem;
            color: #666;
        }
        .register-login-link a {
            color: #222;
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .register-container {
                max-width: 98vw;
                margin: 1rem;
                border-radius: 10px;
            }
            .register-form {
                padding: 1rem 0.5rem 1rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php" class="brand" style="text-decoration: none;">Solitary</a>
        <div class="nav-links">
            <a href="#">Shop</a>
        </div>
    </div>
    <div class="register-container">
        <form class="register-form" method="post" action="#">
            <h2>Create Account</h2>
            <?php if ($registerError): ?>
                <div class="error-message"><?php echo $registerError; ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="name">Username</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="register-btn">Register</button>
            <p class="register-login-link">Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-brand">Solitary</div>
            <div class="footer-links">
                <a href="#">About</a>
                <a href="#">Contact</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms</a>
            </div>
            <div class="footer-social">
                <a href="#" class="social-icon" aria-label="Facebook" title="Facebook">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35C.595 0 0 .592 0 1.326v21.348C0 23.406.595 24 1.325 24h11.495v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.797.143v3.24l-1.918.001c-1.504 0-1.797.715-1.797 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116C23.406 24 24 23.406 24 22.674V1.326C24 .592 23.406 0 22.675 0"/></svg>
                </a>
                <a href="#" class="social-icon" aria-label="Instagram" title="Instagram">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.334 3.608 1.308.974.974 1.246 2.241 1.308 3.608.058 1.266.069 1.646.069 4.85s-.012 3.584-.07 4.85c-.062 1.366-.334 2.633-1.308 3.608-.974.974-2.241 1.246-3.608 1.308-1.266.058-1.646.069-4.85.069s-3.584-.012-4.85-.07c-1.366-.062-2.633-.334-3.608-1.308-.974-.974-1.246-2.241-1.308-3.608C2.175 15.647 2.163 15.267 2.163 12s.012-3.584.07-4.85c.062-1.366.334-2.633 1.308-3.608C4.515 2.567 5.782 2.295 7.148 2.233 8.414 2.175 8.794 2.163 12 2.163zm0-2.163C8.741 0 8.332.012 7.052.07 5.771.128 4.659.334 3.678 1.315c-.98.98-1.187 2.092-1.245 3.373C2.012 5.668 2 6.077 2 12c0 5.923.012 6.332.07 7.612.058 1.281.265 2.393 1.245 3.373.98.98 2.092 1.187 3.373 1.245C8.332 23.988 8.741 24 12 24s3.668-.012 4.948-.07c1.281-.058 2.393-.265 3.373-1.245.98-.98 1.187-2.092 1.245-3.373.058-1.28.07-1.689.07-7.612 0-5.923-.012-6.332-.07-7.612-.058-1.281-.265-2.393-1.245-3.373-.98-.98-2.092-1.187-3.373-1.245C15.668.012 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zm0 10.162a3.999 3.999 0 1 1 0-7.998 3.999 3.999 0 0 1 0 7.998zm7.2-11.162a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"/></svg>
                </a>
                <a href="#" class="social-icon" aria-label="X" title="X (Twitter)">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M17.53 2.477h3.934l-8.59 9.86 10.13 12.186h-7.97l-6.24-7.51-7.14 7.51H.52l9.17-10.53L0 2.477h8.13l5.7 6.86zm-1.13 17.01h2.18L6.47 4.36H4.17z"/></svg>
                </a>
                <a href="#" class="social-icon" aria-label="TikTok" title="TikTok">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12.75 2.001a1 1 0 0 1 1 1v13.25a2.25 2.25 0 1 1-2.25-2.25h.25a1 1 0 1 1 0 2h-.25a.25.25 0 1 0 .25.25V3.001a1 1 0 0 1 1-1zm6.5 0a1 1 0 0 1 1 1v2.25a5.25 5.25 0 0 1-5.25 5.25h-1.25v-2h1.25a3.25 3.25 0 0 0 3.25-3.25V3.001a1 1 0 0 1 1-1z"/></svg>
                </a>
            </div>
            <div class="footer-copy">©2025, Solitary, Designed by Vienz Dinero</div>
        </div>
    </footer>
</body>
</html>
