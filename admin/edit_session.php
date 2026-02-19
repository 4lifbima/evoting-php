<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get session data
$session = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM voting_session WHERE id = $id"));
if (!$session) {
    redirect_with_message('sessions.php', 'Sesi tidak ditemukan!', 'error');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_sesi = isset($_POST['nama_sesi']) ? trim($_POST['nama_sesi']) : '';
    $tanggal_mulai = isset($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : '';
    $tanggal_selesai = isset($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : 'pending';
    
    // Validation
    if (empty($nama_sesi)) {
        $errors[] = "Nama sesi harus diisi";
    }
    if (empty($tanggal_mulai)) {
        $errors[] = "Tanggal mulai harus diisi";
    }
    if (empty($tanggal_selesai)) {
        $errors[] = "Tanggal selesai harus diisi";
    }
    if ($tanggal_mulai && $tanggal_selesai && strtotime($tanggal_mulai) >= strtotime($tanggal_selesai)) {
        $errors[] = "Tanggal selesai harus lebih besar dari tanggal mulai";
    }
    
    if (empty($errors)) {
        $nama_sesi = mysqli_real_escape_string($conn, $nama_sesi);
        $status = mysqli_real_escape_string($conn, $status);
        
        // If setting to active, deactivate other sessions first
        if ($status === 'aktif') {
            mysqli_query($conn, "UPDATE voting_session SET status = 'selesai' WHERE status = 'aktif' AND id != $id");
        }
        
        $query = "UPDATE voting_session SET
                  nama_sesi = '$nama_sesi',
                  tanggal_mulai = '$tanggal_mulai',
                  tanggal_selesai = '$tanggal_selesai',
                  status = '$status'
                  WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            redirect_with_message('sessions.php', 'Sesi voting berhasil diupdate!', 'success');
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sesi - E-Voting</title>
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
                <a href="sessions.php" class="block py-3 px-6 bg-indigo-900 hover:bg-indigo-700 transition border-l-4 border-white">
                    <i class="fas fa-clock mr-2"></i> Sesi Voting
                </a>
                <a href="../logout.php" class="block py-3 px-6 hover:bg-red-700 transition border-l-4 border-transparent mt-10">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-8">
                <div class="mb-6">
                    <a href="sessions.php" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <h2 class="text-3xl font-bold text-gray-800 mt-2">Edit Sesi Voting</h2>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Sesi</label>
                            <input type="text" name="nama_sesi" required
                                   value="<?= isset($_POST['nama_sesi']) ? htmlspecialchars($_POST['nama_sesi']) : $session['nama_sesi'] ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Mulai</label>
                                <input type="datetime-local" name="tanggal_mulai" required
                                       value="<?= isset($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : date('Y-m-d\TH:i', strtotime($session['tanggal_mulai'])) ?>"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Selesai</label>
                                <input type="datetime-local" name="tanggal_selesai" required
                                       value="<?= isset($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : date('Y-m-d\TH:i', strtotime($session['tanggal_selesai'])) ?>"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                            <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="pending" <?= $session['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="aktif" <?= $session['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="selesai" <?= $session['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <a href="sessions.php" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Batal
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                                <i class="fas fa-save mr-1"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
