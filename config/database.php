<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — DATABASE CONFIGURATION & PDO CONNECTION
 * ============================================================================
 * File: config/database.php
 *
 * Beginner-Friendly Explanation (CS Year 2, Semester 1):
 * - We use PHP Data Objects (PDO) to connect to MySQL instead of older
 *   functions because PDO supports true prepared statements, which separate
 *   SQL structure from user-supplied data and prevent SQL Injection attacks.
 * - Credentials are read from environment variables (or a `.env` file stored
 *   outside the web root when possible) so real passwords are never hardcoded
 *   into source code committed to Git.
 * ============================================================================
 */

declare(strict_types=1);

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'database.php') {
    http_response_code(403);
    exit('Direct access is not permitted.');
}

/**
 * Load key=value pairs from a .env file into $_ENV and putenv() if not already set.
 */
function pg_load_env_file(string $filePath): void
{
    if (!is_file($filePath) || !is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }
        $parts = explode('=', $trimmed, 2);
        if (count($parts) !== 2) {
            continue;
        }
        $key = trim($parts[0]);
        $value = trim($parts[1]);

        // Strip optional surrounding quotes
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        if ($key !== '' && getenv($key) === false && !isset($_ENV[$key])) {
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

// 1. Prefer a .env file stored one level outside the public web root for security,
//    then fall back to the project root .env file for local XAMPP development.
$projectRoot = dirname(__DIR__);
pg_load_env_file(dirname($projectRoot) . '/.env.forex_portfolio');
pg_load_env_file($projectRoot . '/.env');

/**
 * Retrieve an environment configuration value with a safe default.
 */
function pg_env(string $key, string $default = ''): string
{
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return (string) $val;
    }
    return $default;
}

// Configure timezone and safe error reporting (never expose stack traces or DB credentials)
$timezone = pg_env('APP_TIMEZONE', 'Africa/Kampala');
@date_default_timezone_set($timezone);

$isDebug = strtolower(pg_env('APP_DEBUG', 'false')) === 'true';
if (!$isDebug) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}
error_reporting(E_ALL);

/**
 * Initialize a local SQLite database schema for automated testing or local fallback
 * when explicitly enabled via DB_DRIVER=sqlite or DB_SQLITE_FALLBACK=true.
 */
