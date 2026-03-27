<?php
// ============================================================
// config.php - Database Connection Configuration
// ============================================================

// Database credentials - change these to match your WAMP setup
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Default WAMP username
define('DB_PASS', '');           // Default WAMP password (empty)
define('DB_NAME', 'student_result_db');

// Site configuration
define('SITE_NAME', 'Student Result Management System');
define('SITE_URL', 'http://localhost/student_result_system');

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("<div style='font-family:sans-serif;padding:20px;color:red;'>
        <h3>❌ Database Connection Failed</h3>
        <p>" . mysqli_connect_error() . "</p>
        <p>Please make sure WAMP server is running and database is set up.</p>
    </div>");
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8");

// -----------------------------------------------
// Helper Functions
// -----------------------------------------------

// Sanitize user input to prevent SQL injection
function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Redirect to a URL
function redirect($url) {
    header("Location: $url");
    exit();
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Check if student is logged in
function isStudentLoggedIn() {
    return isset($_SESSION['student_id']) && !empty($_SESSION['student_id']);
}

// Require admin login - redirect if not logged in
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        redirect('../index.php?error=Please+login+first');
    }
}

// Require student login - redirect if not logged in
function requireStudentLogin() {
    if (!isStudentLoggedIn()) {
        redirect('../index.php?error=Please+login+first');
    }
}

// Calculate grade based on percentage
function calculateGrade($percentage) {
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B+';
    if ($percentage >= 60) return 'B';
    if ($percentage >= 50) return 'C';
    if ($percentage >= 40) return 'D';
    return 'F';
}

// Get grade color for display
function getGradeColor($grade) {
    switch($grade) {
        case 'A+': return '#00b894';
        case 'A':  return '#00cec9';
        case 'B+': return '#0984e3';
        case 'B':  return '#6c5ce7';
        case 'C':  return '#fdcb6e';
        case 'D':  return '#e17055';
        default:   return '#d63031';
    }
}

// Display alert messages
function showAlert($message, $type = 'success') {
    $icon = $type === 'success' ? '✅' : '❌';
    $bg   = $type === 'success' ? '#d4edda' : '#f8d7da';
    $clr  = $type === 'success' ? '#155724' : '#721c24';
    echo "<div style='background:$bg;color:$clr;padding:12px 16px;border-radius:8px;margin:10px 0;font-size:14px;'>
            $icon $message
          </div>";
}
?>
