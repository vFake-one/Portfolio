<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — PROFILE, METHODOLOGY, RISK & SITE SETTINGS
 * ============================================================================
 * File: admin/profile.php
 */

declare(strict_types=1);

$adminPageTitle = 'Profile, Methodology & Risk Settings';
require_once __DIR__ . '/includes/admin_header.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $pdo) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security token validation failed. Please try again.');
        header('Location: profile.php');
        exit;
    }

    $action = (string) ($_POST['form_action'] ?? '');

    try {
        if ($action === 'update_profile') {
            $currentProfile = get_trader_profile($pdo);
            $portraitPath = (string) ($currentProfile['portrait_image'] ?? 'assets/images/patrick-grey-placeholder.svg');

            if (!empty($_FILES['portrait_upload']['tmp_name'])) {
                $uploadRes = handle_safe_image_upload($_FILES['portrait_upload']);
                if ($uploadRes['success'] && !empty($uploadRes['path'])) {
                    $portraitPath = (string) $uploadRes['path'];
                } else {
                    set_flash('warning', 'Portrait upload skipped: ' . ($uploadRes['error'] ?? 'Invalid file.'));
                }
            }

            $stmt = $pdo->prepare("
                UPDATE trader_profile
                SET full_name               = :full_name,
                    professional_title      = :professional_title,
                    hero_subtitle           = :hero_subtitle,
                    hero_headline           = :hero_headline,
                    hero_supporting_text    = :hero_supporting_text,
                    experience_years        = :experience_years,
                    experience_label        = :experience_label,
                    primary_methodology     = :primary_methodology,
                    risk_philosophy_short   = :risk_philosophy_short,
                    risk_philosophy_summary = :risk_philosophy_summary,
                    bio_paragraph_1         = :bio_1,
                    bio_paragraph_2         = :bio_2,
                    bio_paragraph_3         = :bio_3,
                    bio_paragraph_4         = :bio_4,
                    portrait_image          = :portrait_image,
                    updated_by              = :updated_by
                WHERE id = 1
            ");

            $stmt->execute([
                ':full_name'               => trim((string) ($_POST['full_name'] ?? 'Patrick Grey')),
                ':professional_title'      => trim((string) ($_POST['professional_title'] ?? 'Intermediate Forex Trader')),
                ':hero_subtitle'           => trim((string) ($_POST['hero_subtitle'] ?? 'Intermediate Forex Trader | Advanced Price Action')),
                ':hero_headline'           => trim((string) ($_POST['hero_headline'] ?? '')),
                ':hero_supporting_text'    => trim((string) ($_POST['hero_supporting_text'] ?? '')),
                ':experience_years'        => max(1, (int) ($_POST['experience_years'] ?? 3)),
                ':experience_label'        => trim((string) ($_POST['experience_label'] ?? '3 years')),
                ':primary_methodology'     => trim((string) ($_POST['primary_methodology'] ?? 'Advanced Price Action')),
                ':risk_philosophy_short'   => trim((string) ($_POST['risk_philosophy_short'] ?? 'Adaptive, structured risk management')),
                ':risk_philosophy_summary' => trim((string) ($_POST['risk_philosophy_summary'] ?? '')),
                ':bio_1'                   => trim((string) ($_POST['bio_paragraph_1'] ?? '')),
                ':bio_2'                   => trim((string) ($_POST['bio_paragraph_2'] ?? '')),
                ':bio_3'                   => trim((string) ($_POST['bio_paragraph_3'] ?? '')),
                ':bio_4'                   => trim((string) ($_POST['bio_paragraph_4'] ?? '')),
                ':portrait_image'          => $portraitPath,
                ':updated_by'              => $adminUser['id'] ?: null,
            ]);

            set_flash('success', 'Trader profile and biography updated successfully.');
            header('Location: profile.php');
            exit;
        }

        if ($action === 'update_strategy') {
            $strategyId = (int) ($_POST['strategy_id'] ?? 0);
            $stmt = $pdo->prepare("
                UPDATE strategy_sections
                SET technique_name    = :name,
                    brief_explanation = :explanation,
                    role_in_process   = :role,
                    display_order     = :ord,
                    is_active         = :active
                WHERE id = :id
            ");
            $stmt->execute([
                ':name'        => trim((string) ($_POST['technique_name'] ?? '')),
                ':explanation' => trim((string) ($_POST['brief_explanation'] ?? '')),
                ':role'        => trim((string) ($_POST['role_in_process'] ?? '')),
                ':ord'         => max(1, (int) ($_POST['display_order'] ?? 1)),
                ':active'      => !empty($_POST['is_active']) ? 1 : 0,
                ':id'          => $strategyId,
            ]);
            set_flash('success', 'Methodology technique card updated.');
            header('Location: profile.php#methodology-editor');
            exit;
        }

        if ($action === 'update_risk_section') {
            $approachKey = trim((string) ($_POST['approach_key'] ?? ''));
            $rawPrinciples = explode("\n", (string) ($_POST['principles_lines'] ?? ''));
            $cleanPrinciples = array_values(array_filter(array_map('trim', $rawPrinciples), static fn(string $line): bool => $line !== ''));

            $riskPerTrade = trim((string) ($_POST['configurable_risk_per_trade'] ?? ''));
            $maxDrawdown = trim((string) ($_POST['configurable_max_drawdown'] ?? ''));
            $maxExposure = trim((string) ($_POST['configurable_max_exposure'] ?? ''));

            $stmt = $pdo->prepare("
                UPDATE risk_management_sections
                SET title                       = :title,
                    subtitle                    = :subtitle,
                    summary                     = :summary,
                    principles_json             = :principles_json,
                    configurable_risk_per_trade = :risk_per_trade,
                    configurable_max_drawdown   = :max_drawdown,
                    configurable_max_exposure   = :max_exposure,
                    disclaimer_note             = :disclaimer_note
                WHERE approach_key = :approach_key
            ");
            $stmt->execute([
                ':title'           => trim((string) ($_POST['title'] ?? '')),
                ':subtitle'        => trim((string) ($_POST['subtitle'] ?? '')),
                ':summary'         => trim((string) ($_POST['summary'] ?? '')),
                ':principles_json' => json_encode($cleanPrinciples),
                ':risk_per_trade'  => $riskPerTrade !== '' ? $riskPerTrade : null,
                ':max_drawdown'    => $maxDrawdown !== '' ? $maxDrawdown : null,
                ':max_exposure'    => $maxExposure !== '' ? $maxExposure : null,
                ':disclaimer_note' => trim((string) ($_POST['disclaimer_note'] ?? '')),
                ':approach_key'    => $approachKey,
            ]);

            set_flash('success', ucfirst($approachKey) . ' risk management section updated.');
            header('Location: profile.php#risk-editor');
            exit;
        }

        if ($action === 'update_site_settings') {
            $allowedKeys = [
                'site_name',
                'contact_email',
                'whatsapp_number',
                'social_linkedin',
                'social_twitter',
                'social_youtube',
                'social_telegram',
                'performance_account_currency',
                'performance_starting_balance',
                'performance_reporting_note',
                'footer_risk_disclaimer',
                'uganda_compliance_notice',
            ];

            $stmt = $pdo->prepare('UPDATE site_settings SET setting_value = :val WHERE setting_key = :key');
            foreach ($allowedKeys as $k) {
                if (isset($_POST[$k])) {
                    $stmt->execute([
                        ':val' => trim((string) $_POST[$k]),
                        ':key' => $k,
                    ]);
                }
            }

            set_flash('success', 'Contact information, social links, and legal disclaimers updated.');
            header('Location: profile.php#settings-editor');
            exit;
        }
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Admin profile update error: ' . $e->getMessage());
        set_flash('danger', 'Could not save changes. Please verify your inputs.');
        header('Location: profile.php');
        exit;
    }
}

$profile = get_trader_profile($pdo);
$strategies = get_strategy_sections($pdo, false);
$riskSections = get_risk_management_sections($pdo);
$siteSettings = get_site_settings($pdo);
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-white mb-1">Profile, Methodology &amp; Risk Settings</h1>
        <p class="small text-secondary mb-0">
            Edit Patrick Grey's biography, Advanced Price Action cards, Risk Management principles, and contact settings.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="#profile-editor" class="btn btn-sm pg-btn-outline">1. Profile &amp; Bio</a>
        <a href="#methodology-editor" class="btn btn-sm pg-btn-outline">2. Price Action (8)</a>
        <a href="#risk-editor" class="btn btn-sm pg-btn-outline">3. Risk Management</a>
        <a href="#settings-editor" class="btn btn-sm pg-btn-outline">4. Contact &amp; Legal</a>
    </div>
</div>

<!-- 1. Trader Profile & Biography Editor -->
<section class="pg-card mb-5" id="profile-editor">
    <h2 class="h5 fw-bold text-white mb-3">1. Patrick Grey Profile, Hero &amp; Biography</h2>
    <form method="post" action="profile.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="update_profile">

        <div class="row g-3">
            <div class="col-md-4">
                <label for="full_name" class="pg-form-label">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control pg-form-control"
                       value="<?= e($profile['full_name']) ?>" required>
            </div>
            <div class="col-md-4">
                <label for="professional_title" class="pg-form-label">Professional Title</label>
                <input type="text" id="professional_title" name="professional_title" class="form-control pg-form-control"
                       value="<?= e($profile['professional_title']) ?>" required>
            </div>
            <div class="col-md-4">
                <label for="hero_subtitle" class="pg-form-label">Hero Subtitle</label>
                <input type="text" id="hero_subtitle" name="hero_subtitle" class="form-control pg-form-control"
                       value="<?= e($profile['hero_subtitle']) ?>" required>
            </div>

            <div class="col-md-3">
                <label for="experience_years" class="pg-form-label">Experience (Years)</label>
                <input type="number" id="experience_years" name="experience_years" class="form-control pg-form-control"
                       value="<?= (int) $profile['experience_years'] ?>" min="1" required>
            </div>
            <div class="col-md-3">
                <label for="experience_label" class="pg-form-label">Experience Card Label</label>
                <input type="text" id="experience_label" name="experience_label" class="form-control pg-form-control"
                       value="<?= e($profile['experience_label']) ?>" required>
            </div>
            <div class="col-md-3">
                <label for="primary_methodology" class="pg-form-label">Primary Methodology</label>
                <input type="text" id="primary_methodology" name="primary_methodology" class="form-control pg-form-control"
                       value="<?= e($profile['primary_methodology']) ?>" required>
            </div>
            <div class="col-md-3">
                <label for="risk_philosophy_short" class="pg-form-label">Risk Card Short Label</label>
                <input type="text" id="risk_philosophy_short" name="risk_philosophy_short" class="form-control pg-form-control"
                       value="<?= e($profile['risk_philosophy_short']) ?>" required>
            </div>

            <div class="col-12">
                <label for="hero_headline" class="pg-form-label">Hero Section Headline</label>
                <input type="text" id="hero_headline" name="hero_headline" class="form-control pg-form-control"
                       value="<?= e($profile['hero_headline']) ?>" required>
            </div>

            <div class="col-12">
                <label for="hero_supporting_text" class="pg-form-label">Hero Supporting Text</label>
                <textarea id="hero_supporting_text" name="hero_supporting_text" rows="3" class="form-control pg-form-control" required><?= e($profile['hero_supporting_text']) ?></textarea>
            </div>

            <div class="col-md-6">
                <label for="bio_paragraph_1" class="pg-form-label">Biography Paragraph 1</label>
                <textarea id="bio_paragraph_1" name="bio_paragraph_1" rows="3" class="form-control pg-form-control" required><?= e($profile['bio_paragraph_1']) ?></textarea>
            </div>
            <div class="col-md-6">
                <label for="bio_paragraph_2" class="pg-form-label">Biography Paragraph 2</label>
                <textarea id="bio_paragraph_2" name="bio_paragraph_2" rows="3" class="form-control pg-form-control" required><?= e($profile['bio_paragraph_2']) ?></textarea>
            </div>
            <div class="col-md-6">
                <label for="bio_paragraph_3" class="pg-form-label">Biography Paragraph 3</label>
                <textarea id="bio_paragraph_3" name="bio_paragraph_3" rows="3" class="form-control pg-form-control" required><?= e($profile['bio_paragraph_3']) ?></textarea>
            </div>
            <div class="col-md-6">
                <label for="bio_paragraph_4" class="pg-form-label">Biography Paragraph 4</label>
                <textarea id="bio_paragraph_4" name="bio_paragraph_4" rows="3" class="form-control pg-form-control" required><?= e($profile['bio_paragraph_4']) ?></textarea>
            </div>

            <div class="col-md-6">
                <label for="risk_philosophy_summary" class="pg-form-label">Risk Management Summary</label>
                <textarea id="risk_philosophy_summary" name="risk_philosophy_summary" rows="2" class="form-control pg-form-control" required><?= e($profile['risk_philosophy_summary']) ?></textarea>
            </div>

            <div class="col-md-6">
                <label for="portrait_upload" class="pg-form-label">Replace Portrait Photograph (Optional JPG/PNG/WEBP, max 2 MB)</label>
                <input type="file" id="portrait_upload" name="portrait_upload" class="form-control pg-form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text text-secondary small">Current: `<?= e($profile['portrait_image']) ?>`</div>
            </div>

            <div class="col-12 pt-2">
                <button type="submit" class="btn pg-btn-primary">Save Profile &amp; Biography</button>
            </div>
        </div>
    </form>
</section>

<!-- 2. Advanced Price Action Methodology Editor -->
<section class="pg-card mb-5" id="methodology-editor">
    <h2 class="h5 fw-bold text-white mb-1">2. Advanced Price Action Methodology Cards</h2>
    <p class="small text-secondary mb-4">
        Edit any of the 8 core price action techniques displayed on the landing page and methodology page.
    </p>

    <div class="row g-4">
        <?php foreach ($strategies as $strat): ?>
            <div class="col-lg-6">
                <form method="post" action="profile.php#methodology-editor" class="p-3 rounded bg-dark border border-secondary-subtle h-100">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_action" value="update_strategy">
                    <input type="hidden" name="strategy_id" value="<?= (int) $strat['id'] ?>">

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <input type="text" name="technique_name" class="form-control form-control-sm pg-form-control fw-bold me-2"
                               value="<?= e($strat['technique_name']) ?>" aria-label="Technique Name" required>
                        <input type="number" name="display_order" class="form-control form-control-sm pg-form-control font-mono"
                               style="width: 75px;" value="<?= (int) $strat['display_order'] ?>" aria-label="Display Order" min="1">
                    </div>

                    <div class="mb-2">
                        <label class="pg-form-label small">Brief Explanation</label>
                        <textarea name="brief_explanation" rows="2" class="form-control form-control-sm pg-form-control" required><?= e($strat['brief_explanation']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="pg-form-label small">Role in Trading Process</label>
                        <textarea name="role_in_process" rows="2" class="form-control form-control-sm pg-form-control" required><?= e($strat['role_in_process']) ?></textarea>
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                   id="strat_active_<?= (int) $strat['id'] ?>" <?= !empty($strat['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label small text-secondary" for="strat_active_<?= (int) $strat['id'] ?>">
                                Active on Website
                            </label>
                        </div>
                        <button type="submit" class="btn btn-sm pg-btn-outline">Update Technique</button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- 3. Risk Management Philosophy Editor -->
<section class="pg-card mb-5" id="risk-editor">
    <h2 class="h5 fw-bold text-white mb-1">3. Risk Management Philosophy (Conservative &amp; Aggressive)</h2>
    <p class="small text-secondary mb-4">
        Edit the principles for each approach. Optional risk percentages, maximum drawdown limits, and exposure rules remain blank by default unless you supply them.
    </p>

    <div class="row g-4">
        <?php foreach (['conservative', 'aggressive'] as $key): ?>
            <?php $rk = $riskSections[$key] ?? []; ?>
            <div class="col-lg-6">
                <form method="post" action="profile.php#risk-editor" class="p-3 rounded bg-dark border border-secondary-subtle h-100">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_action" value="update_risk_section">
                    <input type="hidden" name="approach_key" value="<?= e($key) ?>">

                    <div class="mb-2">
                        <label class="pg-form-label small">Card Title</label>
                        <input type="text" name="title" class="form-control form-control-sm pg-form-control fw-bold"
                               value="<?= e($rk['title'] ?? '') ?>" required>
                    </div>

                    <div class="mb-2">
                        <label class="pg-form-label small">Subtitle</label>
                        <input type="text" name="subtitle" class="form-control form-control-sm pg-form-control"
                               value="<?= e($rk['subtitle'] ?? '') ?>" required>
                    </div>

                    <div class="mb-2">
                        <label class="pg-form-label small">Summary Description</label>
                        <textarea name="summary" rows="2" class="form-control form-control-sm pg-form-control" required><?= e($rk['summary'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="pg-form-label small">Principles (One per line)</label>
                        <textarea name="principles_lines" rows="6" class="form-control form-control-sm pg-form-control font-mono" required><?= e(implode("\n", $rk['principles'] ?? [])) ?></textarea>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-sm-4">
                            <label class="pg-form-label small">Optional Risk/Trade</label>
                            <input type="text" name="configurable_risk_per_trade" class="form-control form-control-sm pg-form-control"
                                   value="<?= e($rk['configurable_risk_per_trade'] ?? '') ?>" placeholder="Leave blank if unset">
                        </div>
                        <div class="col-sm-4">
                            <label class="pg-form-label small">Optional Max DD</label>
                            <input type="text" name="configurable_max_drawdown" class="form-control form-control-sm pg-form-control"
                                   value="<?= e($rk['configurable_max_drawdown'] ?? '') ?>" placeholder="Leave blank if unset">
                        </div>
                        <div class="col-sm-4">
                            <label class="pg-form-label small">Optional Exposure</label>
                            <input type="text" name="configurable_max_exposure" class="form-control form-control-sm pg-form-control"
                                   value="<?= e($rk['configurable_max_exposure'] ?? '') ?>" placeholder="Leave blank if unset">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="pg-form-label small">Risk Disclosure Note</label>
                        <textarea name="disclaimer_note" rows="2" class="form-control form-control-sm pg-form-control" required><?= e($rk['disclaimer_note'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-sm pg-btn-primary">Save <?= e(ucfirst($key)) ?> Card</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- 4. Contact Details, Social Links & Legal Disclaimers -->
<section class="pg-card" id="settings-editor">
    <h2 class="h5 fw-bold text-white mb-1">4. Contact Information, Social Links &amp; Disclaimers</h2>
    <p class="small text-secondary mb-3">
        Configure public email, WhatsApp number, social media profile links, and legal disclaimers.
    </p>

    <form method="post" action="profile.php#settings-editor">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="update_site_settings">

        <div class="row g-3">
            <div class="col-md-4">
                <label for="contact_email" class="pg-form-label">Public Contact Email</label>
                <input type="email" id="contact_email" name="contact_email" class="form-control pg-form-control"
                       value="<?= e($siteSettings['contact_email'] ?? '') ?>" placeholder="Leave blank until supplied">
            </div>
            <div class="col-md-4">
                <label for="whatsapp_number" class="pg-form-label">WhatsApp Number (International Format)</label>
                <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control pg-form-control"
                       value="<?= e($siteSettings['whatsapp_number'] ?? '') ?>" placeholder="e.g., +256700000000">
            </div>
            <div class="col-md-2">
                <label for="performance_account_currency" class="pg-form-label">Perf. Currency</label>
                <input type="text" id="performance_account_currency" name="performance_account_currency" class="form-control pg-form-control font-mono"
                       value="<?= e($siteSettings['performance_account_currency'] ?? 'USD') ?>">
            </div>
            <div class="col-md-2">
                <label for="performance_starting_balance" class="pg-form-label">Ref. Start Balance</label>
                <input type="text" id="performance_starting_balance" name="performance_starting_balance" class="form-control pg-form-control font-mono"
                       value="<?= e($siteSettings['performance_starting_balance'] ?? '') ?>" placeholder="Optional">
            </div>

            <div class="col-md-3">
                <label for="social_linkedin" class="pg-form-label">LinkedIn URL</label>
                <input type="url" id="social_linkedin" name="social_linkedin" class="form-control pg-form-control"
                       value="<?= e($siteSettings['social_linkedin'] ?? '') ?>" placeholder="https://...">
            </div>
            <div class="col-md-3">
                <label for="social_twitter" class="pg-form-label">X / Twitter URL</label>
                <input type="url" id="social_twitter" name="social_twitter" class="form-control pg-form-control"
                       value="<?= e($siteSettings['social_twitter'] ?? '') ?>" placeholder="https://...">
            </div>
            <div class="col-md-3">
                <label for="social_youtube" class="pg-form-label">YouTube URL</label>
                <input type="url" id="social_youtube" name="social_youtube" class="form-control pg-form-control"
                       value="<?= e($siteSettings['social_youtube'] ?? '') ?>" placeholder="https://...">
            </div>
            <div class="col-md-3">
                <label for="social_telegram" class="pg-form-label">Telegram URL</label>
                <input type="url" id="social_telegram" name="social_telegram" class="form-control pg-form-control"
                       value="<?= e($siteSettings['social_telegram'] ?? '') ?>" placeholder="https://...">
            </div>

            <div class="col-12">
                <label for="footer_risk_disclaimer" class="pg-form-label">Footer Forex Risk Disclaimer</label>
                <textarea id="footer_risk_disclaimer" name="footer_risk_disclaimer" rows="3" class="form-control pg-form-control" required><?= e($siteSettings['footer_risk_disclaimer'] ?? '') ?></textarea>
            </div>

            <div class="col-12">
                <label for="uganda_compliance_notice" class="pg-form-label">Regulatory &amp; Compliance Notice (Uganda &amp; International)</label>
                <textarea id="uganda_compliance_notice" name="uganda_compliance_notice" rows="2" class="form-control pg-form-control" required><?= e($siteSettings['uganda_compliance_notice'] ?? '') ?></textarea>
            </div>

            <div class="col-12 pt-2">
                <button type="submit" class="btn pg-btn-primary">Save Contact &amp; Legal Settings</button>
            </div>
        </div>
    </form>
</section>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
