<?php
/**
 * admin_logout.php
 * Finalization Stage: Clears admin session and returns to index[cite: 3].
 */
session_start();

// 1. Clear all session variables[cite: 3]
session_unset();

// 2. Destroy the session entirely[cite: 3]
session_destroy();

// 3. Redirect back to the main landing page
header('Location: ../../index.php'); 
exit;