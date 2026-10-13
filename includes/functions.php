<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — CORE APPLICATION FUNCTIONS & HELPERS
 * ============================================================================
 * File: includes/functions.php
 *
 * Contains reusable helper functions for:
 * 1. Output escaping (XSS prevention)
 * 2. CSRF token generation and timing-safe verification
 * 3. Session management and flash notifications
 * 4. Database data retrieval with safe fallbacks
 * 5. Genuine Trading Performance statistics calculation (zero fabrication)
 * 6. Contact inquiry validation, anti-spam protection, and PDO persistence
 * 7. Educational SVG chart illustrations for Price Action methodology
 * ============================================================================
 */

declare(strict_types=1);

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'functions.php') {
    http_response_code(403);
    exit('Direct access is not permitted.');
}

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Start a secure PHP session if one is not already active.
 */
function ensure_session_started(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $cookieName = pg_env('SESSION_COOKIE_NAME', 'pg_forex_sess');
    $lifetime = (int) pg_env('SESSION_LIFETIME', '3600');
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_name($cookieName);
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    @session_start();
}

/**
 * Escape a string for safe HTML output (mitigates Cross-Site Scripting / XSS).
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Generate or return the current session's CSRF token.
 */
function csrf_token(): string
{
    ensure_session_started();
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Return a hidden HTML input element containing the CSRF token.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verify a submitted CSRF token using timing-attack-safe comparison.
 */
function verify_csrf_token(?string $submittedToken): bool
{
    ensure_session_started();
    if (empty($submittedToken) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals((string) $_SESSION['csrf_token'], (string) $submittedToken);
}

/**
 * Store a flash message in the session for display on the next page load.
 */
function set_flash(string $type, string $message): void
{
    ensure_session_started();
    $_SESSION['flash_messages'][] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Retrieve and clear all queued flash messages from the session.
 */
function get_flash_messages(): array
{
    ensure_session_started();
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return is_array($messages) ? $messages : [];
}

/**
 * Convert a title into a clean URL-friendly slug.
 */
function slugify(string $text): string
{
    $slug = strtolower(trim($text));
    $slug = (string) preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim($slug, '-');
    return $slug !== '' ? $slug : 'item-' . substr(bin2hex(random_bytes(4)), 0, 8);
}

/**
 * Return the visitor's IP address for login throttling (never stored in inquiries).
 */
function get_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
}

/**
 * Default trader profile matching Patrick Grey's supplied information.
 */
function get_default_trader_profile(): array
{
    return [
        'id'                      => 1,
        'full_name'               => 'Patrick Grey',
        'professional_title'      => 'Intermediate Forex Trader',
        'hero_subtitle'           => 'Intermediate Forex Trader | Advanced Price Action',
        'hero_headline'           => 'Forex Trading Through Price Action, Discipline, and Risk Awareness.',
        'hero_supporting_text'    => "I'm Patrick Grey, an intermediate forex trader with three years of trading experience. My approach centers on advanced price action, market structure analysis, and disciplined trade planning. Explore my methodology, learn about my approach to risk management, and discover educational resources designed to help you understand the forex market.",
        'experience_years'        => 3,
        'experience_label'        => '3 years',
        'primary_methodology'     => 'Advanced Price Action',
        'risk_philosophy_short'   => 'Adaptive, structured risk management',
        'risk_philosophy_summary' => 'A combination of aggressive and conservative approaches, applied according to trading conditions, setup quality, account exposure, and predefined risk limits.',
        'bio_paragraph_1'         => "I'm Patrick Grey, an intermediate forex trader with three years of experience studying and navigating the foreign exchange market. My trading methodology focuses on advanced price action, market structure, and identifying potential opportunities through structured technical analysis.",
        'bio_paragraph_2'         => 'My approach to risk management combines conservative capital protection with a more aggressive approach when market conditions, trade setup quality, and predefined risk limits justify it. I believe that identifying a potential trading opportunity is only one part of trading; understanding exposure, managing losses, and maintaining discipline are equally important.',
        'bio_paragraph_3'         => 'Through this platform, I aim to share my trading approach, provide educational insights, document my trading journey transparently, and connect with individuals who want to develop a better understanding of forex trading.',
        'bio_paragraph_4'         => 'My focus is on continuous learning, disciplined execution, and responsible trading decisions rather than promises of guaranteed profits.',
        'portrait_image'          => 'assets/images/patrick-grey-placeholder.svg',
    ];
}

/**
 * Retrieve Patrick Grey's profile from MySQL, falling back to safe defaults if needed.
 */
function get_trader_profile(?PDO $pdo = null): array
{
    $pdo = $pdo ?? get_db_connection();
    $defaults = get_default_trader_profile();

    if (!$pdo) {
        return $defaults;
    }

    try {
        $stmt = $pdo->query('SELECT * FROM trader_profile ORDER BY id ASC LIMIT 1');
        $row = $stmt ? $stmt->fetch() : false;
        if (is_array($row) && !empty($row)) {
            return array_merge($defaults, $row);
        }
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error loading trader_profile: ' . $e->getMessage());
    }

    return $defaults;
}

/**
 * Default 8 Advanced Price Action techniques.
 */
function get_default_strategy_sections(): array
{
    return [
        [
            'id'                => 1,
            'slug'              => 'market-structure-analysis',
            'technique_name'    => 'Market Structure Analysis',
            'brief_explanation' => 'Examining the sequence of swing highs and swing lows across multiple timeframes to read whether the market is trending, consolidating, or transitioning.',
            'role_in_process'   => 'Establishes the foundational directional context before any individual trade setup is evaluated.',
            'visual_key'        => 'structure',
            'display_order'     => 1,
            'is_active'         => 1,
        ],
        [
            'id'                => 2,
            'slug'              => 'support-and-resistance-zones',
            'technique_name'    => 'Support and Resistance Zones',
            'brief_explanation' => 'Identifying horizontal price regions where buying or selling pressure has historically stepped in to stall, reject, or reverse price movement.',
            'role_in_process'   => 'Highlights high-interest price areas where reactions are more informative than in the middle of a range.',
            'visual_key'        => 'zones',
            'display_order'     => 2,
            'is_active'         => 1,
        ],
        [
            'id'                => 3,
            'slug'              => 'trend-identification',
            'technique_name'    => 'Trend Identification',
            'brief_explanation' => 'Assessing directional momentum across higher and intermediate timeframes to distinguish sustained trends from counter-trend pullbacks or choppy ranges.',
            'role_in_process'   => 'Helps align trade ideas with prevailing market flow or recognize when a trend is losing steam.',
            'visual_key'        => 'trend',
            'display_order'     => 3,
            'is_active'         => 1,
        ],
        [
            'id'                => 4,
            'slug'              => 'candlestick-and-price-action-patterns',
            'technique_name'    => 'Candlestick and Price Action Patterns',
            'brief_explanation' => 'Reading individual and multi-candle formations—such as rejections, engulfing structures, and indecision bars—at key structural levels.',
            'role_in_process'   => 'Provides visual evidence of buyer and seller interaction around planned areas of interest.',
            'visual_key'        => 'candlesticks',
            'display_order'     => 4,
            'is_active'         => 1,
        ],
        [
            'id'                => 5,
            'slug'              => 'breakout-and-reversal-analysis',
            'technique_name'    => 'Breakout and Reversal Analysis',
            'brief_explanation' => 'Evaluating whether price is breaking through a key structural boundary with conviction (and retesting) or failing and reversing back into range.',
            'role_in_process'   => 'Helps differentiate between genuine structural continuation and false breakouts (liquidity sweeps).',
            'visual_key'        => 'breakout',
            'display_order'     => 5,
            'is_active'         => 1,
        ],
        [
            'id'                => 6,
            'slug'              => 'entry-and-exit-planning',
            'technique_name'    => 'Entry and Exit Planning',
            'brief_explanation' => 'Defining the specific trigger conditions required to enter a position and the structural conditions under which the trade will be closed.',
            'role_in_process'   => 'Replaces impulsive execution with a written checklist before any capital is committed.',
            'visual_key'        => 'entry_exit',
            'display_order'     => 6,
            'is_active'         => 1,
        ],
        [
            'id'                => 7,
            'slug'              => 'stop-loss-and-take-profit-placement',
            'technique_name'    => 'Stop-Loss and Take-Profit Placement',
            'brief_explanation' => 'Placing stop-loss orders at the price point that invalidates the technical thesis, and setting take-profit targets near logical opposing structure.',
            'role_in_process'   => 'Ensures every trade has a predefined exit for both adverse and favorable scenarios.',
            'visual_key'        => 'sl_tp',
            'display_order'     => 7,
            'is_active'         => 1,
        ],
        [
            'id'                => 8,
            'slug'              => 'risk-to-reward-assessment',
            'technique_name'    => 'Risk-to-Reward Assessment',
            'brief_explanation' => 'Comparing the distance to the invalidation level (risk) against the realistic distance to the next structural target (potential reward).',
            'role_in_process'   => 'Filters out setups where the potential downside outweighs the realistic structural upside.',
            'visual_key'        => 'risk_reward',
            'display_order'     => 8,
            'is_active'         => 1,
        ],
    ];
}

/**
 * Retrieve Advanced Price Action strategy sections from the database.
 */
function get_strategy_sections(?PDO $pdo = null, bool $onlyActive = true): array
{
    $pdo = $pdo ?? get_db_connection();
    if (!$pdo) {
        return get_default_strategy_sections();
    }

    try {
        $sql = $onlyActive
            ? 'SELECT * FROM strategy_sections WHERE is_active = 1 ORDER BY display_order ASC, id ASC'
            : 'SELECT * FROM strategy_sections ORDER BY display_order ASC, id ASC';
        $stmt = $pdo->query($sql);
        $rows = $stmt ? $stmt->fetchAll() : [];
        if (!empty($rows)) {
            return $rows;
        }
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error loading strategy_sections: ' . $e->getMessage());
    }

    return get_default_strategy_sections();
}

/**
 * Retrieve Conservative and Aggressive Risk Management sections from the database.
 */
function get_risk_management_sections(?PDO $pdo = null): array
{
    $pdo = $pdo ?? get_db_connection();
    $defaults = [
        'conservative' => [
            'id'                          => 1,
            'approach_key'                => 'conservative',
            'title'                       => 'Conservative Risk Management',
            'subtitle'                    => 'Capital Preservation & Strict Exposure Control',
            'summary'                     => 'Applied during uncertain market conditions, transitional structure, or standard setups where protecting account equity is the primary priority.',
            'principles'                  => [
                'Prioritize capital preservation.',
                'Plan trades before entering the market.',
                'Use predefined stop-loss levels.',
                'Maintain controlled position sizes.',
                'Avoid unnecessary leverage.',
                'Limit cumulative exposure.',
                'Prefer setups that meet established trading criteria.',
                'Avoid emotional decision-making and revenge trading.',
            ],
            'configurable_risk_per_trade' => null,
            'configurable_max_drawdown'   => null,
            'configurable_max_exposure'   => null,
            'disclaimer_note'             => 'Conservative risk management seeks to slow down equity drawdowns during losing streaks, but it does not eliminate market risk or guarantee profitable outcomes.',
        ],
        'aggressive' => [
            'id'                          => 2,
            'approach_key'                => 'aggressive',
            'title'                       => 'Aggressive Risk Management',
            'subtitle'                    => 'Selective Conviction Within Predefined Risk Limits',
            'summary'                     => 'Applied selectively when market structure, setup quality, and liquidity conditions align—always bounded by advance stop-loss placement and predefined exposure ceilings.',
            'principles'                  => [
                'Consider higher-conviction opportunities when appropriate.',
                'Adapt trade management to market conditions.',
                'Evaluate volatility and potential downside before entry.',
                'Define stop-loss levels and exposure limits in advance.',
                'Avoid increasing position sizes impulsively.',
                'Never increase risk simply to recover previous losses.',
                'Recognize that aggressive exposure can amplify losses as well as gains.',
            ],
            'configurable_risk_per_trade' => null,
            'configurable_max_drawdown'   => null,
            'configurable_max_exposure'   => null,
            'disclaimer_note'             => 'Important: An aggressive approach does not mean uncontrolled trading, gambling, or ignoring risk limits. Neither conservative nor aggressive risk management guarantees profitable results, and leverage can magnify losses rapidly.',
        ],
    ];

    if (!$pdo) {
        return $defaults;
    }

    try {
        $stmt = $pdo->query('SELECT * FROM risk_management_sections ORDER BY display_order ASC, id ASC');
        $rows = $stmt ? $stmt->fetchAll() : [];
        if (!empty($rows)) {
            $result = $defaults;
            foreach ($rows as $row) {
                $key = $row['approach_key'] ?? '';
                $decoded = json_decode((string) ($row['principles_json'] ?? '[]'), true);
                $row['principles'] = is_array($decoded) && !empty($decoded)
                    ? $decoded
                    : ($defaults[$key]['principles'] ?? []);
                $result[$key] = $row;
            }
            return $result;
        }
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error loading risk_management_sections: ' . $e->getMessage());
    }

    return $defaults;
}

/**
 * Retrieve site settings as a key => value associative array.
 */
function get_site_settings(?PDO $pdo = null): array
{
    $pdo = $pdo ?? get_db_connection();
    $defaults = [
        'site_name'                     => 'Patrick Grey Forex Portfolio',
        'contact_email'                 => '',
        'whatsapp_number'               => '',
        'social_linkedin'               => '',
        'social_twitter'                => '',
        'social_youtube'                => '',
        'social_telegram'               => '',
        'performance_account_currency'  => 'USD',
        'performance_starting_balance'  => '',
        'performance_reporting_note'    => 'All performance figures displayed on this platform are calculated directly from logged database records. Unless explicitly accompanied by third-party audit documentation, records are classified as self-reported.',
        'footer_risk_disclaimer'        => 'Forex trading involves substantial risk and may not be suitable for every individual. You may lose some or all of your trading capital. Historical performance does not guarantee future results. Information on this website is provided for educational and informational purposes and should not be interpreted as a promise of returns or personalized financial advice.',
        'uganda_compliance_notice'      => 'Important Regulatory Notice: Patrick Grey does not accept client deposits, operate pooled investment schemes, provide discretionary account management, or execute automated trades on behalf of clients. Any regulated financial or investment advisory service in Uganda or other jurisdictions requires prior licensing and compliance confirmation under the Capital Markets Authority (CMA) Act or applicable local law.',
    ];

    if (!$pdo) {
        return $defaults;
    }

    try {
        $stmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');
        $rows = $stmt ? $stmt->fetchAll() : [];
        foreach ($rows as $row) {
            $k = (string) ($row['setting_key'] ?? '');
            if ($k !== '') {
                $defaults[$k] = (string) ($row['setting_value'] ?? '');
            }
        }
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error loading site_settings: ' . $e->getMessage());
    }

    return $defaults;
}

/**
 * Calculate genuine Trading Performance Dashboard metrics and Chart.js series
 * directly from actual MySQL records. Never fabricates any numbers.
 */
function get_performance_dashboard_data(?PDO $pdo = null): array
{
    $pdo = $pdo ?? get_db_connection();
    $settings = get_site_settings($pdo);
    $defaultCurrency = !empty($settings['performance_account_currency'])
        ? strtoupper(trim($settings['performance_account_currency']))
        : 'USD';

    $emptyState = [
        'has_records'                  => false,
        'empty_message'                => 'Trading performance data has not yet been published. This section will be updated when records are available.',
        'records'                      => [],
        'total_trades'                 => 0,
        'realized_trades'              => 0,
        'unrealized_trades'            => 0,
        'winning_trades'               => 0,
        'losing_trades'                => 0,
        'breakeven_trades'             => 0,
        'win_rate'                     => null,
        'net_realized_pl'              => 0.0,
        'net_unrealized_pl'            => 0.0,
        'average_win'                  => null,
        'average_loss'                 => null,
        'average_rr'                   => null,
        'max_drawdown_amount'          => null,
        'max_drawdown_percent'         => null,
        'account_currency'             => $defaultCurrency,
        'reporting_period_start'       => null,
        'reporting_period_end'         => null,
        'self_reported_count'          => 0,
        'independently_verified_count' => 0,
        'verification_label'           => 'No records published',
        'chart_equity_labels'          => [],
        'chart_equity_values'          => [],
        'chart_monthly_labels'         => [],
        'chart_monthly_values'         => [],
        'chart_win_loss_values'        => [0, 0, 0],
    ];

    if (!$pdo) {
        return $emptyState;
    }

    try {
        $stmt = $pdo->query("
            SELECT *
            FROM trading_records
            ORDER BY COALESCE(closed_at, opened_at) ASC, id ASC
        ");
        $records = $stmt ? $stmt->fetchAll() : [];
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error fetching trading_records: ' . $e->getMessage());
        return $emptyState;
    }

    if (empty($records)) {
        return $emptyState;
    }

    $totalTrades = count($records);
    $realizedTrades = 0;
    $unrealizedTrades = 0;
    $winningTrades = 0;
    $losingTrades = 0;
    $breakevenTrades = 0;

    $netRealizedPl = 0.0;
    $netUnrealizedPl = 0.0;
    $sumWins = 0.0;
    $sumLosses = 0.0;
    $rrValues = [];

    $selfReportedCount = 0;
    $verifiedCount = 0;

    $dates = [];
    $currencyCounts = [];

    // Equity curve & drawdown tracking (computed on realized trades in chronological order)
    $cumulativeRealized = 0.0;
    $peakEquity = 0.0;
    $maxDrawdownAmount = 0.0;
    $equityLabels = ['Start'];
    $equityValues = [0.0];
    $monthlyBuckets = [];

    foreach ($records as $rec) {
        $pl = (float) ($rec['profit_loss'] ?? 0.0);
        $resultType = strtolower((string) ($rec['result_type'] ?? 'realized'));
        $outcome = strtoupper((string) ($rec['trade_outcome'] ?? ''));
        $currency = strtoupper(trim((string) ($rec['account_currency'] ?? $defaultCurrency)));
        $currencyCounts[$currency] = ($currencyCounts[$currency] ?? 0) + 1;

        $verStatus = strtolower((string) ($rec['verification_status'] ?? 'self_reported'));
        $hasEvidence = !empty(trim((string) ($rec['verification_evidence_note'] ?? '')));
        if ($verStatus === 'independently_verified' && $hasEvidence) {
            $verifiedCount++;
        } else {
            $selfReportedCount++;
        }

        $dateStr = (string) ($rec['closed_at'] ?: $rec['opened_at']);
        if ($dateStr !== '') {
            $dates[] = $dateStr;
        }

        if ($rec['risk_reward_ratio'] !== null && $rec['risk_reward_ratio'] !== '' && (float) $rec['risk_reward_ratio'] > 0) {
            $rrValues[] = (float) $rec['risk_reward_ratio'];
        }

        if ($resultType === 'unrealized') {
            $unrealizedTrades++;
            $netUnrealizedPl += $pl;
            continue;
        }

        // Realized trade calculations
        $realizedTrades++;
        $netRealizedPl += $pl;

        if ($outcome === 'WIN' || ($outcome === '' && $pl > 0)) {
            $winningTrades++;
            $sumWins += $pl;
        } elseif ($outcome === 'LOSS' || ($outcome === '' && $pl < 0)) {
            $losingTrades++;
            $sumLosses += abs($pl);
        } else {
            $breakevenTrades++;
        }

        $cumulativeRealized += $pl;
        if ($cumulativeRealized > $peakEquity) {
            $peakEquity = $cumulativeRealized;
        }
        $currentDrawdown = $peakEquity - $cumulativeRealized;
        if ($currentDrawdown > $maxDrawdownAmount) {
            $maxDrawdownAmount = $currentDrawdown;
        }

        $shortDate = $dateStr !== '' ? date('Y-m-d', strtotime($dateStr)) : ('Trade #' . $rec['id']);
        $equityLabels[] = $shortDate . ' (' . $rec['instrument'] . ')';
        $equityValues[] = round($cumulativeRealized, 2);

        $monthKey = $dateStr !== '' ? date('Y-m', strtotime($dateStr)) : 'Unspecified';
        $monthlyBuckets[$monthKey] = round(($monthlyBuckets[$monthKey] ?? 0.0) + $pl, 2);
    }

    arsort($currencyCounts);
    $primaryCurrency = (string) (array_key_first($currencyCounts) ?? $defaultCurrency);

    $winRate = $realizedTrades > 0
        ? round(($winningTrades / $realizedTrades) * 100, 1)
        : null;

    $averageWin = $winningTrades > 0 ? round($sumWins / $winningTrades, 2) : null;
    $averageLoss = $losingTrades > 0 ? round($sumLosses / $losingTrades, 2) : null;
    $averageRr = !empty($rrValues) ? round(array_sum($rrValues) / count($rrValues), 2) : null;

    // Calculate max drawdown percentage only if a reference starting balance is configured
    $startingBalanceRaw = trim((string) ($settings['performance_starting_balance'] ?? ''));
    $maxDrawdownPercent = null;
    if ($startingBalanceRaw !== '' && is_numeric($startingBalanceRaw) && (float) $startingBalanceRaw > 0 && $realizedTrades > 0) {
        $startingBal = (float) $startingBalanceRaw;
        $peakTotalEquity = $startingBal + $peakEquity;
        if ($peakTotalEquity > 0) {
            $maxDrawdownPercent = round(($maxDrawdownAmount / $peakTotalEquity) * 100, 2);
        }
    }

    sort($dates);
    $periodStart = !empty($dates) ? date('M j, Y', strtotime($dates[0])) : null;
    $periodEnd = !empty($dates) ? date('M j, Y', strtotime($dates[count($dates) - 1])) : null;

    if ($verifiedCount > 0 && $selfReportedCount === 0) {
        $verificationLabel = 'Independently Verified Records (Evidence Logged)';
    } elseif ($verifiedCount > 0 && $selfReportedCount > 0) {
        $verificationLabel = "Mixed: {$selfReportedCount} Self-Reported / {$verifiedCount} Independently Verified";
    } else {
        $verificationLabel = 'Self-Reported Trading Log (Not Independently Audited)';
    }

    ksort($monthlyBuckets);

    // Reverse records for the display table so newest appear first
    $recordsDescending = array_reverse($records);

    return [
        'has_records'                  => true,
        'empty_message'                => '',
        'records'                      => $recordsDescending,
        'total_trades'                 => $totalTrades,
        'realized_trades'              => $realizedTrades,
        'unrealized_trades'            => $unrealizedTrades,
        'winning_trades'               => $winningTrades,
        'losing_trades'                => $losingTrades,
        'breakeven_trades'             => $breakevenTrades,
        'win_rate'                     => $winRate,
        'net_realized_pl'              => round($netRealizedPl, 2),
        'net_unrealized_pl'            => round($netUnrealizedPl, 2),
        'average_win'                  => $averageWin,
        'average_loss'                 => $averageLoss,
        'average_rr'                   => $averageRr,
        'max_drawdown_amount'          => $realizedTrades > 0 ? round($maxDrawdownAmount, 2) : null,
        'max_drawdown_percent'         => $maxDrawdownPercent,
        'account_currency'             => $primaryCurrency,
        'reporting_period_start'       => $periodStart,
        'reporting_period_end'         => $periodEnd,
        'self_reported_count'          => $selfReportedCount,
        'independently_verified_count' => $verifiedCount,
        'verification_label'           => $verificationLabel,
        'chart_equity_labels'          => $equityLabels,
        'chart_equity_values'          => $equityValues,
        'chart_monthly_labels'         => array_keys($monthlyBuckets),
        'chart_monthly_values'         => array_values($monthlyBuckets),
        'chart_win_loss_values'        => [$winningTrades, $losingTrades, $breakevenTrades],
    ];
}

/**
 * Return the 8 initial educational topic categories specified in Section 5.H.
 */
function get_education_categories(): array
{
    return [
        'Forex fundamentals'      => 'Core mechanics of currency pairs, pips, lots, spreads, and market sessions.',
        'Advanced price action'   => 'Reading raw price movement, candle rejections, and structural reactions without indicator clutter.',
        'Market structure'        => 'Swing highs, swing lows, trend continuation, consolidation, and structural breaks.',
        'Technical analysis'      => 'Multi-timeframe chart markup, horizontal support/resistance zones, and confluence.',
        'Risk management'         => 'Position sizing, monetary risk calculation, stop-loss placement, and exposure limits.',
        'Trading psychology'      => 'Emotional regulation, patience, avoiding revenge trading, and following a written plan.',
        'Leverage and margin'     => 'How margin requirements work and why excessive leverage magnifies losses as well as gains.',
        'Common trading mistakes' => 'Overtrading, moving stop-losses, impulsive sizing, and trading without a defined edge.',
    ];
}

/**
 * Retrieve published educational articles from MySQL.
 */
function get_published_articles(?PDO $pdo = null, ?string $category = null, ?int $limit = null): array
{
    $pdo = $pdo ?? get_db_connection();
    if (!$pdo) {
        return [];
    }

    try {
        $sql = 'SELECT * FROM articles WHERE is_published = 1';
        $params = [];

        if ($category !== null && trim($category) !== '') {
            $sql .= ' AND category = :category';
            $params[':category'] = trim($category);
        }

        $sql .= ' ORDER BY COALESCE(published_at, created_at) DESC, id DESC';

        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error loading published articles: ' . $e->getMessage());
        return [];
    }
}

/**
 * Retrieve a single published educational article by its URL slug.
 */
function get_article_by_slug(?PDO $pdo, string $slug): ?array
{
    $pdo = $pdo ?? get_db_connection();
    if (!$pdo || trim($slug) === '') {
        return null;
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM articles WHERE slug = :slug AND is_published = 1 LIMIT 1');
        $stmt->execute([':slug' => trim($slug)]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error loading article by slug: ' . $e->getMessage());
        return null;
    }
}

/**
 * Default configurable services (never invents prices or client counts).
 */
function get_default_services(): array
{
    return [
        [
            'id'                        => 1,
            'name'                      => 'Forex Education & Price Action Foundations',
            'slug'                      => 'forex-education',
            'category'                  => 'Forex education',
            'description'               => 'Structured educational sessions covering foreign exchange market mechanics, candlestick reading, market structure, and risk calculation fundamentals.',
            'intended_audience'         => 'Beginner and developing traders seeking structured forex education.',
            'price_display'             => null,
            'availability_status'       => 'inquire_only',
            'requires_regulatory_check' => 0,
            'compliance_notice'         => 'Educational content only. Does not constitute personalized investment advice or trade signals.',
            'display_order'             => 1,
            'is_active'                 => 1,
        ],
        [
            'id'                        => 2,
            'name'                      => 'Trading Mentorship & Study Guidance',
            'slug'                      => 'trading-mentorship',
            'category'                  => 'Trading mentorship',
            'description'               => 'One-on-one or small-group educational discussions focused on learning advanced price action concepts, chart markup routines, and disciplined trade planning.',
            'intended_audience'         => 'Developing traders looking to refine their technical analysis study workflow.',
            'price_display'             => null,
            'availability_status'       => 'inquire_only',
            'requires_regulatory_check' => 1,
            'compliance_notice'         => 'Subject to scope confirmation. Any service beyond general financial education must be reviewed against applicable Capital Markets Authority (Uganda) and local licensing requirements before engagement.',
            'display_order'             => 2,
            'is_active'                 => 1,
        ],
        [
            'id'                        => 3,
            'name'                      => 'General Market Structure Analysis Discussions',
            'slug'                      => 'general-market-analysis',
            'category'                  => 'General market analysis',
            'description'               => 'Educational walkthroughs of historical and current chart structure, support and resistance zones, and multi-timeframe trend context.',
            'intended_audience'         => 'Traders interested in studying technical price action frameworks.',
            'price_display'             => null,
            'availability_status'       => 'inquire_only',
            'requires_regulatory_check' => 1,
            'compliance_notice'         => 'Presented strictly as general technical commentary and education—never as individualized buy/sell recommendations or regulated advisory services.',
            'display_order'             => 3,
            'is_active'                 => 1,
        ],
        [
            'id'                        => 4,
            'name'                      => 'Trading Journal & Execution Reviews',
            'slug'                      => 'trading-journal-reviews',
            'category'                  => 'Trading journal reviews',
            'description'               => "Collaborative review of a trader's own recorded trade journal to evaluate adherence to predefined rules, stop-loss placement, and risk-to-reward consistency.",
            'intended_audience'         => 'Self-directed traders who keep a trade log and want process feedback.',
            'price_display'             => null,
            'availability_status'       => 'inquire_only',
            'requires_regulatory_check' => 0,
            'compliance_notice'         => 'Focuses on process discipline and risk math review; does not manage client funds or direct trade execution.',
            'display_order'             => 4,
            'is_active'                 => 1,
        ],
        [
            'id'                        => 5,
            'name'                      => 'Price Action & Trading Discipline Discussions',
            'slug'                      => 'price-action-and-discipline',
            'category'                  => 'Discussions about price action and trading discipline',
            'description'               => 'Focused conversations on trading psychology, avoiding revenge trading, managing losing streaks, and balancing conservative and aggressive risk rules.',
            'intended_audience'         => 'Private individuals and beginner-to-intermediate traders working on execution discipline.',
            'price_display'             => null,
            'availability_status'       => 'available',
            'requires_regulatory_check' => 0,
            'compliance_notice'         => 'Educational peer and mentor discussion; no capital deposits, pooled accounts, or automated trading are accepted.',
            'display_order'             => 5,
            'is_active'                 => 1,
        ],
    ];
}

/**
 * Retrieve services from MySQL, falling back to safe defaults if offline.
 */
function get_services(?PDO $pdo = null, bool $onlyActive = true): array
{
    $pdo = $pdo ?? get_db_connection();
    if (!$pdo) {
        return get_default_services();
    }

    try {
        $sql = $onlyActive
            ? 'SELECT * FROM services WHERE is_active = 1 ORDER BY display_order ASC, id ASC'
            : 'SELECT * FROM services ORDER BY display_order ASC, id ASC';
        $stmt = $pdo->query($sql);
        $rows = $stmt ? $stmt->fetchAll() : [];
        if (!empty($rows)) {
            return $rows;
        }
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error loading services: ' . $e->getMessage());
    }

    return get_default_services();
}

/**
 * Validate and store a visitor contact inquiry using PDO prepared statements.
 * Implements CSRF verification, honeypot anti-spam check, rate limiting,
 * and duplicate-submission prevention.
 */
function handle_contact_inquiry_submission(?PDO $pdo, array $post): array
{
    ensure_session_started();
    $pdo = $pdo ?? get_db_connection();

    $old = [
        'full_name'        => trim((string) ($post['full_name'] ?? '')),
        'email'            => trim((string) ($post['email'] ?? '')),
        'phone'            => trim((string) ($post['phone'] ?? '')),
        'area_of_interest' => trim((string) ($post['area_of_interest'] ?? '')),
        'message'          => trim((string) ($post['message'] ?? '')),
        'privacy_consent'  => !empty($post['privacy_consent']) ? '1' : '',
    ];

    $errors = [];

    // 1. Verify CSRF token
    if (!verify_csrf_token($post['csrf_token'] ?? null)) {
        return [
            'success' => false,
            'message' => 'Your security session token expired or was invalid. Please refresh the page and submit your inquiry again.',
            'errors'  => ['csrf' => 'Invalid CSRF token.'],
            'old'     => $old,
        ];
    }

    // 2. Anti-spam honeypot field check (bots fill hidden fields; humans leave them blank)
    if (!empty($post['website_url_hp'])) {
        return [
            'success' => true,
            'message' => 'Thank you. Your inquiry has been received.',
            'errors'  => [],
            'old'     => [],
        ];
    }

    // 3. Session rate-limiting to prevent rapid abusive submissions
    $rateLimitSeconds = (int) pg_env('INQUIRY_RATE_LIMIT_SECONDS', '30');
    $lastSubmitTime = (int) ($_SESSION['last_inquiry_submit_at'] ?? 0);
    if ($lastSubmitTime > 0 && (time() - $lastSubmitTime) < $rateLimitSeconds) {
        $waitSeconds = $rateLimitSeconds - (time() - $lastSubmitTime);
        return [
            'success' => false,
            'message' => "Please wait {$waitSeconds} seconds before submitting another inquiry.",
            'errors'  => ['rate_limit' => 'Submission rate limit reached.'],
            'old'     => $old,
        ];
    }

    // 4. Server-side field validation
    if ($old['full_name'] === '' || mb_strlen($old['full_name']) < 2 || mb_strlen($old['full_name']) > 120) {
        $errors['full_name'] = 'Please enter your full name (between 2 and 120 characters).';
    }

    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($old['phone'] !== '' && (!preg_match('/^[0-9+\-\s()]{6,40}$/', $old['phone']))) {
        $errors['phone'] = 'Please enter a valid phone or WhatsApp number (digits, +, spaces, or hyphens).';
    }

    $allowedInterests = [
        'Forex Education',
        'Advanced Price Action Methodology',
        'Risk Management Discussion',
        'Trading Mentorship Inquiry',
        'General Market Analysis Discussion',
        'Trading Journal Review',
        'General Inquiry',
    ];
    if ($old['area_of_interest'] === '' || mb_strlen($old['area_of_interest']) > 120) {
        $errors['area_of_interest'] = 'Please select an area of interest.';
    } elseif (!in_array($old['area_of_interest'], $allowedInterests, true)) {
        // Allow service names from database as well
        $services = get_services($pdo, false);
        $serviceNames = array_map( static fn(array $s): string => (string) $s['name'], $services);
        if (!in_array($old['area_of_interest'], $serviceNames, true)) {
            $errors['area_of_interest'] = 'Please select a valid area of interest from the list.';
        }
    }

    if ($old['message'] === '' || mb_strlen($old['message']) < 15 || mb_strlen($old['message']) > 3000) {
        $errors['message'] = 'Please enter a clear message (between 15 and 3,000 characters).';
    }

    if ($old['privacy_consent'] !== '1') {
        $errors['privacy_consent'] = 'You must consent to the privacy notice so we can store and respond to your inquiry.';
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => 'Please review the highlighted fields below and correct any errors.',
            'errors'  => $errors,
            'old'     => $old,
        ];
    }

    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'The database is currently unavailable. Please try again once the database service is running.',
            'errors'  => ['database' => 'Database connection unavailable.'],
            'old'     => $old,
        ];
    }

    // 5. Duplicate submission check using SHA-256 content signature
    $submissionHash = hash('sha256', strtolower($old['email']) . '|' . strtolower($old['area_of_interest']) . '|' . strtolower($old['message']));

    try {
        $dupStmt = $pdo->prepare('SELECT id FROM inquiries WHERE submission_hash = :hash LIMIT 1');
        $dupStmt->execute([':hash' => $submissionHash]);
        if ($dupStmt->fetch()) {
            return [
                'success' => false,
                'message' => 'An identical inquiry has already been recorded. Patrick Grey will review your message from the administrator dashboard.',
                'errors'  => ['duplicate' => 'Duplicate inquiry detected.'],
                'old'     => $old,
            ];
        }

        // 6. Store inquiry using PDO prepared statement (records only required contact details)
        $insertStmt = $pdo->prepare("
            INSERT INTO inquiries (
                full_name, email, phone, area_of_interest, message,
                consent_given, inquiry_status, submission_hash
            ) VALUES (
                :full_name, :email, :phone, :area_of_interest, :message,
                1, 'new', :submission_hash
            )
        ");

        $insertStmt->execute([
            ':full_name'        => $old['full_name'],
            ':email'            => $old['email'],
            ':phone'            => $old['phone'] !== '' ? $old['phone'] : null,
            ':area_of_interest' => $old['area_of_interest'],
            ':message'          => $old['message'],
            ':submission_hash'  => $submissionHash,
        ]);

        $_SESSION['last_inquiry_submit_at'] = time();

        return [
            'success' => true,
            'message' => 'Thank you, ' . $old['full_name'] . '. Your inquiry has been securely recorded in the database for review via the administrator dashboard.',
            'errors'  => [],
            'old'     => [
                'full_name'        => '',
                'email'            => '',
                'phone'            => '',
                'area_of_interest' => '',
                'message'          => '',
                'privacy_consent'  => '',
            ],
        ];
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Error saving inquiry: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'An unexpected error occurred while saving your inquiry. Please try again later.',
            'errors'  => ['server' => 'Could not save inquiry.'],
            'old'     => $old,
        ];
    }
}

/**
 * Validate and store an uploaded image file safely in assets/images/uploads/.
 */
function handle_safe_image_upload(array $file): array
{
    if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'path' => null, 'error' => 'No file uploaded.'];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => null, 'error' => 'File upload failed with error code ' . (int) $file['error'] . '.'];
    }

    $maxBytes = 2 * 1024 * 1024; // 2 MB limit
    if (($file['size'] ?? 0) > $maxBytes || ($file['size'] ?? 0) <= 0) {
        return ['success' => false, 'path' => null, 'error' => 'Uploaded image must be smaller than 2 MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedMimes[$mime])) {
        return ['success' => false, 'path' => null, 'error' => 'Only JPG, PNG, and WEBP image files are permitted.'];
    }

    $ext = $allowedMimes[$mime];
    $safeFilename = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $uploadDir = dirname(__DIR__) . '/assets/images/uploads';

    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . '/' . $safeFilename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'path' => null, 'error' => 'Failed to move uploaded file to destination directory.'];
    }

    return [
        'success' => true,
        'path'    => 'assets/images/uploads/' . $safeFilename,
        'error'   => null,
    ];
}