function pg_init_sqlite_schema(PDO $pdo): void
{
    $pdo->exec('PRAGMA foreign_keys = ON;');

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(60) NOT NULL UNIQUE,
            email VARCHAR(150) NOT NULL UNIQUE,
            full_name VARCHAR(100) NOT NULL DEFAULT 'Patrick Grey',
            password_hash VARCHAR(255) NOT NULL,
            is_active INTEGER NOT NULL DEFAULT 1,
            last_login_at DATETIME NULL DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS login_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username_or_email VARCHAR(150) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            was_successful INTEGER NOT NULL DEFAULT 0,
            attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS trader_profile (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name VARCHAR(100) NOT NULL DEFAULT 'Patrick Grey',
            professional_title VARCHAR(150) NOT NULL DEFAULT 'Intermediate Forex Trader',
            hero_subtitle VARCHAR(200) NOT NULL DEFAULT 'Intermediate Forex Trader | Advanced Price Action',
            hero_headline VARCHAR(255) NOT NULL DEFAULT 'Forex Trading Through Price Action, Discipline, and Risk Awareness.',
            hero_supporting_text TEXT NOT NULL,
            experience_years INTEGER NOT NULL DEFAULT 3,
            experience_label VARCHAR(80) NOT NULL DEFAULT '3 years',
            primary_methodology VARCHAR(120) NOT NULL DEFAULT 'Advanced Price Action',
            risk_philosophy_short VARCHAR(150) NOT NULL DEFAULT 'Adaptive, structured risk management',
            risk_philosophy_summary TEXT NOT NULL,
            bio_paragraph_1 TEXT NOT NULL,
            bio_paragraph_2 TEXT NOT NULL,
            bio_paragraph_3 TEXT NOT NULL,
            bio_paragraph_4 TEXT NOT NULL,
            portrait_image VARCHAR(255) NOT NULL DEFAULT 'assets/images/patrick-grey-placeholder.svg',
            updated_by INTEGER NULL DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS strategy_sections (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug VARCHAR(80) NOT NULL UNIQUE,
            technique_name VARCHAR(120) NOT NULL,
            brief_explanation TEXT NOT NULL,
            role_in_process TEXT NOT NULL,
            visual_key VARCHAR(60) NOT NULL DEFAULT 'structure',
            display_order INTEGER NOT NULL DEFAULT 1,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS risk_management_sections (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            approach_key VARCHAR(40) NOT NULL UNIQUE,
            title VARCHAR(120) NOT NULL,
            subtitle VARCHAR(200) NOT NULL,
            summary TEXT NOT NULL,
            principles_json TEXT NOT NULL,
            configurable_risk_per_trade VARCHAR(80) NULL DEFAULT NULL,
            configurable_max_drawdown VARCHAR(80) NULL DEFAULT NULL,
            configurable_max_exposure VARCHAR(80) NULL DEFAULT NULL,
            disclaimer_note TEXT NOT NULL,
            display_order INTEGER NOT NULL DEFAULT 1,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS trading_records (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            instrument VARCHAR(20) NOT NULL,
            trade_direction VARCHAR(10) NOT NULL,
            entry_price DECIMAL(14, 6) NOT NULL,
            exit_price DECIMAL(14, 6) NULL DEFAULT NULL,
            stop_loss_price DECIMAL(14, 6) NULL DEFAULT NULL,
            take_profit_price DECIMAL(14, 6) NULL DEFAULT NULL,
            position_size DECIMAL(10, 4) NOT NULL,
            profit_loss DECIMAL(14, 2) NOT NULL,
            account_currency VARCHAR(10) NOT NULL DEFAULT 'USD',
            result_type VARCHAR(20) NOT NULL DEFAULT 'realized',
            trade_outcome VARCHAR(20) NOT NULL,
            risk_reward_ratio DECIMAL(6, 2) NULL DEFAULT NULL,
            setup_type VARCHAR(100) NULL DEFAULT NULL,
            opened_at DATETIME NOT NULL,
            closed_at DATETIME NULL DEFAULT NULL,
            verification_status VARCHAR(30) NOT NULL DEFAULT 'self_reported',
            verification_evidence_note VARCHAR(255) NULL DEFAULT NULL,
            notes TEXT NULL DEFAULT NULL,
            created_by INTEGER NULL DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS articles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title VARCHAR(200) NOT NULL,
            slug VARCHAR(220) NOT NULL UNIQUE,
            summary TEXT NOT NULL,
            category VARCHAR(80) NOT NULL,
            content TEXT NOT NULL,
            featured_image VARCHAR(255) NULL DEFAULT NULL,
            is_published INTEGER NOT NULL DEFAULT 0,
            published_at DATETIME NULL DEFAULT NULL,
            author_id INTEGER NULL DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(150) NOT NULL,
            slug VARCHAR(160) NOT NULL UNIQUE,
            category VARCHAR(80) NOT NULL,
            description TEXT NOT NULL,
            intended_audience VARCHAR(255) NOT NULL,
            price_display VARCHAR(100) NULL DEFAULT NULL,
            availability_status VARCHAR(30) NOT NULL DEFAULT 'inquire_only',
            requires_regulatory_check INTEGER NOT NULL DEFAULT 0,
            compliance_notice TEXT NULL DEFAULT NULL,
            display_order INTEGER NOT NULL DEFAULT 1,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS inquiries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name VARCHAR(120) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(40) NULL DEFAULT NULL,
            area_of_interest VARCHAR(120) NOT NULL,
            message TEXT NOT NULL,
            consent_given INTEGER NOT NULL DEFAULT 1,
            inquiry_status VARCHAR(30) NOT NULL DEFAULT 'new',
            admin_notes TEXT NULL DEFAULT NULL,
            submission_hash CHAR(64) NULL DEFAULT NULL,
            submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS site_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key VARCHAR(80) NOT NULL UNIQUE,
            setting_value TEXT NULL DEFAULT NULL,
            setting_label VARCHAR(120) NOT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Seed initial profile, methodology, risk management, services, and settings if empty
    $countProfile = (int) $pdo->query('SELECT COUNT(*) FROM trader_profile')->fetchColumn();
    if ($countProfile === 0) {
        $stmt = $pdo->prepare("
            INSERT INTO trader_profile (
                id, full_name, professional_title, hero_subtitle, hero_headline,
                hero_supporting_text, experience_years, experience_label,
                primary_methodology, risk_philosophy_short, risk_philosophy_summary,
                bio_paragraph_1, bio_paragraph_2, bio_paragraph_3, bio_paragraph_4, portrait_image
            ) VALUES (
                1, :full_name, :professional_title, :hero_subtitle, :hero_headline,
                :hero_supporting_text, 3, '3 years',
                'Advanced Price Action', 'Adaptive, structured risk management', :risk_philosophy_summary,
                :bio_1, :bio_2, :bio_3, :bio_4, 'assets/images/patrick-grey-placeholder.svg'
            )
        ");
        $stmt->execute([
            ':full_name' => 'Patrick Grey',
            ':professional_title' => 'Intermediate Forex Trader',
            ':hero_subtitle' => 'Intermediate Forex Trader | Advanced Price Action',
            ':hero_headline' => 'Forex Trading Through Price Action, Discipline, and Risk Awareness.',
            ':hero_supporting_text' => "I'm Patrick Grey, an intermediate forex trader with three years of trading experience. My approach centers on advanced price action, market structure analysis, and disciplined trade planning. Explore my methodology, learn about my approach to risk management, and discover educational resources designed to help you understand the forex market.",
            ':risk_philosophy_summary' => 'A combination of aggressive and conservative approaches, applied according to trading conditions, setup quality, account exposure, and predefined risk limits.',
            ':bio_1' => "I'm Patrick Grey, an intermediate forex trader with three years of experience studying and navigating the foreign exchange market. My trading methodology focuses on advanced price action, market structure, and identifying potential opportunities through structured technical analysis.",
            ':bio_2' => 'My approach to risk management combines conservative capital protection with a more aggressive approach when market conditions, trade setup quality, and predefined risk limits justify it. I believe that identifying a potential trading opportunity is only one part of trading; understanding exposure, managing losses, and maintaining discipline are equally important.',
            ':bio_3' => 'Through this platform, I aim to share my trading approach, provide educational insights, document my trading journey transparently, and connect with individuals who want to develop a better understanding of forex trading.',
            ':bio_4' => 'My focus is on continuous learning, disciplined execution, and responsible trading decisions rather than promises of guaranteed profits.',
        ]);
    }

    $countStrategies = (int) $pdo->query('SELECT COUNT(*) FROM strategy_sections')->fetchColumn();
    if ($countStrategies === 0) {
        $strategies = [
            ['market-structure-analysis', 'Market Structure Analysis', 'Examining the sequence of swing highs and swing lows across multiple timeframes to read whether the market is trending, consolidating, or transitioning.', 'Establishes the foundational directional context before any individual trade setup is evaluated.', 'structure', 1],
            ['support-and-resistance-zones', 'Support and Resistance Zones', 'Identifying horizontal price regions where buying or selling pressure has historically stepped in to stall, reject, or reverse price movement.', 'Highlights high-interest price areas where reactions are more informative than in the middle of a range.', 'zones', 2],
            ['trend-identification', 'Trend Identification', 'Assessing directional momentum across higher and intermediate timeframes to distinguish sustained trends from counter-trend pullbacks or choppy ranges.', 'Helps align trade ideas with prevailing market flow or recognize when a trend is losing steam.', 'trend', 3],
            ['candlestick-and-price-action-patterns', 'Candlestick and Price Action Patterns', 'Reading individual and multi-candle formations—such as rejections, engulfing structures, and indecision bars—at key structural levels.', 'Provides visual evidence of buyer and seller interaction around planned areas of interest.', 'candlesticks', 4],
            ['breakout-and-reversal-analysis', 'Breakout and Reversal Analysis', 'Evaluating whether price is breaking through a key structural boundary with conviction (and retesting) or failing and reversing back into range.', 'Helps differentiate between genuine structural continuation and false breakouts (liquidity sweeps).', 'breakout', 5],
            ['entry-and-exit-planning', 'Entry and Exit Planning', 'Defining the specific trigger conditions required to enter a position and the structural conditions under which the trade will be closed.', 'Replaces impulsive execution with a written checklist before any capital is committed.', 'entry_exit', 6],
            ['stop-loss-and-take-profit-placement', 'Stop-Loss and Take-Profit Placement', 'Placing stop-loss orders at the price point that invalidates the technical thesis, and setting take-profit targets near logical opposing structure.', 'Ensures every trade has a predefined exit for both adverse and favorable scenarios.', 'sl_tp', 7],
            ['risk-to-reward-assessment', 'Risk-to-Reward Assessment', 'Comparing the distance to the invalidation level (risk) against the realistic distance to the next structural target (potential reward).', 'Filters out setups where the potential downside outweighs the realistic structural upside.', 'risk_reward', 8],
        ];
        $stmt = $pdo->prepare('INSERT INTO strategy_sections (slug, technique_name, brief_explanation, role_in_process, visual_key, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)');
        foreach ($strategies as $s) {
            $stmt->execute($s);
        }
    }

    $countRisk = (int) $pdo->query('SELECT COUNT(*) FROM risk_management_sections')->fetchColumn();
    if ($countRisk === 0) {
        $stmt = $pdo->prepare('INSERT INTO risk_management_sections (approach_key, title, subtitle, summary, principles_json, configurable_risk_per_trade, configurable_max_drawdown, configurable_max_exposure, disclaimer_note, display_order) VALUES (?, ?, ?, ?, ?, NULL, NULL, NULL, ?, ?)');
        $stmt->execute([
            'conservative',
            'Conservative Risk Management',
            'Capital Preservation & Strict Exposure Control',
            'Applied during uncertain market conditions, transitional structure, or standard setups where protecting account equity is the primary priority.',
            json_encode([
                'Prioritize capital preservation.',
                'Plan trades before entering the market.',
                'Use predefined stop-loss levels.',
                'Maintain controlled position sizes.',
                'Avoid unnecessary leverage.',
                'Limit cumulative exposure.',
                'Prefer setups that meet established trading criteria.',
                'Avoid emotional decision-making and revenge trading.',
            ]),
            'Conservative risk management seeks to slow down equity drawdowns during losing streaks, but it does not eliminate market risk or guarantee profitable outcomes.',
            1,
        ]);
        $stmt->execute([
            'aggressive',
            'Aggressive Risk Management',
            'Selective Conviction Within Predefined Risk Limits',
            'Applied selectively when market structure, setup quality, and liquidity conditions align—always bounded by advance stop-loss placement and predefined exposure ceilings.',
            json_encode([
                'Consider higher-conviction opportunities when appropriate.',
                'Adapt trade management to market conditions.',
                'Evaluate volatility and potential downside before entry.',
                'Define stop-loss levels and exposure limits in advance.',
                'Avoid increasing position sizes impulsively.',
                'Never increase risk simply to recover previous losses.',
                'Recognize that aggressive exposure can amplify losses as well as gains.',
            ]),
            'Important: An aggressive approach does not mean uncontrolled trading, gambling, or ignoring risk limits. Neither conservative nor aggressive risk management guarantees profitable results, and leverage can magnify losses rapidly.',
            2,
        ]);
    }

    $countServices = (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
    if ($countServices === 0) {
        $services = [
            ['Forex Education & Price Action Foundations', 'forex-education', 'Forex education', 'Structured educational sessions covering foreign exchange market mechanics, candlestick reading, market structure, and risk calculation fundamentals.', 'Beginner and developing traders seeking structured forex education.', null, 'inquire_only', 0, 'Educational content only. Does not constitute personalized investment advice or trade signals.', 1],
            ['Trading Mentorship & Study Guidance', 'trading-mentorship', 'Trading mentorship', 'One-on-one or small-group educational discussions focused on learning advanced price action concepts, chart markup routines, and disciplined trade planning.', 'Developing traders looking to refine their technical analysis study workflow.', null, 'inquire_only', 1, 'Subject to scope confirmation. Any service beyond general financial education must be reviewed against applicable Capital Markets Authority (Uganda) and local licensing requirements before engagement.', 2],
            ['General Market Structure Analysis Discussions', 'general-market-analysis', 'General market analysis', 'Educational walkthroughs of historical and current chart structure, support and resistance zones, and multi-timeframe trend context.', 'Traders interested in studying technical price action frameworks.', null, 'inquire_only', 1, 'Presented strictly as general technical commentary and education—never as individualized buy/sell recommendations or regulated advisory services.', 3],
            ['Trading Journal & Execution Reviews', 'trading-journal-reviews', 'Trading journal reviews', 'Collaborative review of a trader\'s own recorded trade journal to evaluate adherence to predefined rules, stop-loss placement, and risk-to-reward consistency.', 'Self-directed traders who keep a trade log and want process feedback.', null, 'inquire_only', 0, 'Focuses on process discipline and risk math review; does not manage client funds or direct trade execution.', 4],
            ['Price Action & Trading Discipline Discussions', 'price-action-and-discipline', 'Discussions about price action and trading discipline', 'Focused conversations on trading psychology, avoiding revenge trading, managing losing streaks, and balancing conservative and aggressive risk rules.', 'Private individuals and beginner-to-intermediate traders working on execution discipline.', null, 'available', 0, 'Educational peer and mentor discussion; no capital deposits, pooled accounts, or automated trading are accepted.', 5],
        ];
        $stmt = $pdo->prepare('INSERT INTO services (name, slug, category, description, intended_audience, price_display, availability_status, requires_regulatory_check, compliance_notice, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)');
        foreach ($services as $srv) {
            $stmt->execute($srv);
        }
    }

    $countSettings = (int) $pdo->query('SELECT COUNT(*) FROM site_settings')->fetchColumn();
    if ($countSettings === 0) {
        $settings = [
            ['site_name', 'Patrick Grey Forex Portfolio', 'Website Title'],
            ['contact_email', '', 'Public Contact Email (leave blank until supplied)'],
            ['whatsapp_number', '', 'WhatsApp Number in International Format (leave blank until supplied)'],
            ['social_linkedin', '', 'LinkedIn Profile URL (optional)'],
            ['social_twitter', '', 'X / Twitter Profile URL (optional)'],
            ['social_youtube', '', 'YouTube Channel URL (optional)'],
            ['social_telegram', '', 'Telegram Link (optional)'],
            ['performance_account_currency', 'USD', 'Default Performance Reporting Currency'],
            ['performance_starting_balance', '', 'Optional Reference Starting Balance for Drawdown Calculation'],
            ['performance_reporting_note', 'All performance figures displayed on this platform are calculated directly from logged database records. Unless explicitly accompanied by third-party audit documentation, records are classified as self-reported.', 'Performance Transparency Note'],
            ['footer_risk_disclaimer', 'Forex trading involves substantial risk and may not be suitable for every individual. You may lose some or all of your trading capital. Historical performance does not guarantee future results. Information on this website is provided for educational and informational purposes and should not be interpreted as a promise of returns or personalized financial advice.', 'Footer Forex Risk Disclaimer'],
            ['uganda_compliance_notice', 'Important Regulatory Notice: Patrick Grey does not accept client deposits, operate pooled investment schemes, provide discretionary account management, or execute automated trades on behalf of clients. Any regulated financial or investment advisory service in Uganda or other jurisdictions requires prior licensing and compliance confirmation under the Capital Markets Authority (CMA) Act or applicable local law.', 'Regulatory & Compliance Notice'],
        ];
        $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value, setting_label) VALUES (?, ?, ?)');
        foreach ($settings as $st) {
            $stmt->execute($st);
        }
    }
}

/**
 * Return a shared PDO database instance, or null if the database is unreachable.
 */
function get_db_connection(): ?PDO
{
    static $pdo = null;
    static $attempted = false;

    if ($attempted) {
        return $pdo;
    }
    $attempted = true;

    $driver = strtolower(pg_env('DB_DRIVER', 'mysql'));
    $host = pg_env('DB_HOST', '127.0.0.1');
    $port = pg_env('DB_PORT', '3306');
    $dbName = pg_env('DB_NAME', 'forex_portfolio');
    $user = pg_env('DB_USER', 'root');
    $pass = pg_env('DB_PASS', '');
    $charset = pg_env('DB_CHARSET', 'utf8mb4');
    $allowSqliteFallback = strtolower(pg_env('DB_SQLITE_FALLBACK', 'false')) === 'true';

    // When running inside a WebAssembly PHP runtime without a local MySQL daemon,
    // automatically use the local PDO SQLite database so CLI & preview work seamlessly.
    if (extension_loaded('wasm_memory_storage') && getenv('DB_DRIVER') === false) {
        $driver = 'sqlite';
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if ($driver === 'sqlite') {
        try {
            $sqliteRel = pg_env('DB_SQLITE_PATH', 'database/forex_portfolio.sqlite');
            $sqlitePath = str_starts_with($sqliteRel, '/')
                ? $sqliteRel
                : dirname(__DIR__) . '/' . ltrim($sqliteRel, '/');
            $pdo = new PDO('sqlite:' . $sqlitePath, null, null, $options);
            pg_init_sqlite_schema($pdo);
            return $pdo;
        } catch (PDOException $e) {
            error_log('[Patrick Grey Portfolio] SQLite connection failed: ' . $e->getMessage());
            return null;
        }
    }

    // Check if the MySQL TCP socket is reachable before invoking PDO mysqlnd
    $mysqlReachable = true;
    if (function_exists('fsockopen') && $host !== '' && $host !== 'localhost') {
        $fp = @fsockopen($host, (int) $port, $errno, $errstr, 1.0);
        if ($fp === false) {
            $mysqlReachable = false;
        } else {
            fclose($fp);
        }
    }

    if ($mysqlReachable) {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";
            $pdo = new PDO($dsn, $user, $pass, $options);
            return $pdo;
        } catch (PDOException $e) {
            error_log('[Patrick Grey Portfolio] MySQL connection failed: ' . $e->getMessage());
        }
    } else {
        error_log("[Patrick Grey Portfolio] MySQL host {$host}:{$port} is not currently listening.");
    }

    if ($allowSqliteFallback) {
        try {
            $sqliteRel = pg_env('DB_SQLITE_PATH', 'database/forex_portfolio.sqlite');
            $sqlitePath = str_starts_with($sqliteRel, '/')
                ? $sqliteRel
                : dirname(__DIR__) . '/' . ltrim($sqliteRel, '/');
            $pdo = new PDO('sqlite:' . $sqlitePath, null, null, $options);
            pg_init_sqlite_schema($pdo);
            return $pdo;
        } catch (PDOException $fallbackEx) {
            error_log('[Patrick Grey Portfolio] SQLite fallback failed: ' . $fallbackEx->getMessage());
        }
    }

    return null;
}
