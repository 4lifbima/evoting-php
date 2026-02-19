<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/admin_layout.php';

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
<?= renderAdminHead('Edit Sesi - E-Voting') ?>
<body class="bg-slate-50">
    <div class="flex h-screen overflow-hidden">
        <?= renderAdminSidebar('sessions', $_SESSION['admin_nama'] ?? 'Administrator') ?>

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
