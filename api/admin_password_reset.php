<?php
/**
 * Admin Password Reset/Creation API
 * Requires authentication token in X-Admin-Token header
 * 
 * Usage:
 * POST /api/admin_password_reset.php
 * Headers: X-Admin-Token: your-secure-token
 * Body: {
 *   "action": "set_password",
 *   "username": "admin",
 *   "password": "newpassword123"
 * }
 */

session_start();
header('Content-Type: application/json');

// Define admin token (should be stored in environment variable or config)
$ADMIN_TOKEN = 'your_secure_admin_token_here'; // CHANGE THIS!

// Check authentication token
$headers = getallheaders();
$token = $headers['X-Admin-Token'] ?? '';

if ($token !== $ADMIN_TOKEN) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "stylestore";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$conn->set_charset("utf8");

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'set_password') {
    $adminUsername = trim($data['username'] ?? '');
    $newPassword = $data['password'] ?? '';

    // Validation
    if (empty($adminUsername) || empty($newPassword)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password required']);
        exit;
    }

    if (strlen($newPassword) < 8) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

    // Check if admin exists
    $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ?");
    $stmt->bind_param("s", $adminUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        // Update existing admin
        $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashedPassword, $adminUsername);
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update password']);
        }
        $stmt->close();
    } else {
        // Create new admin
        $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $adminUsername, $hashedPassword);
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Admin account created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create admin account']);
        }
        $stmt->close();
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>
