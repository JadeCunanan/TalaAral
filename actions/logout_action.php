<?php
session_start();

// 1. Clear all session variables
session_unset();

// 2. Destroy the session itself
session_destroy();

// 3. Redirect the student back to the login page
header("Location: /login.php");
exit();