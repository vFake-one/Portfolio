-- ============================================================================
-- PATRICK GREY — PERSONAL FOREX TRADER PORTFOLIO DATABASE SCHEMA
-- Database Name : forex_portfolio
-- Target Engine : MySQL 8.0+ / MariaDB 10.4+ (XAMPP Compatible)
-- Character Set : utf8mb4 / utf8mb4_unicode_ci
-- ============================================================================
-- IMPORTANT DATA INTEGRITY NOTICE:
-- 1. This file contains ONLY Patrick Grey's supplied profile information,
--    stated Advanced Price Action methodology, and risk management principles.
-- 2. It intentionally contains ZERO fabricated trading records, ZERO invented
--    articles presented as real publications, ZERO fake testimonials, and
--    ZERO invented personal contact details or service prices.
-- 3. Administrator passwords are NEVER stored in plain text. Create your
--    initial administrator account securely via `/admin/setup.php` or the
--    CLI helper `php scripts/create_admin.php`.
-- ============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `forex_portfolio`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `forex_portfolio`;

-- ----------------------------------------------------------------------------
-- 1. TABLE: admins
-- Stores authenticated administrator accounts. No public registration exists.
-- Passwords must only be stored as secure password_hash() digests.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(60) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL DEFAULT 'Patrick Grey',
  `password_hash` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_username` (`username`),
  UNIQUE KEY `uq_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. TABLE: login_attempts
