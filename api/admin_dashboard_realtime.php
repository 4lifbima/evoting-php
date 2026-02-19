<?php
/**
 * API: Admin Dashboard Realtime
 * Returns dashboard stats and candidate vote results for polling.
 */

header('Content-Type: application/json');

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

$stats = getStatistics($conn);
$candidate_results = getVoteResults($conn);

echo json_encode([
    'success' => true,
    'stats' => $stats,
    'candidate_results' => $candidate_results,
    'last_update' => date('H:i:s')
]);

mysqli_close($conn);
?>
