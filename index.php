<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Voter must login first via NIM/NIS
requireVoter('voter_login.php');
$voter_id = (int)$_SESSION['voter_id'];

// Get voting session
$sesi_aktif = getActiveSession($conn);
$voting_active = isVotingActive($conn);

// Check if voter already voted
$already_voted = hasVoted($conn, $voter_id);

// Get voter info
$voter_info = getVoterById($conn, $voter_id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voting Realtime</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #5442f5;
            --primary-hover: #4635dc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            color: var(--text-main);
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        .surface-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
            border: 1px solid var(--primary);
        }
        .btn-primary:hover {
            background: var(--primary-hover);
        }
        .btn-soft {
            background: #eef2ff;
            color: var(--primary);
            border: 1px solid #dbe4ff;
        }
        .btn-soft:hover {
            background: #e3e9ff;
        }
        .primary-block {
            background: var(--primary);
            color: #fff;
        }
        .vote-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .candidate-card {
            transition: all 0.25s ease;
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
        }
        .candidate-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
        }
    </style>
</head>
<body class="min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fas fa-vote-yea text-2xl mr-3" style="color:#5442f5;"></i>
                    <span class="font-bold text-xl text-gray-800">E-Voting System</span>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($voter_info): ?>
                    <span class="text-sm text-gray-600 hidden md:block">
                        <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($voter_info['nama']) ?>
                    </span>
                    <?php endif; ?>
                    <span class="text-sm text-gray-600">
                        <i class="far fa-clock mr-1"></i>
                        <span id="live-time"></span>
                    </span>
                    <a href="login.php" class="btn-soft px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-lock mr-1"></i> Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Flash Message -->
        <?php $flash = get_flash_message(); if ($flash): ?>
        <div class="surface-card px-4 py-3 mb-6 text-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-700">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <!-- Voting Status Alert -->
        <?php if (!$voting_active): ?>
        <div class="surface-card text-yellow-700 px-4 py-3 mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Voting is currently not active. Please check the voting schedule.
        </div>
        <?php endif; ?>

        <?php if ($already_voted): ?>
        <div class="surface-card text-green-700 px-4 py-3 mb-6">
            <i class="fas fa-check-circle mr-2"></i>
            You have already voted. Thank you for participating!
        </div>
        <?php endif; ?>

        <!-- Info Sesi Voting -->
        <?php if ($sesi_aktif): ?>
        <div class="primary-block rounded-2xl p-4 mb-8 flex flex-wrap items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-2xl mr-3"></i>
                <div>
                    <h3 class="font-bold"><?= htmlspecialchars($sesi_aktif['nama_sesi']) ?></h3>
                    <p class="text-sm text-indigo-100">
                        <i class="far fa-calendar mr-1"></i> <?= tgl_indo($sesi_aktif['tanggal_mulai']) ?> - <?= tgl_indo($sesi_aktif['tanggal_selesai']) ?>
                    </p>
                </div>
            </div>
            <div class="bg-white/15 px-4 py-2 rounded-lg">
                <i class="fas fa-hourglass-half mr-1"></i>
                <span id="countdown-timer">Loading...</span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Status Partisipasi -->
        <div class="surface-card p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-4xl font-bold" style="color:#5442f5;" id="total-voters">0</div>
                    <div class="text-sm text-gray-600 mt-1"><i class="fas fa-users mr-1"></i>Total Pemilih</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-green-600" id="voted-count">0</div>
                    <div class="text-sm text-gray-600 mt-1"><i class="fas fa-check-circle mr-1"></i>Sudah Memilih</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-purple-600" id="partisipasi">0%</div>
                    <div class="text-sm text-gray-600 mt-1"><i class="fas fa-percent mr-1"></i>Partisipasi</div>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-200 rounded-full h-4">
                <div class="h-4 rounded-full transition-all duration-500"
                     style="background:#5442f5; width:0%;"
                     id="progress-bar"></div>
            </div>
        </div>

        <!-- Daftar Kandidat -->
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-users mr-2" style="color:#5442f5;"></i>
            Pilih Kandidat Favorit Anda
        </h2>

        <div id="candidates-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <!-- Akan diisi oleh jQuery -->
            <div class="col-span-full text-center py-12">
                <div class="animate-pulse-slow">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-3"></i>
                    <p class="text-gray-600">Memuat data kandidat...</p>
                </div>
            </div>
        </div>

        <!-- Hasil Voting Real-time -->
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-chart-bar mr-2" style="color:#5442f5;"></i>
            Hasil Voting Real-time
            <span class="text-sm font-normal text-gray-500 ml-2" id="last-update"></span>
        </h2>

        <div class="surface-card p-6">
            <div id="results-container" class="space-y-6">
                <!-- Akan diisi oleh jQuery -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                    <p class="text-gray-500 mt-2">Memuat hasil...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-sm">&copy; <?= date('Y') ?> E-Voting System. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript for Realtime Updates -->
    <script>
    $(document).ready(function() {
        const alreadyVoted = <?= $already_voted ? 'true' : 'false' ?>;
        const votingActive = <?= $voting_active ? 'true' : 'false' ?>;

        // Update live time
        function updateLiveTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            $('#live-time').text(timeStr);
        }
        setInterval(updateLiveTime, 1000);
        updateLiveTime();

        // Main function to load vote data
        function loadVoteData() {
            $.ajax({
                url: 'api/get_votes.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update statistics
                        $('#total-voters').text(response.statistik.total_voters);
                        $('#voted-count').text(response.statistik.voted);
                        $('#partisipasi').text(response.statistik.partisipasi + '%');
                        $('#progress-bar').css('width', response.statistik.partisipasi + '%');
                        $('#last-update').text('(Update: ' + response.statistik.last_update + ')');

                        // Render results
                        renderResults(response.data);

                        // Load candidates if not already loaded
                        if ($('#candidates-container .candidate-card').length === 0) {
                            loadCandidates();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load data:', error);
                }
            });
        }

        // Load candidates
        function loadCandidates() {
            $.ajax({
                url: 'api/get_candidates.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        renderCandidates(response.data);
                    }
                }
            });
        }

        // Render candidate cards
        function renderCandidates(candidates) {
            let html = '';
            candidates.forEach(candidate => {
                const isDisabled = alreadyVoted || !votingActive;
                const btnText = alreadyVoted ? 'Sudah Memilih' : (!votingActive ? 'Voting Tutup' : 'Pilih Kandidat Ini');
                const btnClass = alreadyVoted || !votingActive 
                    ? 'bg-gray-400 cursor-not-allowed' 
                    : 'btn-primary';
                
                html += `
                    <div class="candidate-card">
                        <div class="h-48 primary-block flex items-center justify-center text-white text-6xl font-bold">
                            ${candidate.no_urut}
                        </div>
                        <div class="p-6">
                            <h3 class="font-bold text-xl text-gray-800 mb-1">${escapeHtml(candidate.nama_ketua)}</h3>
                            <p class="text-gray-600 text-sm mb-3">Wakil: ${candidate.nama_wakil ? escapeHtml(candidate.nama_wakil) : '-'}</p>

                            <div class="border-t pt-4 mt-2">
                                <p class="text-sm text-gray-700 mb-2"><span class="font-semibold">Visi:</span> ${escapeHtml(candidate.visi)}</p>
                                <p class="text-sm text-gray-700 mb-4"><span class="font-semibold">Misi:</span> ${escapeHtml(candidate.misi).replace(/\n/g, '<br>')}</p>

                                <button
                                    class="vote-btn w-full ${btnClass} text-white font-bold py-3 px-4 rounded-lg transition disabled:bg-gray-400"
                                    data-candidate-id="${candidate.id}"
                                    ${isDisabled ? 'disabled' : ''}
                                >
                                    <i class="fas ${alreadyVoted ? 'fa-check-circle' : 'fa-vote-yea'} mr-2"></i>
                                    ${btnText}
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#candidates-container').html(html);
        }

        // Render voting results
        function renderResults(results) {
            if (results.length === 0) {
                $('#results-container').html('<p class="text-center text-gray-500">Belum ada suara masuk</p>');
                return;
            }

            let html = '';
            results.forEach((candidate, index) => {
                const isLeader = index === 0 && candidate.jumlah_suara > 0;
                const barColor = isLeader ? '#3726dc' : '#5442f5';
                
                html += `
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex items-center">
                                <span class="font-semibold text-gray-800">${escapeHtml(candidate.nama_ketua)}</span>
                                ${isLeader ? '<span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full"><i class="fas fa-crown mr-1"></i>Pemimpin</span>' : ''}
                            </div>
                            <span class="text-sm text-gray-600">
                                ${candidate.jumlah_suara} suara (${candidate.persentase}%)
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                            <div class="h-4 rounded-full transition-all duration-500"
                                 style="background: ${barColor}; width: ${candidate.persentase}%"></div>
                        </div>
                    </div>
                `;
            });
            $('#results-container').html(html);
        }

        // Handle vote button click
        $(document).on('click', '.vote-btn', function() {
            const $btn = $(this);
            const candidateId = $btn.data('candidate-id');

            if (!votingActive) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Voting Tidak Aktif',
                    text: 'Maaf, voting sedang tidak aktif. Silakan periksa jadwal voting.'
                });
                return;
            }

            // Confirm vote
            Swal.fire({
                title: 'Konfirmasi Pilihan',
                text: 'Yakin ingin memilih kandidat ini? Tindakan ini tidak dapat dibatalkan!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#5442f5',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Pilih!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitVote(candidateId, $btn);
                }
            });
        });

        // Submit vote
        function submitVote(candidateId, $btn) {
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...');

            $.ajax({
                url: 'api/submit_vote.php',
                method: 'POST',
                data: {
                    candidate_id: candidateId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.requires_login) {
                        window.location.href = response.redirect_to || 'voter_login.php';
                        return;
                    }

                    if (response.success) {
                        const logoutDelay = (response.auto_logout_in || 3) * 1000;

                        // Show success notification
                        Swal.fire({
                            icon: 'success',
                            title: 'Vote Berhasil!',
                            html: `Terima kasih telah berpartisipasi.<br><br>
                                   <strong>Anda akan logout otomatis dalam 3 detik.</strong>`,
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });

                        // Disable all vote buttons
                        $('.vote-btn').prop('disabled', true).html('<i class="fas fa-check-circle mr-2"></i>Sudah Memilih');

                        // Refresh data
                        loadVoteData();

                        // Auto logout after 3 seconds
                        setTimeout(function() {
                            window.location.href = response.redirect_to || 'logout.php?reason=vote-complete';
                        }, logoutDelay);
                    } else {
                        let icon = 'error';
                        if (response.already_voted) {
                            icon = 'warning';
                            $('.vote-btn').prop('disabled', true).html('<i class="fas fa-check-circle mr-2"></i>Sudah Memilih');
                        }
                        
                        Swal.fire({
                            icon: icon,
                            title: 'Oops...',
                            text: response.message
                        });
                        
                        $btn.prop('disabled', false).html('<i class="fas fa-vote-yea mr-2"></i>Pilih Kandidat Ini');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal terhubung ke server. Silakan coba lagi.'
                    });
                    $btn.prop('disabled', false).html('<i class="fas fa-vote-yea mr-2"></i>Pilih Kandidat Ini');
                }
            });
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Initial load
        loadVoteData();

        // Auto-refresh every 3 seconds
        setInterval(loadVoteData, 3000);
    });
    </script>

    <!-- Countdown Timer Script -->
    <?php if ($sesi_aktif): ?>
    <script>
    const endTime = new Date('<?= $sesi_aktif['tanggal_selesai'] ?>').getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
            $('#countdown-timer').text('Selesai');
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        let timeStr = '';
        if (days > 0) timeStr += days + 'd ';
        timeStr += hours + 'j ' + minutes + 'm ' + seconds + 'd';

        $('#countdown-timer').text(timeStr);
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();
    </script>
    <?php endif; ?>
</body>
</html>