-- Tracks login attempts by IP and username to enforce brute-force throttling.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username_or_email` VARCHAR(150) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `was_successful` TINYINT(1) NOT NULL DEFAULT 0,
  `attempted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_login_attempts_ip_time` (`ip_address`, `attempted_at`),
  KEY `idx_login_attempts_user_time` (`username_or_email`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. TABLE: trader_profile
-- Stores Patrick Grey's editable biography, hero messaging, and profile cards.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `trader_profile`;
CREATE TABLE `trader_profile` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL DEFAULT 'Patrick Grey',
  `professional_title` VARCHAR(150) NOT NULL DEFAULT 'Intermediate Forex Trader',
  `hero_subtitle` VARCHAR(200) NOT NULL DEFAULT 'Intermediate Forex Trader | Advanced Price Action',
  `hero_headline` VARCHAR(255) NOT NULL DEFAULT 'Forex Trading Through Price Action, Discipline, and Risk Awareness.',
  `hero_supporting_text` TEXT NOT NULL,
  `experience_years` INT UNSIGNED NOT NULL DEFAULT 3,
  `experience_label` VARCHAR(80) NOT NULL DEFAULT '3 years',
  `primary_methodology` VARCHAR(120) NOT NULL DEFAULT 'Advanced Price Action',
  `risk_philosophy_short` VARCHAR(150) NOT NULL DEFAULT 'Adaptive, structured risk management',
  `risk_philosophy_summary` TEXT NOT NULL,
  `bio_paragraph_1` TEXT NOT NULL,
  `bio_paragraph_2` TEXT NOT NULL,
  `bio_paragraph_3` TEXT NOT NULL,
  `bio_paragraph_4` TEXT NOT NULL,
  `portrait_image` VARCHAR(255) NOT NULL DEFAULT 'assets/images/patrick-grey-placeholder.svg',
  `updated_by` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_trader_profile_updated_by` (`updated_by`),
  CONSTRAINT `fk_trader_profile_admin`
    FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trader_profile` (
  `id`,
  `full_name`,
  `professional_title`,
  `hero_subtitle`,
  `hero_headline`,
  `hero_supporting_text`,
  `experience_years`,
  `experience_label`,
  `primary_methodology`,
  `risk_philosophy_short`,
  `risk_philosophy_summary`,
  `bio_paragraph_1`,
  `bio_paragraph_2`,
  `bio_paragraph_3`,
  `bio_paragraph_4`,
  `portrait_image`
) VALUES (
  1,
  'Patrick Grey',
  'Intermediate Forex Trader',
  'Intermediate Forex Trader | Advanced Price Action',
  'Forex Trading Through Price Action, Discipline, and Risk Awareness.',
  'I''m Patrick Grey, an intermediate forex trader with three years of trading experience. My approach centers on advanced price action, market structure analysis, and disciplined trade planning. Explore my methodology, learn about my approach to risk management, and discover educational resources designed to help you understand the forex market.',
  3,
  '3 years',
  'Advanced Price Action',
  'Adaptive, structured risk management',
  'A combination of aggressive and conservative approaches, applied according to trading conditions, setup quality, account exposure, and predefined risk limits.',
  'I''m Patrick Grey, an intermediate forex trader with three years of experience studying and navigating the foreign exchange market. My trading methodology focuses on advanced price action, market structure, and identifying potential opportunities through structured technical analysis.',
  'My approach to risk management combines conservative capital protection with a more aggressive approach when market conditions, trade setup quality, and predefined risk limits justify it. I believe that identifying a potential trading opportunity is only one part of trading; understanding exposure, managing losses, and maintaining discipline are equally important.',
  'Through this platform, I aim to share my trading approach, provide educational insights, document my trading journey transparently, and connect with individuals who want to develop a better understanding of forex trading.',
  'My focus is on continuous learning, disciplined execution, and responsible trading decisions rather than promises of guaranteed profits.',
  'assets/images/patrick-grey-placeholder.svg'
);

-- ----------------------------------------------------------------------------
-- 4. TABLE: strategy_sections
-- Stores the 8 core elements of Patrick Grey's Advanced Price Action methodology.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `strategy_sections`;
CREATE TABLE `strategy_sections` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(80) NOT NULL,
  `technique_name` VARCHAR(120) NOT NULL,
  `brief_explanation` TEXT NOT NULL,
  `role_in_process` TEXT NOT NULL,
  `visual_key` VARCHAR(60) NOT NULL DEFAULT 'structure',
  `display_order` INT UNSIGNED NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_strategy_sections_slug` (`slug`),
  KEY `idx_strategy_sections_order` (`is_active`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `strategy_sections` (
  `slug`, `technique_name`, `brief_explanation`, `role_in_process`, `visual_key`, `display_order`, `is_active`
) VALUES
(
  'market-structure-analysis',
  'Market Structure Analysis',
  'Examining the sequence of swing highs and swing lows across multiple timeframes to read whether the market is trending, consolidating, or transitioning.',
  'Establishes the foundational directional context before any individual trade setup is evaluated.',
  'structure',
  1,
  1
),
(
  'support-and-resistance-zones',
  'Support and Resistance Zones',
  'Identifying horizontal price regions where buying or selling pressure has historically stepped in to stall, reject, or reverse price movement.',
  'Highlights high-interest price areas where reactions are more informative than in the middle of a range.',
  'zones',
  2,
  1
),
(
  'trend-identification',
  'Trend Identification',
  'Assessing directional momentum across higher and intermediate timeframes to distinguish sustained trends from counter-trend pullbacks or choppy ranges.',
  'Helps align trade ideas with prevailing market flow or recognize when a trend is losing steam.',
  'trend',
  3,
  1
),
(
  'candlestick-and-price-action-patterns',
  'Candlestick and Price Action Patterns',
  'Reading individual and multi-candle formations—such as rejections, engulfing structures, and indecision bars—at key structural levels.',
  'Provides visual evidence of buyer and seller interaction around planned areas of interest.',
  'candlesticks',
  4,
  1
),
(
  'breakout-and-reversal-analysis',
  'Breakout and Reversal Analysis',
  'Evaluating whether price is breaking through a key structural boundary with conviction (and retesting) or failing and reversing back into range.',
  'Helps differentiate between genuine structural continuation and false breakouts (liquidity sweeps).',
  'breakout',
  5,
  1
),
(
  'entry-and-exit-planning',
  'Entry and Exit Planning',
  'Defining the specific trigger conditions required to enter a position and the structural conditions under which the trade will be closed.',
  'Replaces impulsive execution with a written checklist before any capital is committed.',
  'entry_exit',
  6,
  1
),
(
  'stop-loss-and-take-profit-placement',
  'Stop-Loss and Take-Profit Placement',
  'Placing stop-loss orders at the price point that invalidates the technical thesis, and setting take-profit targets near logical opposing structure.',
  'Ensures every trade has a predefined exit for both adverse and favorable scenarios.',
  'sl_tp',
  7,
  1
),
(
  'risk-to-reward-assessment',
  'Risk-to-Reward Assessment',
  'Comparing the distance to the invalidation level (risk) against the realistic distance to the next structural target (potential reward).',
  'Filters out setups where the potential downside outweighs the realistic structural upside.',
  'risk_reward',
  8,
  1
);

-- ----------------------------------------------------------------------------
-- 5. TABLE: risk_management_sections
-- Stores Conservative and Aggressive Risk Management philosophy cards and
-- optional configurable risk parameters (left NULL until supplied by owner).
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `risk_management_sections`;
CREATE TABLE `risk_management_sections` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `approach_key` VARCHAR(40) NOT NULL,
  `title` VARCHAR(120) NOT NULL,
  `subtitle` VARCHAR(200) NOT NULL,
  `summary` TEXT NOT NULL,
  `principles_json` TEXT NOT NULL,
  `configurable_risk_per_trade` VARCHAR(80) NULL DEFAULT NULL,
  `configurable_max_drawdown` VARCHAR(80) NULL DEFAULT NULL,
  `configurable_max_exposure` VARCHAR(80) NULL DEFAULT NULL,
  `disclaimer_note` TEXT NOT NULL,
  `display_order` INT UNSIGNED NOT NULL DEFAULT 1,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_risk_approach_key` (`approach_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `risk_management_sections` (
  `approach_key`,
  `title`,
  `subtitle`,
  `summary`,
  `principles_json`,
  `configurable_risk_per_trade`,
  `configurable_max_drawdown`,
  `configurable_max_exposure`,
  `disclaimer_note`,
  `display_order`
) VALUES
(
  'conservative',
  'Conservative Risk Management',
  'Capital Preservation & Strict Exposure Control',
  'Applied during uncertain market conditions, transitional structure, or standard setups where protecting account equity is the primary priority.',
  '["Prioritize capital preservation.","Plan trades before entering the market.","Use predefined stop-loss levels.","Maintain controlled position sizes.","Avoid unnecessary leverage.","Limit cumulative exposure.","Prefer setups that meet established trading criteria.","Avoid emotional decision-making and revenge trading."]',
  NULL,
  NULL,
  NULL,
  'Conservative risk management seeks to slow down equity drawdowns during losing streaks, but it does not eliminate market risk or guarantee profitable outcomes.',
  1
),
(
  'aggressive',
  'Aggressive Risk Management',
  'Selective Conviction Within Predefined Risk Limits',
  'Applied selectively when market structure, setup quality, and liquidity conditions align—always bounded by advance stop-loss placement and predefined exposure ceilings.',
  '["Consider higher-conviction opportunities when appropriate.","Adapt trade management to market conditions.","Evaluate volatility and potential downside before entry.","Define stop-loss levels and exposure limits in advance.","Avoid increasing position sizes impulsively.","Never increase risk simply to recover previous losses.","Recognize that aggressive exposure can amplify losses as well as gains."]',
  NULL,
  NULL,
  NULL,
  'Important: An aggressive approach does not mean uncontrolled trading, gambling, or ignoring risk limits. Neither conservative nor aggressive risk management guarantees profitable results, and leverage can magnify losses rapidly.',
  2
);

-- ----------------------------------------------------------------------------
-- 6. TABLE: trading_records
-- Stores documented trading records for the Trading Performance Dashboard.
-- Intentionally starts EMPTY (0 rows) so no fabricated results ever appear.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `trading_records`;
CREATE TABLE `trading_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `instrument` VARCHAR(20) NOT NULL COMMENT 'Forex pair or instrument e.g. EUR/USD',
  `trade_direction` ENUM('BUY', 'SELL') NOT NULL,
  `entry_price` DECIMAL(14, 6) NOT NULL,
  `exit_price` DECIMAL(14, 6) NULL DEFAULT NULL,
  `stop_loss_price` DECIMAL(14, 6) NULL DEFAULT NULL,
  `take_profit_price` DECIMAL(14, 6) NULL DEFAULT NULL,
  `position_size` DECIMAL(10, 4) NOT NULL COMMENT 'Position size in standard lots',
  `profit_loss` DECIMAL(14, 2) NOT NULL COMMENT 'Net profit or loss in account currency',
  `account_currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `result_type` ENUM('realized', 'unrealized') NOT NULL DEFAULT 'realized',
  `trade_outcome` ENUM('WIN', 'LOSS', 'BREAKEVEN') NOT NULL,
  `risk_reward_ratio` DECIMAL(6, 2) NULL DEFAULT NULL,
  `setup_type` VARCHAR(100) NULL DEFAULT NULL,
  `opened_at` DATETIME NOT NULL,
  `closed_at` DATETIME NULL DEFAULT NULL,
  `verification_status` ENUM('self_reported', 'independently_verified') NOT NULL DEFAULT 'self_reported',
  `verification_evidence_note` VARCHAR(255) NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created_by` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_trading_records_opened` (`opened_at`),
  KEY `idx_trading_records_closed` (`closed_at`),
  KEY `idx_trading_records_instrument` (`instrument`),
  KEY `idx_trading_records_result_type` (`result_type`),
  KEY `idx_trading_records_verification` (`verification_status`),
  KEY `idx_trading_records_created_by` (`created_by`),
  CONSTRAINT `fk_trading_records_admin`
    FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 7. TABLE: articles
-- Stores educational forex articles managed via the administrator dashboard.
-- Intentionally starts EMPTY (0 published rows) so no fake publications appear.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `summary` TEXT NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `featured_image` VARCHAR(255) NULL DEFAULT NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 0,
  `published_at` DATETIME NULL DEFAULT NULL,
  `author_id` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_articles_slug` (`slug`),
  KEY `idx_articles_published` (`is_published`, `published_at`),
  KEY `idx_articles_category` (`category`),
  KEY `idx_articles_author` (`author_id`),
  CONSTRAINT `fk_articles_admin`
    FOREIGN KEY (`author_id`) REFERENCES `admins` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 8. TABLE: services
-- Stores configurable educational and discussion services.
-- Prices are NULL by default ("Do not invent service prices...").
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `description` TEXT NOT NULL,
  `intended_audience` VARCHAR(255) NOT NULL,
  `price_display` VARCHAR(100) NULL DEFAULT NULL COMMENT 'NULL until configured by owner; never invented',
  `availability_status` ENUM('available', 'inquire_only', 'coming_soon', 'unavailable') NOT NULL DEFAULT 'inquire_only',
  `requires_regulatory_check` TINYINT(1) NOT NULL DEFAULT 0,
  `compliance_notice` TEXT NULL DEFAULT NULL,
  `display_order` INT UNSIGNED NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_services_slug` (`slug`),
  KEY `idx_services_active_order` (`is_active`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (
  `name`,
  `slug`,
  `category`,
  `description`,
  `intended_audience`,
  `price_display`,
  `availability_status`,
  `requires_regulatory_check`,
  `compliance_notice`,
  `display_order`,
  `is_active`
) VALUES
(
  'Forex Education & Price Action Foundations',
  'forex-education',
  'Forex education',
  'Structured educational sessions covering foreign exchange market mechanics, candlestick reading, market structure, and risk calculation fundamentals.',
  'Beginner and developing traders seeking structured forex education.',
  NULL,
  'inquire_only',
  0,
  'Educational content only. Does not constitute personalized investment advice or trade signals.',
  1,
  1
),
(
  'Trading Mentorship & Study Guidance',
  'trading-mentorship',
  'Trading mentorship',
  'One-on-one or small-group educational discussions focused on learning advanced price action concepts, chart markup routines, and disciplined trade planning.',
  'Developing traders looking to refine their technical analysis study workflow.',
  NULL,
  'inquire_only',
  1,
  'Subject to scope confirmation. Any service beyond general financial education must be reviewed against applicable Capital Markets Authority (Uganda) and local licensing requirements before engagement.',
  2,
  1
),
(
  'General Market Structure Analysis Discussions',
  'general-market-analysis',
  'General market analysis',
  'Educational walkthroughs of historical and current chart structure, support and resistance zones, and multi-timeframe trend context.',
  'Traders interested in studying technical price action frameworks.',
  NULL,
  'inquire_only',
  1,
  'Presented strictly as general technical commentary and education—never as individualized buy/sell recommendations or regulated advisory services.',
  3,
  1
),
(
  'Trading Journal & Execution Reviews',
  'trading-journal-reviews',
  'Trading journal reviews',
  'Collaborative review of a trader''s own recorded trade journal to evaluate adherence to predefined rules, stop-loss placement, and risk-to-reward consistency.',
  'Self-directed traders who keep a trade log and want process feedback.',
  NULL,
  'inquire_only',
  0,
  'Focuses on process discipline and risk math review; does not manage client funds or direct trade execution.',
  4,
  1
),
(
  'Price Action & Trading Discipline Discussions',
  'price-action-and-discipline',
  'Discussions about price action and trading discipline',
  'Focused conversations on trading psychology, avoiding revenge trading, managing losing streaks, and balancing conservative and aggressive risk rules.',
  'Private individuals and beginner-to-intermediate traders working on execution discipline.',
  NULL,
  'available',
  0,
  'Educational peer and mentor discussion; no capital deposits, pooled accounts, or automated trading are accepted.',
  5,
  1
);

-- ----------------------------------------------------------------------------
-- 9. TABLE: inquiries
-- Stores visitor contact and service inquiries submitted via the contact form.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `inquiries`;
CREATE TABLE `inquiries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(40) NULL DEFAULT NULL,
  `area_of_interest` VARCHAR(120) NOT NULL,
  `message` TEXT NOT NULL,
  `consent_given` TINYINT(1) NOT NULL DEFAULT 1,
  `inquiry_status` ENUM('new', 'in_review', 'responded', 'archived') NOT NULL DEFAULT 'new',
  `admin_notes` TEXT NULL DEFAULT NULL,
  `submission_hash` CHAR(64) NULL DEFAULT NULL,
  `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inquiries_status_date` (`inquiry_status`, `submitted_at`),
  KEY `idx_inquiries_email` (`email`),
  KEY `idx_inquiries_hash` (`submission_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 10. TABLE: site_settings
-- Stores configurable contact links, performance reporting notes, and legal text.
-- Contact email/WhatsApp/social URLs start empty so no details are invented.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` TEXT NULL DEFAULT NULL,
  `setting_label` VARCHAR(120) NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_site_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_label`) VALUES
('site_name', 'Patrick Grey Forex Portfolio', 'Website Title'),
('contact_email', '', 'Public Contact Email (leave blank until supplied)'),
('whatsapp_number', '', 'WhatsApp Number in International Format (leave blank until supplied)'),
('social_linkedin', '', 'LinkedIn Profile URL (optional)'),
('social_twitter', '', 'X / Twitter Profile URL (optional)'),
('social_youtube', '', 'YouTube Channel URL (optional)'),
('social_telegram', '', 'Telegram Link (optional)'),
('performance_account_currency', 'USD', 'Default Performance Reporting Currency'),
('performance_starting_balance', '', 'Optional Reference Starting Balance for Drawdown Calculation'),
('performance_reporting_note', 'All performance figures displayed on this platform are calculated directly from logged database records. Unless explicitly accompanied by third-party audit documentation, records are classified as self-reported.', 'Performance Transparency Note'),
('footer_risk_disclaimer', 'Forex trading involves substantial risk and may not be suitable for every individual. You may lose some or all of your trading capital. Historical performance does not guarantee future results. Information on this website is provided for educational and informational purposes and should not be interpreted as a promise of returns or personalized financial advice.', 'Footer Forex Risk Disclaimer'),
('uganda_compliance_notice', 'Important Regulatory Notice: Patrick Grey does not accept client deposits, operate pooled investment schemes, provide discretionary account management, or execute automated trades on behalf of clients. Any regulated financial or investment advisory service in Uganda or other jurisdictions requires prior licensing and compliance confirmation under the Capital Markets Authority (CMA) Act or applicable local law.', 'Regulatory & Compliance Notice');

SET FOREIGN_KEY_CHECKS = 1;
