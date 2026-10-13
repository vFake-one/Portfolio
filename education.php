<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — FOREX EDUCATION HUB & ARTICLE READER
 * ============================================================================
 * File: education.php
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/sections.php';

$pdo = get_db_connection();
$profile = get_trader_profile($pdo);
$siteSettings = get_site_settings($pdo);
$categories = get_education_categories();

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$selectedCategory = isset($_GET['category']) ? trim((string) $_GET['category']) : null;
if ($selectedCategory !== null && ! array_key_exists($selectedCategory, $categories)) {
    $selectedCategory = null;
}

$singleArticle = ($slug !== '') ? get_article_by_slug($pdo, $slug) : null;

if ($singleArticle) {
    $pageTitle = (string) $singleArticle['title'];
    $pageDescription = (string) $singleArticle['summary'];
} else {
    $pageTitle = 'Forex Education & Study Resources';
    $pageDescription = 'Educational articles and curriculum topics covering forex fundamentals, advanced price action, market structure, risk management, and trading psychology.';
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container pt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-emerald text-decoration-none">Home</a></li>
            <?php if ($singleArticle): ?>
                <li class="breadcrumb-item"><a href="education.php" class="text-emerald text-decoration-none">Forex Education</a></li>
                <li class="breadcrumb-item active text-secondary" aria-current="page"><?= e($singleArticle['title']) ?></li>
            <?php else: ?>
                <li class="breadcrumb-item active text-secondary" aria-current="page">Forex Education</li>
            <?php endif; ?>
        </ol>
    </nav>
</div>

<?php if ($singleArticle): ?>
    <section class="pg-section">
        <div class="container" style="max-width: 860px;">
            <article class="pg-card p-4 p-lg-5">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <span class="badge pg-badge-emerald"><?= e($singleArticle['category']) ?></span>
                    <time class="small text-secondary font-mono"
                          datetime="<?= e(date('Y-m-d', strtotime((string) ($singleArticle['published_at'] ?: $singleArticle['created_at'])))) ?>">
                        Published <?= e(date('F j, Y', strtotime((string) ($singleArticle['published_at'] ?: $singleArticle['created_at'])))) ?>
                    </time>
                </div>

                <h1 class="h2 fw-bold text-white mb-3"><?= e($singleArticle['title']) ?></h1>
                <p class="lead text-secondary mb-4 pb-3 border-bottom border-secondary-subtle">
                    <?= e($singleArticle['summary']) ?>
                </p>

                <?php if (!empty($singleArticle['featured_image'])): ?>
                    <img src="<?= e($singleArticle['featured_image']) ?>"
                         alt="<?= e($singleArticle['title']) ?>"
                         class="img-fluid rounded mb-4 border border-secondary-subtle">
                <?php endif; ?>

                <div class="pg-article-body text-light" style="line-height: 1.8;">
                    <?= nl2br(e($singleArticle['content'])) ?>
                </div>

                <div class="pg-disclaimer-box mt-5">
                    <p class="small text-secondary mb-0">
                        <strong class="text-white">Educational Notice:</strong> This article was written for general forex education and technical study. It does not constitute personalized financial advice or a recommendation to buy or sell any currency pair.
                    </p>
                </div>

                <div class="mt-4 pt-3 border-top border-secondary-subtle d-flex justify-content-between align-items-center">
                    <a href="education.php" class="btn pg-btn-outline btn-sm">&larr; Back to All Articles</a>
                    <a href="contact.php?interest=Forex+Education" class="btn pg-btn-primary btn-sm">Ask a Question About This Topic</a>
                </div>
            </article>
        </div>
    </section>
<?php else: ?>
    <?php
    $articles = get_published_articles($pdo, $selectedCategory, null);
    render_education_section($articles, $categories, $selectedCategory);
    ?>
<?php endif; ?>

<?php
require_once __DIR__ . '/includes/footer.php';
