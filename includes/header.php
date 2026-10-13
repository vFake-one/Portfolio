<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — REUSABLE HEADER & NAVIGATION BAR
 * ============================================================================
 * File: includes/header.php
 */

declare(strict_types=1);

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'header.php') {
    http_response_code(403);
    exit('Direct access is not permitted.');
}

require_once __DIR__ . '/functions.php';

$pdo = $pdo ?? get_db_connection();
$profile = $profile ?? get_trader_profile($pdo);
$siteSettings = $siteSettings ?? get_site_settings($pdo);

$currentScript = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$isLandingPage = ($currentScript === 'index.php');

$pageTitle = isset($pageTitle) && trim((string) $pageTitle) !== ''
    ? $pageTitle . ' | ' . ($profile['full_name'] ?? 'Patrick Grey')
    : ($profile['full_name'] ?? 'Patrick Grey') . ' — ' . ($profile['professional_title'] ?? 'Intermediate Forex Trader') . ' | Advanced Price Action';

$pageDescription = isset($pageDescription) && trim((string) $pageDescription) !== ''
    ? $pageDescription
    : "Official forex trading portfolio of Patrick Grey, an intermediate forex trader with 3 years of experience specializing in Advanced Price Action, market structure analysis, and disciplined risk management.";

// Build navigation hrefs: on index.php use smooth-scroll anchors; on standalone pages link cleanly
$navPrefix = $isLandingPage ? '' : 'index.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="author" content="<?= e($profile['full_name'] ?? 'Patrick Grey') ?>">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Social Sharing Metadata -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:site_name" content="<?= e($siteSettings['site_name'] ?? 'Patrick Grey Forex Portfolio') ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Custom Portfolio Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="pg-body">
    <!-- Accessibility Skip Link -->
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <!-- Top Responsible Trading Disclosure Bar -->
    <div class="pg-top-notice" role="region" aria-label="Forex Risk Notice">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="pg-top-notice-text">
                <strong class="text-emerald">Risk Disclosure:</strong> Forex trading involves substantial risk of loss. Content is strictly educational and informational—never a promise of returns.
            </span>
            <div class="d-flex align-items-center gap-3 small">
                <a href="services.php" class="pg-top-link">Services</a>
                <a href="index.php#risk-calculator" class="pg-top-link">Risk Calculator</a>
                <a href="admin/login.php" class="pg-top-link" aria-label="Administrator Dashboard Login">Admin</a>
            </div>
        </div>
    </div>

    <!-- Primary Navigation Bar (Section 5.A) -->
    <header class="pg-navbar-wrapper sticky-top" id="site-header">
        <nav class="navbar navbar-expand-lg pg-navbar" aria-label="Primary Navigation">
            <div class="container">
                <!-- Brand Identity -->
                <a class="navbar-brand d-flex align-items-center gap-2" href="index.php" aria-label="Patrick Grey Forex Portfolio Home">
                    <span class="pg-brand-mark" aria-hidden="true">PG</span>
                    <span class="d-flex flex-column">
                        <span class="pg-brand-name"><?= e($profile['full_name'] ?? 'Patrick Grey') ?></span>
                        <span class="pg-brand-tag">Advanced Price Action</span>
                    </span>
                </a>

                <!-- Mobile Menu Toggle -->
                <button class="navbar-toggler pg-navbar-toggler" type="button"
                        data-bs-toggle="collapse" data-bs-target="#primaryNavbar"
                        aria-controls="primaryNavbar" aria-expanded="false"
                        aria-label="Toggle navigation menu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navigation Links -->
                <div class="collapse navbar-collapse" id="primaryNavbar">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0 pg-nav-list">
                        <li class="nav-item">
                            <a class="nav-link <?= $currentScript === 'index.php' ? 'active' : '' ?>"
                               href="<?= $isLandingPage ? '#home' : 'index.php' ?>"
                               data-section="home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentScript === 'about.php' ? 'active' : '' ?>"
                               href="<?= $isLandingPage ? '#about' : 'about.php' ?>"
                               data-section="about">About Me</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentScript === 'methodology.php' ? 'active' : '' ?>"
                               href="<?= $isLandingPage ? '#trading-approach' : 'methodology.php' ?>"
                               data-section="trading-approach">Trading Approach</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentScript === 'performance.php' ? 'active' : '' ?>"
                               href="<?= $isLandingPage ? '#performance' : 'performance.php' ?>"
                               data-section="performance">Performance</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentScript === 'risk-management.php' ? 'active' : '' ?>"
                               href="<?= $isLandingPage ? '#risk-management' : 'risk-management.php' ?>"
                               data-section="risk-management">Risk Management</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentScript === 'education.php' ? 'active' : '' ?>"
                               href="<?= $isLandingPage ? '#forex-education' : 'education.php' ?>"
                               data-section="forex-education">Forex Education</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentScript === 'contact.php' ? 'active' : '' ?>"
                               href="<?= $isLandingPage ? '#contact' : 'contact.php' ?>"
                               data-section="contact">Contact</a>
                        </li>
                    </ul>

                    <!-- Prominent "Let's Connect" Call to Action Button -->
                    <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                        <a href="<?= $isLandingPage ? '#contact' : 'contact.php' ?>" class="btn pg-btn-primary">
                            Let's Connect
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main id="main-content">
