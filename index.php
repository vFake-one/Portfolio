<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — COMPLETE HOMEPAGE & LANDING PAGE
 * ============================================================================
 * File: index.php
 *
 * Renders the complete landing page containing:
 * - Hero Section (Section 5.B)
 * - About Patrick Grey (Section 5.C)
 * - Advanced Price Action Methodology (Section 5.D)
 * - Risk Management Philosophy (Section 5.E)
 * - Trading Performance Dashboard (Section 5.F)
 * - Interactive Forex Risk Calculator (Section 5.G)
 * - Forex Education (Section 5.H)
 * - Services & Engagement (Section 5.I)
 * - Contact & Inquiry Form (Section 5.J)
 * ============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/sections.php';

$pdo = get_db_connection();
$profile = get_trader_profile($pdo);
$siteSettings = get_site_settings($pdo);
$strategies = get_strategy_sections($pdo, true);
$riskSections = get_risk_management_sections($pdo);
$perfData = get_performance_dashboard_data($pdo);
$categories = get_education_categories();
$articles = get_published_articles($pdo, null, 6);
$services = get_services($pdo, true);

// Handle Contact & Inquiry Form submission if POSTed on the homepage
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

$loadChartJs = !empty($perfData['has_records']);
$loadRiskCalculatorJs = true;

require_once __DIR__ . '/includes/header.php';

render_hero_section($profile, true);
render_about_section($profile);
render_methodology_section($strategies);
render_risk_management_section($riskSections);
render_performance_section($perfData, $siteSettings);
render_risk_calculator_section();
render_education_section($articles, $categories, null);
render_services_section($services, $siteSettings, true);
render_contact_section($inquiryState, $services, $siteSettings);

require_once __DIR__ . '/includes/footer.php';
