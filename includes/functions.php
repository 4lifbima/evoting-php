<?php
/**
 * Helper Functions
 * E-Voting System
 */

/**
 * Fungsi untuk mendapatkan semua kandidat
 */
function getCandidates($conn) {
    $query = "SELECT * FROM candidates ORDER BY no_urut ASC";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

/**
 * Fungsi untuk mendapatkan kandidat berdasarkan ID
 */
function getCandidateById($conn, $id) {
    $query = "SELECT * FROM candidates WHERE id = " . (int)$id;
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Fungsi untuk mendapatkan hasil vote dengan perhitungan
 */
function getVoteResults($conn) {
    $query = "SELECT
                c.*,
                COUNT(v.id) as jumlah_suara,
                (COUNT(v.id) / (SELECT COUNT(*) FROM votes) * 100) as persentase
              FROM candidates c
              LEFT JOIN votes v ON c.id = v.candidate_id
              GROUP BY c.id
              ORDER BY jumlah_suara DESC";

    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['persentase'] = round($row['persentase'] ?? 0, 1);
        $data[] = $row;
    }
    return $data;
}

/**
 * Cek apakah user sudah vote
 */
function hasVoted($conn, $voter_id) {
    $voter_id = (int)$voter_id;
    $query = "SELECT id FROM votes WHERE voter_id = $voter_id";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

/**
 * Format tanggal Indonesia
 */
function tgl_indo($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

/**
 * Format waktu Indonesia (HH:MM:SS)
 */
function waktu_indo($waktu) {
    return date('H:i:s', strtotime($waktu));
}

/**
 * Redirect dengan pesan flash
 */
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

/**
 * Get flash message
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Sanitize input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Upload file helper
 */
function upload_file($file, $target_dir = 'uploads/', $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
    $target_file = $target_dir . basename($file["name"]);
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if file is valid
    if ($file["error"] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error: ' . $file["error"]];
    }
    
    // Check file type
    if (!in_array($file_type, $allowed_types)) {
        return ['success' => false, 'message' => 'Only ' . implode(', ', $allowed_types) . ' are allowed'];
    }
    
    // Check file size (max 2MB)
    if ($file["size"] > 2000000) {
        return ['success' => false, 'message' => 'File size too large (max 2MB)'];
    }
    
    // Create directory if not exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '.' . $file_type;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
}

/**
 * Get active voting session
 */
function getActiveSession($conn) {
    $query = "SELECT * FROM voting_session WHERE status = 'aktif' LIMIT 1";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Check if voting is currently active
 */
function isVotingActive($conn) {
    $session = getActiveSession($conn);
    if (!$session) {
        return false;
    }

    // In this app, session status "aktif" is the main switch for allowing votes.
    // This prevents accidental lockout when old date ranges remain in database.
    return isset($session['status']) && $session['status'] === 'aktif';
}

/**
 * Get voter by ID
 */
function getVoterById($conn, $id) {
    $id = (int)$id;
    $query = "SELECT * FROM voters WHERE id = $id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

/**
 * Get all voters
 */
function getAllVoters($conn) {
    $query = "SELECT * FROM voters ORDER BY nama ASC";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

/**
 * Get statistics
 */
function getStatistics($conn) {
    $stats = [];
    
    // Total voters
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM voters");
    $stats['total_voters'] = mysqli_fetch_assoc($result)['total'];
    
    // Total voted
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM votes");
    $stats['total_voted'] = mysqli_fetch_assoc($result)['total'];
    
    // Total candidates
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM candidates");
    $stats['total_candidates'] = mysqli_fetch_assoc($result)['total'];
    
    // Participation percentage
    $stats['partisipasi'] = $stats['total_voters'] > 0 
        ? round(($stats['total_voted'] / $stats['total_voters']) * 100, 1) 
        : 0;
    
    return $stats;
}
?>
