<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html?message=' . urlencode('Please log in to view your purchases.'));
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "stylestore";

$conn = new mysqli($servername, $username, $password, $database);
$purchases = [];
$errorMessage = '';

if ($conn->connect_error) {
    $errorMessage = 'Database connection failed.';
} else {
    $conn->set_charset('utf8');

    $sql = "
        SELECT
            po.id AS order_id,
            po.total_amount,
            po.created_at,
            p.name AS product_name,
            pi.quantity,
            pi.unit_price,
            pi.line_total
        FROM purchase_order po
        JOIN purchase_item pi ON po.id = pi.order_id
        JOIN product p ON p.id = pi.product_id
        WHERE po.user_id = ?
        ORDER BY po.created_at DESC, po.id DESC, pi.id ASC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $errorMessage = 'Failed to load purchases.';
    } else {
        $userId = (int)$_SESSION['user_id'];
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $orderId = (int)$row['order_id'];
            if (!isset($purchases[$orderId])) {
                $purchases[$orderId] = [
                    'order_id' => $orderId,
                    'created_at' => $row['created_at'],
                    'total_amount' => $row['total_amount'],
                    'items' => []
                ];
            }

            $purchases[$orderId]['items'][] = [
                'product_name' => $row['product_name'],
                'quantity' => (int)$row['quantity'],
                'unit_price' => $row['unit_price'],
                'line_total' => $row['line_total']
            ];
        }

        $stmt->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Purchases - StyleStore</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        .page-wrap {
            max-width: 900px;
            margin: 24px auto;
            padding: 0 16px 24px;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            gap: 12px;
        }

        .back-link {
            text-decoration: none;
            color: #1f2937;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            font-size: 14px;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 600;
        }

        .order-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 14px;
        }

        .order-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .order-id {
            font-weight: 700;
            color: #111827;
        }

        .order-time {
            color: #6b7280;
            font-size: 13px;
        }

        .total {
            font-weight: 700;
            color: #1d4ed8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            text-align: left;
            padding: 8px 6px;
            border-bottom: 1px solid #f3f4f6;
        }

        th {
            color: #4b5563;
            font-weight: 600;
        }

        .muted {
            color: #6b7280;
            margin-top: 8px;
        }

        .error {
            color: #dc2626;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <header>
        <div class="topbar">
            <a href="index.php" class="brand" style="text-decoration: none; color: #1f2937;">StyleStore</a>
            <div style="color: #4b5563; font-size: 14px;">My Purchases</div>
        </div>
    </header>

    <main class="page-wrap">
        <div class="top-row">
            <h1 style="margin: 0; font-size: 1.6rem;">Purchase History</h1>
            <a href="index.php" class="back-link">Back to Shop</a>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php elseif (empty($purchases)): ?>
            <p class="muted">You have no purchases yet.</p>
        <?php else: ?>
            <?php foreach ($purchases as $order): ?>
                <section class="order-card">
                    <div class="order-head">
                        <div>
                            <div class="order-id">Order #<?php echo (int)$order['order_id']; ?></div>
                            <div class="order-time"><?php echo htmlspecialchars($order['created_at']); ?></div>
                        </div>
                        <div class="total">Total: $<?php echo number_format((float)$order['total_amount'], 2); ?></div>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo (int)$item['quantity']; ?></td>
                                    <td>$<?php echo number_format((float)$item['unit_price'], 2); ?></td>
                                    <td>$<?php echo number_format((float)$item['line_total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>
