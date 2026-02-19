<?php
/**
 * Authentication Helper
 * E-Voting System
 */

// Prevent direct access
if (!defined('APP_NAME')) {
    define('APP_NAME', 'E-Voting System');
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin login - redirect if not logged in
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}

/**
 * Get current admin info
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'email' => $_SESSION['admin_email'] ?? null,
        'nama' => $_SESSION['admin_nama'] ?? null,
        'role' => $_SESSION['admin_role'] ?? null
    ];
}

/**
 * Login admin user
 */
function loginAdmin($conn, $email, $password) {
    $email = trim((string)$email);
    $password = (string)$password;

    if ($email === '' || $password === '') {
        return ['success' => false, 'message' => 'Email or password is incorrect'];
    }

    $email = mysqli_real_escape_string($conn, $email);
    $password = md5($password); // Note: Use password_hash() in production!
    
    $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Set session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_nama'] = $user['nama_lengkap'];
        $_SESSION['admin_role'] = $user['role'];
        
        // Update last login
        mysqli_query($conn, "UPDATE users SET last_login = NOW() WHERE id = " . $user['id']);
        
        return ['success' => true, 'user' => $user];
    }
    
    return ['success' => false, 'message' => 'Email or password is incorrect'];
}

/**
 * Logout admin
 */
function logoutAdmin() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Check if voter is logged in (by session)
 */
function isVoterLoggedIn() {
    return isset($_SESSION['voter_logged_in']) && $_SESSION['voter_logged_in'] === true;
}

/**
 * Require voter login - redirect if not logged in
 */
function requireVoter($redirect = 'voter_login.php') {
    if (!isVoterLoggedIn()) {
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Get current voter info
 */
function getCurrentVoter() {
    if (!isVoterLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['voter_id'] ?? null,
        'nim_nis' => $_SESSION['voter_nim_nis'] ?? null,
        'nama' => $_SESSION['voter_nama'] ?? null
    ];
}

/**
 * Login voter
 */
function loginVoter($conn, $nim_nis) {
    $nim_nis = mysqli_real_escape_string($conn, $nim_nis);
    
    $query = "SELECT * FROM voters WHERE nim_nis = '$nim_nis' AND status = 'aktif'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $voter = mysqli_fetch_assoc($result);
        
        // Set session
        $_SESSION['voter_logged_in'] = true;
        $_SESSION['voter_id'] = $voter['id'];
        $_SESSION['voter_nim_nis'] = $voter['nim_nis'];
        $_SESSION['voter_nama'] = $voter['nama'];
        
        return ['success' => true, 'voter' => $voter];
    }
    
    return ['success' => false, 'message' => 'Voter not found or inactive'];
}

/**
 * Logout voter
 */
function logoutVoter() {
    unset($_SESSION['voter_logged_in']);
    unset($_SESSION['voter_id']);
    unset($_SESSION['voter_nim_nis']);
    unset($_SESSION['voter_nama']);
}
?>
