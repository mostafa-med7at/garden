<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>📅 Shift Schedule</h1>
    <p>View your assigned shifts and request swaps if you can't attend.</p>
</div>

<div class="page-actions">
    <?php if ($user['role_name']==='admin'): ?>
    <button class="btn btn-primary" onclick="toggleSection('create-shift')">+ Create Shift</button>
    <?php endif; ?>
    <a href="tasks.php" class="btn btn-secondary">← Tasks</a>
</div>

<!-- Incoming swap requests -->
<?php if ($mySwapReqs): ?>
<div class="card mb-2" style="border-color:var(--amber)">
    <div class="card-header" style="background:#fff9e6">📨 Incoming Swap Requests (<?= count($mySwapReqs) ?>)</div>
    <div class="card-body">
        <?php foreach ($mySwapReqs as $req): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 0;border-bottom:1px solid var(--gray-200)">
            <div>
                <strong><?= e($req['requester']) ?></strong> wants to swap their shift:
                <strong><?= e($req['shift_title']) ?></strong> on <?= date('d M Y', strtotime($req['shift_date'])) ?>
                <br><span class="text-sm text-muted">Expires: <?= date('d M Y H:i', strtotime($req['expires_at'])) ?></span>
            </div>
            <form method="POST" style="display:flex;gap:.5rem">
                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                <button name="respond_swap" value="1" class="btn btn-primary btn-sm" onclick="this.form.elements.response.value='accepted'">✓ Accept</button>
                <button name="respond_swap" value="1" class="btn btn-secondary btn-sm" onclick="this.form.elements.response.value='rejected'">✗ Decline</button>
                <input type="hidden" name="response" value="">
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Create shift form (admin) -->
<div id="create-shift" style="display:none" class="card mb-2">
    <div class="card-header">Create Shift</div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Shift title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Morning weeding crew" required>
                </div>
                <div class="form-group">
                    <label>Assign to member</label>
                    <select name="assigned_to" class="form-control" required>
                        <?php foreach ($members as $m): ?><option value="<?= $m['id'] ?>"><?= e($m['full_name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Date</label><input type="date" name="shift_date" class="form-control" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required></div>
                <div class="form-group"><label>Start time</label><input type="time" name="start_time" class="form-control" value="09:00" required></div>
                <div class="form-group"><label>End time</label><input type="time" name="end_time" class="form-control" value="12:00" required></div>
            </div>
            <button name="create_shift" value="1" class="btn btn-primary">Create Shift</button>
        </form>
    </div>
</div>

<!-- Shifts table -->
<div class="card">
    <div class="card-header">All Shifts</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Shift</th><th>Date</th><th>Time</th><th>Assigned to</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($shifts as $s): ?>
            <tr <?= $s['assigned_to']==$user['id']?'style="background:var(--green-pale)"':'' ?>>
                <td><strong><?= e($s['title']) ?></strong></td>
                <td><?= date('d M Y', strtotime($s['shift_date'])) ?></td>
                <td><?= e($s['start_time']) ?> – <?= e($s['end_time']) ?></td>
                <td><?= e($s['assignee']) ?> <?= $s['assigned_to']==$user['id']?'<span class="badge badge-success">You</span>':'' ?></td>
                <td><span class="badge badge-<?= ['scheduled'=>'info','completed'=>'success','cancelled'=>'secondary','swapped'=>'warning'][$s['status']]??'secondary' ?>"><?= e($s['status']) ?></span></td>
                <td>
                    <?php if ($s['assigned_to']==$user['id'] && $s['status']==='scheduled' && strtotime($s['shift_date'])>=strtotime('today')): ?>
                    <button class="btn btn-sm btn-warning" onclick="toggleSection('swap-<?= $s['id'] ?>')">Request Swap</button>
                    <div id="swap-<?= $s['id'] ?>" style="display:none;margin-top:.5rem">
                        <form method="POST">
                            <input type="hidden" name="shift_id" value="<?= $s['id'] ?>">
                            <select name="target_member" class="form-control" style="margin-bottom:4px">
                                <?php foreach ($members as $m): if ($m['id']==$user['id']) continue; ?>
                                    <option value="<?= $m['id'] ?>"><?= e($m['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button name="request_swap" value="1" class="btn btn-sm btn-primary">Send Swap Request</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>function toggleSection(id){var el=document.getElementById(id);if(el)el.style.display=el.style.display==='none'?'':'none';}</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
