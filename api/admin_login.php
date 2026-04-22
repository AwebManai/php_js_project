<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "stylestore";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    header('Location: ../admin_login.php?message=' . urlencode('Database connection failed.'));
    exit;
}

$conn->set_charset("utf8");

$adminUsername = trim($_POST['username'] ?? '');
$adminPassword = $_POST['password'] ?? '';

if ($adminUsername === '' || $adminPassword === '') {
    header('Location: ../admin_login.php?message=' . urlencode('Username and password are required.'));
    exit;
}

$stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $adminUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../admin_login.php?message=' . urlencode('Invalid admin credentials.'));
    exit;
}

$admin = $result->fetch_assoc();

if (!password_verify($adminPassword, $admin['password'])) {
    header('Location: ../admin_login.php?message=' . urlencode('Invalid admin credentials.'));
    exit;
}

$_SESSION['admin_id'] = (int)$admin['id'];
$_SESSION['admin_username'] = $admin['username'];

header('Location: ../admin_dashboard.php');
exit;
?>
