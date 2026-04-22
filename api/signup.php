<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "stylestore";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    header('Location: ../signup.html?message=' . urlencode('Connection failed: ' . $conn->connect_error));
    exit;
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Get form data
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (empty($firstname)) {
    $errors[] = 'First name is required';
}

if (empty($lastname)) {
    $errors[] = 'Last name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters long';
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

// If validation failed, return errors
if (!empty($errors)) {
    header('Location: ../signup.html?message=' . urlencode(implode(', ', $errors)));
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header('Location: ../signup.html?message=' . urlencode('Email already registered'));
    exit;
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user into database
$stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $firstname, $lastname, $email, $hashed_password, $phone, $address);

if ($stmt->execute()) {
    header('Location: ../login.html?message=' . urlencode('Account created successfully! Please log in.'));
    exit;
} else {
    header('Location: ../signup.html?message=' . urlencode('Error creating account: ' . $stmt->error));
    exit;
}

$stmt->close();
$conn->close();
?>
