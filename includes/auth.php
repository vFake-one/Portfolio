<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — ADMINISTRATOR AUTHENTICATION & SECURITY
 * ============================================================================
 * File: includes/auth.php
 *
 * Implements:
 * - Session-based administrator authentication
 * - Session ID regeneration after login (prevents Session Fixation)
 * - Inactivity session timeout enforcement
 * - Brute-force login attempt throttling via the `login_attempts` table
 * - Password hashing (`password_hash()`) and verification (`password_verify()`)
 * - Initial administrator account setup protection (only allowed when 0 admins exist)
 * ============================================================================
 */

declare(strict_types=1);

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'auth.php') {
    http_response_code(403);
    exit('Direct access is not permitted.');
}

require_once __DIR__ . '/functions.php';

/**
 * Check whether at least one administrator account exists in the `admins` table.
 */
function has_any_admin_account(?PDO $pdo = null): bool
{
    $pdo = $pdo ?? get_db_connection();
    if (!$pdo) {
        return false;
    }

    try {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
        return $count > 0;
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error checking admins count: ' . $e->getMessage());
        return false;
    }
}

/**
 * Return true if an administrator is currently logged in and the session has not expired.
 */
function is_admin_logged_in(): bool
{
    ensure_session_started();

    if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_username'])) {
        return false;
    }

    $lifetime = (int) pg_env('SESSION_LIFETIME', '3600');
    $lastActivity = (int) ($_SESSION['admin_last_activity'] ?? 0);

    if ($lastActivity > 0 && (time() - $lastActivity) > $lifetime) {
        logout_admin();
        return false;
    }

    $_SESSION['admin_last_activity'] = time();
    return true;
}

/**
 * Enforce administrator authentication on a protected `/admin/` page.
 * Redirects unauthenticated visitors to `/admin/login.php`.
 */
function require_admin_auth(): void
{
    if (!is_admin_logged_in()) {
        set_flash('warning', 'Please sign in with your administrator credentials to access the dashboard.');
        header('Location: login.php');
        exit;
    }
}

/**
 * Return the currently logged-in administrator's basic session details.
 */
function current_admin(): array
{
    ensure_session_started();
    return [
        'id'        => (int) ($_SESSION['admin_id'] ?? 0),
        'username'  => (string) ($_SESSION['admin_username'] ?? ''),
        'full_name' => (string) ($_SESSION['admin_full_name'] ?? 'Patrick Grey'),
        'email'     => (string) ($_SESSION['admin_email'] ?? ''),
    ];
}

/**
 * Check whether login attempts from the current IP or username are throttled.
 */
function is_login_throttled(?PDO $pdo, string $usernameOrEmail, string $ipAddress): array
{
    $pdo = $pdo ?? get_db_connection();
    $maxAttempts = (int) pg_env('LOGIN_MAX_ATTEMPTS', '5');
    $lockoutMinutes = (int) pg_env('LOGIN_LOCKOUT_MINUTES', '15');

    if (!$pdo) {
        return ['throttled' => false, 'remaining_attempts' => $maxAttempts];
    }

    $cutoff = date('Y-m-d H:i:s', time() - ($lockoutMinutes * 60));

    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM login_attempts
            WHERE was_successful = 0
              AND attempted_at >= :cutoff
              AND (ip_address = :ip OR LOWER(username_or_email) = LOWER(:user))
        ");
        $stmt->execute([
            ':cutoff' => $cutoff,
            ':ip'     => $ipAddress,
            ':user'   => trim($usernameOrEmail),
        ]);
        $failedCount = (int) $stmt->fetchColumn();

        if ($failedCount >= $maxAttempts) {
            return [
                'throttled'          => true,
                'remaining_attempts' => 0,
                'lockout_minutes'    => $lockoutMinutes,
            ];
        }

        return [
            'throttled'          => false,
            'remaining_attempts' => max(0, $maxAttempts - $failedCount),
            'lockout_minutes'    => $lockoutMinutes,
        ];
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error checking login throttling: ' . $e->getMessage());
        return ['throttled' => false, 'remaining_attempts' => $maxAttempts];
    }
}

/**
 * Log a login attempt in the `login_attempts` table.
 */
