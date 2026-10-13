<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — ADMIN DASHBOARD HEADER & SIDEBAR
 * ============================================================================
 * File: admin/includes/admin_header.php
 */

declare(strict_types=1);

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'admin_header.php') {
    http_response_code(403);
    exit('Direct access is not permitted.');
}

require_once dirname(__DIR__, 2) . '/includes/auth.php';

require_admin_auth();

$pdo = $pdo ?? get_db_connection();
$adminUser = current_admin();
$currentAdminPage = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');
$flashMessages = get_flash_messages();

// Count unread ("new") inquiries for sidebar badge
$newInquiriesCount = 0;
if ($pdo) {
    try {
        $newInquiriesCount = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE inquiry_status = 'new'")->fetchColumn();
    } catch (Throwable $e) {
        $newInquiriesCount = 0;
    }
}

$adminTitle = isset($adminPageTitle) && trim((string) $adminPageTitle) !== ''
    ? $adminPageTitle . ' — Admin Dashboard | Patrick Grey'
    : 'Administrator Dashboard | Patrick Grey Forex Portfolio';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="pg-body">
    <div class="pg-admin-wrapper">
        <!-- Responsive Sidebar -->
        <aside class="pg-admin-sidebar d-flex flex-column justify-content-between" aria-label="Administrator Sidebar Navigation">
            <div>
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-secondary-subtle">
                    <a href="dashboard.php" class="d-flex align-items-center gap-2 text-decoration-none">
                        <span class="pg-brand-mark">PG</span>
                        <div>
                            <div class="fw-bold text-white small">Patrick Grey</div>
                            <div class="text-emerald" style="font-size: 0.72rem;">Admin Control Panel</div>
                        </div>
                    </a>
                    <button class="btn btn-sm pg-btn-outline d-lg-none" type="button"
                            data-bs-toggle="collapse" data-bs-target="#adminSidebarNav"
                            aria-controls="adminSidebarNav" aria-expanded="false"
                            aria-label="Toggle admin menu">
                        Menu
                    </button>
                </div>

                <div class="collapse d-lg-block" id="adminSidebarNav">
                    <nav aria-label="Dashboard Sections">
                        <a href="dashboard.php" class="pg-admin-nav-link <?= $currentAdminPage === 'dashboard.php' ? 'active' : '' ?>">
                            <span>Overview Dashboard</span>
                        </a>
                        <a href="profile.php" class="pg-admin-nav-link <?= $currentAdminPage === 'profile.php' ? 'active' : '' ?>">
                            <span>Profile, Methodology &amp; Risk</span>
                        </a>
                        <a href="performance.php" class="pg-admin-nav-link <?= $currentAdminPage === 'performance.php' ? 'active' : '' ?>">
                            <span>Trading Performance</span>
                        </a>
                        <a href="articles.php" class="pg-admin-nav-link <?= $currentAdminPage === 'articles.php' ? 'active' : '' ?>">
                            <span>Educational Articles</span>
                        </a>
                        <a href="services.php" class="pg-admin-nav-link <?= $currentAdminPage === 'services.php' ? 'active' : '' ?>">
                            <span>Services &amp; Compliance</span>
                        </a>
                        <a href="inquiries.php" class="pg-admin-nav-link d-flex justify-content-between align-items-center <?= $currentAdminPage === 'inquiries.php' ? 'active' : '' ?>">
                            <span>Visitor Inquiries</span>
                            <?php if ($newInquiriesCount > 0): ?>
                                <span class="badge pg-badge-emerald rounded-pill"><?= $newInquiriesCount ?></span>
                            <?php endif; ?>
                        </a>
                    </nav>

                    <div class="mt-4 pt-3 border-top border-secondary-subtle">
                        <a href="../index.php" class="pg-admin-nav-link" target="_blank" rel="noopener">
                            <span>View Public Website &nearr;</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Signed-in Administrator Info & CSRF-Protected Logout -->
            <div class="pt-3 mt-4 border-top border-secondary-subtle">
                <div class="small text-secondary mb-2">
                    Signed in as <strong class="text-white d-block"><?= e($adminUser['full_name']) ?> (@<?= e($adminUser['username']) ?>)</strong>
                </div>
                <form method="post" action="logout.php" class="m-0">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm pg-btn-outline w-100 text-start">
                        Sign Out Securely
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="pg-admin-main" id="main-content">
            <?php if (!empty($flashMessages)): ?>
                <?php foreach ($flashMessages as $flash): ?>
                    <div class="alert alert-<?= e($flash['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
                        <?= e($flash['message'] ?? '') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
