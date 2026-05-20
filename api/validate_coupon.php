<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
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
    // POST: Validate coupon
    if ($action === 'validate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $code = trim($data['code'] ?? '');
        $cartTotal = (float)($data['cart_total'] ?? 0);
        
        if (empty($code)) {
            throw new Exception('Coupon code is required.');
        }
        
        $userId = (int)$_SESSION['user_id'];
        
        // Fetch coupon details
        $stmt = $conn->prepare('SELECT id, discount_type, discount_value, max_uses, times_used, expiration_date, active FROM coupon WHERE code = ?');
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Coupon code not found.');
        }
        
        $coupon = $result->fetch_assoc();
        $stmt->close();
        
        // Check if coupon is active
        if (!$coupon['active']) {
            throw new Exception('This coupon is no longer active.');
        }
        
        // Check expiration date
        if (!empty($coupon['expiration_date'])) {
            $expirationTime = strtotime($coupon['expiration_date']);
            $currentTime = time();
            if ($currentTime > $expirationTime) {
                throw new Exception('This coupon has expired.');
            }
        }
        
        // Check usage limit
        if ($coupon['times_used'] >= $coupon['max_uses']) {
            throw new Exception('This coupon has reached its usage limit.');
        }
        
        // Check if user already used this coupon
        $checkStmt = $conn->prepare('SELECT id FROM coupon_uses WHERE coupon_id = ? AND user_id = ?');
        $checkStmt->bind_param('ii', $coupon['id'], $userId);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            throw new Exception('You have already used this coupon.');
        }
        $checkStmt->close();
        
        // Calculate discount
        $discountAmount = 0;
        if ($coupon['discount_type'] === 'fixed') {
            $discountAmount = min($coupon['discount_value'], $cartTotal);
        } else { // percent
            $discountAmount = ($cartTotal * $coupon['discount_value']) / 100;
        }
        
        $finalTotal = max(0, $cartTotal - $discountAmount);
        
        $response = [
            'success' => true,
            'message' => 'Coupon is valid.',
            'coupon_id' => $coupon['id'],
            'discount_amount' => round($discountAmount, 2),
            'discount_type' => $coupon['discount_type'],
            'discount_value' => $coupon['discount_value'],
            'final_total' => round($finalTotal, 2)
        ];
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
