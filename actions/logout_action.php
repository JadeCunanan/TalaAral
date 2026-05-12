<?php
session_start();

// 1. Clear all session variables
session_unset();

// 2. Destroy the session itself
session_destroy();

// 3. Redirect back using the Clean URL convention
// This triggers your .htaccess rule to show views/login.php without the .php extension
header("Location: ../login"); 
exit();
?>