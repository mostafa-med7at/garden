<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>🔐 Security & Incident Reports</h1>
    <p>Gate access simulation and safety incident management.</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">

<!-- Gate access simulator (Fn 22) -->
<div class="card">
    <div class="card-header">🚪 Gate Access Simulator</div>
    <div class="card-body">
        <p class="text-sm text-muted mb-2">In a real deployment this would be a physical keypad. Simulate entry/exit here.</p>
        <form method="POST">
            <div class="form-group">
                <label>Enter gate code</label>
                <input type="text" name="gate_code" class="form-control" placeholder="e.g. GATE001" required style="font-family:monospace;letter-spacing:2px">
            </div>
            <div class="form-group">
                <label>Action</label>
                <select name="access_type" class="form-control">
                    <option value="entry">Entry →</option>
                    <option value="exit">← Exit</option>
                </select>
            </div>
            <button name="gate_access" value="1" class="btn btn-primary btn-block">Simulate Access</button>
        </form>
        <p class="text-sm text-muted mt-2"><strong>Demo codes:</strong> GATE001, GATE002, GATE003, GATE004</p>
    </div>
</div>

<!-- Report incident (Fn 23) -->
<div class="card">
    <div class="card-header">⚠️ Report Incident / Hazard</div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. Broken glass on Path B">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2" required placeholder="What happened? What did you see?"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" placeholder="e.g. Near plot A-01">
                </div>
                <div class="form-group">
                    <label>Severity</label>
                    <select name="severity" class="form-control">
                        <option value="low">Low — minor, not urgent</option>
                        <option value="medium" selected>Medium — needs attention</option>
                        <option value="high">High — serious hazard</option>
                        <option value="critical">🚨 CRITICAL — immediate danger</option>
                    </select>
                </div>
            </div>
            <button name="report_incident" value="1" class="btn btn-danger btn-block">Submit Report</button>
        </form>
    </div>
</div>
</div>

<!-- Incidents table -->
<div class="card mb-2">
    <div class="card-header">Incident Reports</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Location</th><th>Severity</th><th>Reported by</th><th>Date</th><th>Status</th><?= $user['role_name']==='admin'?'<th>Update</th>':'' ?></tr></thead>
            <tbody>
            <?php foreach ($incidents as $inc): ?>
            <tr <?= $inc['severity']==='critical'&&$inc['status']!=='resolved'?'style="background:#fde8e8"':'' ?>>
                <td><strong><?= e($inc['title']) ?></strong><br><span class="text-sm text-muted"><?= e(substr($inc['description'],0,60)) ?></span></td>
                <td><?= e($inc['location'] ?: '—') ?></td>
                <td><span class="badge badge-<?= ['low'=>'info','medium'=>'warning','high'=>'danger','critical'=>'danger'][$inc['severity']]??'secondary' ?>"><?= $inc['severity']==='critical'?'🚨 CRITICAL':e($inc['severity']) ?></span></td>
                <td><?= e($inc['reporter']) ?></td>
                <td><?= date('d M Y H:i', strtotime($inc['reported_at'])) ?></td>
                <td><span class="badge badge-<?= ['open'=>'warning','in_process'=>'info','resolved'=>'success'][$inc['status']]??'secondary' ?>"><?= e(str_replace('_',' ',$inc['status'])) ?></span></td>
                <?php if ($user['role_name']==='admin'): ?>
                <td>
                    <?php if ($inc['status']!=='resolved'): ?>
                    <form method="POST" style="display:flex;gap:4px">
                        <input type="hidden" name="incident_id" value="<?= $inc['id'] ?>">
                        <select name="new_status" class="form-control" style="width:auto;height:30px;font-size:13px">
                            <option value="in_process">In Process</option>
                            <option value="resolved">Resolved</option>
                        </select>
                        <button name="update_incident" value="1" class="btn btn-sm btn-primary">Update</button>
                    </form>
                    <?php else: echo '<span class="text-muted text-sm">Resolved</span>'; endif; ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Access log (admin only) -->
<?php if ($user['role_name']==='admin'): ?>
<div class="card">
    <div class="card-header">🚪 Gate Access Log (Last 30)</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date/Time</th><th>Code Entered</th><th>Member</th><th>Type</th><th>Result</th></tr></thead>
            <tbody>
            <?php foreach ($accessLogs as $log): ?>
            <tr <?= !$log['is_valid']?'style="background:#fde8e8"':'' ?>>
                <td><?= date('d M Y H:i:s', strtotime($log['accessed_at'])) ?></td>
                <td><code><?= e($log['gate_code_entered']) ?></code></td>
                <td><?= e($log['full_name'] ?? '—') ?></td>
                <td><?= e($log['access_type']) ?></td>
                <td><span class="badge badge-<?= $log['is_valid']?'success':'danger' ?>"><?= $log['is_valid']?'✅ Granted':'❌ Denied' ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
