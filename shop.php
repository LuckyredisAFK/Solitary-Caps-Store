<?php
session_start();
// Simple cart logic (session-based)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
// Generate a unique token for each add-to-cart form
if (empty($_SESSION['cart_token'])) {
    $_SESSION['cart_token'] = bin2hex(random_bytes(16));
}
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $token = $_POST['cart_token'] ?? '';
    // Only process if token is valid and not already used
    if (isset($_SESSION['cart_token']) && hash_equals($_SESSION['cart_token'], $token)) {
        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = 1;
        } else {
            $_SESSION['cart'][$productId]++;
        }
        // Regenerate token after successful add to cart
        $_SESSION['cart_token'] = bin2hex(random_bytes(16));
        header('Location: shop.php');
        exit;
    }
}
// Handle cart quantity changes and checkout
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
    header('Location: shop.php?checkout=success');
    exit;
}
$cartCount = array_sum($_SESSION['cart']);
// Sample products with categories
$products = [
    // Empty for now, will be filled from add-product.php in the future
];
$categories = ['59fifty', '9seventy', '9fifty', '9twenty'];
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

require_once __DIR__ . '/app/includes/database.php';
// Fetch products from DB
$db = new \Aries\Dbmodel\Includes\Database();
$pdo = $db->getConnection();
$sql = "SELECT p.id, p.name, p.price, p.description, p.image, c.category_name FROM products p JOIN product_categories c ON p.category_id = c.id ORDER BY p.created_at DESC";
$stmt = $pdo->query($sql);
$products = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $products[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'description' => $row['description'],
        'img' => $row['image'],
        'category' => $row['category_name']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | Solitary Caps Store</title>
    <link rel="stylesheet" href="style.css">
    <style>
    .shop-products { display: flex; gap: 2rem; flex-wrap: wrap; justify-content: center; margin: 2rem 0; }
    .shop-product { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 1.5rem; width: 260px; text-align: center; }
    .shop-product img { width: 100%; height: 180px; object-fit: contain; margin-bottom: 1rem; }
    .shop-product h3 { margin: 0.5rem 0; font-size: 1.2rem; }
    .shop-product .price { color: #222; font-weight: bold; font-size: 1.1rem; }
    .shop-product form { margin-top: 1rem; }
    .shop-product button { background: #222; color: #fff; border: none; padding: 0.5rem 1.2rem; border-radius: 4px; cursor: pointer; transition: background 0.2s; }
    .shop-product button:hover { background: #444; }
    .banner-section {
        position: relative;
        width: 100%;
        height: 400px;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .banner-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }
    .banner-text {
        position: relative;
        z-index: 2;
        color: #fff;
        text-align: center;
        padding: 1rem;
    }
    .banner-text h1 {
        font-size: 2.5rem;
        margin: 0;
    }
    .banner-text p {
        font-size: 1.2rem;
        margin: 0.5rem 0 0;
    }
    .banner-dots {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
    }
    .dot {
        display: inline-block;
        width: 7px;
        height: 7px;
        margin: 0 4px;
        background: #fff;
        border-radius: 50%;
        cursor: pointer;
        transition: background 0.3s;
    }
    .dot.active {
        background: #18181b;
    }
    .shop-layout {
        display: flex;
        align-items: stretch;
        gap: 2rem;
        max-width: 1200px;
        margin: 2rem auto;
    }
    .category-sidebar {
        min-width: 180px;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }
    .category-sidebar form {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        padding: 1.2rem;
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
        height: 100%;
    }
    .category-sidebar label {
        font-weight: bold;
        color: #222;
    }
    .category-sidebar select {
        width: 100%;
        padding: 0.4rem;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    .shop-products {
        flex: 1;
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: flex-start;
    }
    .view-btn {
        display: inline-block;
        background: #f3f3f3;
        color: #222;
        border-radius: 4px;
        padding: 0.4rem 1.2rem;
        text-decoration: none;
        font-size: 0.97em;
        transition: background 0.2s, color 0.2s;
        margin-bottom: 0.2rem;
    }
    .view-btn:hover {
        background: #18181b;
        color: #fff;
    }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php" class="brand" style="text-decoration: none;">Solitary</a>
        <div class="nav-links">
            <a href="shop.php">Shop</a>
            <a href="#" id="cart-link">Cart (<?php echo $cartCount; ?>)</a>
        </div>
    </div>
    <!-- Cart Modal (copied and adapted from index.php) -->
    <div id="cart-modal" class="cart-modal">
        <div class="cart-modal-content">
            <span class="close" id="close-cart">&times;</span>
            <h2>Your Cart</h2>
            <?php if (!empty($_SESSION['cart'])): ?>
                <form method="post" id="cart-update-form">
                    <ul style="list-style:none;padding:0;">
                    <?php
                    $total = 0;
                    // Fetch product details for real images and names
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
        // Prevent modal close when clicking inside the cart modal content
        if (cartModal) {
            document.querySelector('.cart-modal-content').addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        // Prevent modal close when clicking inside the form
        var cartForm = document.getElementById('cart-update-form');
        if (cartForm) {
            cartForm.addEventListener('click', function(e) {
                if (e.target.name === 'cart_add' || e.target.name === 'cart_minus') {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent modal from closing
                    const formData = new FormData(cartForm);
                    formData.append(e.target.name, e.target.value);
                    fetch('shop.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text())
                    .then(html => {
                        // Replace cart modal content only
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newCart = doc.querySelector('.cart-modal-content');
                        if (newCart) {
                            document.querySelector('.cart-modal-content').innerHTML = newCart.innerHTML;
                            attachCartModalClose(); // Re-attach close event
                        }
                    });
                }
            });
        }

        // AJAX for Add to Cart
        document.querySelectorAll('.add-to-cart-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Prevent parent div onclick
                const formData = new FormData(form);
                formData.append('add_to_cart', '1'); // Ensure PHP handler is triggered
                fetch('shop.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(html => {
                    // Update cart count in navbar
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newCartLink = doc.getElementById('cart-link');
                    if (newCartLink) {
                        document.getElementById('cart-link').innerHTML = newCartLink.innerHTML;
                    }
                    // Update cart modal content as well
                    const newCartModalContent = doc.querySelector('.cart-modal-content');
                    if (newCartModalContent) {
                        document.querySelector('.cart-modal-content').innerHTML = newCartModalContent.innerHTML;
                        attachCartModalClose(); // Re-attach close event
                    }
                    // Update all cart_token fields in add-to-cart forms
                    const newTokenInput = doc.querySelector('.add-to-cart-form input[name="cart_token"]');
                    if (newTokenInput) {
                        const newToken = newTokenInput.value;
                        document.querySelectorAll('.add-to-cart-form input[name="cart_token"]').forEach(function(input) {
                            input.value = newToken;
                        });
                    }
                    // Optionally, show a quick feedback (e.g., flash message or animation)
                });
            });
        });

        function attachCartModalClose() {
            var closeCart = document.getElementById('close-cart');
            var cartModal = document.getElementById('cart-modal');
            if (closeCart && cartModal) {
                closeCart.onclick = function() {
                    cartModal.classList.remove('open');
                };
            }
        }
    });
    </script>
    <main>
        <div class="shop-layout">
            <aside class="category-sidebar">
                <form method="get">
                    <label for="category">Category</label>
                    <select name="category" id="category" onchange="this.form.submit()">
                        <option value="">All</option>
                        <?php foreach (
                            $categories as $cat): ?>
                            <option value="<?php echo $cat; ?>"<?php if ($selectedCategory === $cat) echo ' selected'; ?>><?php echo strtoupper($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </aside>
            <div class="shop-products">
                <?php if (empty($products)): ?>
                    <div style="text-align:center;width:100%;color:#888;">No products found.</div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <?php if ($selectedCategory && $product['category'] !== $selectedCategory) continue; ?>
                        <div class="shop-product">
                            <img src="<?php echo htmlspecialchars($product['img']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="price">₱<?php echo number_format($product['price'], 2); ?></div>
                            <form class="add-to-cart-form" method="post" style="margin-bottom: 0.5rem;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="cart_token" value="<?php echo $_SESSION['cart_token']; ?>">
                                <button type="submit" name="add_to_cart">Add to Cart</button>
                            </form>
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="view-btn">View Details</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
