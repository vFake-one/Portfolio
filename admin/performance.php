<?php
/**
 * ============================================================================
 * PATRICK GREY FOREX PORTFOLIO — TRADING PERFORMANCE RECORDS MANAGER
 * ============================================================================
 * File: admin/performance.php
 *
 * Allows authenticated administrators to create, edit, and delete documented
 * trading records in the `trading_records` table.
 * ============================================================================
 */

declare(strict_types=1);

$adminPageTitle = 'Trading Performance Records';
require_once __DIR__ . '/includes/admin_header.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $pdo) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security token validation failed.');
        header('Location: performance.php');
        exit;
    }

    $action = (string) ($_POST['form_action'] ?? '');

    try {
        if ($action === 'delete_trade') {
            $tradeId = (int) ($_POST['trade_id'] ?? 0);
            if ($tradeId > 0) {
                $stmt = $pdo->prepare('DELETE FROM trading_records WHERE id = :id');
                $stmt->execute([':id' => $tradeId]);
                set_flash('success', 'Trading record deleted.');
            }
            header('Location: performance.php');
            exit;
        }

        if ($action === 'save_trade') {
            $tradeId = (int) ($_POST['trade_id'] ?? 0);
            $instrument = strtoupper(trim((string) ($_POST['instrument'] ?? 'EUR/USD')));
            $direction = strtoupper(trim((string) ($_POST['trade_direction'] ?? 'BUY')));
            $entryPrice = (float) ($_POST['entry_price'] ?? 0);
            $exitPriceRaw = trim((string) ($_POST['exit_price'] ?? ''));
            $exitPrice = $exitPriceRaw !== '' ? (float) $exitPriceRaw : null;
            $stopLossRaw = trim((string) ($_POST['stop_loss_price'] ?? ''));
            $stopLossPrice = $stopLossRaw !== '' ? (float) $stopLossRaw : null;
            $takeProfitRaw = trim((string) ($_POST['take_profit_price'] ?? ''));
            $takeProfitPrice = $takeProfitRaw !== '' ? (float) $takeProfitRaw : null;
            $positionSize = (float) ($_POST['position_size'] ?? 0.01);
            $profitLoss = (float) ($_POST['profit_loss'] ?? 0.0);
            $accountCurrency = strtoupper(trim((string) ($_POST['account_currency'] ?? 'USD')));
            $resultType = strtolower(trim((string) ($_POST['result_type'] ?? 'realized')));
            $rrRaw = trim((string) ($_POST['risk_reward_ratio'] ?? ''));
            $rrRatio = $rrRaw !== '' ? (float) $rrRaw : null;
            $setupType = trim((string) ($_POST['setup_type'] ?? ''));
            $openedAt = trim((string) ($_POST['opened_at'] ?? date('Y-m-d H:i:s')));
            $closedAtRaw = trim((string) ($_POST['closed_at'] ?? ''));
            $closedAt = $closedAtRaw !== '' ? str_replace('T', ' ', $closedAtRaw) : null;
            $openedAt = str_replace('T', ' ', $openedAt);

            $verificationStatus = strtolower(trim((string) ($_POST['verification_status'] ?? 'self_reported')));
            $verificationNote = trim((string) ($_POST['verification_evidence_note'] ?? ''));
            $notes = trim((string) ($_POST['notes'] ?? ''));

            // Enforce rule: Never describe data as independently verified unless evidence is supplied
            if ($verificationStatus === 'independently_verified' && $verificationNote === '') {
                $verificationStatus = 'self_reported';
                set_flash('warning', 'Verification status was set to Self-Reported because no verification evidence note was provided.');
            }

            if (!in_array($direction, ['BUY', 'SELL'], true)) {
                $direction = 'BUY';
            }
            if (!in_array($resultType, ['realized', 'unrealized'], true)) {
                $resultType = 'realized';
            }

            // Determine trade outcome from profit/loss if not explicitly overridden
            if ($profitLoss > 0) {
                $tradeOutcome = 'WIN';
            } elseif ($profitLoss < 0) {
                $tradeOutcome = 'LOSS';
            } else {
                $tradeOutcome = 'BREAKEVEN';
            }

            if ($entryPrice <= 0 || $positionSize <= 0 || $instrument === '') {
                set_flash('danger', 'Please provide a valid instrument, positive entry price, and positive position size.');
                header('Location: performance.php');
                exit;
            }

            if ($tradeId > 0) {
                $stmt = $pdo->prepare("
                    UPDATE trading_records
                    SET instrument                 = :instrument,
                        trade_direction            = :direction,
                        entry_price                = :entry_price,
                        exit_price                 = :exit_price,
                        stop_loss_price            = :stop_loss,
                        take_profit_price          = :take_profit,
                        position_size              = :position_size,
                        profit_loss                = :profit_loss,
                        account_currency           = :currency,
                        result_type                = :result_type,
                        trade_outcome              = :outcome,
                        risk_reward_ratio          = :rr,
                        setup_type                 = :setup,
                        opened_at                  = :opened_at,
                        closed_at                  = :closed_at,
                        verification_status        = :ver_status,
                        verification_evidence_note = :ver_note,
                        notes                      = :notes
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':instrument'    => $instrument,
                    ':direction'     => $direction,
                    ':entry_price'   => $entryPrice,
                    ':exit_price'    => $exitPrice,
                    ':stop_loss'     => $stopLossPrice,
                    ':take_profit'   => $takeProfitPrice,
                    ':position_size' => $positionSize,
                    ':profit_loss'   => $profitLoss,
                    ':currency'      => $accountCurrency,
                    ':result_type'   => $resultType,
                    ':outcome'       => $tradeOutcome,
                    ':rr'            => $rrRatio,
                    ':setup'         => $setupType !== '' ? $setupType : null,
                    ':opened_at'     => $openedAt,
                    ':closed_at'     => $closedAt,
                    ':ver_status'    => $verificationStatus,
                    ':ver_note'      => $verificationNote !== '' ? $verificationNote : null,
                    ':notes'         => $notes !== '' ? $notes : null,
                    ':id'            => $tradeId,
                ]);
                set_flash('success', 'Trading record #' . $tradeId . ' updated.');
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO trading_records (
                        instrument, trade_direction, entry_price, exit_price,
                        stop_loss_price, take_profit_price, position_size, profit_loss,
                        account_currency, result_type, trade_outcome, risk_reward_ratio,
                        setup_type, opened_at, closed_at, verification_status,
                        verification_evidence_note, notes, created_by
                    ) VALUES (
                        :instrument, :direction, :entry_price, :exit_price,
                        :stop_loss, :take_profit, :position_size, :profit_loss,
                        :currency, :result_type, :outcome, :rr,
                        :setup, :opened_at, :closed_at, :ver_status,
                        :ver_note, :notes, :created_by
                    )
                ");
                $stmt->execute([
                    ':instrument'    => $instrument,
                    ':direction'     => $direction,
                    ':entry_price'   => $entryPrice,
                    ':exit_price'    => $exitPrice,
                    ':stop_loss'     => $stopLossPrice,
                    ':take_profit'   => $takeProfitPrice,
                    ':position_size' => $positionSize,
                    ':profit_loss'   => $profitLoss,
                    ':currency'      => $accountCurrency,
                    ':result_type'   => $resultType,
                    ':outcome'       => $tradeOutcome,
                    ':rr'            => $rrRatio,
                    ':setup'         => $setupType !== '' ? $setupType : null,
                    ':opened_at'     => $openedAt,
                    ':closed_at'     => $closedAt,
                    ':ver_status'    => $verificationStatus,
                    ':ver_note'      => $verificationNote !== '' ? $verificationNote : null,
                    ':notes'         => $notes !== '' ? $notes : null,
                    ':created_by'    => $adminUser['id'] ?: null,
                ]);
                set_flash('success', 'New trading record logged.');
            }

            header('Location: performance.php');
            exit;
        }
    } catch (Throwable $e) {
        error_log('[Patrick Grey Portfolio] Admin performance error: ' . $e->getMessage());
        set_flash('danger', 'Database error while saving trade record.');
        header('Location: performance.php');
        exit;
    }
}

