<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "stylestore";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    header('Location: ../login.html?message=' . urlencode('Connection failed: ' . $conn->connect_error));
    exit;
}

$conn->set_charset("utf8");

// Get form data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($email) || empty($password)) {
    header('Location: ../login.html?message=' . urlencode('Email and password are required'));
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT id, firstname, lastname, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../login.html?message=' . urlencode('Invalid email or password'));
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    header('Location: ../login.html?message=' . urlencode('Invalid email or password'));
    exit;
}

// Login successful - set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['firstname'] = $user['firstname'];
$_SESSION['lastname'] = $user['lastname'];
$_SESSION['email'] = $email;

header('Location: ../index.php');
exit;

$stmt->close();
$conn->close();
?>
