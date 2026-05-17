<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$items = $data['items'] ?? [];

if (!is_array($items) || count($items) === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "stylestore";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$conn->set_charset('utf8');

try {
    $conn->begin_transaction();

    $userId = (int)$_SESSION['user_id'];
    $totalAmount = 0.00;

    $checkStmt = $conn->prepare('SELECT p.id, p.name, p.stock, p.price, COALESCE(s.discount_percentage, 0) as discount_percentage 
                                  FROM product p 
                                  LEFT JOIN sales s ON p.id = s.product_id 
                                  WHERE p.id = ? FOR UPDATE');
    $updateStmt = $conn->prepare('UPDATE product SET stock = stock - ? WHERE id = ?');
    $insertOrderStmt = $conn->prepare('INSERT INTO purchase_order (user_id, total_amount) VALUES (?, ?)');

    if (!$insertOrderStmt) {
        throw new Exception('Failed to create purchase order.');
    }

    $insertOrderStmt->bind_param('id', $userId, $totalAmount);
    if (!$insertOrderStmt->execute()) {
        throw new Exception('Failed to create purchase order.');
    }

    $orderId = (int)$conn->insert_id;
    $insertItemStmt = $conn->prepare('INSERT INTO purchase_item (order_id, product_id, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?)');

    if (!$insertItemStmt) {
        throw new Exception('Failed to create purchase items.');
    }

    foreach ($items as $item) {
        $productId = (int)($item['product_id'] ?? 0);
        $quantity = (int)($item['quantity'] ?? 0);

        if ($productId <= 0 || $quantity <= 0) {
            throw new Exception('Invalid cart item.');
        }

        $checkStmt->bind_param('i', $productId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('A product in your cart no longer exists.');
        }

        $product = $result->fetch_assoc();
        $currentStock = (int)$product['stock'];
        $basePrice = (float)$product['price'];
        $discountPercent = (float)$product['discount_percentage'];
        $unitPrice = $basePrice * (1 - $discountPercent / 100);
        $lineTotal = $unitPrice * $quantity;

        if ($currentStock < $quantity) {
            throw new Exception('Not enough stock for ' . $product['name'] . '. Available: ' . $currentStock);
        }

        $updateStmt->bind_param('ii', $quantity, $productId);
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update stock.');
        }

        $insertItemStmt->bind_param('iiidd', $orderId, $productId, $quantity, $unitPrice, $lineTotal);
        if (!$insertItemStmt->execute()) {
            throw new Exception('Failed to save purchase item.');
        }

        $totalAmount += $lineTotal;
    }

    $updateOrderStmt = $conn->prepare('UPDATE purchase_order SET total_amount = ? WHERE id = ?');
    if (!$updateOrderStmt) {
        throw new Exception('Failed to finalize order.');
    }

    $updateOrderStmt->bind_param('di', $totalAmount, $orderId);
    if (!$updateOrderStmt->execute()) {
        throw new Exception('Failed to finalize order.');
    }

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Purchase successful.']);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($checkStmt)) {
        $checkStmt->close();
    }
    if (isset($updateStmt)) {
        $updateStmt->close();
    }
    if (isset($insertOrderStmt)) {
        $insertOrderStmt->close();
    }
    if (isset($insertItemStmt)) {
        $insertItemStmt->close();
    }
    if (isset($updateOrderStmt)) {
        $updateOrderStmt->close();
    }
    $conn->close();
}
?>
