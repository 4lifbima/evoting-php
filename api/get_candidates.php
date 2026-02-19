<?php
/**
 * API: Get Candidates
 * Returns all candidates in JSON format
 */

header('Content-Type: application/json');
require_once '../includes/config.php';

$query = "SELECT * FROM candidates ORDER BY no_urut ASC";
$result = mysqli_query($conn, $query);

$candidates = [];
while ($row = mysqli_fetch_assoc($result)) {
    $candidates[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $candidates
]);

mysqli_close($conn);
?>
