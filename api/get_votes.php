<?php
/**
 * API: Get Vote Results
 * Returns real-time vote data in JSON format
 */

header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Ambil data hasil vote
$results = getVoteResults($conn);

// Ambil total pemilih
$query_total = "SELECT COUNT(*) as total FROM voters WHERE status = 'aktif'";
$total_voters = mysqli_fetch_assoc(mysqli_query($conn, $query_total))['total'];

// Ambil jumlah yang sudah vote
$query_voted = "SELECT COUNT(*) as voted FROM votes";
$voted_count = mysqli_fetch_assoc(mysqli_query($conn, $query_voted))['voted'];

// Response JSON
echo json_encode([
    'success' => true,
    'data' => $results,
    'statistik' => [
        'total_voters' => $total_voters,
        'voted' => $voted_count,
        'partisipasi' => $total_voters > 0 ? round(($voted_count / $total_voters) * 100, 1) : 0,
        'last_update' => date('H:i:s')
    ]
]);

mysqli_close($conn);
?>
