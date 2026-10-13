<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — CONTACT & INQUIRY PAGE
 * ============================================================================
 * File: contact.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/sections.php';

$pdo = get_db_connection();
$profile = get_trader_profile($pdo);
$siteSettings = get_site_settings($pdo);
$services = get_services($pdo, true);

$inquiryState = [
    'submitted' => false,
    'success'   => false,
    'message'   => '',
    'errors'    => [],
    'old'       => [],
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['contact_inquiry_submit'])) {
    $result = handle_contact_inquiry_submission($pdo, $_POST);
    $inquiryState = array_merge(['submitted' => true], $result);
}

$pageTitle = 'Contact & Inquiry Form';
$pageDescription = 'Send an inquiry to Patrick Grey regarding forex education, advanced price action methodology, or risk management discussions.';

require_once __DIR__ . '/includes/header.php';
?>
<div class="container pt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-emerald text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active text-secondary" aria-current="page">Contact</li>
        </ol>
    </nav>
</div>
<?php
render_contact_section($inquiryState, $services, $siteSettings);

require_once __DIR__ . '/includes/footer.php';
