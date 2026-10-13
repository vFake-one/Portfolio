<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — VISITOR INQUIRIES MANAGER
 * ============================================================================
 * File: admin/inquiries.php
 */

declare(strict_types=1);

$adminPageTitle = 'Visitor Inquiries';
require_once __DIR__ . '/includes/admin_header.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $pdo) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security token validation failed.');
        header('Location: inquiries.php');
        exit;
    }

    $action = (string) ($_POST['form_action'] ?? '');

    try {
        if ($action === 'update_inquiry') {
            $inqId = (int) ($_POST['inquiry_id'] ?? 0);
            $status = trim((string) ($_POST['inquiry_status'] ?? 'new'));
            $notes = trim((string) ($_POST['admin_notes'] ?? ''));

            if (in_array($status, ['new', 'in_review', 'responded', 'archived'], true) && $inqId > 0) {
                $stmt = $pdo->prepare('UPDATE inquiries SET inquiry_status = :status, admin_notes = :notes WHERE id = :id');
                $stmt->execute([
                    ':status' => $status,
                    ':notes'  => $notes !== '' ? $notes : null,
                    ':id'     => $inqId,
                ]);
                set_flash('success', 'Inquiry #' . $inqId . ' status updated to ' . $status . '.');
            }
            header('Location: inquiries.php');
            exit;
        }

        if ($action === 'delete_inquiry') {
            $inqId = (int) ($_POST['inquiry_id'] ?? 0);
            if ($inqId > 0) {
                $stmt = $pdo->prepare('DELETE FROM inquiries WHERE id = :id');
                $stmt->execute([':id' => $inqId]);
                set_flash('success', 'Inquiry deleted.');
            }
            header('Location: inquiries.php');
            exit;
        }
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Admin inquiries error: ' . $e->getMessage());
        set_flash('danger', 'Could not update inquiry.');
        header('Location: inquiries.php');
        exit;
    }
}

$filterStatus = isset($_GET['status']) ? trim((string) $_GET['status']) : '';
$inquiries = [];
if ($pdo) {
    try {
        if (in_array($filterStatus, ['new', 'in_review', 'responded', 'archived'], true)) {
            $stmt = $pdo->prepare('SELECT * FROM inquiries WHERE inquiry_status = :st ORDER BY submitted_at DESC, id DESC');
            $stmt->execute([':st' => $filterStatus]);
        } else {
            $stmt = $pdo->query('SELECT * FROM inquiries ORDER BY submitted_at DESC, id DESC');
        }
        $inquiries = $stmt ? $stmt->fetchAll() : [];
    } catch (Throwable $e) {
        $inquiries = [];
    }
}
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-white mb-1">Visitor Inquiries Management</h1>
        <p class="small text-secondary mb-0">
            Review inquiries submitted through the Contact &amp; Inquiry Form and track response status.
        </p>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a href="inquiries.php" class="btn btn-sm <?= $filterStatus === '' ? 'pg-btn-primary' : 'pg-btn-outline' ?>">All</a>
        <a href="inquiries.php?status=new" class="btn btn-sm <?= $filterStatus === 'new' ? 'pg-btn-primary' : 'pg-btn-outline' ?>">New</a>
        <a href="inquiries.php?status=in_review" class="btn btn-sm <?= $filterStatus === 'in_review' ? 'pg-btn-primary' : 'pg-btn-outline' ?>">In Review</a>
        <a href="inquiries.php?status=responded" class="btn btn-sm <?= $filterStatus === 'responded' ? 'pg-btn-primary' : 'pg-btn-outline' ?>">Responded</a>
        <a href="inquiries.php?status=archived" class="btn btn-sm <?= $filterStatus === 'archived' ? 'pg-btn-primary' : 'pg-btn-outline' ?>">Archived</a>
    </div>
</div>

<section class="pg-card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 class="h6 fw-bold text-white mb-0">Recorded Inquiries (<?= count($inquiries) ?>)</h2>
        <input type="search" class="form-control form-control-sm pg-form-control" style="max-width: 260px;"
               placeholder="Search name, email, or topic..." data-table-search="#adminInquiriesTable" aria-label="Search inquiries">
    </div>

    <?php if (empty($inquiries)): ?>
        <p class="small text-secondary text-center py-4 mb-0">No inquiries match the selected filter.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table pg-table align-top mb-0" id="adminInquiriesTable">
                <thead>
                    <tr>
                        <th>ID &amp; Date</th>
                        <th>Sender Details</th>
                        <th>Topic &amp; Message</th>
                        <th>Status &amp; Internal Notes</th>
                        <th class="text-end">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inquiries as $inq): ?>
                        <tr>
                            <td class="font-mono small">
                                <div class="fw-bold text-white">#<?= (int) $inq['id'] ?></div>
                                <div class="text-secondary"><?= e(date('Y-m-d H:i', strtotime((string) $inq['submitted_at']))) ?></div>
                            </td>
                            <td>
                                <div class="fw-bold text-white"><?= e($inq['full_name']) ?></div>
                                <div class="small"><a href="mailto:<?= e($inq['email']) ?>" class="text-emerald text-decoration-none"><?= e($inq['email']) ?></a></div>
                                <?php if (!empty($inq['phone'])): ?>
                                    <div class="small text-secondary font-mono"><?= e($inq['phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 340px;">
                                <span class="badge pg-badge-emerald mb-1"><?= e($inq['area_of_interest']) ?></span>
                                <p class="small text-light mb-0"><?= nl2br(e($inq['message'])) ?></p>
                            </td>
                            <td style="min-width: 250px;">
                                <form method="post" action="inquiries.php" class="d-flex flex-column gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="form_action" value="update_inquiry">
                                    <input type="hidden" name="inquiry_id" value="<?= (int) $inq['id'] ?>">

                                    <div class="d-flex gap-2">
                                        <select name="inquiry_status" class="form-select form-select-sm pg-form-select" aria-label="Inquiry Status">
                                            <?php foreach (['new', 'in_review', 'responded', 'archived'] as $st): ?>
                                                <option value="<?= e($st) ?>" <?= ($inq['inquiry_status'] === $st) ? 'selected' : '' ?>>
                                                    <?= e(ucfirst(str_replace('_', ' ', $st))) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm pg-btn-outline">Save</button>
                                    </div>
                                    <input type="text" name="admin_notes" class="form-control form-control-sm pg-form-control"
                                           value="<?= e($inq['admin_notes'] ?? '') ?>" placeholder="Internal follow-up note...">
                                </form>
                            </td>
                            <td class="text-end">
                                <form method="post" action="inquiries.php" data-confirm="Delete inquiry #<?= (int) $inq['id'] ?> from <?= e($inq['full_name']) ?>?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="form_action" value="delete_inquiry">
                                    <input type="hidden" name="inquiry_id" value="<?= (int) $inq['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
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
