<?php
session_start();

unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

header('Location: ../admin_login.php?message=' . urlencode('Signed out successfully.'));
exit;
?>
