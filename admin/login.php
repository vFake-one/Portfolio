<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — ADMINISTRATOR LOGIN PAGE
 * ============================================================================
 * File: admin/login.php
 *
 * Security Features:
 * - No public administrator registration.
 * - Uses password_verify() against bcrypt/argon2 hashes.
 * - Regenerates session IDs after successful authentication.
 * - Enforces CSRF token checks and brute-force login throttling.
 * ============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

if (is_admin_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$pdo = get_db_connection();
$adminExists = has_any_admin_account($pdo);
$errorMessage = '';
$submittedUser = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $submittedUser = trim((string) ($_POST['username_or_email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errorMessage = 'Invalid security token. Please refresh the page and try again.';
    } else {
        $loginResult = attempt_admin_login($pdo, $submittedUser, $password);
        if (!empty($loginResult['success'])) {
            set_flash('success', $loginResult['message']);
            header('Location: dashboard.php');
            exit;
        }
        $errorMessage = (string) ($loginResult['message'] ?? 'Authentication failed.');
    }
}

$flashMessages = get_flash_messages();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Sign In | Patrick Grey Forex Portfolio</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="pg-body d-flex align-items-center justify-content-center min-vh-100 py-5">
    <main class="container" style="max-width: 460px;">
        <div class="text-center mb-4">
            <a href="../index.php" class="text-decoration-none d-inline-flex align-items-center gap-2 mb-2">
                <span class="pg-brand-mark">PG</span>
                <span class="h5 mb-0 text-white fw-bold">Patrick Grey</span>
            </a>
            <h1 class="h4 fw-bold text-white mt-2 mb-1">Administrator Portal</h1>
            <p class="small text-secondary mb-0">Protected content management &amp; performance logging</p>
        </div>

        <div class="pg-card p-4">
            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert alert-<?= e($flash['type'] ?? 'info') ?> small" role="alert">
                    <?= e($flash['message'] ?? '') ?>
                </div>
            <?php endforeach; ?>

            <?php if (!$pdo): ?>
                <div class="alert alert-warning small" role="alert">
                    <strong>Database Connection Notice:</strong> Could not connect to MySQL (`forex_portfolio`). Ensure XAMPP MySQL is running and `database/forex_portfolio.sql` has been imported.
                </div>
            <?php elseif (!$adminExists): ?>
                <div class="alert alert-info small" role="alert">
                    <strong>First-Time Setup Required:</strong> No administrator account exists in the database yet.
                    <div class="mt-2">
                        <a href="setup.php" class="btn btn-sm pg-btn-primary w-100">Create Initial Administrator Account &rarr;</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert alert-danger small" role="alert">
                    <?= e($errorMessage) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="username_or_email" class="pg-form-label">Username or Email</label>
                    <input type="text"
                           id="username_or_email"
                           name="username_or_email"
                           class="form-control pg-form-control"
                           value="<?= e($submittedUser) ?>"
                           autocomplete="username"
                           required
                           autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="pg-form-label">Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control pg-form-control"
                           autocomplete="current-password"
                           required>
                </div>

                <button type="submit" class="btn pg-btn-primary w-100 mb-3" <?= !$pdo ? 'disabled' : '' ?>>
                    Sign In to Dashboard
                </button>

                <div class="text-center">
                    <a href="../index.php" class="small text-secondary text-decoration-none">&larr; Return to Public Portfolio</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
