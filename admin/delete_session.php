<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect_with_message('sessions.php', 'ID sesi tidak valid!', 'error');
}

// Check if session exists
$session = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM voting_session WHERE id = $id"));
if (!$session) {
    redirect_with_message('sessions.php', 'Sesi tidak ditemukan!', 'error');
}

// Delete session
$query = "DELETE FROM voting_session WHERE id = $id";

if (mysqli_query($conn, $query)) {
    redirect_with_message('sessions.php', 'Sesi voting berhasil dihapus!', 'success');
} else {
    redirect_with_message('sessions.php', 'Gagal menghapus sesi: ' . mysqli_error($conn), 'error');
}
?>
