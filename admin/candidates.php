<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/admin_layout.php';

// Require admin login
requireAdmin();

// Get all candidates with vote count
$query = "SELECT c.*, 
          COUNT(v.id) as total_votes
          FROM candidates c
          LEFT JOIN votes v ON c.id = v.candidate_id
          GROUP BY c.id
          ORDER BY c.no_urut ASC";
$candidates_result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<?= renderAdminHead('Kelola Kandidat - E-Voting', '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>') ?>
<body class="bg-slate-50">
    <div class="flex h-screen overflow-hidden">
        <?= renderAdminSidebar('candidates', $_SESSION['admin_nama'] ?? 'Administrator') ?>

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
                        <h2 class="text-3xl font-bold text-gray-800">Kelola Kandidat</h2>
                        <p class="text-gray-600 mt-1">Daftar kandidat peserta voting</p>
                    </div>
                    <a href="add_candidate.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-plus-circle mr-2"></i> Tambah Kandidat
                    </a>
                </div>

                <!-- Candidates Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Urut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Ketua</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Wakil</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suara</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (mysqli_num_rows($candidates_result) > 0): ?>
                                <?php while ($candidate = mysqli_fetch_assoc($candidates_result)): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-600 font-bold">
                                            <?= $candidate['no_urut'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($candidate['nama_ketua']) ?></div>
                                        <div class="text-sm text-gray-500">Angkatan: <?= $candidate['angkatan'] ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($candidate['nama_wakil']) ?: '-' ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 truncate max-w-xs"><?= htmlspecialchars($candidate['visi']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <?= $candidate['total_votes'] ?> suara
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="edit_candidate.php?id=<?= $candidate['id'] ?>" 
                                               class="text-blue-600 hover:text-blue-900"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_candidate.php?id=<?= $candidate['id'] ?>" 
                                               class="text-red-600 hover:text-red-900"
                                               title="Hapus"
                                               onclick="return confirmDelete(<?= $candidate['id'] ?>, '<?= htmlspecialchars($candidate['nama_ketua']) ?>', <?= $candidate['total_votes'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-gray-500">Belum ada data kandidat</p>
                                        <a href="add_candidate.php" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                                            <i class="fas fa-plus mr-1"></i> Tambah kandidat pertama
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete(id, name, votes) {
        if (votes > 0) {
            Swal.fire({
                title: 'Kandidat Sudah Memiliki Suara!',
                html: `Kandidat <strong>${name}</strong> sudah memiliki <strong>${votes} suara</strong>.<br><br>
                       <span class="text-red-600">Menghapus kandidat akan menghapus semua suaranya!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `delete_candidate.php?id=${id}`;
                }
            });
            return false;
        } else {
            return confirm(`Yakin ingin menghapus kandidat "${name}"?`);
        }
    }
    </script>
</body>
</html>
