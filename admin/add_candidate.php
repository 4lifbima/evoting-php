<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/admin_layout.php';

// Require admin login
requireAdmin();

$errors = [];
$success = false;

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
    
    // Check duplicate no_urut
    if ($no_urut > 0) {
        $check = mysqli_query($conn, "SELECT id FROM candidates WHERE no_urut = $no_urut");
        if (mysqli_num_rows($check) > 0) {
            $errors[] = "Nomor urut sudah digunakan";
        }
    }
    
    // Handle file upload
    $foto = 'default.jpg';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_file($_FILES['foto'], '../uploads/candidates/', ['jpg', 'jpeg', 'png', 'gif']);
        if ($upload_result['success']) {
            $foto = $upload_result['filename'];
        } else {
            $errors[] = "Upload foto: " . $upload_result['message'];
        }
    }
    
    // If no errors, insert to database
    if (empty($errors)) {
        $nama_ketua = mysqli_real_escape_string($conn, $nama_ketua);
        $nama_wakil = mysqli_real_escape_string($conn, $nama_wakil);
        $visi = mysqli_real_escape_string($conn, $visi);
        $misi = mysqli_real_escape_string($conn, $misi);
        
        $query = "INSERT INTO candidates (no_urut, nama_ketua, nama_wakil, foto, visi, misi, angkatan)
                  VALUES ($no_urut, '$nama_ketua', '$nama_wakil', '$foto', '$visi', '$misi', $angkatan)";
        
        if (mysqli_query($conn, $query)) {
            redirect_with_message('candidates.php', 'Kandidat berhasil ditambahkan!', 'success');
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
<?= renderAdminHead('Tambah Kandidat - E-Voting') ?>
<body class="bg-slate-50">
    <div class="flex h-screen overflow-hidden">
        <?= renderAdminSidebar('candidates', $_SESSION['admin_nama'] ?? 'Administrator') ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-8">
                <div class="mb-6">
                    <a href="candidates.php" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Kelola Kandidat
                    </a>
                    <h2 class="text-3xl font-bold text-gray-800 mt-2">Tambah Kandidat Baru</h2>
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
                                       value="<?= isset($_POST['no_urut']) ? htmlspecialchars($_POST['no_urut']) : '' ?>"
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
                                    <option value="<?= $year ?>" <?= (isset($_POST['angkatan']) && $_POST['angkatan'] == $year) || (!isset($_POST['angkatan']) && $year == $current_year) ? 'selected' : '' ?>>
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
                                       value="<?= isset($_POST['nama_ketua']) ? htmlspecialchars($_POST['nama_ketua']) : '' ?>"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Nama lengkap ketua">
                            </div>

                            <!-- Nama Wakil -->
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="nama_wakil">
                                    <i class="fas fa-user-friends mr-1"></i> Nama Wakil
                                </label>
                                <input type="text" name="nama_wakil" id="nama_wakil"
                                       value="<?= isset($_POST['nama_wakil']) ? htmlspecialchars($_POST['nama_wakil']) : '' ?>"
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
                                          placeholder="Tulis visi kandidat..."><?= isset($_POST['visi']) ? htmlspecialchars($_POST['visi']) : '' ?></textarea>
                            </div>

                            <!-- Misi -->
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="misi">
                                    <i class="fas fa-tasks mr-1"></i> Misi <span class="text-red-500">*</span>
                                </label>
                                <textarea name="misi" id="misi" required rows="5"
                                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Tulis misi kandidat (gunakan enter untuk memisahkan setiap poin)..."><?= isset($_POST['misi']) ? htmlspecialchars($_POST['misi']) : '' ?></textarea>
                                <p class="text-xs text-gray-500 mt-1">Tips: Tekan Enter untuk membuat poin baru</p>
                            </div>

                            <!-- Foto -->
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="foto">
                                    <i class="fas fa-image mr-1"></i> Foto Kandidat
                                </label>
                                <input type="file" name="foto" id="foto" accept="image/*"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG, GIF (Max 2MB)</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6 flex items-center justify-end space-x-4">
                            <a href="candidates.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Batal
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                                <i class="fas fa-save mr-2"></i> Simpan Kandidat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
