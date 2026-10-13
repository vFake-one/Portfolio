<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — PRIVACY POLICY PAGE
 * ============================================================================
 * File: privacy.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();
$profile = get_trader_profile($pdo);
$siteSettings = get_site_settings($pdo);

$pageTitle = 'Privacy Policy';
$pageDescription = 'Privacy policy explaining how visitor inquiry information is collected, protected, and used on the Patrick Grey Forex Portfolio website.';

require_once __DIR__ . '/includes/header.php';
?>
<section class="pg-section">
    <div class="container" style="max-width: 860px;">
        <div class="pg-card p-4 p-lg-5">
            <span class="pg-eyebrow">Data Protection &amp; Transparency</span>
            <h1 class="h2 fw-bold text-white mb-4">Privacy Policy</h1>

            <h2 class="h5 text-white fw-bold mt-4">1. Information Collected</h2>
            <p class="text-secondary">
                When you submit an inquiry through the contact form on this website, we collect only the information necessary to review and respond to your message: your full name, email address, optional phone or WhatsApp number, selected area of interest, message content, and your consent confirmation timestamp.
            </p>

            <h2 class="h5 text-white fw-bold mt-4">2. Purpose of Collection</h2>
            <p class="text-secondary">
                Your inquiry details are used exclusively to respond to your request regarding forex education, price action methodology discussions, or other lawful services listed on this platform. We do not sell, rent, or trade visitor contact information to third parties or marketing brokers.
            </p>

            <h2 class="h5 text-white fw-bold mt-4">3. Security Measures</h2>
            <p class="text-secondary">
                Inquiries are stored in a protected relational database using parameterized PDO prepared statements, CSRF protection, and authenticated administrator access controls.
            </p>

            <h2 class="h5 text-white fw-bold mt-4">4. Essential Cookies</h2>
            <p class="text-secondary">
                This website uses strictly necessary session cookies (`HttpOnly`, `SameSite=Lax`) to protect forms against Cross-Site Request Forgery (CSRF) and to maintain authenticated administrator sessions.
            </p>

            <h2 class="h5 text-white fw-bold mt-4">5. Data Deletion Requests</h2>
            <p class="text-secondary mb-0">
                You may request the review or deletion of any inquiry you have submitted by sending a message through the <a href="contact.php" class="text-emerald text-decoration-none">Contact Form</a> referencing the email address used in your original submission.
            </p>
        </div>
    </div>
</section>
<?php
require_once __DIR__ . '/includes/footer.php';