function record_login_attempt(?PDO $pdo, string $usernameOrEmail, string $ipAddress, bool $wasSuccessful): void
{
    $pdo = $pdo ?? get_db_connection();
    if (!$pdo) {
        return;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO login_attempts (username_or_email, ip_address, was_successful, attempted_at)
            VALUES (:user, :ip, :success, :now)
        ");
        $stmt->execute([
            ':user'    => mb_substr(trim($usernameOrEmail), 0, 150),
            ':ip'      => $ipAddress,
            ':success' => $wasSuccessful ? 1 : 0,
            ':now'     => date('Y-m-d H:i:s'),
        ]);

        // Clear recent failed attempts for this user/IP after a successful login
        if ($wasSuccessful) {
            $clearStmt = $pdo->prepare("
                DELETE FROM login_attempts
                WHERE was_successful = 0
                  AND (ip_address = :ip OR LOWER(username_or_email) = LOWER(:user))
            ");
            $clearStmt->execute([
                ':ip'   => $ipAddress,
                ':user' => trim($usernameOrEmail),
            ]);
        }
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error recording login attempt: ' . $e->getMessage());
    }
}

/**
 * Authenticate an administrator with username/email and password.
 */
function attempt_admin_login(?PDO $pdo, string $usernameOrEmail, string $password): array
{
    ensure_session_started();
    $pdo = $pdo ?? get_db_connection();
    $ip = get_client_ip();
    $identifier = trim($usernameOrEmail);

    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'Database connection is unavailable. Please ensure MySQL is running and forex_portfolio.sql has been imported.',
        ];
    }

    if ($identifier === '' || $password === '') {
        return [
            'success' => false,
            'message' => 'Please enter both your username (or email) and password.',
        ];
    }

    $throttle = is_login_throttled($pdo, $identifier, $ip);
    if (!empty($throttle['throttled'])) {
        $mins = (int) ($throttle['lockout_minutes'] ?? 15);
        return [
            'success' => false,
            'message' => "Too many failed login attempts. For security, login is temporarily locked for {$mins} minutes.",
        ];
    }

    try {
        $stmt = $pdo->prepare("
            SELECT id, username, email, full_name, password_hash, is_active
            FROM admins
            WHERE (LOWER(username) = LOWER(:u1) OR LOWER(email) = LOWER(:u2))
            LIMIT 1
        ");
        $stmt->execute([
            ':u1' => $identifier,
            ':u2' => $identifier,
        ]);
        $admin = $stmt->fetch();

        if (!$admin || (int) $admin['is_active'] !== 1 || !password_verify($password, (string) $admin['password_hash'])) {
            record_login_attempt($pdo, $identifier, $ip, false);
            return [
                'success' => false,
                'message' => 'Invalid username/email or password.',
            ];
        }

        // Upgrade password hash if PHP's default cost factor has increased
        if (password_needs_rehash((string) $admin['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $rehashStmt = $pdo->prepare('UPDATE admins SET password_hash = :hash WHERE id = :id');
            $rehashStmt->execute([':hash' => $newHash, ':id' => $admin['id']]);
        }

        record_login_attempt($pdo, $identifier, $ip, true);

        $updateLogin = $pdo->prepare('UPDATE admins SET last_login_at = :now WHERE id = :id');
        $updateLogin->execute([
            ':now' => date('Y-m-d H:i:s'),
            ':id'  => $admin['id'],
        ]);

        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = (string) $admin['username'];
        $_SESSION['admin_email'] = (string) $admin['email'];
        $_SESSION['admin_full_name'] = (string) $admin['full_name'];
        $_SESSION['admin_last_activity'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        return [
            'success' => true,
            'message' => 'Welcome back, ' . $admin['full_name'] . '.',
        ];
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Login error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'An internal error occurred during authentication.',
        ];
    }
}

/**
 * Create the initial administrator account ONLY when zero admins exist in the database.
 */
function create_initial_admin_account(?PDO $pdo, string $username, string $email, string $fullName, string $password, string $confirmPassword): array
{
    $pdo = $pdo ?? get_db_connection();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection is unavailable.'];
    }

    if (has_any_admin_account($pdo)) {
        return [
            'success' => false,
            'message' => 'An administrator account already exists. Initial setup is permanently disabled.',
        ];
    }

    $username = trim($username);
    $email = trim($email);
    $fullName = trim($fullName) !== '' ? trim($fullName) : 'Patrick Grey';

    if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
        return ['success' => false, 'message' => 'Username must be 3–50 characters (letters, numbers, underscores, dots, or hyphens).'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid administrator email address.'];
    }

    if (strlen($password) < 10) {
        return ['success' => false, 'message' => 'Administrator password must be at least 10 characters long.'];
    }

    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        return ['success' => false, 'message' => 'Password must include at least one uppercase letter, one lowercase letter, and one number.'];
    }

    if ($password !== $confirmPassword) {
        return ['success' => false, 'message' => 'Password confirmation does not match.'];
    }

    try {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO admins (username, email, full_name, password_hash, is_active)
            VALUES (:username, :email, :full_name, :password_hash, 1)
        ");
        $stmt->execute([
            ':username'      => $username,
            ':email'         => $email,
            ':full_name'     => $fullName,
            ':password_hash' => $hash,
        ]);

        return [
            'success' => true,
            'message' => 'Initial administrator account created successfully. You may now sign in.',
        ];
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error creating initial admin: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Could not create administrator account.'];
    }
}

/**
 * Destroy the current administrator session securely.
 */
function logout_admin(): void
{
    ensure_session_started();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            (bool) ($params['secure'] ?? false),
            (bool) ($params['httponly'] ?? true)
        );
    }

    @session_destroy();
}
