<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>📄 Lease Management</h1>
    <p>View, renew, and manage plot leases. The system auto-expires unpaid leases.</p>
</div>

<div class="page-actions">
    <?php if ($user['role_name'] === 'admin'): ?>
    <form method="POST" style="display:inline">
        <button name="run_expiry" value="1" class="btn btn-warning"
                data-confirm="Mark all overdue leases as expired?">⚙️ Run Expiry Check</button>
    </form>
    <a href="lease_create.php" class="btn btn-primary">+ New Lease</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">Leases (<?= count($leases) ?>)</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Plot</th><th>Member</th><th>Start</th><th>Expires</th><th>Days Left</th><th>Fee/yr</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($leases as $l):
                $fee = calculateRentalFee($l['area_sqm'], $l['soil_quality'], $user['membership']);
                $daysLeft = (int)$l['days_left'];
                $urgent   = $daysLeft >= 0 && $daysLeft <= 30;
            ?>
            <tr>
                <td><strong><?= e($l['plot_code']) ?></strong></td>
                <td><?= e($l['full_name']) ?><br><small class="text-muted"><?= e($l['email']) ?></small></td>
                <td><?= date('d M Y', strtotime($l['start_date'])) ?></td>
                <td><?= date('d M Y', strtotime($l['end_date'])) ?></td>
                <td>
                    <?php if ($daysLeft < 0): ?>
                        <span class="badge badge-danger">Overdue</span>
                    <?php elseif ($urgent): ?>
                        <span class="badge badge-warning">⚠️ <?= $daysLeft ?>d</span>
                    <?php else: ?>
                        <?= $daysLeft ?> days
                    <?php endif; ?>
                </td>
                <td>£<?= number_format($fee['total_fee'], 2) ?></td>
                <td>
                    <?php $sb=['active'=>'success','expired'=>'danger','terminated'=>'secondary','grace_period'=>'warning'][$l['status']]??'secondary'; ?>
                    <span class="badge badge-<?= $sb ?>"><?= e($l['status']) ?></span>
                </td>
                <td style="white-space:nowrap">
                    <?php if ($l['status']==='active' && ($l['user_id']==$user['id'] || $user['role_name']==='admin')): ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="lease_id" value="<?= $l['id'] ?>">
                        <select name="payment_method" class="form-control" style="display:inline;width:auto;height:32px;font-size:13px;padding:2px 6px">
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank</option>
                            <option value="cash">Cash</option>
                        </select>
                        <button name="renew" value="1" class="btn btn-sm btn-primary">Renew</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($user['role_name']==='admin' && $l['status']==='active'): ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="lease_id" value="<?= $l['id'] ?>">
                        <button name="terminate" value="1" class="btn btn-sm btn-danger"
                                data-confirm="Terminate this lease? The plot will be freed.">Terminate</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
