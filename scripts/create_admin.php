<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — CLI INITIAL ADMINISTRATOR CREATOR
 * ============================================================================
 * File: scripts/create_admin.php
 *
 * Usage (from terminal on Debian/Ubuntu with XAMPP):
 *   /opt/lampp/bin/php scripts/create_admin.php patrick patrick@example.com "StrongPass123!"
 *
 * Security Note:
 * - Can only be executed from the command line (CLI SAPI).
 * - Refuses to run over HTTP.
 * ============================================================================
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be executed from the command line.');
}

require_once dirname(__DIR__) . '/includes/auth.php';

$pdo = get_db_connection();
if (!$pdo) {
    fwrite(STDERR, "[ERROR] Could not connect to the database. Verify your .env settings and ensure MySQL is running.\n");
    exit(1);
}

if (has_any_admin_account($pdo)) {
    fwrite(STDERR, "[NOTICE] An administrator account already exists in the `admins` table.\n");
    exit(1);
}

$username = $argv[1] ?? '';
$email = $argv[2] ?? '';
$password = $argv[3] ?? '';
$fullName = $argv[4] ?? 'Patrick Grey';

if ($username === '' || $email === '' || $password === '') {
    fwrite(STDOUT, "Usage: php scripts/create_admin.php <username> <email> <password> [full_name]\n");
    fwrite(STDOUT, "Example: php scripts/create_admin.php patrick patrick@example.com \"PriceAction2026!\" \"Patrick Grey\"\n");
    exit(1);
}

$result = create_initial_admin_account($pdo, $username, $email, $fullName, $password, $password);
if ($result['success']) {
    fwrite(STDOUT, "[SUCCESS] " . $result['message'] . "\n");
    exit(0);
}

fwrite(STDERR, "[ERROR] " . $result['message'] . "\n");
exit(1);
