<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdmin();

// Get candidate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect_with_message('candidates.php', 'ID kandidat tidak valid!', 'error');
}

// Get candidate data
$candidate = getCandidateById($conn, $id);
if (!$candidate) {
    redirect_with_message('candidates.php', 'Kandidat tidak ditemukan!', 'error');
}

// Check if candidate has votes
$vote_check = mysqli_query($conn, "SELECT COUNT(*) as total FROM votes WHERE candidate_id = $id");
$vote_count = mysqli_fetch_assoc($vote_check)['total'];

if ($vote_count > 0) {
    // Delete votes first (cascade should handle this, but being explicit)
    mysqli_query($conn, "DELETE FROM votes WHERE candidate_id = $id");
}

// Delete candidate
$query = "DELETE FROM candidates WHERE id = $id";

if (mysqli_query($conn, $query)) {
    // Delete photo file if exists and not default
    if ($candidate['foto'] && $candidate['foto'] !== 'default.jpg') {
        $photo_path = '../uploads/candidates/' . $candidate['foto'];
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }
    
    redirect_with_message('candidates.php', 'Kandidat berhasil dihapus!', 'success');
} else {
    redirect_with_message('candidates.php', 'Gagal menghapus kandidat: ' . mysqli_error($conn), 'error');
}
?>
