<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — TERMS OF USE & RISK DISCLOSURE PAGE
 * ============================================================================
 * File: terms.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();
$profile = get_trader_profile($pdo);
$siteSettings = get_site_settings($pdo);

$pageTitle = 'Terms of Use & Risk Disclosure';
$pageDescription = 'Terms of use, educational scope, and foreign exchange risk disclosures for the Patrick Grey Forex Portfolio website.';

require_once __DIR__ . '/includes/header.php';
?>
<section class="pg-section">
    <div class="container" style="max-width: 860px;">
        <div class="pg-card p-4 p-lg-5">
            <span class="pg-eyebrow">Legal &amp; Educational Scope</span>
            <h1 class="h2 fw-bold text-white mb-4">Terms of Use &amp; Forex Risk Disclosure</h1>

            <h2 class="h5 text-white fw-bold mt-4">1. Educational &amp; Informational Purpose Only</h2>
            <p class="text-secondary">
                All content published on the Patrick Grey Forex Portfolio—including price action methodology descriptions, risk management principles, interactive risk calculator outputs, educational articles, and trading performance logs—is provided strictly for educational and informational purposes. Nothing on this website constitutes personalized financial, investment, legal, or tax advice.
            </p>

            <h2 class="h5 text-white fw-bold mt-4">2. High Risk of Foreign Exchange Trading</h2>
            <p class="text-secondary">
                Trading foreign exchange (forex) on margin carries a high level of risk and may not be suitable for all individuals. Leverage can work against you as well as for you. Before deciding to trade foreign exchange, you should carefully consider your financial objectives, level of experience, and risk appetite. You could sustain a loss of some or all of your initial capital and should never trade with money you cannot afford to lose.
            </p>

            <h2 class="h5 text-white fw-bold mt-4">3. No Guaranteed Returns &amp; No Client Fund Management</h2>
            <p class="text-secondary">
                Patrick Grey does not promise guaranteed returns, does not accept client investment deposits, does not operate pooled investment funds, and does not provide discretionary account management or automated trade execution. Three years of trading experience reflects an intermediate stage of market study and does not constitute a guarantee of future profitability.
            </p>

            <h2 class="h5 text-white fw-bold mt-4">4. Regulatory &amp; Licensing Compliance</h2>
            <p class="text-secondary mb-0">
                <?= e($siteSettings['uganda_compliance_notice'] ?? '') ?>
            </p>
        </div>
    </div>
</section>
<?php
require_once __DIR__ . '/includes/footer.php';
