<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — MODULAR SECTION RENDERERS
 * ============================================================================
 * File: includes/sections.php
 *
 * Provides modular rendering functions for each section of the portfolio so
 * both the single-page landing layout (`index.php`) and the dedicated topic
 * pages (`about.php`, `methodology.php`, `performance.php`, etc.) share the
 * exact same database-driven markup.
 * ============================================================================
 */

declare(strict_types=1);

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'sections.php') {
    http_response_code(403);
    exit('Direct access is not permitted.');
}

require_once __DIR__ . '/functions.php';

/**
 * Section 5.B — Hero Section
 */
function render_hero_section(array $profile, bool $isLandingPage = true): void
{
    $approachHref = $isLandingPage ? '#trading-approach' : 'methodology.php';
    $contactHref = $isLandingPage ? '#contact' : 'contact.php';
    $performanceHref = $isLandingPage ? '#performance' : 'performance.php';
    ?>
    <section class="pg-hero" id="home" aria-labelledby="hero-heading">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="pg-eyebrow">
                        <span><?= e($profile['full_name'] ?? 'Patrick Grey') ?></span>
                        <span aria-hidden="true">&bull;</span>
                        <span><?= e($profile['experience_label'] ?? '3 years') ?> Trading Experience</span>
                    </div>

                    <p class="pg-hero-subtitle mb-2">
                        <?= e($profile['hero_subtitle'] ?? 'Intermediate Forex Trader | Advanced Price Action') ?>
                    </p>

                    <h1 class="pg-hero-title mb-3" id="hero-heading">
                        <?= e($profile['hero_headline'] ?? 'Forex Trading Through Price Action, Discipline, and Risk Awareness.') ?>
                    </h1>

                    <p class="pg-hero-copy mb-4">
                        <?= e($profile['hero_supporting_text'] ?? "I'm Patrick Grey, an intermediate forex trader with three years of trading experience. My approach centers on advanced price action, market structure analysis, and disciplined trade planning. Explore my methodology, learn about my approach to risk management, and discover educational resources designed to help you understand the forex market.") ?>
                    </p>

                    <!-- Two Primary Calls to Action + Secondary Performance Link -->
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                        <a href="<?= e($approachHref) ?>" class="btn pg-btn-primary">
                            Explore My Trading Approach
                        </a>
                        <a href="<?= e($contactHref) ?>" class="btn pg-btn-outline">
                            Get in Touch
                        </a>
                    </div>

                    <div>
                        <a href="<?= e($performanceHref) ?>" class="pg-hero-secondary-link">
                            <span>View Trading Performance</span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Subtle Chart-Inspired Market Visualization -->
                <div class="col-lg-5">
                    <div class="pg-card p-3">
                        <img src="assets/images/candlestick-hero.svg"
                             alt="Advanced Price Action technical chart illustration showing market structure, support and resistance retest, and risk-defined stop-loss placement"
                             class="img-fluid rounded"
                             width="640" height="440">
                        <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 mt-2 border-top border-secondary-subtle small text-secondary gap-2">
                            <span><strong class="text-white">Core Focus:</strong> <?= e($profile['primary_methodology'] ?? 'Advanced Price Action') ?></span>
                            <span class="badge pg-badge-emerald">Predefined Risk Limits</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Section 5.C — About Patrick Grey
 */
function render_about_section(array $profile): void
{
    $portraitPath = !empty($profile['portrait_image']) ? (string) $profile['portrait_image'] : 'assets/images/patrick-grey-placeholder.svg';
    ?>
    <section class="pg-section" id="about" aria-labelledby="about-heading">
        <div class="container">
            <div class="row align-items-center g-5">
                <!-- Portrait Column -->
                <div class="col-lg-5">
                    <div class="pg-card p-3 text-center">
                        <img src="<?= e($portraitPath) ?>"
                             alt="Portrait of <?= e($profile['full_name'] ?? 'Patrick Grey') ?>, <?= e($profile['professional_title'] ?? 'Intermediate Forex Trader') ?>"
                             class="img-fluid rounded"
                             width="480" height="540">
                    </div>
                </div>

                <!-- Biography & Profile Information Cards Column -->
                <div class="col-lg-7">
                    <span class="pg-eyebrow">About the Trader</span>
                    <h2 class="pg-section-title" id="about-heading">About <?= e($profile['full_name'] ?? 'Patrick Grey') ?></h2>

                    <div class="pg-bio-content text-secondary mb-4">
                        <p class="mb-3 text-light"><?= nl2br(e($profile['bio_paragraph_1'] ?? '')) ?></p>
                        <p class="mb-3"><?= nl2br(e($profile['bio_paragraph_2'] ?? '')) ?></p>
                        <p class="mb-3"><?= nl2br(e($profile['bio_paragraph_3'] ?? '')) ?></p>
                        <p class="mb-0 fw-medium text-white"><?= nl2br(e($profile['bio_paragraph_4'] ?? '')) ?></p>
                    </div>

                    <!-- Four Required Profile Information Cards -->
                    <div class="row g-3" role="list" aria-label="Patrick Grey Profile Summary Cards">
                        <div class="col-sm-6" role="listitem">
                            <div class="pg-profile-stat-card">
                                <div class="pg-profile-stat-label">Trader</div>
                                <p class="pg-profile-stat-value"><?= e($profile['full_name'] ?? 'Patrick Grey') ?></p>
                            </div>
                        </div>
                        <div class="col-sm-6" role="listitem">
                            <div class="pg-profile-stat-card">
                                <div class="pg-profile-stat-label">Experience</div>
                                <p class="pg-profile-stat-value"><?= e($profile['experience_label'] ?? '3 years') ?></p>
                            </div>
                        </div>
                        <div class="col-sm-6" role="listitem">
                            <div class="pg-profile-stat-card">
                                <div class="pg-profile-stat-label">Primary Methodology</div>
                                <p class="pg-profile-stat-value"><?= e($profile['primary_methodology'] ?? 'Advanced Price Action') ?></p>
                            </div>
                        </div>
                        <div class="col-sm-6" role="listitem">
                            <div class="pg-profile-stat-card">
                                <div class="pg-profile-stat-label">Risk Philosophy</div>
                                <p class="pg-profile-stat-value"><?= e($profile['risk_philosophy_short'] ?? 'Adaptive, structured risk management') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Section 5.D — Advanced Price Action Methodology
 */
function render_methodology_section(array $strategies): void
{
    ?>
    <section class="pg-section pg-section-alt" id="trading-approach" aria-labelledby="methodology-heading">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 760px;">
                <span class="pg-eyebrow">Core Trading Methodology</span>
                <h2 class="pg-section-title" id="methodology-heading">Advanced Price Action Methodology</h2>
                <p class="pg-section-lead mx-auto mb-0">
                    Patrick Grey's stated methodology focuses on reading raw price movement, market structure, and key horizontal zones—combining technical context with predefined trade planning and invalidation levels.
                </p>
            </div>

            <div class="row g-4">
                <?php foreach ($strategies as $index => $item): ?>
                    <?php
                    $stepNumber = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                    $visualKey = (string) ($item['visual_key'] ?? 'structure');
                    ?>
                    <div class="col-md-6 col-xl-3">
                        <article class="pg-card d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="pg-step-badge" aria-label="Step <?= e($stepNumber) ?>"><?= e($stepNumber) ?></span>
                                    <span class="small text-secondary font-mono">Price Action</span>
                                </div>

                                <?= render_methodology_svg($visualKey) ?>

                                <h3 class="h5 fw-bold text-white mb-2">
                                    <?= e($item['technique_name'] ?? '') ?>
                                </h3>

                                <p class="small text-secondary mb-3">
                                    <?= e($item['brief_explanation'] ?? '') ?>
                                </p>
                            </div>

                            <div class="pt-3 border-top border-secondary-subtle mt-auto">
                                <span class="d-block small text-emerald fw-semibold mb-1">Role in Trading Process:</span>
                                <p class="small text-light mb-0">
                                    <?= e($item['role_in_process'] ?? '') ?>
                                </p>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pg-card mt-4 p-3 border-secondary-subtle">
                <p class="small text-secondary mb-0 text-center">
                    <strong class="text-white">Methodology Transparency Note:</strong> These eight elements represent Patrick Grey's structured price action workflow. Technical analysis helps organize trade ideas and define invalidation levels, but no analytical method predicts market movement with certainty.
                </p>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Section 5.E — Risk Management Philosophy
 */
function render_risk_management_section(array $riskSections): void
{
    $conservative = $riskSections['conservative'] ?? [];
    $aggressive = $riskSections['aggressive'] ?? [];
    ?>
    <section class="pg-section" id="risk-management" aria-labelledby="risk-heading">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 780px;">
                <span class="pg-eyebrow">Capital Protection &amp; Exposure Control</span>
                <h2 class="pg-section-title" id="risk-heading">Risk Management Philosophy</h2>
                <p class="pg-section-lead mx-auto mb-0">
                    A combination of conservative and selective aggressive risk management, applied according to market conditions, setup quality, account exposure, and predefined risk limits.
                </p>
            </div>

            <div class="row g-4 align-items-stretch">
                <!-- Card 1: Conservative Risk Management -->
                <div class="col-lg-6">
                    <article class="pg-card pg-risk-card-conservative d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <span class="badge pg-badge-emerald">Foundational Mode</span>
                                <span class="small text-secondary font-mono">Capital Preservation</span>
                            </div>
                            <h3 class="h4 fw-bold text-white mb-1">
                                <?= e($conservative['title'] ?? 'Conservative Risk Management') ?>
                            </h3>
                            <p class="small text-emerald fw-semibold mb-3">
                                <?= e($conservative['subtitle'] ?? 'Capital Preservation & Strict Exposure Control') ?>
                            </p>
                            <p class="text-secondary small mb-4">
                                <?= e($conservative['summary'] ?? '') ?>
                            </p>

                            <h4 class="h6 text-uppercase text-light fw-bold mb-3 small">Core Principles</h4>
                            <ul class="pg-principle-list mb-4">
                                <?php foreach (($conservative['principles'] ?? []) as $principle): ?>
                                    <li><?= e($principle) ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <?php if (!empty($conservative['configurable_risk_per_trade']) || !empty($conservative['configurable_max_drawdown']) || !empty($conservative['configurable_max_exposure'])): ?>
                                <div class="p-3 rounded bg-dark border border-secondary-subtle mb-3 small">
                                    <div class="fw-bold text-white mb-2">Configured Risk Parameters:</div>
                                    <?php if (!empty($conservative['configurable_risk_per_trade'])): ?>
                                        <div><span class="text-secondary">Risk per Trade:</span> <span class="font-mono text-emerald"><?= e($conservative['configurable_risk_per_trade']) ?></span></div>
                                    <?php endif; ?>
                                    <?php if (!empty($conservative['configurable_max_drawdown'])): ?>
                                        <div><span class="text-secondary">Max Drawdown Limit:</span> <span class="font-mono text-emerald"><?= e($conservative['configurable_max_drawdown']) ?></span></div>
                                    <?php endif; ?>
                                    <?php if (!empty($conservative['configurable_max_exposure'])): ?>
                                        <div><span class="text-secondary">Max Account Exposure:</span> <span class="font-mono text-emerald"><?= e($conservative['configurable_max_exposure']) ?></span></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="pt-3 border-top border-secondary-subtle mt-auto">
                            <p class="small text-secondary mb-0">
                                <?= e($conservative['disclaimer_note'] ?? '') ?>
                            </p>
                        </div>
                    </article>
                </div>

                <!-- Card 2: Aggressive Risk Management -->
                <div class="col-lg-6">
                    <article class="pg-card pg-risk-card-aggressive d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <span class="badge pg-badge-amber">Selective Conviction Mode</span>
                                <span class="small text-secondary font-mono">Predefined Limits</span>
                            </div>
                            <h3 class="h4 fw-bold text-white mb-1">
                                <?= e($aggressive['title'] ?? 'Aggressive Risk Management') ?>
                            </h3>
                            <p class="small text-warning fw-semibold mb-3">
                                <?= e($aggressive['subtitle'] ?? 'Selective Conviction Within Predefined Risk Limits') ?>
                            </p>
                            <p class="text-secondary small mb-4">
                                <?= e($aggressive['summary'] ?? '') ?>
                            </p>

                            <h4 class="h6 text-uppercase text-light fw-bold mb-3 small">Core Principles</h4>
                            <ul class="pg-principle-list mb-4">
                                <?php foreach (($aggressive['principles'] ?? []) as $principle): ?>
                                    <li><?= e($principle) ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <?php if (!empty($aggressive['configurable_risk_per_trade']) || !empty($aggressive['configurable_max_drawdown']) || !empty($aggressive['configurable_max_exposure'])): ?>
                                <div class="p-3 rounded bg-dark border border-secondary-subtle mb-3 small">
                                    <div class="fw-bold text-white mb-2">Configured Risk Parameters:</div>
                                    <?php if (!empty($aggressive['configurable_risk_per_trade'])): ?>
                                        <div><span class="text-secondary">Risk per Trade:</span> <span class="font-mono text-warning"><?= e($aggressive['configurable_risk_per_trade']) ?></span></div>
                                    <?php endif; ?>
                                    <?php if (!empty($aggressive['configurable_max_drawdown'])): ?>
                                        <div><span class="text-secondary">Max Drawdown Limit:</span> <span class="font-mono text-warning"><?= e($aggressive['configurable_max_drawdown']) ?></span></div>
                                    <?php endif; ?>
                                    <?php if (!empty($aggressive['configurable_max_exposure'])): ?>
                                        <div><span class="text-secondary">Max Account Exposure:</span> <span class="font-mono text-warning"><?= e($aggressive['configurable_max_exposure']) ?></span></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="pt-3 border-top border-secondary-subtle mt-auto">
                            <p class="small text-secondary mb-0">
                                <?= e($aggressive['disclaimer_note'] ?? '') ?>
                            </p>
                        </div>
                    </article>
                </div>
            </div>

            <!-- Important Clarification Statement Required by Section 5.E -->
            <div class="pg-disclaimer-box mt-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-12">
                        <h4 class="h6 text-white fw-bold mb-1">
                            Discipline &amp; Leverage Disclosure: What Aggressive Risk Management Does (and Does Not) Mean
                        </h4>
                        <p class="small text-secondary mb-0">
                            Applying an aggressive risk approach in selective market conditions <strong class="text-white">does not mean uncontrolled trading, gambling, or ignoring risk limits</strong>. Every trade requires a predefined invalidation level (stop-loss) and position size calculation before entry. Neither conservative nor aggressive risk management guarantees profitable results, and the use of margin leverage can magnify losses as well as gains.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Section 5.F — Trading Performance Dashboard
 */
function render_performance_section(array $perfData, array $siteSettings): void
{
    $currency = (string) ($perfData['account_currency'] ?? 'USD');
    ?>
    <section class="pg-section pg-section-alt" id="performance" aria-labelledby="performance-heading">
        <div class="container">
            <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between mb-5 gap-3">
                <div>
                    <span class="pg-eyebrow">Documented Trade Log &amp; Analytics</span>
                    <h2 class="pg-section-title mb-2" id="performance-heading">Trading Performance Dashboard</h2>
                    <p class="pg-section-lead mb-0">
                        Statistics and charts below are calculated dynamically from logged trading records stored in the database. No hypothetical or fabricated figures are ever displayed.
                    </p>
                </div>

                <?php if (!empty($perfData['has_records'])): ?>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge pg-badge-amber px-3 py-2">
                            <?= e($perfData['verification_label'] ?? 'Self-Reported Trading Log') ?>
                        </span>
                        <span class="badge pg-badge-emerald px-3 py-2 font-mono">
                            Account Currency: <?= e($currency) ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (empty($perfData['has_records'])): ?>
                <!-- Exact Required Empty State When No Records Exist -->
                <div class="pg-empty-state" role="status" aria-live="polite">
                    <div class="mb-3">
                        <span class="pg-step-badge mx-auto" style="width:3rem;height:3rem;font-size:1.1rem;" aria-hidden="true">&#128202;</span>
                    </div>
                    <h3 class="h5 text-white fw-bold mb-2">No Trading Performance Records Published Yet</h3>
                    <p class="text-light mx-auto mb-3" style="max-width: 620px; font-size: 1.05rem;">
                        <?= e($perfData['empty_message']) ?>
                    </p>
                    <p class="small text-secondary mx-auto mb-0" style="max-width: 640px;">
                        To maintain complete transparency, this portfolio does not display simulated equity curves or placeholder profit statistics. Authenticated administrators can log documented trading records via the <a href="admin/performance.php" class="text-emerald text-decoration-none">Administrator Dashboard</a>.
                    </p>
                </div>
            <?php else: ?>
                <!-- Reporting Period & Transparency Metadata Bar -->
                <div class="pg-card mb-4 py-3 px-4">
                    <div class="row g-3 align-items-center small">
                        <div class="col-md-4">
                            <span class="text-secondary">Reporting Period:</span>
                            <strong class="text-white ms-1 font-mono">
                                <?= e($perfData['reporting_period_start'] ?? '—') ?> &ndash; <?= e($perfData['reporting_period_end'] ?? '—') ?>
                            </strong>
                        </div>
                        <div class="col-md-4">
                            <span class="text-secondary">Result Breakdown:</span>
                            <strong class="text-white ms-1 font-mono">
                                <?= (int) $perfData['realized_trades'] ?> Realized / <?= (int) $perfData['unrealized_trades'] ?> Unrealized
                            </strong>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="text-secondary">Verification Status:</span>
                            <strong class="text-warning ms-1">
                                <?= (int) $perfData['self_reported_count'] ?> Self-Reported, <?= (int) $perfData['independently_verified_count'] ?> Verified
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- Summary Metric Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="pg-metric-card">
                            <div class="pg-metric-label">Total Recorded Trades</div>
                            <div class="pg-metric-value"><?= (int) $perfData['total_trades'] ?></div>
                            <div class="small text-secondary"><?= (int) $perfData['realized_trades'] ?> closed (realized)</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="pg-metric-card">
                            <div class="pg-metric-label">Win / Loss / Break-Even</div>
                            <div class="pg-metric-value">
                                <span class="text-emerald"><?= (int) $perfData['winning_trades'] ?>W</span> /
                                <span class="text-loss"><?= (int) $perfData['losing_trades'] ?>L</span> /
                                <span class="text-secondary"><?= (int) $perfData['breakeven_trades'] ?>BE</span>
                            </div>
                            <div class="small text-secondary">Realized trade outcomes</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="pg-metric-card">
                            <div class="pg-metric-label">Realized Win Rate</div>
                            <div class="pg-metric-value">
                                <?= $perfData['win_rate'] !== null ? e(number_format((float) $perfData['win_rate'], 1)) . '%' : 'N/A' ?>
                            </div>
                            <div class="small text-secondary">Based on <?= (int) $perfData['realized_trades'] ?> closed trades</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="pg-metric-card">
                            <div class="pg-metric-label">Net Realized P/L</div>
                            <?php $netPl = (float) $perfData['net_realized_pl']; ?>
                            <div class="pg-metric-value <?= $netPl >= 0 ? 'text-emerald' : 'text-loss' ?>">
                                <?= ($netPl >= 0 ? '+' : '') . e(number_format($netPl, 2)) . ' ' . e($currency) ?>
                            </div>
                            <div class="small text-secondary">
                                Unrealized: <?= e(number_format((float) $perfData['net_unrealized_pl'], 2)) . ' ' . e($currency) ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="pg-metric-card">
                            <div class="pg-metric-label">Average Win</div>
                            <div class="pg-metric-value text-emerald">
                                <?= $perfData['average_win'] !== null ? '+' . e(number_format((float) $perfData['average_win'], 2)) . ' ' . e($currency) : 'N/A' ?>
                            </div>
                            <div class="small text-secondary">Mean profit on winning trades</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="pg-metric-card">
                            <div class="pg-metric-label">Average Loss</div>
                            <div class="pg-metric-value text-loss">
                                <?= $perfData['average_loss'] !== null ? '-' . e(number_format((float) $perfData['average_loss'], 2)) . ' ' . e($currency) : 'N/A' ?>
                            </div>
                            <div class="small text-secondary">Mean loss on losing trades</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="pg-metric-card">
                            <div class="pg-metric-label">Peak-to-Trough Drawdown</div>
                            <div class="pg-metric-value text-loss">
                                <?php if ($perfData['max_drawdown_amount'] !== null): ?>
                                    <?= e(number_format((float) $perfData['max_drawdown_amount'], 2)) . ' ' . e($currency) ?>
                                    <?php if ($perfData['max_drawdown_percent'] !== null): ?>
                                        <span class="fs-6">(<?= e(number_format((float) $perfData['max_drawdown_percent'], 2)) ?>%)</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </div>
                            <div class="small text-secondary">From cumulative realized P/L curve</div>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="pg-metric-card">
                            <div class="pg-metric-label">Average Planned R:R</div>
                            <div class="pg-metric-value">
                                <?= $perfData['average_rr'] !== null ? '1 : ' . e(number_format((float) $perfData['average_rr'], 2)) : 'N/A' ?>
                            </div>
                            <div class="small text-secondary">Where R:R is logged</div>
                        </div>
                    </div>
                </div>

                <!-- Chart.js Visualizations -->
                <script id="pgPerformanceChartData" type="application/json">
                    <?= json_encode([
                        'hasRecords'    => true,
                        'currency'      => $currency,
                        'equityLabels'  => $perfData['chart_equity_labels'],
                        'equityValues'  => $perfData['chart_equity_values'],
                        'monthlyLabels' => $perfData['chart_monthly_labels'],
                        'monthlyValues' => $perfData['chart_monthly_values'],
                        'winLossValues' => $perfData['chart_win_loss_values'],
                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                </script>

                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="pg-card">
                            <h3 class="h6 fw-bold text-white mb-3">Historical Cumulative Realized P/L Curve (<?= e($currency) ?>)</h3>
                            <div style="height: 290px;">
                                <canvas id="equityCurveChart" aria-label="Historical cumulative realized profit and loss chart" role="img"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="pg-card">
                            <h3 class="h6 fw-bold text-white mb-3">Win / Loss Distribution</h3>
                            <div style="height: 290px;">
                                <canvas id="winLossChart" aria-label="Win and loss trade distribution chart" role="img"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="pg-card">
                            <h3 class="h6 fw-bold text-white mb-3">Monthly Realized Profit &amp; Loss (<?= e($currency) ?>)</h3>
                            <div style="height: 250px;">
                                <canvas id="monthlyPlChart" aria-label="Monthly realized profit and loss bar chart" role="img"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Logged Trades Table -->
                <div class="pg-card">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h3 class="h6 fw-bold text-white mb-0">Documented Trade Records</h3>
                        <span class="small text-secondary"><?= e($siteSettings['performance_reporting_note'] ?? '') ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table pg-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">Instrument</th>
                                    <th scope="col">Direction</th>
                                    <th scope="col">Entry / Exit</th>
                                    <th scope="col">Lots</th>
                                    <th scope="col">R:R</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Verification</th>
                                    <th scope="col" class="text-end">P/L</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($perfData['records'], 0, 15) as $rec): ?>
                                    <?php
                                    $pl = (float) ($rec['profit_loss'] ?? 0);
                                    $isVerified = ($rec['verification_status'] ?? '') === 'independently_verified'
                                        && !empty(trim((string) ($rec['verification_evidence_note'] ?? '')));
                                    ?>
                                    <tr>
                                        <td class="font-mono small"><?= e(date('Y-m-d', strtotime((string) ($rec['closed_at'] ?: $rec['opened_at'])))) ?></td>
                                        <td class="fw-bold"><?= e($rec['instrument']) ?></td>
                                        <td>
                                            <span class="badge <?= ($rec['trade_direction'] === 'BUY') ? 'pg-badge-emerald' : 'pg-badge-amber' ?>">
                                                <?= e($rec['trade_direction']) ?>
                                            </span>
                                        </td>
                                        <td class="font-mono small">
                                            <?= e(rtrim(rtrim((string) $rec['entry_price'], '0'), '.')) ?>
                                            &rarr;
                                            <?= $rec['exit_price'] !== null ? e(rtrim(rtrim((string) $rec['exit_price'], '0'), '.')) : 'Open' ?>
                                        </td>
                                        <td class="font-mono small"><?= e(number_format((float) $rec['position_size'], 2)) ?></td>
                                        <td class="font-mono small"><?= $rec['risk_reward_ratio'] !== null ? '1:' . e(number_format((float) $rec['risk_reward_ratio'], 2)) : '—' ?></td>
                                        <td>
                                            <span class="small text-secondary text-capitalize"><?= e($rec['result_type']) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($isVerified): ?>
                                                <span class="badge pg-badge-emerald" title="<?= e($rec['verification_evidence_note']) ?>">Independently Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Self-Reported</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end font-mono fw-bold <?= $pl >= 0 ? 'text-emerald' : 'text-loss' ?>">
                                            <?= ($pl >= 0 ? '+' : '') . e(number_format($pl, 2)) . ' ' . e($rec['account_currency']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

/**
 * Section 5.G — Interactive Forex Risk Calculator
 */
function render_risk_calculator_section(): void
{
    ?>
    <section class="pg-section" id="risk-calculator" aria-labelledby="calculator-heading">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 780px;">
                <span class="pg-eyebrow">Position Sizing &amp; Exposure Tool</span>
                <h2 class="pg-section-title" id="calculator-heading">Interactive Forex Risk Calculator</h2>
                <p class="pg-section-lead mx-auto mb-0">
                    Calculate your planned monetary risk, stop-loss distance in pips, and estimated position size before entering a trade. Values below are illustrative and educational—not a recommended risk percentage.
                </p>
            </div>

            <div class="row g-4 align-items-start">
                <!-- Calculator Inputs Column -->
                <div class="col-lg-7">
                    <div class="pg-card">
                        <h3 class="h5 fw-bold text-white mb-3">1. Trade &amp; Account Parameters</h3>

                        <div id="rcValidationAlert" class="alert alert-danger d-none small" role="alert"></div>

                        <form id="forexRiskCalculatorForm" novalidate>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="rcAccountBalance" class="pg-form-label">Account Balance</label>
                                    <input type="number" id="rcAccountBalance" class="form-control pg-form-control font-mono"
                                           value="100" min="1" step="any" required>
                                    <div class="form-text text-secondary small">Example: 100</div>
                                </div>

                                <div class="col-sm-6">
                                    <label for="rcAccountCurrency" class="pg-form-label">Account Currency</label>
                                    <select id="rcAccountCurrency" class="form-select pg-form-select font-mono">
                                        <option value="USD" selected>USD — US Dollar</option>
                                        <option value="EUR">EUR — Euro</option>
                                        <option value="GBP">GBP — British Pound</option>
                                        <option value="UGX">UGX — Ugandan Shilling</option>
                                        <option value="JPY">JPY — Japanese Yen</option>
                                        <option value="AUD">AUD — Australian Dollar</option>
                                        <option value="CAD">CAD — Canadian Dollar</option>
                                        <option value="CHF">CHF — Swiss Franc</option>
                                    </select>
                                </div>

                                <div class="col-sm-6">
                                    <label for="rcRiskPercent" class="pg-form-label">Planned Risk Percentage (%)</label>
                                    <input type="number" id="rcRiskPercent" class="form-control pg-form-control font-mono"
                                           value="1" min="0.01" max="100" step="0.1" required>
                                    <div class="form-text text-secondary small">Illustrative input (e.g., 1% of 100 = 1.00)</div>
                                </div>

                                <div class="col-sm-6">
                                    <label for="rcCurrencyPair" class="pg-form-label">Currency Pair</label>
                                    <select id="rcCurrencyPair" class="form-select pg-form-select font-mono">
                                        <option value="EUR/USD" selected>EUR/USD</option>
                                        <option value="GBP/USD">GBP/USD</option>
                                        <option value="USD/JPY">USD/JPY</option>
                                        <option value="AUD/USD">AUD/USD</option>
                                        <option value="USD/CHF">USD/CHF</option>
                                        <option value="USD/CAD">USD/CAD</option>
                                        <option value="EUR/GBP">EUR/GBP</option>
                                        <option value="GBP/JPY">GBP/JPY</option>
                                    </select>
                                </div>

                                <div class="col-sm-4">
                                    <label for="rcEntryPrice" class="pg-form-label">Entry Price</label>
                                    <input type="number" id="rcEntryPrice" class="form-control pg-form-control font-mono"
                                           value="1.0850" min="0.00001" step="any" required>
                                </div>

                                <div class="col-sm-4">
                                    <label for="rcStopLossPrice" class="pg-form-label">Stop-Loss Price</label>
                                    <input type="number" id="rcStopLossPrice" class="form-control pg-form-control font-mono"
                                           value="1.0830" min="0.00001" step="any" required>
                                </div>

                                <div class="col-sm-4">
                                    <label for="rcTakeProfitPrice" class="pg-form-label">Take-Profit (Optional)</label>
                                    <input type="number" id="rcTakeProfitPrice" class="form-control pg-form-control font-mono"
                                           value="1.0900" min="0.00001" step="any">
                                </div>

                                <div class="col-12 pt-2">
                                    <div class="p-3 rounded bg-dark border border-secondary-subtle">
                                        <div class="small fw-bold text-white mb-2">Instrument &amp; Conversion Specifications</div>
                                        <div class="row g-3">
                                            <div class="col-sm-4">
                                                <label for="rcPipSize" class="pg-form-label small">Pip Size</label>
                                                <input type="number" id="rcPipSize" class="form-control form-control-sm pg-form-control font-mono"
                                                       value="0.0001" step="any" required>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="rcContractSize" class="pg-form-label small">Std Lot Units</label>
                                                <input type="number" id="rcContractSize" class="form-control form-control-sm pg-form-control font-mono"
                                                       value="100000" step="1000" required>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="rcConversionRate" class="pg-form-label small">Quote &rarr; Acct Rate</label>
                                                <input type="number" id="rcConversionRate" class="form-control form-control-sm pg-form-control font-mono"
                                                       value="1" step="any" required>
                                            </div>
                                        </div>
                                        <div id="rcConversionHelp" class="form-text text-secondary small mt-2">
                                            Account currency (USD) matches the quote currency (USD), so conversion factor is 1.0.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                                    <button type="submit" class="btn pg-btn-primary">Calculate Position Size</button>
                                    <button type="button" id="rcResetBtn" class="btn pg-btn-outline">Reset to Example (Balance 100, Risk 1%)</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Calculator Output Column -->
                <div class="col-lg-5">
                    <div class="pg-card">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="h5 fw-bold text-white mb-0">2. Calculated Risk &amp; Sizing</h3>
                            <span id="rcOutDirectionBadge" class="badge pg-badge-emerald font-mono">LONG (BUY) SETUP</span>
                        </div>

                        <div class="pg-calc-result-box mb-3">
                            <div class="small text-secondary text-uppercase fw-semibold">Planned Monetary Risk</div>
                            <div id="rcOutMonetaryRisk" class="pg-calc-highlight" aria-live="polite">1.00 USD</div>
                            <div class="small text-secondary">Capital at risk if the stop-loss level is hit</div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="pg-calc-result-box h-100">
                                    <div class="small text-secondary">Stop-Loss Distance</div>
                                    <div id="rcOutStopDistancePips" class="h5 fw-bold text-white font-mono mb-1">20.0 pips</div>
                                    <div id="rcOutStopDistancePrice" class="small text-secondary font-mono">0.00200 (USD)</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="pg-calc-result-box h-100">
                                    <div class="small text-secondary">Estimated Standard Lots</div>
                                    <div id="rcOutStandardLots" class="h5 fw-bold text-emerald font-mono mb-1">0.05 lots</div>
                                    <div id="rcOutUnits" class="small text-secondary font-mono">5,000 EUR</div>
                                </div>
                            </div>
                        </div>

                        <div class="pg-calc-result-box mb-3 small">
                            <div class="d-flex justify-content-between py-1 border-bottom border-secondary-subtle">
                                <span class="text-secondary">Mini Lots (10,000 units):</span>
                                <strong id="rcOutMiniLots" class="font-mono text-white">0.50 mini</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom border-secondary-subtle">
                                <span class="text-secondary">Micro Lots (1,000 units):</span>
                                <strong id="rcOutMicroLots" class="font-mono text-white">5.00 micro</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom border-secondary-subtle">
                                <span class="text-secondary">Pip Value (per 1.00 Std Lot):</span>
                                <strong id="rcOutPipValue" class="font-mono text-white">10.00 USD / std lot</strong>
                            </div>
                            <div class="d-flex justify-content-between pt-1">
                                <span class="text-secondary">Planned Risk-to-Reward:</span>
                                <strong id="rcOutRiskReward" class="font-mono text-emerald">1 : 2.50 (50.0 pips target)</strong>
                            </div>
                        </div>

                        <p id="rcOutConversionNote" class="small text-secondary mb-0"></p>
                    </div>
                </div>
            </div>

            <!-- Educational Notes Required by Section 5.G -->
            <div class="row g-3 mt-3">
                <div class="col-md-6 col-lg-4">
                    <div class="pg-card p-3">
                        <h4 class="h6 text-white fw-bold mb-1">Monetary Risk vs. Position Size</h4>
                        <p class="small text-secondary mb-0">
                            <strong class="text-light">Monetary risk</strong> is the amount of account capital you are willing to lose if your stop-loss is triggered (e.g., 1% of 100 = 1). <strong class="text-light">Position size</strong> (lots/units) is adjusted based on your stop-loss distance so that a wider stop uses a smaller lot size to keep monetary risk constant.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="pg-card p-3">
                        <h4 class="h6 text-white fw-bold mb-1">Pip Value, Contract Size &amp; Conversion</h4>
                        <p class="small text-secondary mb-0">
                            A standard forex contract is typically 100,000 units of the base currency, making 1 pip (0.0001) equal to 10 units of the quote currency. When your account currency differs from the pair's quote currency, the pip value must be converted using the current exchange rate.
                        </p>
                    </div>
                </div>
                <div class="col-md-12 col-lg-4">
                    <div class="pg-card p-3">
                        <h4 class="h6 text-white fw-bold mb-1">Execution Differences &amp; Slippage</h4>
                        <p class="small text-secondary mb-0">
                            Broker contract specifications, minimum lot steps (0.01 micro lot), bid-ask spreads, commissions, and rollover swaps can cause real execution to differ from this estimate. Furthermore, <strong class="text-light">stop-loss orders do not guarantee exact execution prices</strong> during high volatility, news releases, or weekend market gaps (slippage).
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Section 5.H — Forex Education
 */
function render_education_section(array $articles, array $categories, ?string $selectedCategory = null): void
{
    ?>
    <section class="pg-section pg-section-alt" id="forex-education" aria-labelledby="education-heading">
        <div class="container">
            <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between mb-4 gap-3">
                <div>
                    <span class="pg-eyebrow">Knowledge Base &amp; Study Guides</span>
                    <h2 class="pg-section-title mb-2" id="education-heading">Forex Education</h2>
                    <p class="pg-section-lead mb-0">
                        Educational resources covering forex fundamentals, advanced price action, market structure, risk management, leverage mechanics, and trading psychology.
                    </p>
                </div>
                <div>
                    <a href="education.php" class="btn pg-btn-outline btn-sm">Browse All Topics &rarr;</a>
                </div>
            </div>

            <!-- Topic Category Pills (8 Required Initial Categories) -->
            <div class="d-flex flex-wrap gap-2 mb-4" role="navigation" aria-label="Educational Topic Categories">
                <a href="education.php"
                   class="btn btn-sm <?= ($selectedCategory === null || $selectedCategory === '') ? 'pg-btn-primary' : 'pg-btn-outline' ?>">
                    All Categories
                </a>
                <?php foreach ($categories as $catName => $catDesc): ?>
                    <a href="education.php?category=<?= urlencode($catName) ?>"
                       class="btn btn-sm <?= ($selectedCategory === $catName) ? 'pg-btn-primary' : 'pg-btn-outline' ?>">
                        <?= e($catName) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($articles)): ?>
                <!-- Professional Empty State + Topic Curriculum Overview When No Articles Are Published -->
                <div class="pg-empty-state mb-4" role="status">
                    <h3 class="h5 text-white fw-bold mb-2">
                        <?= $selectedCategory ? 'No Published Articles in "' . e($selectedCategory) . '" Yet' : 'Educational Articles Are Currently Being Prepared for Publication' ?>
                    </h3>
                    <p class="text-secondary mx-auto mb-3" style="max-width: 640px;">
                        Rather than displaying placeholder or synthetic blog posts as real publications, only genuine articles written and published through the Administrator Dashboard appear here.
                    </p>
                    <p class="small text-secondary mb-0">
                        Explore the core curriculum topic pillars below or <a href="#contact" class="text-emerald text-decoration-none">submit an educational inquiry</a>.
                    </p>
                </div>

                <!-- Display the 8 Core Educational Topic Pillars -->
                <div class="row g-3">
                    <?php foreach ($categories as $catName => $catDesc): ?>
                        <div class="col-sm-6 col-lg-3">
                            <div class="pg-card p-3">
                                <span class="badge pg-badge-emerald mb-2">Curriculum Topic</span>
                                <h4 class="h6 text-white fw-bold mb-1"><?= e($catName) ?></h4>
                                <p class="small text-secondary mb-0"><?= e($catDesc) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($articles as $article): ?>
                        <div class="col-md-6 col-lg-4">
                            <article class="pg-card d-flex flex-column justify-content-between">
                                <div>
                                    <?php if (!empty($article['featured_image'])): ?>
                                        <img src="<?= e($article['featured_image']) ?>"
                                             alt="<?= e($article['title']) ?>"
                                             class="img-fluid rounded mb-3 border border-secondary-subtle"
                                             style="max-height: 190px; width: 100%; object-fit: cover;">
                                    <?php endif; ?>

                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                        <span class="badge pg-badge-emerald"><?= e($article['category']) ?></span>
                                        <time class="small text-secondary font-mono"
                                              datetime="<?= e(date('Y-m-d', strtotime((string) ($article['published_at'] ?: $article['created_at'])))) ?>">
                                            <?= e(date('M j, Y', strtotime((string) ($article['published_at'] ?: $article['created_at'])))) ?>
                                        </time>
                                    </div>

                                    <h3 class="h5 fw-bold text-white mb-2">
                                        <a href="education.php?slug=<?= urlencode((string) $article['slug']) ?>" class="text-white text-decoration-none">
                                            <?= e($article['title']) ?>
                                        </a>
                                    </h3>

                                    <p class="small text-secondary mb-3">
                                        <?= e($article['summary']) ?>
                                    </p>
                                </div>

                                <div class="pt-3 border-top border-secondary-subtle mt-auto">
                                    <a href="education.php?slug=<?= urlencode((string) $article['slug']) ?>"
                                       class="text-emerald text-decoration-none small fw-semibold">
                                        Read Full Article &rarr;
                                    </a>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

/**
 * Section 5.I — Services and Engagement
 */
function render_services_section(array $services, array $siteSettings, bool $isLandingPage = true): void
{
    $contactHref = $isLandingPage ? '#contact' : 'contact.php';
    $statusLabels = [
        'available'    => ['label' => 'Available', 'class' => 'pg-badge-emerald'],
        'inquire_only' => ['label' => 'By Inquiry', 'class' => 'pg-badge-amber'],
        'coming_soon'  => ['label' => 'Coming Soon', 'class' => 'bg-secondary-subtle text-secondary border border-secondary-subtle'],
        'unavailable'  => ['label' => 'Currently Unavailable', 'class' => 'pg-badge-red'],
    ];
    ?>
    <section class="pg-section" id="services" aria-labelledby="services-heading">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 780px;">
                <span class="pg-eyebrow">Educational Engagement &amp; Discussions</span>
                <h2 class="pg-section-title" id="services-heading">Services &amp; Engagement</h2>
                <p class="pg-section-lead mx-auto mb-0">
                    Explore educational discussions, price action study sessions, and trading journal process reviews. No investment deposits, pooled funds, or discretionary account management are accepted.
                </p>
            </div>

            <div class="row g-4">
                <?php foreach ($services as $srv): ?>
                    <?php
                    $statusKey = (string) ($srv['availability_status'] ?? 'inquire_only');
                    $statusMeta = $statusLabels[$statusKey] ?? $statusLabels['inquire_only'];
                    $priceText = !empty($srv['price_display']) ? (string) $srv['price_display'] : 'Details upon inquiry';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="pg-card d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                    <span class="small text-secondary"><?= e($srv['category'] ?? 'Education') ?></span>
                                    <span class="badge <?= e($statusMeta['class']) ?>"><?= e($statusMeta['label']) ?></span>
                                </div>

                                <h3 class="h5 fw-bold text-white mb-2"><?= e($srv['name']) ?></h3>
                                <p class="small text-secondary mb-3"><?= e($srv['description']) ?></p>

                                <div class="p-2 rounded bg-dark border border-secondary-subtle small mb-3">
                                    <div class="text-secondary">Intended Audience:</div>
                                    <div class="text-light"><?= e($srv['intended_audience']) ?></div>
                                </div>

                                <?php if (!empty($srv['compliance_notice'])): ?>
                                    <p class="small text-secondary fst-italic mb-3">
                                        <?= e($srv['compliance_notice']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="pt-3 border-top border-secondary-subtle d-flex align-items-center justify-content-between gap-2 mt-auto">
                                <span class="small font-mono text-emerald fw-semibold"><?= e($priceText) ?></span>
                                <a href="<?= e($contactHref) ?>?interest=<?= urlencode((string) $srv['name']) ?>"
                                   data-service-interest="<?= e($srv['name']) ?>"
                                   class="btn btn-sm pg-btn-outline">
                                    Inquire &rarr;
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Regulatory & Licensing Compliance Notice (Section 5.I) -->
            <div class="pg-disclaimer-box mt-4">
                <h3 class="h6 text-white fw-bold mb-1">Regulatory &amp; Compliance Policy (Uganda &amp; International)</h3>
                <p class="small text-secondary mb-0">
                    <?= e($siteSettings['uganda_compliance_notice'] ?? 'Important Regulatory Notice: Patrick Grey does not accept client deposits, operate pooled investment schemes, provide discretionary account management, or execute automated trades on behalf of clients. Any regulated financial or investment advisory service in Uganda or other jurisdictions requires prior licensing and compliance confirmation under the Capital Markets Authority (CMA) Act or applicable local law.') ?>
                </p>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Section 5.J — Contact and Inquiry Form
 */
function render_contact_section(array $inquiryState, array $services, array $siteSettings): void
{
    $old = $inquiryState['old'] ?? [];
    $errors = $inquiryState['errors'] ?? [];
    $preselectedInterest = $old['area_of_interest'] ?? trim((string) ($_GET['interest'] ?? ''));

    $contactEmail = trim((string) ($siteSettings['contact_email'] ?? ''));
    $whatsappNumber = trim((string) ($siteSettings['whatsapp_number'] ?? ''));
    $whatsappClean = preg_replace('/[^0-9]/', '', $whatsappNumber);
    ?>
    <section class="pg-section pg-section-alt" id="contact" aria-labelledby="contact-heading">
        <div class="container">
            <div class="row g-5">
                <!-- Left Column: Inquiry Guidance & Direct Channels -->
                <div class="col-lg-5">
                    <span class="pg-eyebrow">Connect with Patrick Grey</span>
                    <h2 class="pg-section-title" id="contact-heading">Contact &amp; Inquiry Form</h2>
                    <p class="pg-section-lead mb-4">
                        Interested in forex education, discussing price action market structure, or learning more about risk management? Submit an inquiry below.
                    </p>

                    <div class="pg-card mb-3">
                        <h3 class="h6 fw-bold text-white mb-2">What to Expect</h3>
                        <ul class="pg-principle-list small mb-0">
                            <li>All inquiries are validated and stored securely in the portfolio database.</li>
                            <li>Only the contact details necessary to respond to your message are collected.</li>
                            <li>Educational and general methodology inquiries are welcomed from beginner and intermediate traders alike.</li>
                        </ul>
                    </div>

                    <?php if ($contactEmail !== '' || $whatsappClean !== ''): ?>
                        <div class="pg-card">
                            <h3 class="h6 fw-bold text-white mb-2">Configured Direct Channels</h3>
                            <?php if ($contactEmail !== '' && filter_var($contactEmail, FILTER_VALIDATE_EMAIL)): ?>
                                <p class="small mb-2">
                                    <span class="text-secondary">Email:</span>
                                    <a href="mailto:<?= e($contactEmail) ?>" class="text-emerald text-decoration-none ms-1"><?= e($contactEmail) ?></a>
                                </p>
                            <?php endif; ?>
                            <?php if ($whatsappClean !== ''): ?>
                                <p class="small mb-0">
                                    <span class="text-secondary">WhatsApp:</span>
                                    <a href="https://wa.me/<?= e($whatsappClean) ?>" target="_blank" rel="noopener noreferrer" class="text-emerald text-decoration-none ms-1">
                                        <?= e($whatsappNumber) ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Secure Contact Form -->
                <div class="col-lg-7">
                    <div class="pg-card">
                        <h3 class="h5 fw-bold text-white mb-3">Send an Inquiry</h3>

                        <?php if (!empty($inquiryState['submitted'])): ?>
                            <div class="alert <?= !empty($inquiryState['success']) ? 'alert-success' : 'alert-danger' ?>" role="alert">
                                <?= e($inquiryState['message'] ?? '') ?>
                            </div>
                        <?php endif; ?>

                        <div id="contactClientErrors" class="alert alert-danger d-none small" role="alert"></div>

                        <form id="contactInquiryForm" method="post" action="#contact" novalidate>
                            <?= csrf_field() ?>

                            <!-- Anti-Spam Honeypot Field (hidden from assistive tech and human visitors) -->
                            <div class="visually-hidden" aria-hidden="true">
                                <label for="website_url_hp">Leave this field blank</label>
                                <input type="text" name="website_url_hp" id="website_url_hp" tabindex="-1" autocomplete="off" value="">
                            </div>

                            <input type="hidden" name="contact_inquiry_submit" value="1">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="contactFullName" class="pg-form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           id="contactFullName"
                                           name="full_name"
                                           class="form-control pg-form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                                           value="<?= e($old['full_name'] ?? '') ?>"
                                           maxlength="120"
                                           placeholder="e.g., Alex Mukasa"
                                           required>
                                    <?php if (isset($errors['full_name'])): ?>
                                        <div class="invalid-feedback"><?= e($errors['full_name']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="contactEmail" class="pg-form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email"
                                           id="contactEmail"
                                           name="email"
                                           class="form-control pg-form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                           value="<?= e($old['email'] ?? '') ?>"
                                           maxlength="150"
                                           placeholder="you@example.com"
                                           required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="contactPhone" class="pg-form-label">Phone / WhatsApp Number <span class="text-secondary fw-normal">(Optional)</span></label>
                                    <input type="tel"
                                           id="contactPhone"
                                           name="phone"
                                           class="form-control pg-form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                                           value="<?= e($old['phone'] ?? '') ?>"
                                           maxlength="40"
                                           placeholder="+256 ...">
                                    <?php if (isset($errors['phone'])): ?>
                                        <div class="invalid-feedback"><?= e($errors['phone']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="contactAreaOfInterest" class="pg-form-label">Area of Interest <span class="text-danger">*</span></label>
                                    <select id="contactAreaOfInterest"
                                            name="area_of_interest"
                                            class="form-select pg-form-select <?= isset($errors['area_of_interest']) ? 'is-invalid' : '' ?>"
                                            required>
                                        <option value="">Select an area of interest...</option>
                                        <?php
                                        $standardOptions = [
                                            'Forex Education',
                                            'Advanced Price Action Methodology',
                                            'Risk Management Discussion',
                                            'Trading Mentorship Inquiry',
                                            'General Market Analysis Discussion',
                                            'Trading Journal Review',
                                            'General Inquiry',
                                        ];
                                        foreach ($services as $srv) {
                                            $sName = (string) ($srv['name'] ?? '');
                                            if ($sName !== '' && !in_array($sName, $standardOptions, true)) {
                                                $standardOptions[] = $sName;
                                            }
                                        }
                                        foreach ($standardOptions as $opt):
                                        ?>
                                            <option value="<?= e($opt) ?>" <?= ($preselectedInterest === $opt) ? 'selected' : '' ?>>
                                                <?= e($opt) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['area_of_interest'])): ?>
                                        <div class="invalid-feedback"><?= e($errors['area_of_interest']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12">
                                    <label for="contactMessage" class="pg-form-label">Your Message <span class="text-danger">*</span></label>
                                    <textarea id="contactMessage"
                                              name="message"
                                              rows="4"
                                              maxlength="3000"
                                              class="form-control pg-form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>"
                                              placeholder="Describe your current trading experience, what you would like to learn or discuss, and how Patrick can assist..."
                                              required><?= e($old['message'] ?? '') ?></textarea>
                                    <?php if (isset($errors['message'])): ?>
                                        <div class="invalid-feedback"><?= e($errors['message']) ?></div>
                                    <?php endif; ?>
                                    <div id="contactMessageCounter" class="form-text text-secondary small">0 / 3000 characters (minimum 15)</div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input <?= isset($errors['privacy_consent']) ? 'is-invalid' : '' ?>"
                                               type="checkbox"
                                               id="contactPrivacyConsent"
                                               name="privacy_consent"
                                               value="1"
                                               <?= !empty($old['privacy_consent']) ? 'checked' : '' ?>
                                               required>
                                        <label class="form-check-label small text-secondary" for="contactPrivacyConsent">
                                            I consent to having my name, contact details, and message stored in the portfolio database so Patrick Grey can review and respond to my inquiry, in accordance with the <a href="privacy.php" class="text-emerald text-decoration-none">Privacy Policy</a>. I understand that no personalized financial advice or guaranteed returns are offered. <span class="text-danger">*</span>
                                        </label>
                                        <?php if (isset($errors['privacy_consent'])): ?>
                                            <div class="invalid-feedback d-block"><?= e($errors['privacy_consent']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn pg-btn-primary w-100">
                                        Submit Inquiry Securely
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}
