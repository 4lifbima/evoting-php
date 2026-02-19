<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdmin();

// Get statistics
$stats = getStatistics($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - E-Voting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
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
                <a href="index.php" class="block py-3 px-6 bg-indigo-900 hover:bg-indigo-700 transition border-l-4 border-white">
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
                <!-- Flash Message -->
                <?php $flash = get_flash_message(); if ($flash): ?>
                <div class="bg-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-100 border border-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-400 text-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-800">Dashboard</h2>
                    <div class="text-sm text-gray-600">
                        <i class="far fa-calendar-alt mr-1"></i> <?= date('d F Y, H:i') ?>
                        <span class="ml-3 text-xs text-gray-500" id="admin-last-update">(Realtime: -)</span>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="bg-blue-100 rounded-full p-4 mr-4">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Pemilih</p>
                                <p class="text-2xl font-bold" id="stat-total-voters"><?= $stats['total_voters'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="bg-green-100 rounded-full p-4 mr-4">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Sudah Memilih</p>
                                <p class="text-2xl font-bold" id="stat-total-voted"><?= $stats['total_voted'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="bg-yellow-100 rounded-full p-4 mr-4">
                                <i class="fas fa-percent text-yellow-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Partisipasi</p>
                                <p class="text-2xl font-bold" id="stat-partisipasi"><?= $stats['partisipasi'] ?>%</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="bg-purple-100 rounded-full p-4 mr-4">
                                <i class="fas fa-trophy text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Kandidat</p>
                                <p class="text-2xl font-bold" id="stat-total-candidates"><?= $stats['total_candidates'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Grafik Batang Voting Realtime</h3>
                    <p class="text-sm text-gray-500 mb-6">Batang akan naik otomatis ketika suara kandidat bertambah.</p>
                    <div id="chart"></div>
                </div>
            </div>
        </div>
    </div>
    <script>
    $(document).ready(function() {
        const colors = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26A69A', '#D10CE8', '#6D4C41', '#2E93fA'];
        let voteChart = null;

        function splitLabel(name) {
            const words = String(name || '').trim().split(/\s+/);
            if (words.length <= 1) return name || '-';
            if (words.length === 2) return [words[0], words[1]];
            return [words[0], words.slice(1).join(' ')];
        }

        function initChart() {
            var options = {
                series: [{
                    data: []
                }],
                chart: {
                    height: 380,
                    type: 'bar',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 900
                    },
                    events: {
                        click: function(chart, w, e) {}
                    }
                },
                colors: colors,
                plotOptions: {
                    bar: {
                        columnWidth: '45%',
                        distributed: true
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: false
                },
                noData: {
                    text: 'Memuat data voting...'
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + ' suara';
                        }
                    }
                },
                xaxis: {
                    categories: [],
                    labels: {
                        style: {
                            colors: [],
                            fontSize: '12px'
                        }
                    }
                }
            };

            voteChart = new ApexCharts(document.querySelector('#chart'), options);
            voteChart.render();
        }

        function renderVoteChart(results) {
            if (!voteChart) return;
            const safeResults = Array.isArray(results) ? results : [];

            const data = safeResults.map(function(item) {
                return Number(item.jumlah_suara) || 0;
            });
            const categories = safeResults.map(function(item) {
                return splitLabel(item.nama_ketua);
            });
            const labelColors = safeResults.map(function(_, i) {
                return colors[i % colors.length];
            });

            voteChart.updateOptions({
                xaxis: {
                    categories: categories,
                    labels: {
                        style: {
                            colors: labelColors,
                            fontSize: '12px'
                        }
                    }
                }
            }, false, true);

            voteChart.updateSeries([{
                data: data
            }], true);
        }

        function loadAdminRealtime() {
            $.ajax({
                url: '../api/admin_dashboard_realtime.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (!response.success) {
                        return;
                    }

                    $('#stat-total-voters').text(response.stats.total_voters);
                    $('#stat-total-voted').text(response.stats.total_voted);
                    $('#stat-partisipasi').text(response.stats.partisipasi + '%');
                    $('#stat-total-candidates').text(response.stats.total_candidates);
                    renderVoteChart(response.candidate_results || []);
                    $('#admin-last-update').text('(Realtime: ' + response.last_update + ')');
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        window.location.href = '../login.php';
                    }
                }
            });
        }

        initChart();
        loadAdminRealtime();
        setInterval(loadAdminRealtime, 2000);
    });
    </script>
</body>
</html>
