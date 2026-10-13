<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — EDUCATIONAL ARTICLES MANAGER
 * ============================================================================
 * File: admin/articles.php
 */

declare(strict_types=1);

$adminPageTitle = 'Educational Articles';
require_once __DIR__ . '/includes/admin_header.php';

$categories = get_education_categories();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $pdo) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security token validation failed.');
        header('Location: articles.php');
        exit;
    }

    $action = (string) ($_POST['form_action'] ?? '');

    try {
        if ($action === 'delete_article') {
            $articleId = (int) ($_POST['article_id'] ?? 0);
            if ($articleId > 0) {
                $stmt = $pdo->prepare('DELETE FROM articles WHERE id = :id');
                $stmt->execute([':id' => $articleId]);
                set_flash('success', 'Educational article deleted.');
            }
            header('Location: articles.php');
            exit;
        }

        if ($action === 'toggle_publish') {
            $articleId = (int) ($_POST['article_id'] ?? 0);
            $newState = !empty($_POST['publish_state']) ? 1 : 0;
            $pubDate = $newState === 1 ? date('Y-m-d H:i:s') : null;

            $stmt = $pdo->prepare('UPDATE articles SET is_published = :pub, published_at = COALESCE(published_at, :pub_date) WHERE id = :id');
            $stmt->execute([
                ':pub'      => $newState,
                ':pub_date' => $pubDate,
                ':id'       => $articleId,
            ]);
            set_flash('success', $newState === 1 ? 'Article published to website.' : 'Article moved to drafts.');
            header('Location: articles.php');
            exit;
        }

        if ($action === 'save_article') {
            $articleId = (int) ($_POST['article_id'] ?? 0);
            $title = trim((string) ($_POST['title'] ?? ''));
            $category = trim((string) ($_POST['category'] ?? 'Forex fundamentals'));
            $summary = trim((string) ($_POST['summary'] ?? ''));
            $content = trim((string) ($_POST['content'] ?? ''));
            $isPublished = !empty($_POST['is_published']) ? 1 : 0;
            $slugInput = trim((string) ($_POST['slug'] ?? ''));
            $slug = slugify($slugInput !== '' ? $slugInput : $title);

            if ($title === '' || $summary === '' || $content === '') {
                set_flash('danger', 'Please provide an article title, summary, and content.');
                header('Location: articles.php');
                exit;
            }

            $featuredImage = trim((string) ($_POST['existing_featured_image'] ?? ''));
            if (!empty($_FILES['featured_image_upload']['tmp_name'])) {
                $up = handle_safe_image_upload($_FILES['featured_image_upload']);
                if ($up['success'] && !empty($up['path'])) {
                    $featuredImage = (string) $up['path'];
                } else {
                    set_flash('warning', 'Featured image upload skipped: ' . ($up['error'] ?? 'Invalid file.'));
                }
            }

            $publishedAt = $isPublished === 1 ? date('Y-m-d H:i:s') : null;

            if ($articleId > 0) {
                $stmt = $pdo->prepare("
                    UPDATE articles
                    SET title          = :title,
                        slug           = :slug,
                        category       = :category,
                        summary        = :summary,
                        content        = :content,
                        featured_image = :img,
                        is_published   = :pub,
                        published_at   = CASE WHEN :pub2 = 1 AND published_at IS NULL THEN :pub_at ELSE published_at END
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':title'    => $title,
                    ':slug'     => $slug,
                    ':category' => $category,
                    ':summary'  => $summary,
                    ':content'  => $content,
                    ':img'      => $featuredImage !== '' ? $featuredImage : null,
                    ':pub'      => $isPublished,
                    ':pub2'     => $isPublished,
                    ':pub_at'   => $publishedAt,
                    ':id'       => $articleId,
                ]);
                set_flash('success', 'Article updated successfully.');
            } else {
                // Ensure unique slug
                $checkSlug = $pdo->prepare('SELECT COUNT(*) FROM articles WHERE slug = :slug');
                $checkSlug->execute([':slug' => $slug]);
                if ((int) $checkSlug->fetchColumn() > 0) {
                    $slug .= '-' . substr(bin2hex(random_bytes(3)), 0, 5);
                }

                $stmt = $pdo->prepare("
                    INSERT INTO articles (
                        title, slug, summary, category, content,
                        featured_image, is_published, published_at, author_id
                    ) VALUES (
                        :title, :slug, :summary, :category, :content,
                        :img, :pub, :pub_at, :author_id
                    )
                ");
                $stmt->execute([
                    ':title'     => $title,
                    ':slug'      => $slug,
                    ':summary'   => $summary,
                    ':category'  => $category,
                    ':content'   => $content,
                    ':img'       => $featuredImage !== '' ? $featuredImage : null,
                    ':pub'       => $isPublished,
                    ':pub_at'    => $publishedAt,
                    ':author_id' => $adminUser['id'] ?: null,
                ]);
                set_flash('success', 'Educational article created.');
            }

            header('Location: articles.php');
            exit;
        }
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Admin articles error: ' . $e->getMessage());
        set_flash('danger', 'Could not save article. Check for duplicate slugs or database connection.');
        header('Location: articles.php');
        exit;
    }
}

$editingArticle = null;
if (isset($_GET['edit']) && $pdo) {
    $stmtEdit = $pdo->prepare('SELECT * FROM articles WHERE id = :id LIMIT 1');
    $stmtEdit->execute([':id' => (int) $_GET['edit']]);
    $editingArticle = $stmtEdit->fetch() ?: null;
}

