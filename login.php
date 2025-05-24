<?php
session_start();
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

require_once 'vendor/autoload.php';
use Aries\Dbmodel\Models\User;

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $user = new User();
        $users = $user->getUsers();
        $found = false;
        foreach ($users as $u) {
            if (strtolower($u['email']) === strtolower($email) && password_verify($password, $u['password'])) {
                $_SESSION['user_id'] = $u['id'];
                $_SESSION['user_name'] = $u['name'];
                $found = true;
                header('Location: index.php');
                exit();
            }
        }
        if (!$found) {
            $loginError = 'Invalid email or password.';
        }
    } else {
        $loginError = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Solitary</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container {
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
        .login-form {
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
        .login-form h2 {
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
        .login-btn {
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
        .login-btn:hover {
            background: #444;
        }
        .login-register-link {
            text-align: center;
            font-size: 0.98rem;
            color: #666;
        }
        .login-register-link a {
            color: #222;
            text-decoration: underline;
        }
        .error-message {
            color: #e53935;
            background: #fffbe7;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            margin-bottom: 0.7rem;
            text-align: center;
            font-size: 0.98rem;
        }
        @media (max-width: 600px) {
            .login-container {
                max-width: 98vw;
                margin: 1rem;
                border-radius: 10px;
            }
            .login-form {
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
    <div class="login-container">
        <form class="login-form" method="post" action="#">
            <h2>Login</h2>
            <?php if ($loginError): ?>
                <div class="error-message"><?php echo $loginError; ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
            <p class="login-register-link">Don't have an account? <a href="register.php">Register</a></p>
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
                </a>
                <a href="#" class="social-icon" aria-label="Instagram" title="Instagram">
                </a>
                <a href="#" class="social-icon" aria-label="X" title="X (Twitter)">
                </a>
                <a href="#" class="social-icon" aria-label="TikTok" title="TikTok">
                </a>
            </div>
            <div class="footer-copy">©2025, Solitary, Designed by Vienz Dinero</div>
        </div>
    </footer>
</body>
</html>
