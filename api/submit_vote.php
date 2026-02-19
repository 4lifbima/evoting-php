<?php
/**
 * API: Submit Vote
 * Process voting and return auto-logout instruction
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Set JSON response header
header('Content-Type: application/json');

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Voter session is mandatory
if (!isVoterLoggedIn() || !isset($_SESSION['voter_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Session voter tidak valid. Silakan login ulang.',
        'requires_login' => true,
        'redirect_to' => 'voter_login.php'
    ]);
    exit;
}

// Get and sanitize input
$voter_id = (int)$_SESSION['voter_id'];
$candidate_id = isset($_POST['candidate_id']) ? (int)$_POST['candidate_id'] : 0;

// Validate input
if (empty($voter_id) || empty($candidate_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Check if voter exists and is active
$check_voter = mysqli_query($conn, "SELECT id, nama FROM voters WHERE id = $voter_id AND status = 'aktif'");
if (mysqli_num_rows($check_voter) == 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Voter not found or inactive']);
    exit;
}

$voter = mysqli_fetch_assoc($check_voter);

// Check if already voted (prevent double voting)
$check_vote = mysqli_query($conn, "SELECT id FROM votes WHERE voter_id = $voter_id");
if (mysqli_num_rows($check_vote) > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'You have already voted',
        'already_voted' => true
    ]);
    exit;
}

// Verify candidate exists
$check_candidate = mysqli_query($conn, "SELECT id FROM candidates WHERE id = $candidate_id");
if (mysqli_num_rows($check_candidate) == 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Candidate not found']);
    exit;
}

// Get IP address and user agent
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$user_agent = mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

// Insert vote using prepared statement to prevent SQL injection
$stmt = mysqli_prepare($conn, "INSERT INTO votes (voter_id, candidate_id, ip_address, user_agent) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iiss", $voter_id, $candidate_id, $ip_address, $user_agent);

if (mysqli_stmt_execute($stmt)) {
    // Vote successful
    mysqli_stmt_close($stmt);
    
    echo json_encode([
        'success' => true,
        'message' => 'Vote successfully recorded',
        'auto_logout_in' => 3,
        'redirect_to' => 'logout.php?reason=vote-complete',
        'voter_name' => $voter['nama']
    ]);
} else {
    mysqli_stmt_close($stmt);
    
    // Check for duplicate entry (race condition protection)
    if (mysqli_errno($conn) == 1062) {
        echo json_encode([
            'success' => false,
            'message' => 'You have already voted (duplicate entry)',
            'already_voted' => true
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to record vote: ' . mysqli_error($conn)]);
    }
}

mysqli_close($conn);
?>
