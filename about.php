<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — ABOUT PATRICK GREY PAGE
 * ============================================================================
 * File: about.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/sections.php';

$pdo = get_db_connection();
$profile = get_trader_profile($pdo);
$siteSettings = get_site_settings($pdo);
$riskSections = get_risk_management_sections($pdo);

$pageTitle = 'About Patrick Grey';
$pageDescription = 'Learn about Patrick Grey, an intermediate forex trader with 3 years of experience focusing on Advanced Price Action, market structure, and disciplined risk management.';

require_once __DIR__ . '/includes/header.php';
?>
<div class="container pt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-emerald text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active text-secondary" aria-current="page">About Me</li>
        </ol>
    </nav>
</div>
<?php
render_about_section($profile);
render_risk_management_section($riskSections);

require_once __DIR__ . '/includes/footer.php';
