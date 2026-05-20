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
$statusType = 'success'; // success or error

// Get current tab from URL
$currentTab = $_GET['tab'] ?? 'users';

// Handle User Management Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentTab === 'users') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $firstname = trim($_POST['firstname'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($userId > 0 && !empty($firstname) && !empty($lastname) && !empty($email)) {
            $stmt = $conn->prepare('UPDATE users SET firstname = ?, lastname = ?, email = ?, phone = ?, address = ? WHERE id = ?');
            $stmt->bind_param('sssssi', $firstname, $lastname, $email, $phone, $address, $userId);
            if ($stmt->execute()) {
                $statusMessage = 'User updated successfully.';
                $statusType = 'success';
            } else {
                $statusMessage = 'Failed to update user.';
                $statusType = 'error';
            }
            $stmt->close();
        } else {
            $statusMessage = 'Invalid user data.';
            $statusType = 'error';
        }
    }

    if ($action === 'delete_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId > 0) {
            $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
            $stmt->bind_param('i', $userId);
            if ($stmt->execute()) {
                $statusMessage = 'User deleted successfully.';
                $statusType = 'success';
            } else {
                $statusMessage = 'Failed to delete user.';
                $statusType = 'error';
            }
            $stmt->close();
        }
    }
}

// Handle Product Management Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentTab === 'products') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_product') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');

        if ($productId > 0 && !empty($name) && $price > 0) {
            $stmt = $conn->prepare('UPDATE product SET name = ?, price = ?, stock = ?, category = ?, description = ?, image = ? WHERE id = ?');
            $stmt->bind_param('sdiissi', $name, $price, $stock, $category, $description, $image, $productId);
            if ($stmt->execute()) {
                $statusMessage = 'Product updated successfully.';
                $statusType = 'success';
            } else {
                $statusMessage = 'Failed to update product.';
                $statusType = 'error';
            }
            $stmt->close();
        } else {
            $statusMessage = 'Invalid product data.';
            $statusType = 'error';
        }
    }

    if ($action === 'delete_product') {
        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId > 0) {
            $stmt = $conn->prepare('DELETE FROM product WHERE id = ?');
            $stmt->bind_param('i', $productId);
            if ($stmt->execute()) {
                $statusMessage = 'Product deleted successfully.';
                $statusType = 'success';
            } else {
                $statusMessage = 'Failed to delete product.';
                $statusType = 'error';
            }
            $stmt->close();
        }
    }
}

// Get data
$users = [];
$products = [];
$userDetail = null;
$productDetail = null;

