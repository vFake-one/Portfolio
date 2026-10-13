<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — ONE-TIME INITIAL ADMIN ACCOUNT SETUP
 * ============================================================================
 * File: admin/setup.php
 *
 * Security Design:
 * - Allows creating the first administrator account ONLY when zero records
 *   exist in the `admins` table.
 * - If `ADMIN_SETUP_TOKEN` is configured in `.env` (and not the default
 *   placeholder), requires the setup token as an extra safeguard.
 * - Permanently locks itself as soon as 1 administrator account exists.
 * ============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

$pdo = get_db_connection();
if ($pdo && has_any_admin_account($pdo)) {
    set_flash('info', 'Initial administrator setup has already been completed. Please sign in below.');
    header('Location: login.php');
    exit;
}

$configuredToken = pg_env('ADMIN_SETUP_TOKEN', '');
$requiresToken = ($configuredToken !== '' && $configuredToken !== 'replace_with_a_random_32_character_setup_token');

$errorMessage = '';
$old = [
    'username'  => 'patrick',
    'email'     => '',
    'full_name' => 'Patrick Grey',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $old['username'] = trim((string) ($_POST['username'] ?? ''));
    $old['email'] = trim((string) ($_POST['email'] ?? ''));
    $old['full_name'] = trim((string) ($_POST['full_name'] ?? 'Patrick Grey'));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $submittedSetupToken = trim((string) ($_POST['setup_token'] ?? ''));

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errorMessage = 'Invalid CSRF security token. Please refresh and try again.';
    } elseif ($requiresToken && !hash_equals($configuredToken, $submittedSetupToken)) {
        $errorMessage = 'Invalid ADMIN_SETUP_TOKEN. Check your .env file.';
    } else {
        $res = create_initial_admin_account($pdo, $old['username'], $old['email'], $old['full_name'], $password, $confirmPassword);
        if (!empty($res['success'])) {
            set_flash('success', $res['message']);
            header('Location: login.php');
            exit;
        }
        $errorMessage = (string) ($res['message'] ?? 'Could not create administrator account.');
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initial Administrator Setup | Patrick Grey Forex Portfolio</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="pg-body d-flex align-items-center justify-content-center min-vh-100 py-5">
    <main class="container" style="max-width: 520px;">
        <div class="text-center mb-4">
            <span class="pg-brand-mark mb-2">PG</span>
            <h1 class="h4 fw-bold text-white mb-1">One-Time Administrator Setup</h1>
            <p class="small text-secondary mb-0">
                Create the initial administrator account for Patrick Grey. This page automatically locks once the account is created.
            </p>
        </div>

        <div class="pg-card p-4">
            <?php if (!$pdo): ?>
                <div class="alert alert-danger small" role="alert">
                    Database connection is unavailable. Start MySQL in XAMPP and import `database/forex_portfolio.sql` first.
                </div>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert alert-danger small" role="alert"><?= e($errorMessage) ?></div>
            <?php endif; ?>

            <form method="post" action="setup.php" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="full_name" class="pg-form-label">Administrator Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control pg-form-control"
                           value="<?= e($old['full_name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="username" class="pg-form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control pg-form-control"
                           value="<?= e($old['username']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="pg-form-label">Administrator Email</label>
                    <input type="email" id="email" name="email" class="form-control pg-form-control"
                           value="<?= e($old['email']) ?>" placeholder="patrick@example.com" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="pg-form-label">Password (minimum 10 characters, upper + lower + number)</label>
                    <input type="password" id="password" name="password" class="form-control pg-form-control"
                           autocomplete="new-password" required>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="pg-form-label">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control pg-form-control"
                           autocomplete="new-password" required>
                </div>

                <?php if ($requiresToken): ?>
                    <div class="mb-3">
                        <label for="setup_token" class="pg-form-label">Setup Token (`ADMIN_SETUP_TOKEN` from `.env`)</label>
                        <input type="password" id="setup_token" name="setup_token" class="form-control pg-form-control" required>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn pg-btn-primary w-100 mb-3" <?= !$pdo ? 'disabled' : '' ?>>
                    Create Administrator Account
                </button>

                <div class="text-center">
                    <a href="login.php" class="small text-secondary text-decoration-none">&larr; Back to Sign In</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
