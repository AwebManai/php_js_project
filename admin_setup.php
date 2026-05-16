<?php
/**
 * Admin Setup - Generate admin credentials
 * Usage: php admin_setup.php admin123
 * Then copy the SQL and insert it into the database
 */

if ($argc < 2) {
    echo "Usage: php admin_setup.php <password>\n";
    echo "Example: php admin_setup.php mypassword123\n";
    exit(1);
}

$password = $argv[1];

echo "Password: " . $password . "\n\n";
echo "Use this SQL to add/update admin:\n";
echo "INSERT INTO admin (username, password, email) VALUES ('admin', '$password', 'admin@stylestore.com')\n";
echo "ON DUPLICATE KEY UPDATE password = '$password';\n";
?>
