<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Sign In - StyleStore</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        body {
            background: #f5f5f5;
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 420px;
            margin: 60px auto;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 28px;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 14px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #374151;
            font-weight: 600;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
        }

        button {
            width: 100%;
            border: none;
            background: #111827;
            color: #ffffff;
            border-radius: 8px;
            padding: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .back {
            text-align: center;
            margin-top: 14px;
            font-size: 14px;
        }

        .back a {
            color: #1f2937;
            text-decoration: none;
            font-weight: 600;
        }

        .message {
            margin-bottom: 12px;
            padding: 10px;
            border-radius: 8px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Sign In</h1>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="api/admin_login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required />
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required />
            </div>

            <button type="submit">Sign In</button>
        </form>

        <div class="back"><a href="index.php">Back to shop</a></div>
    </div>
</body>
</html>
