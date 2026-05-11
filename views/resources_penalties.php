<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>⚠️ Tool Return Penalties</h1>
    <p>Late returns incur a fine of £<?= LATE_FINE_PER_DAY ?>/day or <?= LATE_SERVICE_HOURS_PER_DAY ?>h community service per day late.</p>
</div>
<div class="page-actions"><a href="tools.php" class="btn btn-secondary">← Tools</a></div>

<div class="card">
    <div class="card-header">Penalties</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Member</th><th>Tool</th><th>Days Late</th><th>Fine</th><th>Service hrs</th><th>Status</th><?= $user['role_name']==='admin'?'<th>Resolve</th>':'' ?></tr></thead>
            <tbody>
            <?php if ($penalties): foreach ($penalties as $p): ?>
            <tr>
                <td><?= e($p['full_name']) ?></td>
                <td><?= e($p['tool_name']) ?></td>
                <td><span class="badge badge-danger"><?= $p['days_late'] ?> days</span></td>
                <td>£<?= number_format($p['fine_amount'],2) ?></td>
                <td><?= number_format($p['service_hours'],1) ?>h</td>
                <td><span class="badge badge-<?= ['pending'=>'warning','paid'=>'success','served'=>'success','waived'=>'secondary'][$p['status']]??'secondary' ?>"><?= e($p['status']) ?></span></td>
                <?php if ($user['role_name']==='admin' && $p['status']==='pending'): ?>
                <td>
                    <form method="POST" style="display:flex;gap:4px;align-items:center;flex-wrap:wrap">
                        <input type="hidden" name="penalty_id" value="<?= $p['id'] ?>">
                        <select name="penalty_type" class="form-control" style="width:auto;height:30px;font-size:13px">
                            <option value="fine">Collect Fine (£<?= number_format($p['fine_amount'],2) ?>)</option>
                            <option value="community_service_hours">Community Service (<?= $p['service_hours'] ?>h)</option>
                        </select>
                        <input type="hidden" name="service_hours" value="<?= $p['service_hours'] ?>">
                        <button name="resolve" value="1" class="btn btn-sm btn-primary">Resolve</button>
                    </form>
                </td>
                <?php elseif ($user['role_name']==='admin'): ?><td>—</td><?php endif; ?>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--gray-600)">No penalties on record.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
