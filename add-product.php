<?php
session_start();
require_once __DIR__ . '/app/includes/database.php';
// Handle form submission
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $img = $_FILES['img_file'];
    $success = false;
    $uploadError = '';
    $uploadedImagePath = '';

    // Connect to DB
    $db = new \Aries\Dbmodel\Includes\Database();
    $pdo = $db->getConnection();

    // Handle image upload
    if ($img['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $targetDir = __DIR__ . "/products/{$category}/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $filename = uniqid('prod_', true) . '.' . $ext;
            $targetPath = $targetDir . $filename;
            if (move_uploaded_file($img['tmp_name'], $targetPath)) {
                $uploadedImagePath = "products/{$category}/$filename";
                // Insert or get category
                $pdo->beginTransaction();
                $catStmt = $pdo->prepare("SELECT id FROM product_categories WHERE category_name = ? LIMIT 1");
                $catStmt->execute([$category]);
                $catRow = $catStmt->fetch(PDO::FETCH_ASSOC);
                if ($catRow) {
                    $categoryId = $catRow['id'];
                } else {
                    $now = date('Y-m-d H:i:s');
                    $insertCat = $pdo->prepare("INSERT INTO product_categories (category_name, created_at, updated_at) VALUES (?, ?, ?)");
                    $insertCat->execute([$category, $now, $now]);
                    $categoryId = $pdo->lastInsertId();
                }
                // Insert product
                $now = date('Y-m-d H:i:s');
                $prodStmt = $pdo->prepare("INSERT INTO products (name, price, description, image, created_at, updated_at, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $prodStmt->execute([
                    $name,
                    $price,
                    $description,
                    $uploadedImagePath,
                    $now,
                    $now,
                    $categoryId
                ]);
                $pdo->commit();
                $success = true;
            } else {
                $uploadError = 'Failed to move uploaded file.';
            }
        } else {
            $uploadError = 'Invalid file type.';
        }
    } else {
        $uploadError = 'Image upload failed.';
    }
}
$categories = ['59fifty', '9seventy', '9fifty', '9twenty'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Solitary Caps Store</title>
    <link rel="stylesheet" href="style.css">
    <style>
    .add-product-container {
        max-width: 400px;
        margin: 3rem auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        padding: 2rem 2.5rem;
    }
    .add-product-container h2 {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .add-product-container label {
        display: block;
        margin-bottom: 0.3rem;
        font-weight: bold;
        color: #222;
    }
    .add-product-container input, .add-product-container select {
        width: 100%;
        padding: 0.5rem;
        margin-bottom: 1.2rem;
        border-radius: 4px;
        border: 1px solid #ddd;
        font-size: 1rem;
    }
    .add-product-container button {
        width: 100%;
        background: #18181b;
        color: #fff;
        border: none;
        padding: 0.7rem;
        border-radius: 4px;
        font-size: 1.1rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .add-product-container button:hover {
        background: #444;
    }
    .success-message {
        background: #e6ffed;
        color: #1a7f37;
        border: 1px solid #b7ebc6;
        padding: 0.7rem 1rem;
        border-radius: 4px;
        margin-bottom: 1.2rem;
        text-align: center;
    }
    </style>
</head>
<body>
    <div class="add-product-container">
        <h2>Add New Product</h2>
        <?php if (!empty($success)): ?>
            <div class="success-message">Product added successfully! Image uploaded to <code><?php echo htmlspecialchars($uploadedImagePath); ?></code></div>
        <?php elseif (!empty($uploadError)): ?>
            <div class="success-message" style="background:#ffeaea;color:#b71c1c;border:1px solid #f5c6cb;">Error: <?php echo htmlspecialchars($uploadError); ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label for="name">Product Name</label>
            <input type="text" name="name" id="name" required>

            <label for="price">Price (₱)</label>
            <input type="number" name="price" id="price" min="0" step="0.01" required>

            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3" required style="width:100%;margin-bottom:1.2rem;border-radius:4px;border:1px solid #ddd;font-size:1rem;padding:0.5rem;"></textarea>

            <label for="category">Category</label>
            <select name="category" id="category" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>"><?php echo strtoupper($cat); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="img">Product Image</label>
            <input type="file" name="img_file" id="img_file" accept="image/*" required>

            <button type="submit" name="add_product">Add Product</button>
        </form>
    </div>
</body>
</html>
