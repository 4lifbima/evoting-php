<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $result = loginAdmin($conn, $username, $password);
    
    if ($result['success']) {
        header('Location: admin/index.php');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - E-Voting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <i class="fas fa-vote-yea text-3xl" style="color:#5442f5;"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Login Admin</h1>
            <p class="text-gray-600">Masuk ke dashboard administrasi</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                    <i class="fas fa-user mr-1"></i> Username
                </label>
                <input type="text" name="username" id="username" required autofocus
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus-primary"
                       placeholder="Masukkan username">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    <i class="fas fa-lock mr-1"></i> Password
                </label>
                <input type="password" name="password" id="password" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus-primary"
                       placeholder="••••••••">
            </div>

            <button type="submit"
                    class="w-full btn-primary font-bold py-3 px-4 rounded-lg transition">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="index.php" class="text-sm text-gray-600 hover:text-indigo-700">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Halaman Voting
            </a>
        </div>

        <div class="mt-6 pt-6 border-t text-center text-xs text-gray-500">
            <p>Demo Credentials: <strong>admin</strong> / <strong>admin123</strong></p>
        </div>
    </div>
</body>
</html>