$editingTrade = null;
if (isset($_GET['edit']) && $pdo) {
    $editId = (int) $_GET['edit'];
    $stmtEdit = $pdo->prepare('SELECT * FROM trading_records WHERE id = :id LIMIT 1');
    $stmtEdit->execute([':id' => $editId]);
    $editingTrade = $stmtEdit->fetch() ?: null;
}

$perfData = get_performance_dashboard_data($pdo);
$records = $perfData['records'] ?? [];
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-white mb-1">Manage Trading Performance Records</h1>
        <p class="small text-secondary mb-0">
            Log documented trades to dynamically update the public Performance Dashboard and Chart.js equity curve.
        </p>
    </div>
    <a href="../performance.php" target="_blank" class="btn btn-sm pg-btn-outline">View Public Performance Page &nearr;</a>
</div>

<!-- Add / Edit Trade Form -->
<section class="pg-card mb-4">
    <h2 class="h5 fw-bold text-white mb-3">
        <?= $editingTrade ? 'Edit Trade Record #' . (int) $editingTrade['id'] : 'Log a Documented Trade Record' ?>
    </h2>

    <form method="post" action="performance.php">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="save_trade">
        <input type="hidden" name="trade_id" value="<?= (int) ($editingTrade['id'] ?? 0) ?>">

        <div class="row g-3">
            <div class="col-sm-6 col-md-3">
                <label for="instrument" class="pg-form-label">Instrument / Pair <span class="text-danger">*</span></label>
                <input type="text" id="instrument" name="instrument" class="form-control pg-form-control font-mono"
                       value="<?= e($editingTrade['instrument'] ?? 'EUR/USD') ?>" placeholder="EUR/USD" required>
            </div>

            <div class="col-sm-6 col-md-2">
                <label for="trade_direction" class="pg-form-label">Direction</label>
                <select id="trade_direction" name="trade_direction" class="form-select pg-form-select font-mono">
                    <option value="BUY" <?= (($editingTrade['trade_direction'] ?? 'BUY') === 'BUY') ? 'selected' : '' ?>>BUY (Long)</option>
                    <option value="SELL" <?= (($editingTrade['trade_direction'] ?? '') === 'SELL') ? 'selected' : '' ?>>SELL (Short)</option>
                </select>
            </div>

            <div class="col-sm-6 col-md-2">
                <label for="position_size" class="pg-form-label">Position Size (Lots) <span class="text-danger">*</span></label>
                <input type="number" id="position_size" name="position_size" step="0.01" min="0.01"
                       class="form-control pg-form-control font-mono"
                       value="<?= e($editingTrade['position_size'] ?? '0.05') ?>" required>
            </div>

            <div class="col-sm-6 col-md-3">
                <label for="profit_loss" class="pg-form-label">Net Profit / Loss <span class="text-danger">*</span></label>
                <input type="number" id="profit_loss" name="profit_loss" step="0.01"
                       class="form-control pg-form-control font-mono"
                       value="<?= e($editingTrade['profit_loss'] ?? '0.00') ?>" required>
            </div>

            <div class="col-sm-6 col-md-2">
                <label for="account_currency" class="pg-form-label">Currency</label>
                <input type="text" id="account_currency" name="account_currency" class="form-control pg-form-control font-mono"
                       value="<?= e($editingTrade['account_currency'] ?? 'USD') ?>" required>
            </div>

            <div class="col-sm-6 col-md-3">
                <label for="entry_price" class="pg-form-label">Entry Price <span class="text-danger">*</span></label>
                <input type="number" id="entry_price" name="entry_price" step="any"
                       class="form-control pg-form-control font-mono"
                       value="<?= e($editingTrade['entry_price'] ?? '') ?>" required>
            </div>

            <div class="col-sm-6 col-md-3">
                <label for="exit_price" class="pg-form-label">Exit Price</label>
                <input type="number" id="exit_price" name="exit_price" step="any"
                       class="form-control pg-form-control font-mono"
                       value="<?= e($editingTrade['exit_price'] ?? '') ?>">
            </div>

            <div class="col-sm-6 col-md-3">
                <label for="stop_loss_price" class="pg-form-label">Stop-Loss Price</label>
                <input type="number" id="stop_loss_price" name="stop_loss_price" step="any"
                       class="form-control pg-form-control font-mono"
                       value="<?= e($editingTrade['stop_loss_price'] ?? '') ?>">
            </div>

            <div class="col-sm-6 col-md-3">
                <label for="take_profit_price" class="pg-form-label">Take-Profit Price</label>
                <input type="number" id="take_profit_price" name="take_profit_price" step="any"
                       class="form-control pg-form-control font-mono"
                       value="<?= e($editingTrade['take_profit_price'] ?? '') ?>">
            </div>

            <div class="col-sm-6 col-md-3">
                <label for="opened_at" class="pg-form-label">Opened Date/Time <span class="text-danger">*</span></label>
                <input type="text" id="opened_at" name="opened_at" class="form-control pg-form-control font-mono"
                       value="<?= e($editingTrade['opened_at'] ?? date('Y-m-d H:i:s')) ?>" placeholder="YYYY-MM-DD HH:MM:SS" required>
            </div>

            <div class="col-sm-6 col-md-3">
                <label for="closed_at" class="pg-form-label">Closed Date/Time</label>
                <input type="text" id="closed_at" name="closed_at" class="form-control pg-form-control font-mono"
                       value="<?= e($editingTrade['closed_at'] ?? date('Y-m-d H:i:s')) ?>" placeholder="YYYY-MM-DD HH:MM:SS">
            </div>

            <div class="col-sm-6 col-md-2">
                <label for="result_type" class="pg-form-label">Result Type</label>
                <select id="result_type" name="result_type" class="form-select pg-form-select">
                    <option value="realized" <?= (($editingTrade['result_type'] ?? 'realized') === 'realized') ? 'selected' : '' ?>>Realized (Closed)</option>
                    <option value="unrealized" <?= (($editingTrade['result_type'] ?? '') === 'unrealized') ? 'selected' : '' ?>>Unrealized (Open)</option>
                </select>
            </div>

            <div class="col-sm-6 col-md-2">
                <label for="risk_reward_ratio" class="pg-form-label">Planned R:R (1:X)</label>
                <input type="number" id="risk_reward_ratio" name="risk_reward_ratio" step="0.01"
                       class="form-control pg-form-control font-mono"
                       value="<?= e($editingTrade['risk_reward_ratio'] ?? '') ?>" placeholder="2.00">
            </div>

            <div class="col-sm-6 col-md-2">
                <label for="setup_type" class="pg-form-label">Price Action Setup</label>
                <input type="text" id="setup_type" name="setup_type" class="form-control pg-form-control"
                       value="<?= e($editingTrade['setup_type'] ?? '') ?>" placeholder="SR Break & Retest">
            </div>

            <div class="col-md-5">
                <label for="verification_status" class="pg-form-label">Verification Classification</label>
                <select id="verification_status" name="verification_status" class="form-select pg-form-select">
                    <option value="self_reported" <?= (($editingTrade['verification_status'] ?? 'self_reported') === 'self_reported') ? 'selected' : '' ?>>
                        Self-Reported (Default — Not Independently Audited)
                    </option>
                    <option value="independently_verified" <?= (($editingTrade['verification_status'] ?? '') === 'independently_verified') ? 'selected' : '' ?>>
                        Independently Verified (Requires Evidence Note)
                    </option>
                </select>
            </div>

            <div class="col-md-7">
                <label for="verification_evidence_note" class="pg-form-label">Verification Evidence Note (Required if Independently Verified)</label>
                <input type="text" id="verification_evidence_note" name="verification_evidence_note"
                       class="form-control pg-form-control"
                       value="<?= e($editingTrade['verification_evidence_note'] ?? '') ?>"
                       placeholder="e.g., Broker statement reference or third-party audit link">
            </div>

            <div class="col-12">
                <label for="notes" class="pg-form-label">Trade Execution &amp; Journal Notes</label>
                <textarea id="notes" name="notes" rows="2" class="form-control pg-form-control"
                          placeholder="Document market structure context, stop-loss placement rationale, and post-trade review..."><?= e($editingTrade['notes'] ?? '') ?></textarea>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn pg-btn-primary">
                    <?= $editingTrade ? 'Update Trade Record' : 'Save Trade Record' ?>
                </button>
                <?php if ($editingTrade): ?>
                    <a href="performance.php" class="btn pg-btn-outline">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</section>

