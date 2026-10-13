<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — ADMINISTRATOR LOGOUT HANDLER
 * ============================================================================
 * File: admin/logout.php
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? null)) {
        logout_admin();
        ensure_session_started();
        set_flash('success', 'You have been signed out securely.');
    } else {
        set_flash('danger', 'Logout request failed CSRF validation.');
    }
} else {
    logout_admin();
}

header('Location: login.php');
exit;
