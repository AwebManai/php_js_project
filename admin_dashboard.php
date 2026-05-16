<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php?message=' . urlencode('Please sign in as admin.'));
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "stylestore";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die('Database connection failed.');
}

$conn->set_charset("utf8");

$statusMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_product') {
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $imageUrl = trim($_POST['image_url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $image = '';

        // Handle image upload or URL
        if (!empty($_FILES['image_upload']['name'])) {
            // File upload
            $uploadDir = __DIR__ . '/uploads/';
            $fileName = basename($_FILES['image_upload']['name']);
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array(strtolower($fileExt), $allowedExts) && $_FILES['image_upload']['size'] <= 5242880) { // 5MB max
                $uniqueName = uniqid() . '.' . $fileExt;
                $uploadPath = $uploadDir . $uniqueName;
                
                if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $uploadPath)) {
                    $image = 'uploads/' . $uniqueName;
                } else {
                    $statusMessage = 'Failed to upload image file.';
                    $image = '';
                }
            } else {
                $statusMessage = 'Invalid image file. Only JPG, PNG, GIF, WebP allowed (max 5MB).';
                $image = '';
            }
        } elseif (!empty($imageUrl)) {
            // URL provided
            $image = $imageUrl;
        }

        if ($name === '' || $price <= 0) {
            $statusMessage = 'Product name and a valid price are required.';
        } elseif (empty($image)) {
            $statusMessage = 'Please provide either an image file or image URL.';
        } else {
            $stmt = $conn->prepare('INSERT INTO product (name, price, image, description, category, stock) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sdsssi', $name, $price, $image, $description, $category, $stock);
            if ($stmt->execute()) {
                $statusMessage = 'Product added successfully.';
            } else {
                $statusMessage = 'Failed to add product.';
            }
            $stmt->close();
        }
    }

    if ($action === 'update_stock') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);

        if ($productId <= 0 || $stock < 0) {
            $statusMessage = 'Invalid product or stock value.';
        } else {
            $stmt = $conn->prepare('UPDATE product SET stock = ? WHERE id = ?');
            $stmt->bind_param('ii', $stock, $productId);
            if ($stmt->execute()) {
                $statusMessage = 'Stock updated.';
            } else {
                $statusMessage = 'Failed to update stock.';
            }
            $stmt->close();
        }
    }

    if ($action === 'delete_product') {
        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId > 0) {
            $stmt = $conn->prepare('DELETE FROM product WHERE id = ?');
            $stmt->bind_param('i', $productId);
            if ($stmt->execute()) {
                $statusMessage = 'Product deleted.';
            } else {
                $statusMessage = 'Failed to delete product.';
            }
            $stmt->close();
        }
    }

    if ($action === 'delete_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId > 0) {
            $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
            $stmt->bind_param('i', $userId);
            if ($stmt->execute()) {
                $statusMessage = 'User deleted.';
            } else {
                $statusMessage = 'Failed to delete user.';
            }
            $stmt->close();
        }
    }
}

$users = [];
$products = [];

$userResult = $conn->query('SELECT id, firstname, lastname, email, created_at FROM users ORDER BY id DESC');
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
}

$productResult = $conn->query('SELECT id, name, price, stock, category FROM product ORDER BY id DESC');
if ($productResult) {
    while ($row = $productResult->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - StyleStore</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            color: #111827;
        }

        .topbar {
            background: #111827;
            color: #ffffff;
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .topbar a {
            color: #ffffff;
            text-decoration: none;
            border: 1px solid #374151;
            padding: 7px 10px;
            border-radius: 8px;
            font-size: 14px;
        }

        .wrap {
            max-width: 1100px;
            margin: 20px auto;
            padding: 0 14px 24px;
        }

        .msg {
            background: #ecfeff;
            border: 1px solid #a5f3fc;
            color: #155e75;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 14px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px;
            overflow-x: auto;
        }

        .card h2 {
            margin-top: 0;
            font-size: 1.1rem;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 8px;
        }

        input, textarea {
            width: 100%;
            padding: 9px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
        }

        button {
            border: none;
            background: #1d4ed8;
            color: #ffffff;
            border-radius: 8px;
            padding: 8px 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .danger {
            background: #dc2626;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 520px;
        }

        th, td {
            text-align: left;
            padding: 8px 6px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }

        th {
            color: #4b5563;
            font-weight: 600;
        }

        .inline {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .small {
            width: 90px;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div>
            <strong>Admin Dashboard</strong>
            <span style="margin-left: 10px; font-size: 14px; color: #d1d5db;">Signed in as <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
        </div>
        <div class="inline">
            <a href="index.php">Back to Shop</a>
            <a href="api/admin_logout.php">Sign Out</a>
        </div>
    </div>

    <div class="wrap">
        <?php if (!empty($statusMessage)): ?>
            <div class="msg"><?php echo htmlspecialchars($statusMessage); ?></div>
        <?php endif; ?>

        <div class="grid">
            <section class="card">
                <h2>Add Product</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_product" />
                    <div class="row">
                        <input type="text" name="name" placeholder="Product name" required />
                        <input type="number" name="price" step="0.01" min="0.01" placeholder="Price" required />
                    </div>
                    <div class="row">
                        <input type="number" name="stock" min="0" placeholder="Stock" value="0" />
                        <input type="text" name="category" placeholder="Category" />
                    </div>
                    <div class="row">
                        <input type="file" name="image_upload" accept="image/*" />
                        <input type="text" name="image_url" placeholder="Or enter image URL" />
                    </div>
                    <div class="row">
                        <input type="text" name="description" placeholder="Short description" />
                    </div>
                    <button type="submit">Add Product</button>
                </form>
            </section>

            <section class="card">
                <h2>Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo (int)$user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Delete this user?');">
                                        <input type="hidden" name="action" value="delete_user" />
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>" />
                                        <button type="submit" class="danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="card" style="grid-column: 1 / -1;">
                <h2>Products</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo (int)$product['id']; ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category'] ?? ''); ?></td>
                                <td>$<?php echo number_format((float)$product['price'], 2); ?></td>
                                <td><?php echo (int)$product['stock']; ?></td>
                                <td>
                                    <div class="inline">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="update_stock" />
                                            <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>" />
                                            <input type="number" class="small" name="stock" min="0" value="<?php echo (int)$product['stock']; ?>" required />
                                            <button type="submit">Update Stock</button>
                                        </form>
                                        <form method="POST" onsubmit="return confirm('Delete this product?');">
                                            <input type="hidden" name="action" value="delete_product" />
                                            <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>" />
                                            <button type="submit" class="danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</body>
</html>
