<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/admin_layout.php';

// Require admin login
requireAdmin();

$errors = [];
$success = false;

// Handle form submission
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
            mysqli_query($conn, "UPDATE voting_session SET status = 'selesai' WHERE status = 'aktif'");
        }
        
        $query = "INSERT INTO voting_session (nama_sesi, tanggal_mulai, tanggal_selesai, status)
                  VALUES ('$nama_sesi', '$tanggal_mulai', '$tanggal_selesai', '$status')";
        
        if (mysqli_query($conn, $query)) {
            redirect_with_message('sessions.php', 'Sesi voting berhasil ditambahkan!', 'success');
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}

// Get all sessions
$sessions_query = "SELECT * FROM voting_session ORDER BY tanggal_mulai DESC";
$sessions_result = mysqli_query($conn, $sessions_query);

// Get active session
$active_session = getActiveSession($conn);
?>
<!DOCTYPE html>
<html lang="id">
<?= renderAdminHead('Sesi Voting - E-Voting', '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>') ?>
<body class="bg-slate-50">
    <div class="flex h-screen overflow-hidden">
        <?= renderAdminSidebar('sessions', $_SESSION['admin_nama'] ?? 'Administrator') ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-8">
                <!-- Flash Message -->
                <?php $flash = get_flash_message(); if ($flash): ?>
                <div class="bg-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-100 border border-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-400 text-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">Sesi Voting</h2>
                        <p class="text-gray-600 mt-1">Kelola periode dan jadwal voting</p>
                    </div>
                    <button onclick="toggleModal('addSessionModal', true)" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Sesi
                    </button>
                </div>

                <!-- Active Session Alert -->
                <?php if ($active_session): ?>
                <div class="bg-green-50 border-2 border-green-300 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                            <div>
                                <h3 class="font-bold text-green-800"><?= htmlspecialchars($active_session['nama_sesi']) ?></h3>
                                <p class="text-sm text-green-700">
                                    <i class="far fa-calendar mr-1"></i> <?= tgl_indo($active_session['tanggal_mulai']) ?> - <?= tgl_indo($active_session['tanggal_selesai']) ?>
                                </p>
                            </div>
                        </div>
                        <div class="bg-green-600 text-white px-4 py-2 rounded-full text-sm font-semibold">
                            <i class="fas fa-circle text-xs mr-1"></i> Sedang Berlangsung
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-bold text-yellow-800">Tidak Ada Sesi Aktif</h3>
                            <p class="text-sm text-yellow-700">Voting tidak dapat dilakukan sampai ada sesi yang aktif</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Sessions Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Sesi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Mulai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Selesai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (mysqli_num_rows($sessions_result) > 0): ?>
                                <?php while ($session = mysqli_fetch_assoc($sessions_result)): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($session['nama_sesi']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= date('d/m/Y H:i', strtotime($session['tanggal_mulai'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= date('d/m/Y H:i', strtotime($session['tanggal_selesai'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $status_class = '';
                                        $status_text = '';
                                        $status_icon = '';
                                        
                                        switch($session['status']) {
                                            case 'aktif':
                                                $status_class = 'bg-green-100 text-green-800';
                                                $status_text = 'Aktif';
                                                $status_icon = 'fa-check-circle';
                                                break;
                                            case 'selesai':
                                                $status_class = 'bg-red-100 text-red-800';
                                                $status_text = 'Selesai';
                                                $status_icon = 'fa-times-circle';
                                                break;
                                            case 'pending':
                                                $status_class = 'bg-yellow-100 text-yellow-800';
                                                $status_text = 'Pending';
                                                $status_icon = 'fa-clock';
                                                break;
                                        }
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?= $status_class ?>">
                                            <i class="fas <?= $status_icon ?> mr-1"></i><?= $status_text ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <?php if ($session['status'] !== 'aktif'): ?>
                                            <a href="activate_session.php?id=<?= $session['id'] ?>" 
                                               class="text-green-600 hover:text-green-900"
                                               title="Aktifkan"
                                               onclick="return confirm('Aktifkan sesi ini? Sesi aktif lainnya akan dinonaktifkan.')">
                                                <i class="fas fa-play"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="edit_session.php?id=<?= $session['id'] ?>" 
                                               class="text-blue-600 hover:text-blue-900"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_session.php?id=<?= $session['id'] ?>" 
                                               class="text-red-600 hover:text-red-900"
                                               title="Hapus"
                                               onclick="return confirm('Hapus sesi ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-gray-500">Belum ada sesi voting</p>
                                        <button onclick="toggleModal('addSessionModal', true)" 
                                                class="text-blue-600 hover:text-blue-800 mt-2">
                                            <i class="fas fa-plus mr-1"></i> Tambah sesi pertama
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Session Modal -->
    <div id="addSessionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Tambah Sesi Voting</h3>
                <button onclick="toggleModal('addSessionModal', false)" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Sesi</label>
                    <input type="text" name="nama_sesi" required
                           placeholder="Contoh: Pemilihan Ketua OSIS 2024"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Mulai</label>
                        <input type="datetime-local" name="tanggal_mulai" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Selesai</label>
                        <input type="datetime-local" name="tanggal_selesai" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="pending">Pending</option>
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="toggleModal('addSessionModal', false)"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleModal(modalId, show) {
        const modal = document.getElementById(modalId);
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
    </script>
</body>
</html>
