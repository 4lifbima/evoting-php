<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Redirect if voter already logged in
if (isVoterLoggedIn()) {
    $current_voter_id = (int)($_SESSION['voter_id'] ?? 0);
    if ($current_voter_id > 0 && hasVoted($conn, $current_voter_id)) {
        logoutVoter();
        $_SESSION['flash_message'] = 'Maaf anda sudah melakukan vote. Silahkan hubungi panitia jika ada kekeliruan dalam pemilihan anda.';
        $_SESSION['flash_type'] = 'warning';
    } else {
        header('Location: index.php');
        exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim_nis = trim($_POST['nim_nis'] ?? '');

    if ($nim_nis === '') {
        $error = 'NIM/NIS wajib diisi.';
    } else {
        $result = loginVoter($conn, $nim_nis);
        if ($result['success']) {
            $logged_voter_id = (int)($result['voter']['id'] ?? 0);
            if ($logged_voter_id > 0 && hasVoted($conn, $logged_voter_id)) {
                logoutVoter();
                $error = 'Maaf anda sudah melakukan vote. Silahkan hubungi panitia jika ada kekeliruan dalam pemilihan anda.';
            } else {
                redirect_with_message('index.php', 'Login berhasil. Silakan pilih kandidat Anda.', 'success');
            }
        }
        if ($error === '') {
            $error = 'NIM/NIS tidak ditemukan atau tidak aktif.';
        }
    }
}

$flash = get_flash_message();
$swalMessage = '';
$swalIcon = '';

if ($flash && !empty($flash['message'])) {
    $swalMessage = $flash['message'];
    $swalIcon = $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'error');
} elseif (!empty($error)) {
    $swalMessage = $error;
    $swalIcon = 'warning';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Voter - E-Voting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #5442f5;
            --primary-hover: #4635dc;
            --border: #e2e8f0;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
        }
        .surface {
            border: 1px solid var(--border);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.06);
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
        }
        .btn-primary:hover {
            background: var(--primary-hover);
        }
        .focus-primary:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(84, 66, 245, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl surface p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#eef2ff;">
                <i class="fas fa-id-card text-3xl" style="color:#5442f5;"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Login Pemilih</h1>
            <p class="text-gray-600">Masuk menggunakan NIM/NIS</p>
        </div>

        <form method="POST" action="">
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nim_nis">
                    <i class="fas fa-user-graduate mr-1"></i> NIM / NIS
                </label>
                <input type="text" name="nim_nis" id="nim_nis" required autofocus
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus-primary"
                       placeholder="Contoh: 2024001">
            </div>

            <button type="submit"
                    class="w-full btn-primary font-bold py-3 px-4 rounded-lg transition">
                <i class="fas fa-sign-in-alt mr-2"></i> Masuk Sebagai Pemilih
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="login.php" class="text-sm text-gray-600 hover:text-indigo-700">
                <i class="fas fa-lock mr-1"></i> Login Admin
            </a>
        </div>
    </div>

    <?php if ($swalMessage !== ''): ?>
    <script>
    Swal.fire({
        icon: <?= json_encode($swalIcon) ?>,
        title: 'Informasi',
        text: <?= json_encode($swalMessage) ?>,
        confirmButtonColor: '#5442f5'
    });
    </script>
    <?php endif; ?>
</body>
</html>
