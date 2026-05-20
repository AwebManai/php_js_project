<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Admin access required.']);
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

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    // GET: List all coupons
    if ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $result = $conn->query('SELECT id, code, discount_type, discount_value, max_uses, times_used, expiration_date, active, created_at FROM coupon ORDER BY created_at DESC');
        
        if (!$result) {
            throw new Exception('Failed to fetch coupons.');
        }
        
        $coupons = [];
        while ($row = $result->fetch_assoc()) {
            $coupons[] = $row;
        }
        
        $response = ['success' => true, 'coupons' => $coupons];
    }
    
    // POST: Create new coupon
    else if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $code = trim($data['code'] ?? '');
        $discountType = $data['discount_type'] ?? 'fixed';
        $discountValue = (float)($data['discount_value'] ?? 0);
        $maxUses = (int)($data['max_uses'] ?? 5);
        $expirationDate = trim($data['expiration_date'] ?? '');
        
        if (empty($code) || $discountValue <= 0 || $maxUses <= 0) {
            throw new Exception('Invalid coupon data.');
        }
        
        if (!in_array($discountType, ['fixed', 'percent'])) {
            throw new Exception('Invalid discount type.');
        }
        
        // Check if code already exists
        $checkStmt = $conn->prepare('SELECT id FROM coupon WHERE code = ?');
        $checkStmt->bind_param('s', $code);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            throw new Exception('Coupon code already exists.');
        }
        $checkStmt->close();
        
        $expirationDateValue = !empty($expirationDate) ? $expirationDate : null;
        
        $stmt = $conn->prepare('INSERT INTO coupon (code, discount_type, discount_value, max_uses, expiration_date, active) VALUES (?, ?, ?, ?, ?, 1)');
        
        if (!$stmt) {
            throw new Exception('Failed to prepare statement.');
        }
        
        $stmt->bind_param('ssdis', $code, $discountType, $discountValue, $maxUses, $expirationDateValue);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create coupon.');
        }
        
        $couponId = $conn->insert_id;
        $stmt->close();
        
        $response = ['success' => true, 'message' => 'Coupon created successfully.', 'coupon_id' => $couponId];
    }
    
    // POST: Update coupon
    else if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $couponId = (int)($data['id'] ?? 0);
        $discountValue = (float)($data['discount_value'] ?? 0);
        $maxUses = (int)($data['max_uses'] ?? 5);
        $expirationDate = trim($data['expiration_date'] ?? '');
        $active = isset($data['active']) ? (int)$data['active'] : 1;
        
        if ($couponId <= 0 || $discountValue <= 0 || $maxUses <= 0) {
            throw new Exception('Invalid coupon data.');
        }
        
        $expirationDateValue = !empty($expirationDate) ? $expirationDate : null;
        
        $stmt = $conn->prepare('UPDATE coupon SET discount_value = ?, max_uses = ?, expiration_date = ?, active = ? WHERE id = ?');
        
        if (!$stmt) {
            throw new Exception('Failed to prepare statement.');
        }
        
        $stmt->bind_param('disii', $discountValue, $maxUses, $expirationDateValue, $active, $couponId);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update coupon.');
        }
        
        $stmt->close();
        $response = ['success' => true, 'message' => 'Coupon updated successfully.'];
    }
    
    // POST: Delete coupon
    else if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $couponId = (int)($data['id'] ?? 0);
        
        if ($couponId <= 0) {
            throw new Exception('Invalid coupon ID.');
        }
        
        $stmt = $conn->prepare('DELETE FROM coupon WHERE id = ?');
        $stmt->bind_param('i', $couponId);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete coupon.');
        }
        
        $stmt->close();
        $response = ['success' => true, 'message' => 'Coupon deleted successfully.'];
    }
    
    else {
        throw new Exception('Invalid action.');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    $response = ['success' => false, 'message' => $e->getMessage()];
} finally {
    $conn->close();
}

echo json_encode($response);
?>