<!-- Existing Trade Records Table -->
<section class="pg-card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 class="h6 fw-bold text-white mb-0">Logged Trading Records (<?= count($records) ?>)</h2>
        <input type="search" class="form-control form-control-sm pg-form-control" style="max-width: 260px;"
               placeholder="Search pair, setup, or date..." data-table-search="#adminTradesTable" aria-label="Search trading records">
    </div>

    <?php if (empty($records)): ?>
        <p class="small text-secondary text-center py-4 mb-0">
            No trading records have been added yet. The public performance section currently displays the honest empty-state notice.
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table pg-table align-middle mb-0" id="adminTradesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Instrument</th>
                        <th>Dir</th>
                        <th>Entry / Exit</th>
                        <th>Lots</th>
                        <th>Type</th>
                        <th>Verification</th>
                        <th class="text-end">P/L</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $rec): ?>
                        <?php $pl = (float) $rec['profit_loss']; ?>
                        <tr>
                            <td class="font-mono small">#<?= (int) $rec['id'] ?></td>
                            <td class="font-mono small"><?= e(date('Y-m-d', strtotime((string) ($rec['closed_at'] ?: $rec['opened_at'])))) ?></td>
                            <td class="fw-bold"><?= e($rec['instrument']) ?></td>
                            <td><span class="badge <?= $rec['trade_direction'] === 'BUY' ? 'pg-badge-emerald' : 'pg-badge-amber' ?>"><?= e($rec['trade_direction']) ?></span></td>
                            <td class="font-mono small"><?= e($rec['entry_price']) ?> &rarr; <?= e($rec['exit_price'] ?? 'Open') ?></td>
                            <td class="font-mono small"><?= e($rec['position_size']) ?></td>
                            <td class="small text-capitalize"><?= e($rec['result_type']) ?></td>
                            <td class="small"><?= e($rec['verification_status']) ?></td>
                            <td class="text-end font-mono fw-bold <?= $pl >= 0 ? 'text-emerald' : 'text-loss' ?>">
                                <?= ($pl >= 0 ? '+' : '') . e(number_format($pl, 2)) . ' ' . e($rec['account_currency']) ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="performance.php?edit=<?= (int) $rec['id'] ?>" class="btn btn-sm pg-btn-outline">Edit</a>
                                    <form method="post" action="performance.php" class="d-inline" data-confirm="Delete trade record #<?= (int) $rec['id'] ?> permanently?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="form_action" value="delete_trade">
                                        <input type="hidden" name="trade_id" value="<?= (int) $rec['id'] ?>">
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
