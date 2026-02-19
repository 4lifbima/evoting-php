<?php
/**
 * Admin layout helpers:
 * - Global head (font, icon set, theme tokens)
 * - Shared white sidebar with solid primary color
 */

if (!function_exists('admin_active_class')) {
    function admin_active_class($active, $key) {
        return $active === $key ? 'admin-nav-link active' : 'admin-nav-link';
    }
}

if (!function_exists('renderAdminHead')) {
    function renderAdminHead($title, $extraHead = '') {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $extra = $extraHead;
        return <<<HTML
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeTitle}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {$extra}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <style>
        :root {
            --admin-primary: #5442f5;
            --admin-text: #0f172a;
            --admin-muted: #64748b;
            --admin-border: #e2e8f0;
            --admin-bg: #f8fafc;
            --admin-white: #ffffff;
        }

        body {
            font-family: "Plus Jakarta Sans", sans-serif;
            background: var(--admin-bg);
            color: var(--admin-text);
        }

        .admin-sidebar {
            background: var(--admin-white);
            border-right: 1px solid var(--admin-border);
        }

        .admin-brand-icon {
            color: var(--admin-primary);
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #334155;
            font-weight: 600;
            padding: 11px 16px;
            margin: 6px 12px;
            border-radius: 12px;
            transition: background-color .2s ease, color .2s ease;
        }

        .admin-nav-link:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .admin-nav-link.active {
            background: var(--admin-primary);
            color: #ffffff;
        }

        .admin-primary {
            background: var(--admin-primary) !important;
            color: #ffffff !important;
        }

        .admin-primary:hover {
            background: #4635dc !important;
        }

        .admin-card {
            background: var(--admin-white);
            border: 1px solid var(--admin-border);
            border-radius: 16px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        }

        .bg-blue-600, .bg-indigo-600 {
            background-color: var(--admin-primary) !important;
        }

        .hover\:bg-blue-700:hover, .hover\:bg-indigo-700:hover {
            background-color: #4635dc !important;
        }

        .text-blue-600, .text-indigo-600 {
            color: var(--admin-primary) !important;
        }

        .hover\:text-blue-800:hover, .hover\:text-blue-900:hover {
            color: #3b2ec0 !important;
        }

        .focus\:ring-blue-500:focus, .focus\:ring-blue-600:focus {
            --tw-ring-color: rgba(84, 66, 245, .35) !important;
        }

        .admin-mobile-topbar {
            background: var(--admin-white);
            border-bottom: 1px solid var(--admin-border);
            color: var(--admin-text);
        }
    </style>
</head>
HTML;
    }
}

if (!function_exists('renderAdminSidebar')) {
    function renderAdminSidebar($active, $adminName = 'Administrator') {
        $safeAdminName = htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8');
        $dashboardClass = admin_active_class($active, 'dashboard');
        $candidatesClass = admin_active_class($active, 'candidates');
        $votersClass = admin_active_class($active, 'voters');
        $resultsClass = admin_active_class($active, 'results');
        $sessionsClass = admin_active_class($active, 'sessions');

        return <<<HTML
<div class="w-72 admin-sidebar flex-shrink-0 hidden md:flex md:flex-col">
    <div class="px-6 py-7 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl admin-primary flex items-center justify-center shadow-sm">
                <iconify-icon icon="solar:shield-check-bold-duotone" width="22"></iconify-icon>
            </div>
            <div>
                <h1 class="text-lg font-extrabold text-slate-900 leading-tight">Admin Dashboard</h1>
                <p class="text-xs text-slate-500 mt-1">{$safeAdminName}</p>
            </div>
        </div>
    </div>

    <nav class="py-4 flex-1">
        <a href="index.php" class="{$dashboardClass}">
            <iconify-icon icon="solar:widget-5-bold-duotone" width="20"></iconify-icon>
            <span>Dashboard</span>
        </a>
        <a href="candidates.php" class="{$candidatesClass}">
            <iconify-icon icon="solar:users-group-rounded-bold-duotone" width="20"></iconify-icon>
            <span>Kelola Kandidat</span>
        </a>
        <a href="voters.php" class="{$votersClass}">
            <iconify-icon icon="solar:user-check-rounded-bold-duotone" width="20"></iconify-icon>
            <span>Data Pemilih</span>
        </a>
        <a href="results.php" class="{$resultsClass}">
            <iconify-icon icon="solar:chart-square-bold-duotone" width="20"></iconify-icon>
            <span>Hasil Voting</span>
        </a>
        <a href="sessions.php" class="{$sessionsClass}">
            <iconify-icon icon="solar:calendar-bold-duotone" width="20"></iconify-icon>
            <span>Sesi Voting</span>
        </a>
    </nav>

    <div class="px-4 pb-5">
        <a href="#" class="admin-nav-link js-admin-logout">
            <iconify-icon icon="solar:logout-3-bold-duotone" width="20"></iconify-icon>
            <span>Logout</span>
        </a>
    </div>
</div>

<div class="md:hidden admin-mobile-topbar p-4 flex justify-between items-center">
    <div class="flex items-center gap-2">
        <iconify-icon icon="solar:shield-check-bold-duotone" width="20" class="admin-brand-icon"></iconify-icon>
        <h1 class="font-bold">Admin Dashboard</h1>
    </div>
    <a href="#" class="text-slate-600 hover:text-slate-900 js-admin-logout">
        <iconify-icon icon="solar:logout-3-bold-duotone" width="22"></iconify-icon>
    </a>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.js-admin-logout').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Keluar dari admin?',
                text: 'Sesi admin akan diakhiri.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#5442f5'
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = '../logout.php?silent=1';
                }
            });
        });
    });
});
</script>
HTML;
    }
}
?>
