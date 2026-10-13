<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — ADVANCED PRICE ACTION METHODOLOGY PAGE
 * ============================================================================
 * File: methodology.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/sections.php';

$pdo = get_db_connection();
$profile = get_trader_profile($pdo);
$siteSettings = get_site_settings($pdo);
$strategies = get_strategy_sections($pdo, true);

$pageTitle = 'Advanced Price Action Methodology';
$pageDescription = "Explore Patrick Grey's 8-step Advanced Price Action methodology: market structure, support & resistance zones, trend identification, candlestick patterns, and risk-to-reward planning.";

require_once __DIR__ . '/includes/header.php';
?>
<div class="container pt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-emerald text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active text-secondary" aria-current="page">Trading Approach</li>
        </ol>
    </nav>
</div>
<?php
render_methodology_section($strategies);

require_once __DIR__ . '/includes/footer.php';
