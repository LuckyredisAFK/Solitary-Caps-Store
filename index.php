<?php

require_once 'vendor/autoload.php';

use Aries\Dbmodel\Models\User;

$user = new User();

session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (empty($_SESSION['cart_token'])) {
    $_SESSION['cart_token'] = bin2hex(random_bytes(16));
}
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $token = $_POST['cart_token'] ?? '';
    if (isset($_SESSION['cart_token']) && hash_equals($_SESSION['cart_token'], $token)) {
        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = 1;
        } else {
            $_SESSION['cart'][$productId]++;
        }
        $_SESSION['cart_token'] = bin2hex(random_bytes(16));
        header('Location: index.php');
        exit;
    }
}
if (isset($_POST['cart_add'])) {
    $pid = $_POST['cart_add'];
    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid]++;
    }
}
if (isset($_POST['cart_minus'])) {
    $pid = $_POST['cart_minus'];
    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid]--;
        if ($_SESSION['cart'][$pid] <= 0) {
            unset($_SESSION['cart'][$pid]);
        }
    }
}
if (isset($_POST['checkout'])) {
    $_SESSION['cart'] = [];
    header('Location: index.php?checkout=success');
    exit;
}
$cartCount = array_sum($_SESSION['cart']);

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    if (empty($_SESSION['username']) || empty($_SESSION['role'])) {
        require_once __DIR__ . '/app/includes/database.php';
        $db = new \Aries\Dbmodel\Includes\Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare('SELECT name, role FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $_SESSION['username'] = $row['name'] ?? 'User';
            $_SESSION['role'] = $row['role'] ?? 'user';
        } else {
            $_SESSION['username'] = 'User';
            $_SESSION['role'] = 'user';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solitary | Caps Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php" class="brand" style="text-decoration: none;">Solitary</a>
        <div class="nav-links">
            <a href="shop.php">Shop</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="nav-link-btn" style="cursor:default;"> <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?> </span>
                <form method="post" style="display:inline;margin:0;padding:0;">
                    <button type="submit" name="logout" class="nav-link-btn">Logout</button>
                </form>
                <a href="#" id="cart-link">Cart (<?php echo $cartCount; ?>)</a>
            <?php else: ?>
                <a href="register.php" id="cart-link">Cart (<?php echo $cartCount; ?>)</a>
            <?php endif; ?>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var username = document.getElementById('user-dropdown-username');
        var menu = document.getElementById('user-dropdown-menu');
        if(username && menu) {
            username.addEventListener('click', function(e) {
                e.preventDefault();
                menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
            });
            document.addEventListener('click', function(e) {
                if (!username.contains(e.target) && !menu.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });
        }
    });
    </script>
    <div id="cart-modal" class="cart-modal">
        <div class="cart-modal-content">
            <span class="close" id="close-cart">&times;</span>
            <h2>Your Cart</h2>
            <?php if (!empty($_SESSION['cart'])): ?>
                <form method="post" id="cart-update-form">
                    <ul style="list-style:none;padding:0;">
                    <?php
                    $total = 0;
                    require_once __DIR__ . '/app/includes/database.php';
                    $db = new \Aries\Dbmodel\Includes\Database();
                    $pdo = $db->getConnection();
                    $productIds = array_keys($_SESSION['cart']);
                    $productsMap = [];
                    if ($productIds) {
                        $in = str_repeat('?,', count($productIds) - 1) . '?';
                        $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id IN ($in)");
                        $stmt->execute($productIds);
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $productsMap[$row['id']] = $row;
                        }
                    }
                    foreach ($_SESSION['cart'] as $productId => $qty):
                        $product = isset($productsMap[$productId]) ? $productsMap[$productId] : null;
                        if (!$product) continue;
                        $subtotal = $product['price'] * $qty;
                        $total += $subtotal;
                    ?>
                        <li style="display:flex;align-items:center;gap:1rem;margin-bottom:1.2rem;">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width:60px;height:60px;object-fit:contain;border-radius:0;background:transparent;border:none;">
                            <div style="flex:1;">
                                <div style="font-weight:bold;"> <?php echo htmlspecialchars($product['name']); ?> </div>
                                <div style="color:#555;">₱<?php echo number_format($product['price'],2); ?></div>
                            </div>
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <button type="submit" name="cart_minus" value="<?php echo $productId; ?>" style="width:28px;height:28px;font-size:1.2em;">-</button>
                                <span style="min-width:24px;display:inline-block;text-align:center;"> <?php echo $qty; ?> </span>
                                <button type="submit" name="cart_add" value="<?php echo $productId; ?>" style="width:28px;height:28px;font-size:1.2em;">+</button>
                            </div>
                            <div style="width:80px;text-align:right;">₱<?php echo number_format($subtotal,2); ?></div>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                    <div style="text-align:right;font-weight:bold;font-size:1.1em;margin-bottom:1.2rem;">Total: ₱<?php echo number_format($total,2); ?></div>
                    <button type="submit" name="checkout" style="width:100%;background:#18181b;color:#fff;padding:0.7rem 0;font-size:1.1em;border-radius:4px;border:none;">Checkout</button>
                </form>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>
    </div>
    <style>
    .cart-modal {
        position: fixed;
        z-index: 1000;
        left: 0; top: 0; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.4);
        display: none;
        pointer-events: none;
    }
    .cart-modal.open {
        display: block;
        pointer-events: auto;
    }
    .cart-modal-content {
        background: #fff; padding: 2rem; border-radius: 8px; min-width: 320px; position: fixed;
        top: 0; right: 0; height: 100vh; max-width: 90vw;
        box-shadow: -2px 0 16px rgba(0,0,0,0.2);
        transform: translateX(100%);
        transition: transform 0.45s cubic-bezier(.4,0,.2,1);
        pointer-events: auto;
        overflow-y: auto;
        will-change: transform;
    }
    .cart-modal.open .cart-modal-content {
        transform: translateX(0);
    }
    .cart-modal .close {
        position: absolute; top: 10px; right: 16px; font-size: 1.5rem; cursor: pointer;
    }
    .nav-links a {
        color: #fff;
        text-decoration: none;
        font-weight: bold;
        letter-spacing: 1px;
        transition: color 0.2s;
    }
    .nav-links a:hover {
        color: #e53935;
    }
    .nav-link-btn {
        color: #fff;
        background: none;
        border: none;
        font: inherit;
        font-weight: bold;
        letter-spacing: 1px;
        cursor: pointer;
        padding: 0 0.7em;
        transition: color 0.2s;
        text-decoration: none;
        vertical-align: middle;
        display: inline-block;
    }
    .nav-link-btn:hover {
        color: #e53935;
    }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var cartLink = document.getElementById('cart-link');
        var cartModal = document.getElementById('cart-modal');
        var closeCart = document.getElementById('close-cart');
        if(cartLink && cartModal && closeCart) {
            cartLink.addEventListener('click', function(e) {
                e.preventDefault();
                cartModal.classList.add('open');
            });
            closeCart.addEventListener('click', function() {
                cartModal.classList.remove('open');
            });
            window.addEventListener('click', function(e) {
                if (e.target === cartModal) {
                    cartModal.classList.remove('open');
                }
            });
        }
        if (cartModal) {
            document.querySelector('.cart-modal-content').addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        var cartForm = document.getElementById('cart-update-form');
        if (cartForm) {
            cartForm.addEventListener('click', function(e) {
                if (e.target.name === 'cart_add' || e.target.name === 'cart_minus') {
                    e.preventDefault();
                    e.stopPropagation();
                    const formData = new FormData(cartForm);
                    formData.append(e.target.name, e.target.value);
                    fetch('index.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newCart = doc.querySelector('.cart-modal-content');
                        if (newCart) {
                            document.querySelector('.cart-modal-content').innerHTML = newCart.innerHTML;
                        }
                    });
                }
            });
        }
    });
    </script>
    <div class="banner-section" id="banner-section">
        <div class="banner-overlay"></div>
        <div class="banner-text">
            <h1>Solitary</h1>
            <p>Your destination for the latest and greatest caps</p>
        </div>
        <div class="banner-dots" id="banner-dots"></div>
    </div>
    <div class="collection-featured-image">
        <img src="collection-images/1.png" alt="Collection 1">
    </div>
    <div class="collection-featured-image">
        <img src="collection-images/2.jpg" alt="Collection 2">
    </div>
    <div class="collection-featured-image">
        <img src="collection-images/3.jpg" alt="Collection 3">
    </div>
    <section class="featured-products">
        <h2>New Arrivals</h2>
        <div class="products">
            <div class="product">
                <img src="new-arrivals-images/1.png" alt="Classic Black Cap">
                <h3>NewEra White NewYork Yankees Fitted</h3>
                <span class="price">₱2899</span>
                <a href="#" class="view-btn">View Details</a>
            </div>
            <div class="product">
                <img src="new-arrivals-images/2.png" alt="Red Snapback">
                <h3>NewEra CherryRed NewYork Yankees Fitted</h3>
                <span class="price">₱2599</span>
                <a href="#" class="view-btn">View Details</a>
            </div>
            <div class="product">
                <img src="new-arrivals-images/3.png" alt="Blue Fitted Cap">
                <h3>NewEra Black NewYork Yankees Fitted</h3>
                <span class="price">₱1199</span>
                <a href="#" class="view-btn">View Details</a>
            </div>
        </div>
    </section>
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
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.334 3.608 1.308.974.974 1.246 2.241 1.308 3.608.058 1.266.069 1.646.069 4.85s-.012 3.584-.07 4.85c-.062 1.366-.334 2.633-1.308 3.608-.974.974-2.241 1.246-3.608 1.308-1.266.058-1.646.069-4.85.069s-3.584-.012-4.85-.07c-1.281-.058-2.393-.265-3.373-1.245-.98-.98-1.187-2.092-1.245-3.373C2.012 5.668 2 6.077 2 12c0 5.923.012 6.332.07 7.612.058 1.281.265 2.393 1.245 3.373.98.98 2.092 1.187 3.373 1.245C8.332 23.988 8.741 24 12 24s3.668-.012 4.948-.07c1.281-.058 2.393-.265 3.373-1.245.98-.98 1.187-2.092 1.245-3.373.058-1.28.07-1.689.07-7.612 0-5.923-.012-6.332-.07-7.612-.058-1.281-.265-2.393-1.245-3.373-.98-.98-2.092-1.187-3.373-1.245C15.668.012 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zm0 10.162a3.999 3.999 0 1 1 0-7.998 3.999 3.999 0 0 1 0 7.998zm7.2-11.162a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z"/></svg>
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
    <script>
    const images = [
        'banner-images/3.jpg',
        'banner-images/4.jpg',
        'banner-images/5.jpg',
        'banner-images/6.jpg',
        'banner-images/7.jpg'
    ];
    let current = 0;
    const bannerSection = document.getElementById('banner-section');
    const bannerDots = document.getElementById('banner-dots');
    let intervalId;
    function setBannerBg(idx) {
        bannerSection.style.backgroundImage = `url('${images[idx]}')`;
        bannerSection.style.backgroundSize = 'cover';
        bannerSection.style.backgroundPosition = 'center';
        bannerSection.style.transition = 'background-image 0.7s ease-in-out';
        updateDots(idx);
    }
    function showAt(idx) {
        current = idx;
        setBannerBg(current);
        resetInterval();
    }
    function updateDots(activeIdx) {
        bannerDots.innerHTML = images.map((_, i) =>
            `<span class="dot${i === activeIdx ? ' active' : ''}" data-idx="${i}"></span>`
        ).join('');
        document.querySelectorAll('.dot').forEach(dot => {
            dot.onclick = () => showAt(Number(dot.dataset.idx));
        });
    }
    function showNext() {
        current = (current + 1) % images.length;
        setBannerBg(current);
    }
    function resetInterval() {
        clearInterval(intervalId);
        intervalId = setInterval(showNext, 3500);
    }
    setBannerBg(current);
    intervalId = setInterval(showNext, 3500);
    </script>
</body>
</html>