$allArticles = [];
if ($pdo) {
    $stmtAll = $pdo->query('SELECT * FROM articles ORDER BY created_at DESC, id DESC');
    $allArticles = $stmtAll ? $stmtAll->fetchAll() : [];
}
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-white mb-1">Manage Educational Articles</h1>
        <p class="small text-secondary mb-0">
            Create, edit, publish, or unpublish educational forex articles across the 8 topic categories.
        </p>
    </div>
    <a href="../education.php" target="_blank" class="btn btn-sm pg-btn-outline">View Public Education Page &nearr;</a>
</div>

<section class="pg-card mb-4">
    <h2 class="h5 fw-bold text-white mb-3">
        <?= $editingArticle ? 'Edit Article: ' . e($editingArticle['title']) : 'Create New Educational Article' ?>
    </h2>

    <form method="post" action="articles.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="save_article">
        <input type="hidden" name="article_id" value="<?= (int) ($editingArticle['id'] ?? 0) ?>">
        <input type="hidden" name="existing_featured_image" value="<?= e($editingArticle['featured_image'] ?? '') ?>">

        <div class="row g-3">
            <div class="col-md-5">
                <label for="title" class="pg-form-label">Article Title <span class="text-danger">*</span></label>
                <input type="text" id="title" name="title" class="form-control pg-form-control"
                       value="<?= e($editingArticle['title'] ?? '') ?>"
                       placeholder="e.g., Understanding Higher Highs and Higher Lows in Market Structure" required>
            </div>

            <div class="col-md-4">
                <label for="category" class="pg-form-label">Topic Category <span class="text-danger">*</span></label>
                <select id="category" name="category" class="form-select pg-form-select" required>
                    <?php foreach ($categories as $catName => $catDesc): ?>
                        <option value="<?= e($catName) ?>" <?= (($editingArticle['category'] ?? '') === $catName) ? 'selected' : '' ?>>
                            <?= e($catName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="slug" class="pg-form-label">URL Slug (Auto if blank)</label>
                <input type="text" id="slug" name="slug" class="form-control pg-form-control font-mono"
                       value="<?= e($editingArticle['slug'] ?? '') ?>" placeholder="market-structure-basics">
            </div>

            <div class="col-12">
                <label for="summary" class="pg-form-label">Short Summary <span class="text-danger">*</span></label>
                <textarea id="summary" name="summary" rows="2" class="form-control pg-form-control"
                          placeholder="Concise 1-2 sentence overview displayed on the article card..." required><?= e($editingArticle['summary'] ?? '') ?></textarea>
            </div>

            <div class="col-12">
                <label for="content" class="pg-form-label">Full Article Content <span class="text-danger">*</span></label>
                <textarea id="content" name="content" rows="8" class="form-control pg-form-control"
                          placeholder="Write clear, educational forex content here..." required><?= e($editingArticle['content'] ?? '') ?></textarea>
            </div>

            <div class="col-md-6">
                <label for="featured_image_upload" class="pg-form-label">Optional Featured Image (JPG/PNG/WEBP, max 2 MB)</label>
                <input type="file" id="featured_image_upload" name="featured_image_upload" class="form-control pg-form-control"
                       accept="image/jpeg,image/png,image/webp">
            </div>

            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1"
                           <?= !empty($editingArticle['is_published']) ? 'checked' : '' ?>>
                    <label class="form-check-label text-white fw-semibold" for="is_published">
                        Publish immediately to the public Forex Education section
                    </label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn pg-btn-primary">
                    <?= $editingArticle ? 'Save Article Changes' : 'Save Educational Article' ?>
                </button>
                <?php if ($editingArticle): ?>
                    <a href="articles.php" class="btn pg-btn-outline">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</section>

<section class="pg-card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 class="h6 fw-bold text-white mb-0">All Articles (<?= count($allArticles) ?>)</h2>
        <input type="search" class="form-control form-control-sm pg-form-control" style="max-width: 260px;"
               placeholder="Search title or category..." data-table-search="#adminArticlesTable" aria-label="Search articles">
    </div>

    <?php if (empty($allArticles)): ?>
        <p class="small text-secondary text-center py-4 mb-0">
            No articles created yet. The public Forex Education section currently displays the curriculum overview and honest empty state.
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table pg-table align-middle mb-0" id="adminArticlesTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Published Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allArticles as $art): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-white"><?= e($art['title']) ?></div>
                                <div class="small text-secondary font-mono">/education.php?slug=<?= e($art['slug']) ?></div>
                            </td>
                            <td><span class="badge pg-badge-emerald"><?= e($art['category']) ?></span></td>
                            <td>
                                <?php if (!empty($art['is_published'])): ?>
                                    <span class="badge pg-badge-emerald">Published</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td class="small font-mono text-secondary"><?= e($art['published_at'] ?? '—') ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <form method="post" action="articles.php" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="form_action" value="toggle_publish">
                                        <input type="hidden" name="article_id" value="<?= (int) $art['id'] ?>">
                                        <input type="hidden" name="publish_state" value="<?= !empty($art['is_published']) ? '0' : '1' ?>">
                                        <button type="submit" class="btn btn-sm pg-btn-outline">
                                            <?= !empty($art['is_published']) ? 'Unpublish' : 'Publish' ?>
                                        </button>
                                    </form>
                                    <a href="articles.php?edit=<?= (int) $art['id'] ?>" class="btn btn-sm pg-btn-outline">Edit</a>
                                    <form method="post" action="articles.php" class="d-inline" data-confirm="Delete article &quot;<?= e($art['title']) ?>&quot;?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="form_action" value="delete_article">
                                        <input type="hidden" name="article_id" value="<?= (int) $art['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
