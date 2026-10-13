# Patrick Grey — Personal Forex Trader Portfolio Website

A complete, responsive, database-driven personal forex trader portfolio and landing website built for **Patrick Grey** (*Intermediate Forex Trader | 3 Years Experience | Advanced Price Action*), engineered with **HTML5, CSS3, Vanilla JavaScript, Bootstrap 5, Chart.js, PHP 8+, and MySQL (PDO)** for local development on **Debian/Ubuntu Linux via XAMPP** and production PHP/MySQL hosting.

---

## Table of Contents

1. [System Requirements & Architecture (Phase 1)](#1-system-requirements--architecture-phase-1)
2. [UI/UX Design & Homepage Wireframe (Phase 2)](#2-uiux-design--homepage-wireframe-phase-2)
3. [Refined MySQL Database Schema & Relationships (Phase 3)](#3-refined-mysql-database-schema--relationships-phase-3)
4. [Project Folder Structure](#4-project-folder-structure)
5. [Debian/Ubuntu Linux Installation Guide with XAMPP (Phase 7)](#5-debianubuntu-linux-installation-guide-with-xampp-phase-7)
6. [Creating the Initial Administrator Account](#6-creating-the-initial-administrator-account)
7. [Security Architecture & Beginner-Friendly Code Notes](#7-security-architecture--beginner-friendly-code-notes)
8. [Testing Checklist & Executed Verification Suite (Phase 6)](#8-testing-checklist--executed-verification-suite-phase-6)
9. [Assumptions, Limitations & Regulatory Compliance](#9-assumptions-limitations--regulatory-compliance)
10. [Troubleshooting Guide](#10-troubleshooting-guide)

---

## 1. System Requirements & Architecture (Phase 1)

### System Overview
The platform establishes a credible, transparent online presence for **Patrick Grey**, presenting his **Advanced Price Action** trading methodology, explaining his adaptive **Conservative and Aggressive Risk Management** philosophy, providing an interactive **Forex Risk Calculator**, publishing educational forex articles, displaying genuine database-logged **Trading Performance** metrics when records are available, and capturing visitor inquiries in MySQL.

### User Roles
1. **Public Visitor (Unauthenticated)**
   - Can browse the complete landing page (`index.php`) and dedicated topic pages (`about.php`, `methodology.php`, `performance.php`, `risk-management.php`, `education.php`, `services.php`, `contact.php`, `privacy.php`, `terms.php`).
   - Can use the interactive client-side **Forex Risk Calculator** (`assets/js/risk-calculator.js`).
   - Can filter and read published educational articles.
   - Can submit inquiries through the CSRF-protected **Contact & Inquiry Form**.
2. **Administrator (`Patrick Grey` — Authenticated via `/admin/`)**
   - Authenticates at `/admin/login.php` (protected by `password_verify()`, session fixation regeneration, and brute-force throttling).
   - Manages profile & biography paragraphs, the 8 Price Action methodology cards, Conservative/Aggressive Risk Management cards, and site/contact settings (`/admin/profile.php`).
   - Logs, edits, and deletes documented trading records (`/admin/performance.php`).
   - Creates, edits, publishes, unpublishes, and deletes educational articles (`/admin/articles.php`).
   - Manages service offerings and regulatory compliance notices (`/admin/services.php`).
   - Reviews, filters, updates statuses (`new`, `in_review`, `responded`, `archived`), and deletes visitor inquiries (`/admin/inquiries.php`).

### Three-Tier Application Architecture
```
+-------------------------------------------------------------------------+
| PRESENTATION TIER (Browser: Mobile / Tablet / Desktop)                  |
| HTML5 Semantic Markup + CSS3 Custom Variables + Bootstrap 5.3           |
| Vanilla JS (main.js, risk-calculator.js) + Chart.js (Performance)       |
+-------------------------------------------------------------------------+
                                    |
                                    | HTTPS / HTTP Requests (CSRF Protected)
                                    v
+-------------------------------------------------------------------------+
| APPLICATION TIER (Apache + PHP 8+)                                      |
| Public Pages : index.php, about.php, methodology.php, performance.php,  |
|                risk-management.php, education.php, services.php,        |
|                contact.php, privacy.php, terms.php                      |
| Admin Pages  : /admin/ (login.php, setup.php, dashboard.php,            |
|                profile.php, performance.php, articles.php,              |
|                services.php, inquiries.php, logout.php)                 |
| Shared Logic : config/database.php, includes/functions.php,             |
|                includes/auth.php, includes/sections.php                 |
+-------------------------------------------------------------------------+
                                    |
                                    | PDO Prepared Statements (UTF8MB4)
                                    v
+-------------------------------------------------------------------------+
| DATA TIER (MySQL 8+ / MariaDB 10.4+ — `forex_portfolio`)                |
| Tables: admins, login_attempts, trader_profile, strategy_sections,      |
|         risk_management_sections, trading_records, articles,            |
|         services, inquiries, site_settings                              |
+-------------------------------------------------------------------------+
```

---

## 2. UI/UX Design & Homepage Wireframe (Phase 2)

### Color Palette & Typography
| Token | Hex / Value | Purpose |
|---|---|---|
| `--pg-bg-primary` | `#090f1d` | Deep navy primary background |
| `--pg-bg-secondary` | `#10192d` | Slightly lighter navy alternating section background |
| `--pg-bg-card` | `#131e36` | Elevated card surface |
| `--pg-text-primary` | `#f8fafc` | Crisp white primary typography |
| `--pg-text-secondary` | `#94a3b8` | Muted slate gray body/supporting copy |
| `--pg-emerald` | `#10b981` | Emerald green accent & positive performance |
| `--pg-red` | `#ef4444` | Muted red for drawdowns, losses & warnings |
| `--pg-border` | `#1e293b` | Low-contrast structural borders |
| Sans-Serif Font | `Inter, system-ui, sans-serif` | Clean, legible UI and editorial hierarchy |
| Monospace Font | `JetBrains Mono, monospace` | Prices, pip counts, lot sizes, and P/L figures |

### Homepage Wireframe (`index.php`)
```
+--------------------------------------------------------------------------+
| TOP RISK DISCLOSURE BAR: High-risk forex notice | Quick links | Admin    |
+--------------------------------------------------------------------------+
| STICKY NAVBAR: [PG] Patrick Grey | Home | About Me | Trading Approach |  |
| Performance | Risk Management | Forex Education | Contact | [Let's Connect]
+--------------------------------------------------------------------------+
| HERO SECTION (#home)                                                     |
| Left (7 cols):                                  Right (5 cols):          |
| - Eyebrow: Patrick Grey • 3 years Experience    [SVG Market Structure &  |
| - Subtitle: Intermediate Forex Trader |          Candlestick Retest      |
|             Advanced Price Action                Technical Illustration] |
| - H1: "Forex Trading Through Price Action,                               |
|        Discipline, and Risk Awareness."                                  |
| - Supporting Copy                                                        |
| - CTAs: [Explore My Trading Approach] [Get in Touch]                     |
| - Secondary Link: View Trading Performance ->                            |
+--------------------------------------------------------------------------+
| ABOUT PATRICK GREY (#about)                                              |
| Left (5 cols):                     Right (7 cols):                       |
| [Portrait Placeholder SVG]         - 4 Editable Biography Paragraphs     |
|                                    - 4 Profile Info Cards:               |
|                                      [Trader: Patrick Grey]              |
|                                      [Experience: 3 years]               |
|                                      [Methodology: Advanced Price Action]|
|                                      [Risk: Adaptive, structured...]     |
+--------------------------------------------------------------------------+
| ADVANCED PRICE ACTION METHODOLOGY (#trading-approach)                    |
| 8 Cards (4x2 Grid on Desktop, 2x4 Tablet, 1x8 Mobile):                   |
| [01 Market Structure] [02 S/R Zones] [03 Trend ID] [04 Candlesticks]     |
| [05 Breakout/Reversal] [06 Entry/Exit] [07 SL & TP] [08 Risk-to-Reward]  |
+--------------------------------------------------------------------------+
| RISK MANAGEMENT PHILOSOPHY (#risk-management)                            |
| +----------------------------------+ +---------------------------------+ |
| | Conservative Risk Management     | | Aggressive Risk Management      | |
| | - 8 Core Principles              | | - 7 Core Principles             | |
| | - Optional Configurable Limits   | | - Optional Configurable Limits  | |
| +----------------------------------+ +---------------------------------+ |
| [Clarification Box: Aggressive != Uncontrolled; Leverage Risk Warning]   |
+--------------------------------------------------------------------------+
| TRADING PERFORMANCE DASHBOARD (#performance)                             |
| - When 0 trades in MySQL: Displays exact honest empty-state notice       |
| - When trades exist: 8 Metric Cards + 3 Chart.js Charts + Trade Log      |
+--------------------------------------------------------------------------+
| INTERACTIVE FOREX RISK CALCULATOR (#risk-calculator)                     |
| Left (7 cols): Inputs (Balance, Currency, Risk %, Pair, Entry, SL, TP)   |
| Right (5 cols): Monetary Risk, Stop Distance (Pips), Lots (Std/Mini/Mic) |
| Bottom (3 Cards): Educational notes on Pip Value, Conversion & Slippage  |
+--------------------------------------------------------------------------+
| FOREX EDUCATION (#forex-education)                                       |
| - 8 Category Filter Pills                                                |
| - Published Articles Grid (or Honest Empty State + 8 Curriculum Cards)   |
+--------------------------------------------------------------------------+
| SERVICES & ENGAGEMENT (#services)                                        |
| - Configurable Service Cards + Ugandan CMA Regulatory Compliance Notice  |
+--------------------------------------------------------------------------+
| CONTACT & INQUIRY FORM (#contact)                                        |
| - Full Name, Email, Phone/WhatsApp, Area of Interest, Message, Consent   |
+--------------------------------------------------------------------------+
| FOOTER: Identity | Navigation | Legal | Mandatory Forex Risk Disclaimer  |
+--------------------------------------------------------------------------+
```

---

## 3. Refined MySQL Database Schema & Relationships (Phase 3)

The complete SQL schema is located at `database/forex_portfolio.sql` and creates the `forex_portfolio` database with 10 normalized tables:

1. `admins` — Administrator accounts (`password_hash` only; no public registration).
2. `login_attempts` — Brute-force throttling log indexed by `(ip_address, attempted_at)` and `(username_or_email, attempted_at)`.
3. `trader_profile` — Patrick Grey's editable hero text, biography paragraphs, and profile cards (`updated_by` FK -> `admins.id`).
4. `strategy_sections` — The 8 Advanced Price Action techniques.
5. `risk_management_sections` — The Conservative and Aggressive risk management cards and optional configurable parameters.
6. `trading_records` — Documented trades (`created_by` FK -> `admins.id`). **Starts with 0 rows** so no fabricated performance ever appears.
7. `articles` — Educational forex articles (`author_id` FK -> `admins.id`). **Starts with 0 published rows** so no fake publications appear.
8. `services` — Configurable educational and discussion services (`price_display` is `NULL` by default).
9. `inquiries` — Visitor contact form submissions stored via PDO prepared statements.
10. `site_settings` — Configurable contact details, social links, and legal disclaimers.

---

## 4. Project Folder Structure

```text
forex-portfolio/
├── index.php                       # Complete Homepage & Landing Page
├── about.php                       # Dedicated About Patrick Grey page
├── methodology.php                 # Dedicated Advanced Price Action page
├── performance.php                 # Dedicated Trading Performance Dashboard page
├── risk-management.php             # Dedicated Risk Management & Calculator page
├── education.php                   # Dedicated Forex Education hub & article reader
├── services.php                    # Dedicated Services & Compliance page
├── contact.php                     # Dedicated Contact & Inquiry Form page
├── privacy.php                     # Privacy Policy page
├── terms.php                       # Terms of Use & Risk Disclosure page
├── config/
│   ├── database.php                # PDO MySQL connection + .env loader
│   └── .htaccess                   # Blocks direct HTTP access
├── includes/
│   ├── header.php                  # Reusable HTML head, top risk bar, sticky navbar
│   ├── footer.php                  # Reusable footer, mandatory risk disclaimer, scripts
│   ├── functions.php               # Escaping, CSRF, DB queries, performance math
│   ├── auth.php                    # Session auth, login throttling, password hashing
│   ├── sections.php                # Modular section renderers shared across pages
│   └── .htaccess                   # Blocks direct HTTP access
├── assets/
│   ├── css/
│   │   └── style.css               # Custom dark-navy & emerald responsive stylesheet
│   ├── js/
│   │   ├── main.js                 # Navigation, form usability validation, Chart.js init
│   │   └── risk-calculator.js      # Interactive Forex Risk & Position Size Calculator
│   └── images/
│       ├── patrick-grey-placeholder.svg
│       ├── candlestick-hero.svg
│       └── uploads/                # Validated admin image uploads
├── admin/
│   ├── login.php                   # Protected administrator sign-in page
│   ├── setup.php                   # One-time initial administrator setup wizard
│   ├── dashboard.php               # Overview summary metrics & recent inquiries
│   ├── profile.php                 # Edit biography, 8 methodology cards, risk & settings
│   ├── performance.php             # CRUD for documented trading performance records
│   ├── articles.php                # CRUD & publish/unpublish for educational articles
│   ├── services.php                # CRUD for services + CMA regulatory compliance flags
│   ├── inquiries.php               # View, filter, annotate & update inquiry statuses
│   ├── logout.php                  # CSRF-protected session termination
│   └── includes/
│       ├── admin_header.php        # Admin layout header & responsive sidebar
│       └── admin_footer.php        # Admin layout footer & table search/confirm scripts
├── scripts/
│   ├── create_admin.php            # CLI helper for creating the initial admin account
│   └── .htaccess                   # Blocks direct HTTP access
├── database/
│   ├── forex_portfolio.sql         # Complete MySQL schema & safe initial seed data
│   └── .htaccess                   # Blocks direct HTTP access
├── .env.example                    # Environment configuration template (placeholders only)
├── .gitignore                      # Excludes .env, uploads, and temporary files
├── .htaccess                       # Apache security headers & hidden file protection
└── README.md                       # Complete documentation & testing checklist
```

---

## 5. Debian/Ubuntu Linux Installation Guide with XAMPP (Phase 7)

### Step 1: Install and Start XAMPP on Debian / Ubuntu
1. Download the XAMPP for Linux installer (`xampp-linux-x64-8.2.x-0-installer.run`) from Apache Friends.
2. Make the installer executable and run it:
   ```bash
   chmod +x xampp-linux-x64-*-installer.run
   sudo ./xampp-linux-x64-*-installer.run
   ```
3. Start Apache and MySQL/MariaDB:
   ```bash
   sudo /opt/lampp/lampp start
   ```
   Verify both `Apache` and `MySQL` report `OK`.

### Step 2: Deploy the Project into XAMPP `htdocs`
Copy or clone this repository into `/opt/lampp/htdocs/forex-portfolio`:
```bash
sudo cp -r /path/to/Portfolio /opt/lampp/htdocs/forex-portfolio
sudo chown -R $USER:daemon /opt/lampp/htdocs/forex-portfolio
sudo chmod -R 755 /opt/lampp/htdocs/forex-portfolio
sudo chmod -R 775 /opt/lampp/htdocs/forex-portfolio/assets/images/uploads
```

### Step 3: Create and Import the MySQL Database
You can import `database/forex_portfolio.sql` using **either** the terminal or **phpMyAdmin**:

- **Option A — Terminal (Recommended):**
  ```bash
  /opt/lampp/bin/mysql -u root -p < /opt/lampp/htdocs/forex-portfolio/database/forex_portfolio.sql
  ```
  *(By default on a fresh local XAMPP installation, the `root` MySQL password is empty—press Enter when prompted.)*

- **Option B — phpMyAdmin:**
  1. Open `http://localhost/phpmyadmin` in your browser.
  2. Click **Import** in the top navigation bar.
  3. Select `/opt/lampp/htdocs/forex-portfolio/database/forex_portfolio.sql`.
  4. Click **Import / Go**.

### Step 4: Configure Environment Variables (`.env`)
Copy `.env.example` to `.env` (or place `.env.forex_portfolio` one directory above `htdocs` so it resides completely outside the web root):
```bash
cd /opt/lampp/htdocs/forex-portfolio
cp .env.example .env
```
Verify the database settings in `.env`:
```ini
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=forex_portfolio
DB_USER=root
DB_PASS=
```

---

## 6. Creating the Initial Administrator Account

For security, **no default plain-text administrator password is stored in `forex_portfolio.sql`**, and **public registration does not exist**. You can create Patrick Grey's initial administrator account using either method below:

### Method A: Web One-Time Setup Wizard (`/admin/setup.php`)
1. Visit `http://localhost/forex-portfolio/admin/setup.php`.
2. Because `admins` currently has `0` rows, the setup wizard will be unlocked.
3. Enter your administrator username, email, and a strong password (at least 10 characters including uppercase, lowercase, and a number).
4. As soon as the account is created, `/admin/setup.php` **permanently locks itself** and redirects to `/admin/login.php`.

### Method B: Command-Line Script (`scripts/create_admin.php`)
Run from your Debian/Ubuntu terminal:
```bash
/opt/lampp/bin/php /opt/lampp/htdocs/forex-portfolio/scripts/create_admin.php patrick patrick@example.com "PriceAction2026!" "Patrick Grey"
```

---

## 7. Security Architecture & Beginner-Friendly Code Notes

Designed to be clear and maintainable for a **Computer Science Year 2, Semester 1** student:

1. **SQL Injection Prevention (`config/database.php`, `includes/functions.php`)**:
   All queries that accept user input use **PDO Prepared Statements** (`$pdo->prepare(...)` with bound parameters like `:email`) and `PDO::ATTR_EMULATE_PREPARES => false`. MySQL compiles the SQL structure before inserting parameters, making SQL injection impossible through bound values.
2. **Cross-Site Scripting (XSS) Prevention (`includes/functions.php`)**:
   Every dynamic value rendered in HTML passes through the helper function `e($value)`, which wraps `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.
3. **Cross-Site Request Forgery (CSRF) Protection (`includes/functions.php`)**:
   Every POST form includes `<?= csrf_field() ?>`, generating a cryptographic 256-bit token via `random_bytes(32)` and verifying it on the server with `hash_equals()`.
4. **Password Hashing & Session Hardening (`includes/auth.php`)**:
   Passwords are hashed using PHP's `password_hash($password, PASSWORD_DEFAULT)` and verified with `password_verify()`. Upon login, `session_regenerate_id(true)` rotates the session ID to prevent session fixation, and cookies use `HttpOnly` and `SameSite=Lax`.
5. **Brute-Force Login Throttling (`includes/auth.php`)**:
   Failed login attempts are recorded in the `login_attempts` table by IP and username. Reaching `LOGIN_MAX_ATTEMPTS` (default `5`) within `LOGIN_LOCKOUT_MINUTES` (default `15`) temporarily blocks further login attempts.

---

## 8. Testing Checklist & Executed Verification Suite (Phase 6)

The following automated and functional checks were executed using PHP 8.5 CLI and Node.js:

| # | Test Category | Test Case Description | Executed Result |
|---|---|---|---|
| 1 | PHP Syntax (`php -l`) | Lint all 26 PHP files across root, `config/`, `includes/`, `admin/`, and `scripts/` | **PASSED** (0 errors) |
| 2 | JS Syntax (`node --check`) | Lint `assets/js/main.js` and `assets/js/risk-calculator.js` | **PASSED** (0 errors) |
| 3 | Profile Identity | Verify `Patrick Grey`, `3 years` experience, and `Advanced Price Action` load from DB | **PASSED** |
| 4 | Methodology Cards | Verify all 8 Advanced Price Action techniques load in order with SVG diagrams | **PASSED** |
| 5 | Risk Philosophy | Verify Conservative (8 principles) and Aggressive (7 principles) load with `NULL` uninvented personal limits | **PASSED** |
| 6 | Empty Performance State | Verify `trading_records` has 0 rows initially and outputs exact required message: *"Trading performance data has not yet been published. This section will be updated when records are available."* | **PASSED** |
| 7 | Empty Education State | Verify `articles` has 0 fake published articles initially and renders honest empty state | **PASSED** |
| 8 | Services Integrity | Verify 5 configurable services load with `NULL` prices (no invented prices/testimonials) | **PASSED** |
| 9 | Admin Setup Security | Reject weak passwords (<10 chars), create initial admin with `password_hash()`, and block second setup attempt | **PASSED** |
| 10 | Admin Auth & Throttling | Reject wrong password, accept valid password, regenerate session, and lock out after 5 failed attempts | **PASSED** |
| 11 | Contact Form & CSRF | Reject missing/invalid CSRF token, validate inputs, insert via PDO prepared statement, and block duplicate rapid submissions | **PASSED** |
| 12 | Performance Math | Insert sample trades in isolated test DB, verify win rate, net realized P/L, peak-to-trough drawdown, and `Self-Reported` classification | **PASSED** |
| 13 | Risk Calculator Math | Verify Account Balance `100` and Risk `1%` calculates `1.00` planned monetary risk in account currency | **PASSED** |

---

## 9. Assumptions, Limitations & Regulatory Compliance

1. **No Fabricated Data**: In strict compliance with project requirements, no trading performance records, no fake published articles, no service prices, no client testimonials, and no personal phone/email details are pre-populated.
2. **Email Delivery**: Submitting the contact form stores the inquiry in the `inquiries` MySQL table for review in `/admin/inquiries.php`. The system does not claim an email has been sent unless an SMTP mailer is explicitly configured.
3. **Risk Calculator Scope**: The interactive risk calculator provides an educational estimate based on standard contract specifications. Real broker execution can differ due to spreads, commissions, swap rates, minimum lot steps, and market slippage.
4. **Ugandan & International Regulatory Compliance**: The platform does not accept deposits, operate pooled funds, or manage client accounts. If Patrick Grey intends to offer any regulated financial or investment advisory service in Uganda or another jurisdiction, licensing and compliance requirements under the **Capital Markets Authority (CMA) Act of Uganda** and applicable laws must be confirmed prior to publication.

---

## 10. Troubleshooting Guide

- **`Database Connection Notice` on Login or Homepage**:
  - Ensure XAMPP MySQL is running: `sudo /opt/lampp/lampp status` (or `sudo /opt/lampp/lampp startmysql`).
  - Confirm `forex_portfolio.sql` has been imported into MySQL.
  - Check that `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_NAME=forex_portfolio`, `DB_USER=root`, and `DB_PASS=` in `.env` match your MySQL credentials.
- **XAMPP Apache fails to start on port 80**:
  - Another service (such as system `apache2` or `nginx`) may be using port 80:
    ```bash
    sudo systemctl stop apache2
    sudo /opt/lampp/lampp restartapache
    ```
- **XAMPP MySQL fails to start on port 3306**:
  - Stop any system MariaDB/MySQL service occupying port 3306:
    ```bash
    sudo systemctl stop mysql mariadb
    sudo /opt/lampp/lampp startmysql
    ```
- **Image Upload Permission Error in `/admin/profile.php` or `/admin/articles.php`**:
  - Grant write permission on the upload directory to the Apache user (`daemon` in XAMPP):
    ```bash
    sudo chown -R $USER:daemon /opt/lampp/htdocs/forex-portfolio/assets/images/uploads
    sudo chmod -R 775 /opt/lampp/htdocs/forex-portfolio/assets/images/uploads
    ```
