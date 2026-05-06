<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "stylestore";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$conn->set_charset("utf8");

// Fetch products from database
$query = "SELECT id, name, price, image, description, category, stock FROM product ORDER BY id DESC";
$result = $conn->query($query);

$products = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($products);

$conn->close();
?>
