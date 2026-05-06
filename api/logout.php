<?php
session_start();

// Destroy session
session_destroy();

// Redirect to login page
header('Location: ../login.html?message=Logged out successfully');
exit;
?>
