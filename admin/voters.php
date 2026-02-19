<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdmin();

// Handle import voters
if (isset($_POST['import_voters'])) {
    $success_count = 0;
    $error_count = 0;
    $errors = [];
    
    if (isset($_FILES['voters_file']) && $_FILES['voters_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['voters_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        // Skip header row
        $header = fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 3) {
                $nim_nis = mysqli_real_escape_string($conn, $row[0]);
                $nama = mysqli_real_escape_string($conn, $row[1]);
                $kelas_jurusan = isset($row[2]) ? mysqli_real_escape_string($conn, $row[2]) : '';
                $angkatan = isset($row[3]) ? (int)$row[3] : date('Y');
                
                $query = "INSERT INTO voters (nim_nis, nama, kelas_jurusan, angkatan) 
                         VALUES ('$nim_nis', '$nama', '$kelas_jurusan', $angkatan)";
                
                if (mysqli_query($conn, $query)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }
        
        fclose($handle);
        
        if ($success_count > 0) {
            redirect_with_message('voters.php', "Berhasil import $success_count pemilih. Gagal: $error_count", 'success');
        }
    } else {
        $errors[] = "File tidak valid";
    }
}

// Get all voters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT * FROM voters WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (nama LIKE '%$search%' OR nim_nis LIKE '%$search%')";
}

if (!empty($status_filter)) {
    $query .= " AND status = '$status_filter'";
}

$query .= " ORDER BY nama ASC";

$voters_result = mysqli_query($conn, $query);

// Get stats
$total_voters = mysqli_num_rows($voters_result);
$active_voters = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM voters WHERE status = 'aktif'"))['total'];
$inactive_voters = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM voters WHERE status = 'tidak_aktif'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pemilih - E-Voting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
                <a href="voters.php" class="block py-3 px-6 bg-indigo-900 hover:bg-indigo-700 transition border-l-4 border-white">
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
                <!-- Flash Message -->
                <?php $flash = get_flash_message(); if ($flash): ?>
                <div class="bg-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-100 border border-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-400 text-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">Data Pemilih</h2>
                        <p class="text-gray-600 mt-1">Kelola data pemilih yang berhak voting</p>
                    </div>
                    <div class="gap-2 flex">
                        <a href="reset_votes.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                            <i class="fas fa-file-export mr-2"></i> Reset Suara
                        </a>
                        <button onclick="document.getElementById('importModal').classList.remove('hidden')" 
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                            <i class="fas fa-file-import mr-2"></i> Import Pemilih
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="bg-blue-100 rounded-full p-4 mr-4">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Pemilih</p>
                                <p class="text-2xl font-bold"><?= $total_voters ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="bg-green-100 rounded-full p-4 mr-4">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status Aktif</p>
                                <p class="text-2xl font-bold"><?= $active_voters ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="bg-red-100 rounded-full p-4 mr-4">
                                <i class="fas fa-times-circle text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Tidak Aktif</p>
                                <p class="text-2xl font-bold"><?= $inactive_voters ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <form method="GET" action="" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-64">
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Cari nama atau NIM/NIS..."
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua Status</option>
                                <option value="aktif" <?= $status_filter === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="tidak_aktif" <?= $status_filter === 'tidak_aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                            </select>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            <i class="fas fa-search mr-1"></i> Cari
                        </button>
                        <a href="voters.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            <i class="fas fa-redo"></i>
                        </a>
                    </form>
                </div>

                <!-- Voters Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIM/NIS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas/Jurusan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Angkatan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sudah Vote</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (mysqli_num_rows($voters_result) > 0): ?>
                                <?php while ($voter = mysqli_fetch_assoc($voters_result)): 
                                    $has_voted = hasVoted($conn, $voter['id']);
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?= htmlspecialchars($voter['nim_nis']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?= htmlspecialchars($voter['nama']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?= htmlspecialchars($voter['kelas_jurusan']) ?: '-' ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?= $voter['angkatan'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?= $voter['status'] === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $voter['status'] === 'aktif' ? 'Aktif' : 'Tidak Aktif' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($has_voted): ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-check mr-1"></i>Sudah
                                        </span>
                                        <?php else: ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-times mr-1"></i>Belum
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-gray-500">Belum ada data pemilih</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Import Pemilih</h3>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Upload File CSV</label>
                    <input type="file" name="voters_file" accept=".csv" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Format: NIM/NIS, Nama, Kelas/Jurusan, Angkatan</p>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" name="import_voters"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                        <i class="fas fa-upload mr-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
