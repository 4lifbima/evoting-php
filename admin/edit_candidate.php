<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdmin();

// Get candidate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get candidate data
$candidate = getCandidateById($conn, $id);
if (!$candidate) {
    redirect_with_message('candidates.php', 'Kandidat tidak ditemukan!', 'error');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $no_urut = isset($_POST['no_urut']) ? (int)$_POST['no_urut'] : 0;
    $nama_ketua = isset($_POST['nama_ketua']) ? trim($_POST['nama_ketua']) : '';
    $nama_wakil = isset($_POST['nama_wakil']) ? trim($_POST['nama_wakil']) : '';
    $visi = isset($_POST['visi']) ? trim($_POST['visi']) : '';
    $misi = isset($_POST['misi']) ? trim($_POST['misi']) : '';
    $angkatan = isset($_POST['angkatan']) ? (int)$_POST['angkatan'] : date('Y');
    
    // Validation
    if ($no_urut <= 0) {
        $errors[] = "Nomor urut harus diisi";
    }
    if (empty($nama_ketua)) {
        $errors[] = "Nama ketua harus diisi";
    }
    if (empty($visi)) {
        $errors[] = "Visi harus diisi";
    }
    if (empty($misi)) {
        $errors[] = "Misi harus diisi";
    }
    
    // Check duplicate no_urut (exclude current candidate)
    if ($no_urut > 0) {
        $check = mysqli_query($conn, "SELECT id FROM candidates WHERE no_urut = $no_urut AND id != $id");
        if (mysqli_num_rows($check) > 0) {
            $errors[] = "Nomor urut sudah digunakan kandidat lain";
        }
    }
    
    // Handle file upload
    $foto = $candidate['foto']; // Keep existing photo
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_file($_FILES['foto'], '../uploads/candidates/', ['jpg', 'jpeg', 'png', 'gif']);
        if ($upload_result['success']) {
            // Delete old photo if not default
            if ($foto !== 'default.jpg' && file_exists('../uploads/candidates/' . $foto)) {
                unlink('../uploads/candidates/' . $foto);
            }
            $foto = $upload_result['filename'];
        } else {
            $errors[] = "Upload foto: " . $upload_result['message'];
        }
    }
    
    // If no errors, update database
    if (empty($errors)) {
        $nama_ketua = mysqli_real_escape_string($conn, $nama_ketua);
        $nama_wakil = mysqli_real_escape_string($conn, $nama_wakil);
        $visi = mysqli_real_escape_string($conn, $visi);
        $misi = mysqli_real_escape_string($conn, $misi);
        
        $query = "UPDATE candidates SET
                  no_urut = $no_urut,
                  nama_ketua = '$nama_ketua',
                  nama_wakil = '$nama_wakil',
                  foto = '$foto',
                  visi = '$visi',
                  misi = '$misi',
                  angkatan = $angkatan
                  WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            redirect_with_message('candidates.php', 'Kandidat berhasil diupdate!', 'success');
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}

// Get current year for dropdown
$current_year = date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kandidat - E-Voting</title>
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
                <a href="candidates.php" class="block py-3 px-6 bg-indigo-900 hover:bg-indigo-700 transition border-l-4 border-white">
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
                    <a href="candidates.php" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Kelola Kandidat
                    </a>
                    <h2 class="text-3xl font-bold text-gray-800 mt-2">Edit Kandidat</h2>
                </div>

                <!-- Error Messages -->
                <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- No. Urut -->
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="no_urut">
                                    <i class="fas fa-sort-numeric-up mr-1"></i> Nomor Urut <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="no_urut" id="no_urut" required min="1"
                                       value="<?= isset($_POST['no_urut']) ? htmlspecialchars($_POST['no_urut']) : $candidate['no_urut'] ?>"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Contoh: 1, 2, 3">
                            </div>

                            <!-- Angkatan -->
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="angkatan">
                                    <i class="fas fa-graduation-cap mr-1"></i> Angkatan <span class="text-red-500">*</span>
                                </label>
                                <select name="angkatan" id="angkatan" required
                                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <?php for ($year = $current_year; $year >= $current_year - 5; $year--): ?>
                                    <option value="<?= $year ?>" 
                                        <?= (isset($_POST['angkatan']) && $_POST['angkatan'] == $year) || (!isset($_POST['angkatan']) && $candidate['angkatan'] == $year) ? 'selected' : '' ?>>
                                        <?= $year ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <!-- Nama Ketua -->
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="nama_ketua">
                                    <i class="fas fa-user mr-1"></i> Nama Ketua <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_ketua" id="nama_ketua" required
                                       value="<?= isset($_POST['nama_ketua']) ? htmlspecialchars($_POST['nama_ketua']) : $candidate['nama_ketua'] ?>"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Nama lengkap ketua">
                            </div>

                            <!-- Nama Wakil -->
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="nama_wakil">
                                    <i class="fas fa-user-friends mr-1"></i> Nama Wakil
                                </label>
                                <input type="text" name="nama_wakil" id="nama_wakil"
                                       value="<?= isset($_POST['nama_wakil']) ? htmlspecialchars($_POST['nama_wakil']) : $candidate['nama_wakil'] ?>"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Nama lengkap wakil (kosongkan jika tidak ada)">
                            </div>

                            <!-- Visi -->
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="visi">
                                    <i class="fas fa-lightbulb mr-1"></i> Visi <span class="text-red-500">*</span>
                                </label>
                                <textarea name="visi" id="visi" required rows="3"
                                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Tulis visi kandidat..."><?= isset($_POST['visi']) ? htmlspecialchars($_POST['visi']) : $candidate['visi'] ?></textarea>
                            </div>

                            <!-- Misi -->
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="misi">
                                    <i class="fas fa-tasks mr-1"></i> Misi <span class="text-red-500">*</span>
                                </label>
                                <textarea name="misi" id="misi" required rows="5"
                                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Tulis misi kandidat (gunakan enter untuk memisahkan setiap poin)..."><?= isset($_POST['misi']) ? htmlspecialchars($_POST['misi']) : $candidate['misi'] ?></textarea>
                                <p class="text-xs text-gray-500 mt-1">Tips: Tekan Enter untuk membuat poin baru</p>
                            </div>

                            <!-- Foto -->
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="foto">
                                    <i class="fas fa-image mr-1"></i> Foto Kandidat
                                </label>
                                <div class="flex items-center space-x-4">
                                    <?php if ($candidate['foto'] && $candidate['foto'] !== 'default.jpg' && file_exists('../uploads/candidates/' . $candidate['foto'])): ?>
                                    <img src="../uploads/candidates/<?= htmlspecialchars($candidate['foto']) ?>" 
                                         alt="Foto kandidat" 
                                         class="w-24 h-24 object-cover rounded-lg border">
                                    <?php else: ?>
                                    <div class="w-24 h-24 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-3xl text-gray-400"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex-1">
                                        <input type="file" name="foto" id="foto" accept="image/*"
                                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="text-xs text-gray-500 mt-1">Upload foto baru untuk mengganti (Max 2MB)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6 flex items-center justify-end space-x-4">
                            <a href="candidates.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Batal
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                                <i class="fas fa-save mr-2"></i> Update Kandidat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
