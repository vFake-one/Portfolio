<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — RISK MANAGEMENT & RISK CALCULATOR PAGE
 * ============================================================================
 * File: risk-management.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/sections.php';

$pdo = get_db_connection();
$profile = get_trader_profile($pdo);
$siteSettings = get_site_settings($pdo);
$riskSections = get_risk_management_sections($pdo);

$pageTitle = 'Risk Management Philosophy & Forex Risk Calculator';
$pageDescription = 'Understand Patrick Grey’s conservative and aggressive risk management principles and calculate forex monetary risk and position sizing.';

$loadRiskCalculatorJs = true;

require_once __DIR__ . '/includes/header.php';
?>
<div class="container pt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-emerald text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active text-secondary" aria-current="page">Risk Management</li>
        </ol>
    </nav>
</div>
<?php
render_risk_management_section($riskSections);
render_risk_calculator_section();

require_once __DIR__ . '/includes/footer.php';
