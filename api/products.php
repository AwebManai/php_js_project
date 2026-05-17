<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "stylestore";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['products' => [], 'categories' => []]);
    exit;
}

$conn->set_charset("utf8");

// Get category filter from request
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch all unique categories
$categoryQuery = "SELECT DISTINCT category FROM product WHERE category IS NOT NULL AND category != '' ORDER BY category ASC";
$categoryResult = $conn->query($categoryQuery);

$categories = [];
if ($categoryResult && $categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

// Build products query with filters
$query = "SELECT p.id, p.name, p.price, p.image, p.description, p.category, p.stock,
                 COALESCE(s.discount_percentage, 0) as discount_percentage
          FROM product p
          LEFT JOIN sales s ON p.id = s.product_id
          WHERE 1=1";

if (!empty($category)) {
    $category = $conn->real_escape_string($category);
    $query .= " AND category = '$category'";
}

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

$query .= " ORDER BY id DESC";
$result = $conn->query($query);

$products = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode([
    'products' => $products,
    'categories' => $categories
]);

$conn->close();
?>
