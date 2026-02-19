<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/admin_layout.php';

// Require admin login
requireAdmin();

// Get vote results
$results = getVoteResults($conn);
$stats = getStatistics($conn);

// Get total valid votes
$total_votes_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM votes");
$total_votes = mysqli_fetch_assoc($total_votes_query)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<?= renderAdminHead('Hasil Voting - E-Voting') ?>
<body class="bg-slate-50">
    <div class="flex h-screen overflow-hidden">
        <?= renderAdminSidebar('results', $_SESSION['admin_nama'] ?? 'Administrator') ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-8">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold text-gray-800">Hasil Voting Real-time</h2>
                    <p class="text-gray-600 mt-1">Monitor hasil voting secara langsung</p>
                </div>

                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <div class="text-4xl font-bold text-blue-600"><?= $stats['total_voters'] ?></div>
                            <div class="text-sm text-gray-600 mt-1">Total Pemilih</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <div class="text-4xl font-bold text-green-600"><?= $stats['total_voted'] ?></div>
                            <div class="text-sm text-gray-600 mt-1">Suara Masuk</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <div class="text-4xl font-bold text-purple-600"><?= $stats['partisipasi'] ?>%</div>
                            <div class="text-sm text-gray-600 mt-1">Partisipasi</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-center">
                            <div class="text-4xl font-bold text-yellow-600"><?= $total_votes ?></div>
                            <div class="text-sm text-gray-600 mt-1">Total Suara Valid</div>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">Hasil Per Kandidat</h3>
                    
                    <?php if (count($results) > 0): ?>
                    <div class="space-y-6">
                        <?php foreach ($results as $index => $candidate): 
                            $isLeader = $index === 0 && $candidate['jumlah_suara'] > 0;
                        ?>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-600 font-bold mr-3">
                                        <?= $candidate['no_urut'] ?>
                                    </span>
                                    <div>
                                        <div class="font-semibold text-lg"><?= htmlspecialchars($candidate['nama_ketua']) ?></div>
                                        <?php if ($candidate['nama_wakil']): ?>
                                        <div class="text-sm text-gray-600">Wakil: <?= htmlspecialchars($candidate['nama_wakil']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($isLeader): ?>
                                    <span class="ml-3 bg-yellow-100 text-yellow-800 text-xs px-3 py-1 rounded-full font-semibold">
                                        <i class="fas fa-crown mr-1"></i>Pemimpin Sementara
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-800"><?= $candidate['jumlah_suara'] ?></div>
                                    <div class="text-sm text-gray-600"><?= $candidate['persentase'] ?>%</div>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-6">
                                <div class="h-6 rounded-full transition-all duration-500 flex items-center justify-end pr-2"
                                     style="background-color:#5442f5; width: <?= $candidate['persentase'] ?>%">
                                    <?php if ($candidate['persentase'] > 10): ?>
                                    <span class="text-xs text-white font-semibold"><?= $candidate['persentase'] ?>%</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Belum ada suara masuk</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Vote Timeline -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Timeline Suara</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Waktu</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Pemilih</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Kandidat</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $timeline_query = "SELECT v.vote_time, vr.nama as voter_name, vr.nim_nis, c.nama_ketua, v.ip_address
                                                  FROM votes v
                                                  JOIN voters vr ON v.voter_id = vr.id
                                                  JOIN candidates c ON v.candidate_id = c.id
                                                  ORDER BY v.vote_time DESC
                                                  LIMIT 50";
                                $timeline_result = mysqli_query($conn, $timeline_query);
                                
                                if (mysqli_num_rows($timeline_result) > 0):
                                    while ($row = mysqli_fetch_assoc($timeline_result)):
                                ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm"><?= date('d/m/Y H:i:s', strtotime($row['vote_time'])) ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium"><?= htmlspecialchars($row['voter_name']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($row['nim_nis']) ?></div>
                                    </td>
                                    <td class="px-4 py-3 text-sm"><?= htmlspecialchars($row['nama_ketua']) ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($row['ip_address']) ?></td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                        Belum ada data vote
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