/**
 * Render an educational SVG chart illustration for each Advanced Price Action technique.
 */
function render_methodology_svg(string $visualKey): string
{
    switch ($visualKey) {
        case 'structure':
            return '
            <svg viewBox="0 0 280 110" class="methodology-chart-svg" role="img" aria-label="Market structure swing highs and swing lows diagram">
                <rect width="280" height="110" rx="8" fill="#0b1325"/>
                <line x1="16" y1="28" x2="264" y2="28" stroke="#1e293b" stroke-dasharray="3 3"/>
                <line x1="16" y1="55" x2="264" y2="55" stroke="#1e293b" stroke-dasharray="3 3"/>
                <line x1="16" y1="82" x2="264" y2="82" stroke="#1e293b" stroke-dasharray="3 3"/>
                <polyline fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                    points="22,88 68,52 104,72 162,32 198,54 254,18"/>
                <circle cx="68" cy="52" r="4" fill="#10b981"/>
                <circle cx="104" cy="72" r="4" fill="#38bdf8"/>
                <circle cx="162" cy="32" r="4" fill="#10b981"/>
                <circle cx="198" cy="54" r="4" fill="#38bdf8"/>
                <circle cx="254" cy="18" r="4" fill="#10b981"/>
                <text x="58" y="42" fill="#94a3b8" font-size="9" font-family="sans-serif">HH</text>
                <text x="96" y="88" fill="#94a3b8" font-size="9" font-family="sans-serif">HL</text>
                <text x="152" y="24" fill="#10b981" font-size="9" font-weight="bold" font-family="sans-serif">BOS / HH</text>
                <text x="190" y="70" fill="#94a3b8" font-size="9" font-family="sans-serif">HL</text>
            </svg>';

        case 'zones':
            return '
            <svg viewBox="0 0 280 110" class="methodology-chart-svg" role="img" aria-label="Support and resistance zones diagram">
                <rect width="280" height="110" rx="8" fill="#0b1325"/>
                <rect x="16" y="18" width="248" height="14" rx="3" fill="rgba(239,68,68,0.16)" stroke="rgba(239,68,68,0.45)"/>
                <text x="24" y="28" fill="#f87171" font-size="8.5" font-family="sans-serif">RESISTANCE ZONE</text>
                <rect x="16" y="78" width="248" height="14" rx="3" fill="rgba(16,185,129,0.16)" stroke="rgba(16,185,129,0.45)"/>
                <text x="24" y="88" fill="#34d399" font-size="8.5" font-family="sans-serif">SUPPORT ZONE</text>
                <polyline fill="none" stroke="#e2e8f0" stroke-width="2" stroke-linejoin="round"
                    points="24,79 65,31 110,79 158,31 205,78 252,44"/>
                <circle cx="65" cy="31" r="3.5" fill="#ef4444"/>
                <circle cx="110" cy="79" r="3.5" fill="#10b981"/>
                <circle cx="158" cy="31" r="3.5" fill="#ef4444"/>
                <circle cx="205" cy="78" r="3.5" fill="#10b981"/>
            </svg>';

        case 'trend':
            return '
            <svg viewBox="0 0 280 110" class="methodology-chart-svg" role="img" aria-label="Trend identification channel diagram">
                <rect width="280" height="110" rx="8" fill="#0b1325"/>
                <line x1="24" y1="92" x2="256" y2="36" stroke="#10b981" stroke-width="1.5" stroke-dasharray="4 4"/>
                <line x1="24" y1="68" x2="256" y2="12" stroke="#38bdf8" stroke-width="1.2" stroke-opacity="0.5"/>
                <polyline fill="none" stroke="#10b981" stroke-width="2.3" stroke-linejoin="round"
                    points="26,86 58,60 86,74 128,44 156,58 202,28 228,42 256,18"/>
                <text x="24" y="24" fill="#94a3b8" font-size="9" font-family="sans-serif">HTF Directional Bias</text>
            </svg>';

        case 'candlesticks':
            return '
            <svg viewBox="0 0 280 110" class="methodology-chart-svg" role="img" aria-label="Candlestick rejection and engulfing pattern diagram">
                <rect width="280" height="110" rx="8" fill="#0b1325"/>
                <line x1="16" y1="84" x2="264" y2="84" stroke="#10b981" stroke-opacity="0.5" stroke-dasharray="4 3"/>
                <!-- Bearish candle -->
                <line x1="65" y1="30" x2="65" y2="75" stroke="#ef4444" stroke-width="1.8"/>
                <rect x="58" y="38" width="14" height="28" rx="1.5" fill="#ef4444"/>
                <!-- Pin bar rejection at support -->
                <line x1="115" y1="44" x2="115" y2="96" stroke="#10b981" stroke-width="1.8"/>
                <rect x="108" y="48" width="14" height="12" rx="1.5" fill="#10b981"/>
                <!-- Bullish engulfing candle -->
                <line x1="165" y1="24" x2="165" y2="78" stroke="#10b981" stroke-width="1.8"/>
                <rect x="157" y="30" width="16" height="40" rx="1.5" fill="#10b981"/>
                <!-- Follow-through candle -->
                <line x1="215" y1="16" x2="215" y2="54" stroke="#10b981" stroke-width="1.8"/>
                <rect x="208" y="20" width="14" height="26" rx="1.5" fill="#10b981"/>
                <text x="92" y="104" fill="#94a3b8" font-size="8.5" font-family="sans-serif">Rejection Wick + Bullish Engulfing</text>
            </svg>';

        case 'breakout':
            return '
            <svg viewBox="0 0 280 110" class="methodology-chart-svg" role="img" aria-label="Breakout and retest structural diagram">
                <rect width="280" height="110" rx="8" fill="#0b1325"/>
                <rect x="16" y="52" width="248" height="10" fill="rgba(56,189,248,0.15)" stroke="rgba(56,189,248,0.4)"/>
                <polyline fill="none" stroke="#e2e8f0" stroke-width="2.2" stroke-linejoin="round"
                    points="22,86 64,56 96,76 134,55 172,24 204,53 254,18"/>
                <circle cx="204" cy="53" r="4.5" fill="#10b981"/>
                <text x="184" y="72" fill="#10b981" font-size="9" font-weight="bold" font-family="sans-serif">Break &amp; Retest</text>
            </svg>';

        case 'entry_exit':
            return '
            <svg viewBox="0 0 280 110" class="methodology-chart-svg" role="img" aria-label="Entry and exit checklist diagram">
                <rect width="280" height="110" rx="8" fill="#0b1325"/>
                <line x1="24" y1="68" x2="256" y2="68" stroke="#38bdf8" stroke-width="1.4" stroke-dasharray="4 3"/>
                <line x1="24" y1="24" x2="256" y2="24" stroke="#10b981" stroke-width="1.4" stroke-dasharray="4 3"/>
                <polyline fill="none" stroke="#94a3b8" stroke-width="2"
                    points="26,88 76,48 116,68 186,24 236,30"/>
                <circle cx="116" cy="68" r="4.5" fill="#38bdf8"/>
                <circle cx="186" cy="24" r="4.5" fill="#10b981"/>
                <text x="95" y="84" fill="#38bdf8" font-size="9" font-family="sans-serif">Planned Entry</text>
                <text x="165" y="17" fill="#10b981" font-size="9" font-family="sans-serif">Planned Exit</text>
            </svg>';

        case 'sl_tp':
            return '
            <svg viewBox="0 0 280 110" class="methodology-chart-svg" role="img" aria-label="Stop-loss and take-profit placement diagram">
                <rect width="280" height="110" rx="8" fill="#0b1325"/>
                <rect x="30" y="16" width="220" height="44" fill="rgba(16,185,129,0.12)" stroke="#10b981" stroke-width="1"/>
                <rect x="30" y="60" width="220" height="30" fill="rgba(239,68,68,0.14)" stroke="#ef4444" stroke-width="1"/>
                <line x1="30" y1="60" x2="250" y2="60" stroke="#f8fafc" stroke-width="1.5"/>
                <text x="38" y="30" fill="#34d399" font-size="9" font-weight="bold" font-family="sans-serif">TAKE-PROFIT TARGET (Next Structure)</text>
                <text x="38" y="56" fill="#f8fafc" font-size="8.5" font-family="sans-serif">ENTRY TRIGGER LEVEL</text>
                <text x="38" y="82" fill="#f87171" font-size="9" font-weight="bold" font-family="sans-serif">STOP-LOSS (Technical Invalidation)</text>
            </svg>';

        case 'risk_reward':
        default:
            return '
            <svg viewBox="0 0 280 110" class="methodology-chart-svg" role="img" aria-label="Risk to reward assessment diagram">
                <rect width="280" height="110" rx="8" fill="#0b1325"/>
                <rect x="44" y="64" width="76" height="28" rx="4" fill="rgba(239,68,68,0.22)" stroke="#ef4444"/>
                <text x="56" y="82" fill="#fca5a5" font-size="9.5" font-weight="bold" font-family="sans-serif">1R Risk</text>
                <rect x="148" y="20" width="88" height="72" rx="4" fill="rgba(16,185,129,0.2)" stroke="#10b981"/>
                <text x="160" y="60" fill="#6ee7b7" font-size="9.5" font-weight="bold" font-family="sans-serif">Potential Reward</text>
                <text x="160" y="74" fill="#94a3b8" font-size="8.5" font-family="sans-serif">(Asymmetric Setup)</text>
            </svg>';
    }
}
