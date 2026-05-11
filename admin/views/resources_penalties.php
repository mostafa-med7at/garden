<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>⚠️ Penalties</h1>
    <p>Late tool returns incur £<?= LATE_FINE_PER_DAY ?>/day or <?= LATE_SERVICE_HOURS_PER_DAY ?>h community service per day late. Admins may also issue manual penalties.</p>
</div>
<div class="page-actions">
    <a href="tools.php" class="btn btn-secondary">← Tools</a>
    <?php if ($user['role_name'] === 'admin'): ?>
        <button class="btn btn-danger" onclick="toggleSection('issue-form')">⚠️ Issue Manual Penalty</button>
    <?php endif; ?>
</div>

<?php if ($user['role_name'] === 'admin'): ?>
<!-- Manual Penalty Form -->
<div id="issue-form" style="display:none" class="card mb-2">
    <div class="card-header">Issue Manual Penalty</div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Member <span style="color:var(--coral)">*</span></label>
                    <select name="target_user_id" class="form-control" required>
                        <option value="">— Select member —</option>
                        <?php foreach ($activeUsers as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= e($u['full_name']) ?> (<?= e($u['role_name']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fine Amount (£)</label>
                    <input type="number" name="fine_amount" class="form-control" min="0" step="0.01" value="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Community Service Hours (alternative)</label>
                    <input type="number" name="service_hours_manual" class="form-control" min="0" step="0.5" value="0" placeholder="0">
                </div>
            </div>
            <div class="form-group">
                <label>Reason <span style="color:var(--coral)">*</span></label>
                <input type="text" name="reason" class="form-control" required placeholder="e.g. Plot compliance violation, unsafe behaviour, missed service hours...">
            </div>
            <button name="issue_penalty" value="1" class="btn btn-danger">Issue Penalty</button>
            <button type="button" class="btn btn-secondary" onclick="toggleSection('issue-form')">Cancel</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Penalties Table -->
<div class="card">
    <div class="card-header">All Penalties (<?= count($penalties) ?>)</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Source</th>
                    <th>Tool / Reason</th>
                    <th>Days Late</th>
                    <th>Fine</th>
                    <th>Service hrs</th>
                    <th>Status</th>
                    <?= $user['role_name'] === 'admin' ? '<th>Action</th>' : '' ?>
                </tr>
            </thead>
            <tbody>
            <?php if ($penalties): foreach ($penalties as $p): ?>
            <tr>
                <td>
                    <strong><?= e($p['full_name']) ?></strong>
                    <?php if (!empty($p['email'])): ?>
                        <br><span class="text-sm text-muted"><?= e($p['email']) ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (($p['source'] ?? 'automatic') === 'manual'): ?>
                        <span class="badge badge-danger">Manual</span>
                    <?php else: ?>
                        <span class="badge badge-info">Auto</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($p['tool_name'])): ?>
                        🔧 <?= e($p['tool_name']) ?>
                        <?php if (!empty($p['due_date'])): ?>
                            <br><span class="text-sm text-muted">Due: <?= date('d M Y', strtotime($p['due_date'])) ?></span>
                        <?php endif; ?>
                    <?php elseif (!empty($p['reason'])): ?>
                        📝 <?= e($p['reason']) ?>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($p['days_late'] > 0): ?>
                        <span class="badge badge-danger"><?= $p['days_late'] ?> days</span>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td><?= $p['fine_amount'] > 0 ? '£' . number_format($p['fine_amount'], 2) : '—' ?></td>
                <td><?= $p['service_hours'] > 0 ? number_format($p['service_hours'], 1) . 'h' : '—' ?></td>
                <td>
                    <span class="badge badge-<?= ['pending'=>'warning','paid'=>'success','served'=>'success','waived'=>'secondary'][$p['status']] ?? 'secondary' ?>">
                        <?= e($p['status']) ?>
                    </span>
                </td>
                <?php if ($user['role_name'] === 'admin'): ?>
                <td>
                    <?php if ($p['status'] === 'pending'): ?>
                    <form method="POST" style="display:flex;gap:4px;align-items:center;flex-wrap:wrap">
                        <input type="hidden" name="penalty_id" value="<?= $p['id'] ?>">
                        <select name="penalty_type" class="form-control" style="width:auto;height:30px;font-size:13px">
                            <option value="fine">Collect Fine<?= $p['fine_amount'] > 0 ? ' (£' . number_format($p['fine_amount'],2) . ')' : '' ?></option>
                            <option value="community_service_hours">Community Service<?= $p['service_hours'] > 0 ? ' (' . $p['service_hours'] . 'h)' : '' ?></option>
                        </select>
                        <input type="hidden" name="service_hours" value="<?= $p['service_hours'] ?>">
                        <button name="resolve" value="1" class="btn btn-sm btn-primary">Resolve</button>
                        <button name="resolve" value="1" class="btn btn-sm btn-outline-secondary"
                                onclick="this.previousElementSibling.previousElementSibling.value='waived'"
                                formaction="">Waive</button>
                    </form>
                    <?php else: ?>
                        <span class="text-muted text-sm">—</span>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--gray-600)">No penalties on record.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleSection(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = el.style.display === 'none' ? '' : 'none';
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
