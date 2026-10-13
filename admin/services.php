<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — SERVICES & REGULATORY COMPLIANCE MANAGER
 * ============================================================================
 * File: admin/services.php
 */

declare(strict_types=1);

$adminPageTitle = 'Services & Compliance';
require_once __DIR__ . '/includes/admin_header.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $pdo) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security token validation failed.');
        header('Location: services.php');
        exit;
    }

    $action = (string) ($_POST['form_action'] ?? '');

    try {
        if ($action === 'delete_service') {
            $serviceId = (int) ($_POST['service_id'] ?? 0);
            if ($serviceId > 0) {
                $stmt = $pdo->prepare('DELETE FROM services WHERE id = :id');
                $stmt->execute([':id' => $serviceId]);
                set_flash('success', 'Service listing deleted.');
            }
            header('Location: services.php');
            exit;
        }

        if ($action === 'save_service') {
            $serviceId = (int) ($_POST['service_id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));
            $category = trim((string) ($_POST['category'] ?? 'Forex education'));
            $description = trim((string) ($_POST['description'] ?? ''));
            $audience = trim((string) ($_POST['intended_audience'] ?? ''));
            $priceDisplay = trim((string) ($_POST['price_display'] ?? ''));
            $availability = trim((string) ($_POST['availability_status'] ?? 'inquire_only'));
            $requiresRegCheck = !empty($_POST['requires_regulatory_check']) ? 1 : 0;
            $complianceNotice = trim((string) ($_POST['compliance_notice'] ?? ''));
            $displayOrder = max(1, (int) ($_POST['display_order'] ?? 1));
            $isActive = !empty($_POST['is_active']) ? 1 : 0;
            $slug = slugify($name);

            if ($name === '' || $description === '' || $audience === '') {
                set_flash('danger', 'Please complete the service name, description, and intended audience.');
                header('Location: services.php');
                exit;
            }

            if ($serviceId > 0) {
                $stmt = $pdo->prepare("
                    UPDATE services
                    SET name                      = :name,
                        category                  = :category,
                        description               = :description,
                        intended_audience         = :audience,
                        price_display             = :price,
                        availability_status       = :status,
                        requires_regulatory_check = :reg,
                        compliance_notice         = :notice,
                        display_order             = :ord,
                        is_active                 = :active
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':name'        => $name,
                    ':category'    => $category,
                    ':description' => $description,
                    ':audience'    => $audience,
                    ':price'       => $priceDisplay !== '' ? $priceDisplay : null,
                    ':status'      => $availability,
                    ':reg'         => $requiresRegCheck,
                    ':notice'      => $complianceNotice !== '' ? $complianceNotice : null,
                    ':ord'         => $displayOrder,
                    ':active'      => $isActive,
                    ':id'          => $serviceId,
                ]);
                set_flash('success', 'Service updated.');
            } else {
                $checkSlug = $pdo->prepare('SELECT COUNT(*) FROM services WHERE slug = :slug');
                $checkSlug->execute([':slug' => $slug]);
                if ((int) $checkSlug->fetchColumn() > 0) {
                    $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
                }

                $stmt = $pdo->prepare("
                    INSERT INTO services (
                        name, slug, category, description, intended_audience,
                        price_display, availability_status, requires_regulatory_check,
                        compliance_notice, display_order, is_active
                    ) VALUES (
                        :name, :slug, :category, :description, :audience,
                        :price, :status, :reg,
                        :notice, :ord, :active
                    )
                ");
                $stmt->execute([
                    ':name'        => $name,
                    ':slug'        => $slug,
                    ':category'    => $category,
                    ':description' => $description,
                    ':audience'    => $audience,
                    ':price'       => $priceDisplay !== '' ? $priceDisplay : null,
                    ':status'      => $availability,
                    ':reg'         => $requiresRegCheck,
                    ':notice'      => $complianceNotice !== '' ? $complianceNotice : null,
                    ':ord'         => $displayOrder,
                    ':active'      => $isActive,
                ]);
                set_flash('success', 'New service added.');
            }

            header('Location: services.php');
            exit;
        }
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Admin services error: ' . $e->getMessage());
        set_flash('danger', 'Could not save service.');
        header('Location: services.php');
        exit;
    }
}

$editingService = null;
if (isset($_GET['edit']) && $pdo) {
    $stmtEdit = $pdo->prepare('SELECT * FROM services WHERE id = :id LIMIT 1');
    $stmtEdit->execute([':id' => (int) $_GET['edit']]);
    $editingService = $stmtEdit->fetch() ?: null;
}

$services = get_services($pdo, false);
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-white mb-1">Manage Services &amp; Engagement</h1>
        <p class="small text-secondary mb-0">
            Configure educational and mentorship offerings. Prices remain blank unless explicitly set by Patrick Grey.
        </p>
    </div>
    <a href="../services.php" target="_blank" class="btn btn-sm pg-btn-outline">View Public Services Page &nearr;</a>
</div>

<!-- Mandatory Ugandan & International Licensing Compliance Warning -->
<div class="pg-disclaimer-box mb-4">
    <h2 class="h6 text-white fw-bold mb-1">Ugandan (CMA) &amp; International Regulatory Compliance Warning</h2>
    <p class="small text-secondary mb-0">
        Do not enable client investment deposits, pooled funds, discretionary account management, or automatic trade execution. Before publishing or providing any service that may constitute regulated investment advice or portfolio management in Uganda or another jurisdiction, confirm applicable licensing and compliance obligations under the Capital Markets Authority (CMA) Act of Uganda and relevant local laws.
    </p>
</div>

<section class="pg-card mb-4">
    <h2 class="h5 fw-bold text-white mb-3">
        <?= $editingService ? 'Edit Service: ' . e($editingService['name']) : 'Add Configurable Service' ?>
    </h2>

    <form method="post" action="services.php">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="save_service">
        <input type="hidden" name="service_id" value="<?= (int) ($editingService['id'] ?? 0) ?>">

        <div class="row g-3">
            <div class="col-md-5">
                <label for="name" class="pg-form-label">Service Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control pg-form-control"
                       value="<?= e($editingService['name'] ?? '') ?>" required>
            </div>

            <div class="col-md-4">
                <label for="category" class="pg-form-label">Service Category</label>
                <select id="category" name="category" class="form-select pg-form-select">
                    <?php
                    $serviceCats = [
                        'Forex education',
                        'Trading mentorship',
                        'General market analysis',
                        'Trading journal reviews',
                        'Discussions about price action and trading discipline',
                    ];
                    foreach ($serviceCats as $sc):
                    ?>
                        <option value="<?= e($sc) ?>" <?= (($editingService['category'] ?? '') === $sc) ? 'selected' : '' ?>>
                            <?= e($sc) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="availability_status" class="pg-form-label">Availability Status</label>
                <select id="availability_status" name="availability_status" class="form-select pg-form-select">
                    <option value="available" <?= (($editingService['availability_status'] ?? '') === 'available') ? 'selected' : '' ?>>Available</option>
                    <option value="inquire_only" <?= (($editingService['availability_status'] ?? 'inquire_only') === 'inquire_only') ? 'selected' : '' ?>>By Inquiry Only</option>
                    <option value="coming_soon" <?= (($editingService['availability_status'] ?? '') === 'coming_soon') ? 'selected' : '' ?>>Coming Soon</option>
                    <option value="unavailable" <?= (($editingService['availability_status'] ?? '') === 'unavailable') ? 'selected' : '' ?>>Unavailable</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="intended_audience" class="pg-form-label">Intended Audience <span class="text-danger">*</span></label>
                <input type="text" id="intended_audience" name="intended_audience" class="form-control pg-form-control"
                       value="<?= e($editingService['intended_audience'] ?? '') ?>" required>
            </div>

            <div class="col-md-4">
                <label for="price_display" class="pg-form-label">Price (Optional — Leave blank if upon inquiry)</label>
                <input type="text" id="price_display" name="price_display" class="form-control pg-form-control font-mono"
                       value="<?= e($editingService['price_display'] ?? '') ?>" placeholder="Leave blank until set">
            </div>

            <div class="col-md-2">
                <label for="display_order" class="pg-form-label">Order</label>
                <input type="number" id="display_order" name="display_order" class="form-control pg-form-control font-mono"
                       value="<?= (int) ($editingService['display_order'] ?? 1) ?>" min="1">
            </div>

            <div class="col-12">
                <label for="description" class="pg-form-label">Service Description <span class="text-danger">*</span></label>
                <textarea id="description" name="description" rows="3" class="form-control pg-form-control" required><?= e($editingService['description'] ?? '') ?></textarea>
            </div>

            <div class="col-12">
                <label for="compliance_notice" class="pg-form-label">Service Scope &amp; Compliance Note</label>
                <input type="text" id="compliance_notice" name="compliance_notice" class="form-control pg-form-control"
                       value="<?= e($editingService['compliance_notice'] ?? 'Educational content only. Does not constitute personalized investment advice.') ?>">
            </div>

            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="requires_regulatory_check" name="requires_regulatory_check" value="1"
                           <?= !empty($editingService['requires_regulatory_check']) ? 'checked' : '' ?>>
                    <label class="form-check-label small text-warning" for="requires_regulatory_check">
                        Flag for Regulatory/Licensing Review (Ugandan CMA / Local Compliance Check Required)
                    </label>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                           <?= (!isset($editingService['is_active']) || !empty($editingService['is_active'])) ? 'checked' : '' ?>>
                    <label class="form-check-label small text-white" for="is_active">
                        Display this service on the public website
                    </label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn pg-btn-primary">
                    <?= $editingService ? 'Update Service' : 'Save Service' ?>
                </button>
                <?php if ($editingService): ?>
                    <a href="services.php" class="btn pg-btn-outline">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</section>

<section class="pg-card">
    <h2 class="h6 fw-bold text-white mb-3">Configured Services (<?= count($services) ?>)</h2>
    <div class="table-responsive">
        <table class="table pg-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Service Name</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Compliance Flag</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $srv): ?>
                    <tr>
                        <td class="font-mono small"><?= (int) $srv['display_order'] ?></td>
                        <td>
                            <div class="fw-bold text-white"><?= e($srv['name']) ?></div>
                            <div class="small text-secondary"><?= e($srv['category']) ?></div>
                        </td>
                        <td class="small font-mono"><?= !empty($srv['price_display']) ? e($srv['price_display']) : 'Upon Inquiry' ?></td>
                        <td><span class="badge pg-badge-emerald"><?= e($srv['availability_status']) ?></span></td>
                        <td>
                            <?php if (!empty($srv['requires_regulatory_check'])): ?>
                                <span class="badge pg-badge-amber">Confirm CMA / Local Licensing</span>
                            <?php else: ?>
                                <span class="small text-secondary">General Education</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="services.php?edit=<?= (int) $srv['id'] ?>" class="btn btn-sm pg-btn-outline">Edit</a>
                                <form method="post" action="services.php" class="d-inline" data-confirm="Delete service &quot;<?= e($srv['name']) ?>&quot;?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="form_action" value="delete_service">
                                    <input type="hidden" name="service_id" value="<?= (int) $srv['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
