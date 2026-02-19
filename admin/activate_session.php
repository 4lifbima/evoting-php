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

// Deactivate all sessions first
mysqli_query($conn, "UPDATE voting_session SET status = 'selesai' WHERE status = 'aktif'");

// Activate selected session
$query = "UPDATE voting_session SET status = 'aktif' WHERE id = $id";

if (mysqli_query($conn, $query)) {
    redirect_with_message('sessions.php', 'Sesi voting berhasil diaktifkan!', 'success');
} else {
    redirect_with_message('sessions.php', 'Gagal mengaktifkan sesi: ' . mysqli_error($conn), 'error');
}
?>