// For users tab
if ($currentTab === 'users') {
    $userId = $_GET['user_id'] ?? null;
    if ($userId) {
        $stmt = $conn->prepare('SELECT id, firstname, lastname, email, phone, address, created_at FROM users WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $userDetail = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    $userResult = $conn->query('SELECT id, firstname, lastname, email, phone, created_at FROM users ORDER BY id DESC');
    if ($userResult) {
        while ($row = $userResult->fetch_assoc()) {
            $users[] = $row;
        }
    }
}

// For products tab
if ($currentTab === 'products') {
    $productId = $_GET['product_id'] ?? null;
    if ($productId) {
        $stmt = $conn->prepare('SELECT id, name, price, stock, category, description, image, created_at FROM product WHERE id = ?');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $productDetail = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    $productResult = $conn->query('SELECT id, name, price, stock, category, image FROM product ORDER BY id DESC');
    if ($productResult) {
        while ($row = $productResult->fetch_assoc()) {
            $products[] = $row;
        }
    }
}

// Get user statistics
$userStats = [];
$statsResult = $conn->query('SELECT COUNT(*) as total FROM users');
if ($statsResult) {
    $userStats = $statsResult->fetch_assoc();
}

// Get product statistics
$productStats = [];
$statsResult = $conn->query('SELECT COUNT(*) as total, SUM(stock) as total_stock FROM product');
if ($statsResult) {
    $productStats = $statsResult->fetch_assoc();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Management - StyleStore</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f3f4f6;
            color: #111827;
        }

        .topbar {
            background: #111827;
            color: #ffffff;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topbar h1 {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .topbar-user {
            font-size: 14px;
            color: #d1d5db;
        }

        .topbar-links {
            display: flex;
            gap: 12px;
        }

        .topbar a {
            color: #ffffff;
            text-decoration: none;
            border: 1px solid #374151;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .topbar a:hover {
            background: #1f2937;
            border-color: #4b5563;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        .msg {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .msg.success {
            background: #ecfeff;
            border: 1px solid #a5f3fc;
            color: #155e75;
        }

        .msg.error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }

        .tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            border-bottom: 2px solid #e5e7eb;
        }

        .tab-btn {
            padding: 12px 20px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            color: #6b7280;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            margin-bottom: -2px;
        }

        .tab-btn.active {
            color: #1d4ed8;
            border-bottom-color: #1d4ed8;
        }

        .tab-btn:hover {
            color: #111827;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1d4ed8;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 24px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .card h2 {
            margin-bottom: 16px;
            font-size: 1.2rem;
            color: #111827;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            border-bottom: 2px solid #e5e7eb;
        }

        th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            color: #4b5563;
            font-size: 14px;
        }

        td {
            padding: 12px 8px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }

        tr:hover {
            background: #f9fafb;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .row.full {
            grid-template-columns: 1fr;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        button {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #1d4ed8;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #1e40af;
        }

        .btn-secondary {
            background: #6b7280;
            color: #ffffff;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-danger {
            background: #dc2626;
            color: #ffffff;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-success {
            background: #16a34a;
            color: #ffffff;
        }

        .btn-success:hover {
            background: #15803d;
        }

        .btn-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .link-btn {
            background: none;
            border: none;
            color: #1d4ed8;
            cursor: pointer;
            text-decoration: underline;
            padding: 0;
            font-size: 14px;
        }

        .link-btn:hover {
            color: #1e40af;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-primary {
            background: #dbeafe;
            color: #1e40af;
        }

        .form-section {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .btn-back {
            background: #6b7280;
            color: #ffffff;
            margin-bottom: 16px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #6b7280;
        }

        .detail-value {
            color: #111827;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .row {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar-left {
                width: 100%;
            }

            .topbar-links {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-left">
            <h1>Admin Management</h1>
            <div class="topbar-user">Signed in as <?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
        </div>
        <div class="topbar-links">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="index.php">Back to Shop</a>
            <a href="api/admin_logout.php">Sign Out</a>
        </div>
    </div>

    <div class="container">
        <?php if (!empty($statusMessage)): ?>
            <div class="msg <?php echo $statusType; ?>">
                <span><?php echo htmlspecialchars($statusMessage); ?></span>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-btn <?php echo $currentTab === 'users' ? 'active' : ''; ?>" onclick="switchTab('users')">Manage Users</button>
            <button class="tab-btn <?php echo $currentTab === 'products' ? 'active' : ''; ?>" onclick="switchTab('products')">Manage Products</button>
            <button class="tab-btn <?php echo $currentTab === 'coupons' ? 'active' : ''; ?>" onclick="switchTab('coupons')">Manage Coupons</button>
        </div>

        <!-- USERS TAB -->
        <div id="users" class="tab-content <?php echo $currentTab === 'users' ? 'active' : ''; ?>">
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value"><?php echo $userStats['total'] ?? 0; ?></div>
                </div>
            </div>

            <?php if ($userDetail): ?>
                <!-- User Detail View -->
                <div class="grid">
                    <div></div>
                    <div class="card">
                        <button class="btn-back btn-secondary" onclick="location.href='admin_manage.php?tab=users'">← Back to Users List</button>

                        <h2>Edit User</h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_user" />
                            <input type="hidden" name="user_id" value="<?php echo (int)$userDetail['id']; ?>" />

                            <div class="form-section">
                                <label class="form-label">User ID</label>
                                <input type="text" value="<?php echo (int)$userDetail['id']; ?>" disabled />
                            </div>

                            <div class="row">
                                <div class="form-section">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" name="firstname" value="<?php echo htmlspecialchars($userDetail['firstname']); ?>" required />
                                </div>
                                <div class="form-section">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" name="lastname" value="<?php echo htmlspecialchars($userDetail['lastname']); ?>" required />
                                </div>
                            </div>

                            <div class="form-section">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($userDetail['email']); ?>" required />
                            </div>

                            <div class="row">
                                <div class="form-section">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($userDetail['phone'] ?? ''); ?>" />
                                </div>
                                <div class="form-section">
                                    <label class="form-label">Member Since</label>
                                    <input type="text" value="<?php echo $userDetail['created_at']; ?>" disabled />
                                </div>
                            </div>

                            <div class="form-section">
                                <label class="form-label">Address</label>
                                <textarea name="address"><?php echo htmlspecialchars($userDetail['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="btn-group">
                                <button type="submit" class="btn-primary">Save Changes</button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this user? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete_user" />
                                    <input type="hidden" name="user_id" value="<?php echo (int)$userDetail['id']; ?>" />
                                    <button type="submit" class="btn-danger">Delete User</button>
                                </form>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Users List View -->
                <div class="card">
                    <h2>All Users</h2>
                    <?php if (!empty($users)): ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Member Since</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo (int)$user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                            <td><?php echo substr($user['created_at'], 0, 10); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="admin_manage.php?tab=users&user_id=<?php echo (int)$user['id']; ?>" style="text-decoration: none;">
                                                        <button class="btn-secondary" style="width: auto;">View/Edit</button>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No users found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- PRODUCTS TAB -->
        <div id="products" class="tab-content <?php echo $currentTab === 'products' ? 'active' : ''; ?>">
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?php echo $productStats['total'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Stock</div>
                    <div class="stat-value"><?php echo $productStats['total_stock'] ?? 0; ?></div>
                </div>
            </div>

            <?php if ($productDetail): ?>
                <!-- Product Detail View -->
                <div class="grid">
                    <div></div>
                    <div class="card">
                        <button class="btn-back btn-secondary" onclick="location.href='admin_manage.php?tab=products'">← Back to Products List</button>

                        <h2>Edit Product</h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_product" />
                            <input type="hidden" name="product_id" value="<?php echo (int)$productDetail['id']; ?>" />

                            <div class="form-section">
                                <label class="form-label">Product ID</label>
                                <input type="text" value="<?php echo (int)$productDetail['id']; ?>" disabled />
                            </div>

                            <div class="form-section">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($productDetail['name']); ?>" required />
                            </div>

                            <div class="row">
                                <div class="form-section">
                                    <label class="form-label">Price *</label>
                                    <input type="number" name="price" step="0.01" min="0.01" value="<?php echo (float)$productDetail['price']; ?>" required />
                                </div>
                                <div class="form-section">
                                    <label class="form-label">Stock</label>
                                    <input type="number" name="stock" min="0" value="<?php echo (int)$productDetail['stock']; ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-section">
                                    <label class="form-label">Category</label>
                                    <input type="text" name="category" value="<?php echo htmlspecialchars($productDetail['category'] ?? ''); ?>" />
                                </div>
                                <div class="form-section">
                                    <label class="form-label">Created</label>
                                    <input type="text" value="<?php echo $productDetail['created_at']; ?>" disabled />
                                </div>
                            </div>

                            <div class="form-section">
                                <label class="form-label">Description</label>
                                <textarea name="description"><?php echo htmlspecialchars($productDetail['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-section">
                                <label class="form-label">Image URL</label>
                                <input type="text" name="image" value="<?php echo htmlspecialchars($productDetail['image'] ?? ''); ?>" />
                            </div>

                            <div class="btn-group">
                                <button type="submit" class="btn-primary">Save Changes</button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this product? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete_product" />
                                    <input type="hidden" name="product_id" value="<?php echo (int)$productDetail['id']; ?>" />
                                    <button type="submit" class="btn-danger">Delete Product</button>
                                </form>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Products List View -->
                <div class="card">
                    <h2>All Products</h2>
                    <?php if (!empty($products)): ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?php echo (int)$product['id']; ?></td>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category'] ?? '-'); ?></td>
                                            <td>$<?php echo number_format((float)$product['price'], 2); ?></td>
                                            <td>
                                                <span class="badge badge-primary"><?php echo (int)$product['stock']; ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="admin_manage.php?tab=products&product_id=<?php echo (int)$product['id']; ?>" style="text-decoration: none;">
                                                        <button class="btn-secondary" style="width: auto;">View/Edit</button>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No products found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- COUPONS TAB -->
        <div id="coupons" class="tab-content <?php echo $currentTab === 'coupons' ? 'active' : ''; ?>">
            <div class="card">
                <h2>Create New Coupon</h2>
                <form id="couponForm" style="display: grid; gap: 12px;">
                    <div class="row">
                        <div class="form-section">
                            <label class="form-label">Coupon Code</label>
                            <input type="text" id="couponCode" placeholder="e.g., SUMMER20" required style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;" />
                        </div>
                        <div class="form-section">
                            <label class="form-label">Discount Type</label>
                            <select id="discountType" required style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                                <option value="fixed">Fixed Amount ($)</option>
                                <option value="percent">Percentage (%)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-section">
                            <label class="form-label">Discount Value</label>
                            <input type="number" id="discountValue" placeholder="e.g., 20" step="0.01" min="0" required style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;" />
                        </div>
                        <div class="form-section">
                            <label class="form-label">Max Uses</label>
                            <input type="number" id="maxUses" placeholder="e.g., 5" value="5" min="1" required style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;" />
                        </div>
                    </div>
                    <div class="form-section">
                        <label class="form-label">Expiration Date (Optional)</label>
                        <input type="datetime-local" id="expirationDate" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;" />
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn-primary" onclick="createCoupon()" style="padding: 10px 20px; background: #1d4ed8; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Create Coupon</button>
                    </div>
                </form>
            </div>

            <div class="card" style="margin-top: 24px;">
                <h2>Active Coupons</h2>
                <div id="couponsListContainer" class="table-wrapper">
                    <p style="text-align: center; color: #6b7280; padding: 20px;">Loading coupons...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');

            // Update URL
            window.history.pushState(null, '', 'admin_manage.php?tab=' + tabName);

            // Load coupons if switching to coupons tab
            if (tabName === 'coupons') {
                loadCoupons();
            }
        }

        async function createCoupon() {
            const code = document.getElementById('couponCode').value.trim();
            const discountType = document.getElementById('discountType').value;
            const discountValue = parseFloat(document.getElementById('discountValue').value);
            const maxUses = parseInt(document.getElementById('maxUses').value);
            const expirationDate = document.getElementById('expirationDate').value;

            if (!code || discountValue <= 0 || maxUses <= 0) {
                alert('Please fill in all required fields correctly.');
                return;
            }

            try {
                const response = await fetch('api/admin_coupons.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        code,
                        discount_type: discountType,
                        discount_value: discountValue,
                        max_uses: maxUses,
                        expiration_date: expirationDate || null
                    })
                });

                const result = await response.json();

                if (!result.success) {
                    alert(result.message || 'Failed to create coupon.');
                    return;
                }

                alert('Coupon created successfully!');
                document.getElementById('couponForm').reset();
                loadCoupons();
            } catch (error) {
                alert('Error creating coupon.');
            }
        }

        async function loadCoupons() {
            const container = document.getElementById('couponsListContainer');

            try {
                const response = await fetch('api/admin_coupons.php?action=list');
                const result = await response.json();

                if (!result.success || !result.coupons) {
                    container.innerHTML = '<p style="text-align: center; color: #6b7280; padding: 20px;">No coupons found.</p>';
                    return;
                }

                if (result.coupons.length === 0) {
                    container.innerHTML = '<p style="text-align: center; color: #6b7280; padding: 20px;">No coupons created yet.</p>';
                    return;
                }

                let tableHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Discount</th>
                                <th>Usage</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                result.coupons.forEach(coupon => {
                    const expiryDate = coupon.expiration_date ? new Date(coupon.expiration_date).toLocaleDateString() : 'Never';
                    const isExpired = coupon.expiration_date && new Date(coupon.expiration_date) < new Date();
                    const statusClass = coupon.active && !isExpired ? 'badge-success' : 'badge-danger';
                    const statusText = !coupon.active ? 'Inactive' : (isExpired ? 'Expired' : 'Active');
                    const discountText = coupon.discount_type === 'fixed' ? `$${coupon.discount_value}` : `${coupon.discount_value}%`;
                    const usagePercent = Math.round((coupon.times_used / coupon.max_uses) * 100);

                    tableHTML += `
                        <tr>
                            <td><strong>${coupon.code}</strong></td>
                            <td>${coupon.discount_type === 'fixed' ? 'Fixed' : 'Percent'}</td>
                            <td>${discountText}</td>
                            <td>${coupon.times_used}/${coupon.max_uses} <span style="font-size: 12px; color: #6b7280;">(${usagePercent}%)</span></td>
                            <td>${expiryDate}</td>
                            <td><span class="badge ${statusClass}">${statusText}</span></td>
                            <td>
                                <button onclick="editCoupon(${coupon.id})" style="padding: 6px 12px; background: #1d4ed8; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; margin-right: 4px;">Edit</button>
                                <button onclick="deleteCoupon(${coupon.id})" style="padding: 6px 12px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">Delete</button>
                            </td>
                        </tr>
                    `;
                });

                tableHTML += '</tbody></table>';
                container.innerHTML = tableHTML;
            } catch (error) {
                container.innerHTML = '<p style="text-align: center; color: #dc2626; padding: 20px;">Error loading coupons.</p>';
            }
        }

        async function editCoupon(id) {
            const newDiscount = prompt('Enter new discount value:');
            if (newDiscount === null) return;

            const newMaxUses = prompt('Enter new max uses:', '5');
            if (newMaxUses === null) return;

            const newExpiry = prompt('Enter expiration date (YYYY-MM-DD HH:MM or leave blank):', '');

            try {
                const response = await fetch('api/admin_coupons.php?action=update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id,
                        discount_value: parseFloat(newDiscount),
                        max_uses: parseInt(newMaxUses),
                        expiration_date: newExpiry || null,
                        active: 1
                    })
                });

                const result = await response.json();

                if (!result.success) {
                    alert(result.message || 'Failed to update coupon.');
                    return;
                }

                alert('Coupon updated successfully!');
                loadCoupons();
            } catch (error) {
                alert('Error updating coupon.');
            }
        }

        async function deleteCoupon(id) {
            if (!confirm('Delete this coupon? This action cannot be undone.')) return;

            try {
                const response = await fetch('api/admin_coupons.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const result = await response.json();

                if (!result.success) {
                    alert(result.message || 'Failed to delete coupon.');
                    return;
                }

                alert('Coupon deleted successfully!');
                loadCoupons();
            } catch (error) {
                alert('Error deleting coupon.');
            }
        }

        // Load coupons on page load if coupons tab is active
        window.addEventListener('load', () => {
            const currentTab = new URLSearchParams(window.location.search).get('tab') || 'users';
            if (currentTab === 'coupons') {
                loadCoupons();
            }
        });
    </script>
</body>
</html>
