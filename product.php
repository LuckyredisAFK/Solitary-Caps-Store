<?php
session_start();
require_once __DIR__ . '/app/includes/database.php';

// Get product ID from query string
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$productId) {
    echo '<h2 style="text-align:center;margin-top:3rem;">Product not found.</h2>';
    exit;
}

// Fetch product from DB
$db = new \Aries\Dbmodel\Includes\Database();
$pdo = $db->getConnection();
$stmt = $pdo->prepare('SELECT p.id, p.name, p.price, p.description, p.image, c.category_name FROM products p JOIN product_categories c ON p.category_id = c.id WHERE p.id = ? LIMIT 1');
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo '<h2 style="text-align:center;margin-top:3rem;">Product not found.</h2>';
    exit;
}

// Cart token for CSRF
if (empty($_SESSION['cart_token'])) {
    $_SESSION['cart_token'] = bin2hex(random_bytes(16));
}

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $token = $_POST['cart_token'] ?? '';
    if (isset($_SESSION['cart_token']) && hash_equals($_SESSION['cart_token'], $token)) {
        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = 1;
        } else {
            $_SESSION['cart'][$productId]++;
        }
        $_SESSION['cart_token'] = bin2hex(random_bytes(16));
        header('Location: product.php?id=' . $productId . '&added=1');
        exit;
    }
}

// Cart count for navbar
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | Solitary Caps Store</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
    .product-detail-container {
        max-width: 900px;
        margin: 3rem auto;
        display: flex;
        gap: 2.5rem;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 2.5rem 2rem;
        align-items: flex-start;
    }
    .product-detail-image {
        flex: 0 0 340px;
        max-width: 340px;
        background: transparent; /* Make the container transparent */
        border-radius: 8px;
        padding: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 340px;
    }
    .product-detail-image img {
        width: 100%;
        max-height: 320px;
        object-fit: contain;
        border-radius: 6px;
        background: #fff;
    }
    .product-detail-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
    }
    .product-detail-info h2 {
        margin: 0 0 0.5rem 0;
        font-size: 2rem;
        font-weight: bold;
    }
    .product-detail-info .price {
        color: #18181b;
        font-size: 1.3rem;
        font-weight: bold;
        margin-bottom: 0.7rem;
    }
    .product-detail-info .desc {
        color: #444;
        font-size: 1.08rem;
        margin-bottom: 1.2rem;
    }
    .product-detail-info .category {
        color: #888;
        font-size: 0.98rem;
        margin-bottom: 1.2rem;
    }
    .product-detail-actions {
        display: flex;
        gap: 1.2rem;
        align-items: center;
        margin-top: 1.2rem;
    }
    .product-detail-actions button, .product-detail-actions a.order-btn {
        background: #18181b;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 0.7rem 2.2rem;
        font-size: 1.08rem;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .product-detail-actions button:hover, .product-detail-actions a.order-btn:hover {
        background: #e53935;
    }
    .added-msg {
        color: #388e3c;
        font-weight: bold;
        margin-bottom: 1rem;
    }
    .product-detail-container {
        max-width: 900px;
        margin: 3rem auto;
        display: flex;
        gap: 2.5rem;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 2.5rem 2rem;
        align-items: flex-start;
    }
    .product-detail-image {
        flex: 0 0 340px;
        max-width: 340px;
        background: transparent; /* Make the container transparent */
        border-radius: 8px;
        padding: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 340px;
    }
    .product-detail-image img {
        width: 100%;
        max-height: 320px;
        object-fit: contain;
        border-radius: 6px;
        background: #fff;
    }
    .product-detail-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
    }
    .product-detail-info h2 {
        margin: 0 0 0.5rem 0;
        font-size: 2rem;
        font-weight: bold;
    }
    .product-detail-info .price {
        color: #18181b;
        font-size: 1.3rem;
        font-weight: bold;
        margin-bottom: 0.7rem;
    }
    .product-detail-info .desc {
        color: #444;
        font-size: 1.08rem;
        margin-bottom: 1.2rem;
    }
    .product-detail-info .category {
        color: #888;
        font-size: 0.98rem;
        margin-bottom: 1.2rem;
    }
    .product-detail-actions {
        display: flex;
        gap: 1.2rem;
        align-items: center;
        margin-top: 1.2rem;
    }
    .product-detail-actions button, .product-detail-actions a.order-btn {
        background: #18181b;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 0.7rem 2.2rem;
        font-size: 1.08rem;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .product-detail-actions button:hover, .product-detail-actions a.order-btn:hover {
        background: #e53935;
    }
    .added-msg {
        color: #388e3c;
        font-weight: bold;
        margin-bottom: 1rem;
    }
    </style>
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
    <main>
        <div class="product-detail-container">
            <div class="product-detail-image">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="product-detail-info">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <div class="price">₱<?php echo number_format($product['price'], 2); ?></div>
                <div class="category">Category: <?php echo htmlspecialchars($product['category_name']); ?></div>
                <div class="desc"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
                <?php if (isset($_GET['added'])): ?>
                    <div class="added-msg">Added to cart!</div>
                <?php endif; ?>
                <div class="product-detail-actions">
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="cart_token" value="<?php echo $_SESSION['cart_token']; ?>">
                        <button type="submit" name="add_to_cart">Add to Cart</button>
                    </form>
                    <a href="payment.php?order_single=<?php echo $product['id']; ?>" class="order-btn">Order</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
