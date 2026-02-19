<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Redirect if voter already logged in
if (isVoterLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim_nis = trim($_POST['nim_nis'] ?? '');

    if ($nim_nis === '') {
        $error = 'NIM/NIS wajib diisi.';
    } else {
        $result = loginVoter($conn, $nim_nis);
        if ($result['success']) {
            redirect_with_message('index.php', 'Login berhasil. Silakan pilih kandidat Anda.', 'success');
        }
        $error = 'NIM/NIS tidak ditemukan atau tidak aktif.';
    }
}

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Voter - E-Voting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-600 to-indigo-800 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <i class="fas fa-id-card text-5xl text-blue-600 mb-3"></i>
            <h1 class="text-2xl font-bold text-gray-800">Login Pemilih</h1>
            <p class="text-gray-600">Masuk menggunakan NIM/NIS</p>
        </div>

        <?php if ($flash): ?>
        <div class="bg-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-100 border border-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-400 text-<?= $flash['type'] === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-info-circle mr-1"></i> <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nim_nis">
                    <i class="fas fa-user-graduate mr-1"></i> NIM / NIS
                </label>
                <input type="text" name="nim_nis" id="nim_nis" required autofocus
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Contoh: 2024001">
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition">
                <i class="fas fa-sign-in-alt mr-2"></i> Masuk Sebagai Pemilih
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="login.php" class="text-sm text-gray-600 hover:text-blue-600">
                <i class="fas fa-lock mr-1"></i> Login Admin
            </a>
        </div>
    </div>
</body>
</html>
