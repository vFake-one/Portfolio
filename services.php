<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — SERVICES & ENGAGEMENT PAGE
 * ============================================================================
 * File: services.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/sections.php';

$pdo = get_db_connection();
$profile = get_trader_profile($pdo);
$siteSettings = get_site_settings($pdo);
$services = get_services($pdo, true);

$pageTitle = 'Services & Educational Engagement';
$pageDescription = 'Explore forex education, price action study guidance, trading journal reviews, and regulatory compliance information from Patrick Grey.';

require_once __DIR__ . '/includes/header.php';
?>
<div class="container pt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-emerald text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active text-secondary" aria-current="page">Services</li>
        </ol>
    </nav>
</div>
<?php
render_services_section($services, $siteSettings, false);

require_once __DIR__ . '/includes/footer.php';
