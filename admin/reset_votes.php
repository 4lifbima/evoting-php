<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdmin();

// Check if confirmed
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

if ($confirmed) {
    // Delete all votes
    $query = "DELETE FROM votes";
    
    if (mysqli_query($conn, $query)) {
        redirect_with_message('index.php', 'Semua suara berhasil direset! Voting dapat dimulai dari awal.', 'success');
    } else {
        redirect_with_message('index.php', 'Gagal mereset suara: ' . mysqli_error($conn), 'error');
    }
}

// Get statistics before reset
$stats = getStatistics($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Voting - E-Voting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="w-64 bg-indigo-800 text-white flex-shrink-0 hidden md:block">
            <div class="p-6">
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-vote-yea mr-2"></i>
                    Admin Panel
                </h1>
                <p class="text-sm text-indigo-200 mt-1"><?= htmlspecialchars($_SESSION['admin_nama']) ?></p>
            </div>

            <nav class="mt-6">
                <a href="index.php" class="block py-3 px-6 hover:bg-indigo-700 transition border-l-4 border-transparent">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="candidates.php" class="block py-3 px-6 hover:bg-indigo-700 transition border-l-4 border-transparent">
                    <i class="fas fa-users mr-2"></i> Kelola Kandidat
                </a>
                <a href="voters.php" class="block py-3 px-6 hover:bg-indigo-700 transition border-l-4 border-transparent">
                    <i class="fas fa-user-check mr-2"></i> Data Pemilih
                </a>
                <a href="results.php" class="block py-3 px-6 hover:bg-indigo-700 transition border-l-4 border-transparent">
                    <i class="fas fa-chart-bar mr-2"></i> Hasil Voting
                </a>
                <a href="sessions.php" class="block py-3 px-6 hover:bg-indigo-700 transition border-l-4 border-transparent">
                    <i class="fas fa-clock mr-2"></i> Sesi Voting
                </a>
                <a href="../logout.php" class="block py-3 px-6 hover:bg-red-700 transition border-l-4 border-transparent mt-10">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <!-- Topbar (Mobile) -->
            <div class="md:hidden bg-indigo-800 text-white p-4 flex justify-between items-center">
                <h1 class="text-xl font-bold"><i class="fas fa-vote-yea mr-2"></i>Admin Panel</h1>
                <a href="../logout.php" class="text-white hover:text-red-200">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </a>
            </div>

            <div class="p-8">
                <div class="mb-6">
                    <a href="index.php" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                    </a>
                    <h2 class="text-3xl font-bold text-gray-800 mt-2">Reset Voting</h2>
                </div>

                <!-- Warning Box -->
                <div class="bg-red-50 border-2 border-red-300 rounded-lg p-6 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-600 text-3xl mr-4"></i>
                        <div>
                            <h3 class="text-lg font-bold text-red-800 mb-2">PERINGATAN: Tindakan Ini Tidak Dapat Dibatalkan!</h3>
                            <p class="text-red-700">
                                Anda akan menghapus <strong>SEMUA data suara</strong> yang telah masuk. 
                                Pastikan Anda telah mencatat atau mengekspor data jika diperlukan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Current Statistics -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Data Saat Ini</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="text-3xl font-bold text-green-600"><?= $stats['total_voted'] ?></div>
                            <div class="text-sm text-gray-600 mt-1">Total Suara Masuk</div>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="text-3xl font-bold text-blue-600"><?= $stats['total_voters'] ?></div>
                            <div class="text-sm text-gray-600 mt-1">Total Pemilih</div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="text-3xl font-bold text-purple-600"><?= $stats['partisipasi'] ?>%</div>
                            <div class="text-sm text-gray-600 mt-1">Partisipasi</div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-700 mb-6">
                        Apakah Anda yakin ingin melanjutkan? Setelah reset, semua pemilih dapat voting kembali.
                    </p>
                    
                    <div class="flex items-center space-x-4">
                        <a href="index.php" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            <i class="fas fa-times mr-2"></i> Batal
                        </a>
                        <a href="reset_votes.php?confirm=yes" 
                           class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition"
                           onclick="return confirm('YAKIN? Semua data suara akan dihapus permanen!')">
                            <i class="fas fa-sync-alt mr-2"></i> Ya, Reset Semua Suara
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
