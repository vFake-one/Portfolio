<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — REUSABLE FOOTER & RISK DISCLAIMER
 * ============================================================================
 * File: includes/footer.php
 */

declare(strict_types=1);

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'footer.php') {
    http_response_code(403);
    exit('Direct access is not permitted.');
}

$pdo = $pdo ?? get_db_connection();
$profile = $profile ?? get_trader_profile($pdo);
$siteSettings = $siteSettings ?? get_site_settings($pdo);

$contactEmail = trim((string) ($siteSettings['contact_email'] ?? ''));
$whatsappNumber = trim((string) ($siteSettings['whatsapp_number'] ?? ''));
$whatsappClean = preg_replace('/[^0-9]/', '', $whatsappNumber);

$socialLinks = [
    'LinkedIn' => trim((string) ($siteSettings['social_linkedin'] ?? '')),
    'X / Twitter' => trim((string) ($siteSettings['social_twitter'] ?? '')),
    'YouTube' => trim((string) ($siteSettings['social_youtube'] ?? '')),
    'Telegram' => trim((string) ($siteSettings['social_telegram'] ?? '')),
];
$activeSocials = array_filter($socialLinks, static fn(string $url): bool => $url !== '' && filter_var($url, FILTER_VALIDATE_URL) !== false);

$footerDisclaimer = trim((string) ($siteSettings['footer_risk_disclaimer'] ?? ''))
    ?: 'Forex trading involves substantial risk and may not be suitable for every individual. You may lose some or all of your trading capital. Historical performance does not guarantee future results. Information on this website is provided for educational and informational purposes and should not be interpreted as a promise of returns or personalized financial advice.';
?>
    </main>

    <!-- Site Footer (Section 5.K) -->
    <footer class="pg-footer" role="contentinfo">
        <div class="container">
            <div class="row g-4 pb-4 border-bottom border-secondary-subtle">
                <!-- Column 1: Patrick Grey Identity & Short Description -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="pg-brand-mark" aria-hidden="true">PG</span>
                        <div>
                            <h2 class="h5 mb-0 text-white fw-bold"><?= e($profile['full_name'] ?? 'Patrick Grey') ?></h2>
                            <p class="small text-emerald mb-0"><?= e($profile['professional_title'] ?? 'Intermediate Forex Trader') ?> • <?= e($profile['experience_label'] ?? '3 years') ?> Experience</p>
                        </div>
                    </div>
                    <p class="pg-footer-text mb-3">
                        Personal forex trading portfolio centered on <?= e($profile['primary_methodology'] ?? 'Advanced Price Action') ?>, multi-timeframe market structure analysis, adaptive risk management, and transparent forex education.
                    </p>
                    <?php if (!empty($activeSocials)): ?>
                        <div class="d-flex flex-wrap gap-2 mt-2" aria-label="Social Media Links">
                            <?php foreach ($activeSocials as $label => $url): ?>
                                <a href="<?= e($url) ?>" class="btn btn-sm pg-btn-outline" target="_blank" rel="noopener noreferrer">
                                    <?= e($label) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Column 2: Quick Navigation Links -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h3 class="pg-footer-heading">Navigation</h3>
                    <ul class="list-unstyled pg-footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Me</a></li>
                        <li><a href="methodology.php">Trading Approach</a></li>
                        <li><a href="performance.php">Performance</a></li>
                        <li><a href="risk-management.php">Risk Management</a></li>
                        <li><a href="education.php">Forex Education</a></li>
                        <li><a href="services.php">Services</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>

                <!-- Column 3: Tools & Legal Links -->
                <div class="col-lg-3 col-md-6 col-6">
                    <h3 class="pg-footer-heading">Resources &amp; Legal</h3>
                    <ul class="list-unstyled pg-footer-links">
                        <li><a href="index.php#risk-calculator">Forex Risk Calculator</a></li>
                        <li><a href="education.php">Educational Articles</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms of Use</a></li>
                        <li><a href="#forex-risk-disclaimer">Forex Risk Disclaimer</a></li>
                        <li><a href="admin/login.php">Administrator Portal</a></li>
                    </ul>
                </div>

                <!-- Column 4: Contact Details -->
                <div class="col-lg-3 col-md-6">
                    <h3 class="pg-footer-heading">Connect &amp; Inquiries</h3>
                    <p class="pg-footer-text small mb-3">
                        Have questions about forex education, price action methodology, or risk management? Use the secure inquiry form to get in touch.
                    </p>
                    <ul class="list-unstyled pg-footer-contact small mb-3">
                        <?php if ($contactEmail !== '' && filter_var($contactEmail, FILTER_VALIDATE_EMAIL)): ?>
                            <li class="mb-2">
                                <span class="text-secondary d-block">Direct Email:</span>
                                <a href="mailto:<?= e($contactEmail) ?>" class="text-emerald text-decoration-none"><?= e($contactEmail) ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if ($whatsappClean !== ''): ?>
                            <li class="mb-2">
                                <span class="text-secondary d-block">WhatsApp:</span>
                                <a href="https://wa.me/<?= e($whatsappClean) ?>" target="_blank" rel="noopener noreferrer" class="text-emerald text-decoration-none">
                                    <?= e($whatsappNumber) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($contactEmail === '' && $whatsappClean === ''): ?>
                            <li class="text-secondary">
                                Direct email &amp; WhatsApp links can be configured in the Administrator Dashboard. All inquiries can be sent directly via the online contact form.
                            </li>
                        <?php endif; ?>
                    </ul>
                    <a href="contact.php" class="btn btn-sm pg-btn-primary w-100">Submit an Inquiry</a>
                </div>
            </div>

            <!-- Mandatory Forex Risk Disclaimer Box -->
            <div class="pg-disclaimer-box my-4" id="forex-risk-disclaimer" role="note" aria-label="Forex Risk Disclaimer">
                <div class="d-flex align-items-start gap-3">
                    <span class="pg-disclaimer-badge" aria-hidden="true">!</span>
                    <div>
                        <h4 class="h6 text-white fw-semibold mb-1">Forex Trading Risk Disclaimer</h4>
                        <p class="mb-2 small pg-disclaimer-copy"><?= e($footerDisclaimer) ?></p>
                        <?php if (!empty($siteSettings['uganda_compliance_notice'])): ?>
                            <p class="mb-0 small text-secondary"><?= e($siteSettings['uganda_compliance_notice']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Copyright & Attribution -->
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between pt-2 small text-secondary gap-2">
                <p class="mb-0">
                    &copy; <?= date('Y') ?> <?= e($profile['full_name'] ?? 'Patrick Grey') ?>. All rights reserved.
                </p>
                <p class="mb-0">
                    <?= e($profile['professional_title'] ?? 'Intermediate Forex Trader') ?> &bull; <?= e($profile['primary_methodology'] ?? 'Advanced Price Action') ?>
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <?php if (!empty($loadChartJs)): ?>
        <!-- Chart.js for Trading Performance Dashboard Visualizations -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" crossorigin="anonymous"></script>
    <?php endif; ?>

    <?php if (!empty($loadRiskCalculatorJs)): ?>
        <!-- Interactive Forex Risk Calculator Logic -->
        <script src="assets/js/risk-calculator.js"></script>
    <?php endif; ?>

    <!-- Main Portfolio Frontend Script -->
    <script src="assets/js/main.js"></script>
</body>
</html>
