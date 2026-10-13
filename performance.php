<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — TRADING PERFORMANCE DASHBOARD PAGE
 * ============================================================================
 * File: performance.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/sections.php';

$pdo = get_db_connection();
$profile = get_trader_profile($pdo);
$siteSettings = get_site_settings($pdo);
$perfData = get_performance_dashboard_data($pdo);

$pageTitle = 'Trading Performance Dashboard';
$pageDescription = 'Documented forex trading performance records, win rate, drawdown analytics, and transparency disclosures for Patrick Grey.';

$loadChartJs = !empty($perfData['has_records']);

require_once __DIR__ . '/includes/header.php';
?>
<div class="container pt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-emerald text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active text-secondary" aria-current="page">Performance</li>
        </ol>
    </nav>
</div>
<?php
render_performance_section($perfData, $siteSettings);

require_once __DIR__ . '/includes/footer.php';
