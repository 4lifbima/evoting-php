<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';


$reason = isset($_GET['reason']) ? $_GET['reason'] : '';
$silent = isset($_GET['silent']) && $_GET['silent'] === '1';
$next = isset($_GET['next']) ? trim($_GET['next']) : '';

logoutVoter();
 
logoutAdmin();

if ($reason === 'vote-complete') {
    $message = 'Terima kasih telah berpartisipasi dalam voting!';
} else {
    $message = 'Anda telah logout.';
}

$_SESSION['flash_message'] = $message;
$_SESSION['flash_type'] = 'success';

if ($silent) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $projectBasePath = rtrim($scriptDir, '/');
    if ($projectBasePath === '') {
        $projectBasePath = '/';
    }

    if ($next === '' || $next === '/') {
        $next = $projectBasePath . '/';
    }

    if (strpos($next, '/') !== 0 || strpos($next, '//') === 0) {
        $next = $projectBasePath . '/';
    }

    header('Location: ' . $next);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - E-Voting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="bg-gradient-to-br from-blue-600 to-indigo-800 min-h-screen flex items-center justify-center">
    <div class="text-center text-white">
        <i class="fas fa-sign-out-alt text-6xl mb-4"></i>
        <h1 class="text-2xl font-bold mb-2">Logout Berhasil</h1>
        <p class="text-blue-100"><?= htmlspecialchars($message) ?></p>
        
        <div class="mt-8">
            <a href="voter_login.php" class="bg-white text-blue-600 px-6 py-2 rounded-lg font-semibold hover:bg-blue-50 transition inline-block">
                <i class="fas fa-sign-in-alt mr-2"></i>Login Kembali
            </a>
        </div>
        
        <div class="mt-4">
            <a href="index.php" class="text-blue-200 hover:text-white text-sm">
                <i class="fas fa-home mr-1"></i>Kembali ke Halaman Utama
            </a>
        </div>
    </div>

    <script>
    // Show SweetAlert for vote-complete logout
    <?php if ($reason === 'vote-complete'): ?>
    Swal.fire({
        icon: 'success',
        title: 'Terima Kasih!',
        text: 'Suara Anda telah berhasil disimpan. Partisipasi Anda sangat berarti.',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3085d6'
    }).then(() => {
        window.location.href = 'voter_login.php';
    });
    <?php endif; ?>
    </script>
</body>
</html>
