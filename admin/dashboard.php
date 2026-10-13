<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — ADMINISTRATOR OVERVIEW DASHBOARD
 * ============================================================================
 * File: admin/dashboard.php
 */

declare(strict_types=1);

$adminPageTitle = 'Overview';
require_once __DIR__ . '/includes/admin_header.php';

$perfData = get_performance_dashboard_data($pdo);

$publishedArticlesCount = 0;
$draftArticlesCount = 0;
$activeServicesCount = 0;
$totalInquiriesCount = 0;
$newInquiriesList = [];

if ($pdo) {
    try {
        $publishedArticlesCount = (int) $pdo->query('SELECT COUNT(*) FROM articles WHERE is_published = 1')->fetchColumn();
        $draftArticlesCount = (int) $pdo->query('SELECT COUNT(*) FROM articles WHERE is_published = 0')->fetchColumn();
        $activeServicesCount = (int) $pdo->query('SELECT COUNT(*) FROM services WHERE is_active = 1')->fetchColumn();
        $totalInquiriesCount = (int) $pdo->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();

        $stmtInq = $pdo->query('SELECT * FROM inquiries ORDER BY submitted_at DESC, id DESC LIMIT 6');
        $newInquiriesList = $stmtInq ? $stmtInq->fetchAll() : [];
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Dashboard query error: ' . $e->getMessage());
    }
}
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-white mb-1">Administrator Dashboard</h1>
        <p class="small text-secondary mb-0">
            Real-time overview of actual database records for Patrick Grey's Forex Portfolio.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="performance.php" class="btn btn-sm pg-btn-primary">+ Log Trade Record</a>
        <a href="articles.php" class="btn btn-sm pg-btn-outline">+ New Educational Article</a>
    </div>
</div>

<!-- Summary Metric Cards Based on Actual Database Records -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="pg-metric-card">
            <div class="pg-metric-label">Recorded Trades</div>
            <div class="pg-metric-value"><?= (int) $perfData['total_trades'] ?></div>
            <div class="small text-secondary">
                <?php if ($perfData['has_records']): ?>
                    Net Realized: <span class="<?= $perfData['net_realized_pl'] >= 0 ? 'text-emerald' : 'text-loss' ?> font-mono">
                        <?= ($perfData['net_realized_pl'] >= 0 ? '+' : '') . e(number_format((float) $perfData['net_realized_pl'], 2)) . ' ' . e($perfData['account_currency']) ?>
                    </span>
                <?php else: ?>
                    No trades logged yet
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="pg-metric-card">
            <div class="pg-metric-label">Educational Articles</div>
            <div class="pg-metric-value"><?= $publishedArticlesCount ?> <span class="fs-6 text-secondary fw-normal">Published</span></div>
            <div class="small text-secondary"><?= $draftArticlesCount ?> drafts saved</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="pg-metric-card">
            <div class="pg-metric-label">Active Services</div>
            <div class="pg-metric-value"><?= $activeServicesCount ?></div>
            <div class="small text-secondary"><a href="services.php" class="text-emerald text-decoration-none">Manage service listings &rarr;</a></div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="pg-metric-card">
            <div class="pg-metric-label">Visitor Inquiries</div>
            <div class="pg-metric-value"><?= $totalInquiriesCount ?></div>
            <div class="small text-secondary">
                <strong class="text-emerald"><?= $newInquiriesCount ?></strong> unread (`new`) inquiries
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Visitor Inquiries Table -->
    <div class="col-lg-7">
        <div class="pg-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h6 fw-bold text-white mb-0">Recent Visitor Inquiries</h2>
                <a href="inquiries.php" class="small text-emerald text-decoration-none">View All &rarr;</a>
            </div>

            <?php if (empty($newInquiriesList)): ?>
                <p class="small text-secondary mb-0 py-3 text-center">No visitor inquiries have been submitted yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table pg-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Visitor</th>
                                <th scope="col">Area of Interest</th>
                                <th scope="col">Status</th>
                                <th scope="col">Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($newInquiriesList as $inq): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-white"><?= e($inq['full_name']) ?></div>
                                        <div class="small text-secondary"><?= e($inq['email']) ?></div>
                                    </td>
                                    <td class="small"><?= e($inq['area_of_interest']) ?></td>
                                    <td>
                                        <span class="badge <?= ($inq['inquiry_status'] === 'new') ? 'pg-badge-emerald' : 'bg-secondary-subtle text-secondary' ?>">
                                            <?= e($inq['inquiry_status']) ?>
                                        </span>
                                    </td>
                                    <td class="small font-mono text-secondary"><?= e(date('Y-m-d H:i', strtotime((string) $inq['submitted_at']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Management Links & Security Checklist -->
    <div class="col-lg-5">
        <div class="pg-card">
            <h2 class="h6 fw-bold text-white mb-3">Quick Content &amp; Compliance Controls</h2>
            <div class="d-grid gap-2 mb-4">
                <a href="profile.php" class="btn pg-btn-outline text-start">Edit Patrick Grey Biography &amp; Profile Cards</a>
                <a href="profile.php#methodology-editor" class="btn pg-btn-outline text-start">Edit 8 Price Action Methodology Cards</a>
                <a href="profile.php#risk-editor" class="btn pg-btn-outline text-start">Edit Conservative &amp; Aggressive Risk Cards</a>
                <a href="profile.php#settings-editor" class="btn pg-btn-outline text-start">Update Contact Email, WhatsApp &amp; Disclaimers</a>
            </div>

            <div class="p-3 rounded bg-dark border border-secondary-subtle small">
                <div class="fw-bold text-emerald mb-1">Active Security Protections</div>
                <ul class="mb-0 ps-3 text-secondary">
                    <li>PDO Prepared Statements (`ATTR_EMULATE_PREPARES = false`)</li>
                    <li>Timing-Safe CSRF Tokens (`hash_equals`)</li>
                    <li>Session Fixation Regeneration &amp; `HttpOnly` Cookies</li>
                    <li>Brute-Force Login Throttling (`login_attempts` table)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
