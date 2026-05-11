<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>🐛 Pest, Disease & Compliance</h1>
    <p>Report infestations and record plot inspections. Transmissible pests trigger automatic neighbor alerts.</p>
</div>

<!-- Pest Report Form -->
<div class="card mb-2">
    <div class="card-header">Report Pest / Disease Infestation</div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Affected plot</label>
                    <select name="plot_id" class="form-control" required>
                        <?php foreach ($allPlots as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($myPlot && $myPlot['id']==$p['id'])? 'selected':'' ?>>
                                <?= e($p['plot_code']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pest / disease name</label>
                    <input type="text" name="pest_type" class="form-control" required placeholder="e.g. Potato Blight, Aphids">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Severity</label>
                    <select name="severity" class="form-control">
                        <option value="low">Low — isolated, not spreading</option>
                        <option value="medium" selected>Medium — active infestation</option>
                        <option value="high">High — severe, rapid spread</option>
                    </select>
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:.5rem">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-weight:normal">
                        <input type="checkbox" name="is_transmissible" value="1">
                        <strong>Highly transmissible?</strong> (will alert neighboring plots)
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Describe symptoms, affected area..."></textarea>
            </div>
            <button type="submit" name="report_pest" value="1" class="btn btn-danger">Submit Report</button>
        </form>
    </div>
</div>

<!-- Warden Inspection Form (Fn 7) -->
<?php if (in_array($user['role_name'],['admin','warden'])): ?>
<div class="card mb-2">
    <div class="card-header">📋 Record Plot Inspection (Warden Only)</div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Plot to inspect</label>
                    <select name="insp_plot_id" class="form-control" required>
                        <?php foreach ($allPlots as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= e($p['plot_code']) ?> (<?= e($p['compliance_status']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Inspection result</label>
                    <select name="insp_result" class="form-control" required>
                        <option value="pass">Pass — compliant</option>
                        <option value="warning">Warning — minor issues</option>
                        <option value="fail">Fail — violation found</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Inspection notes</label>
                <textarea name="insp_notes" class="form-control" rows="3" required placeholder="Describe what was observed..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Violation details (if any)</label>
                    <input type="text" name="violation_details" class="form-control" placeholder="e.g. Overgrown weeds, abandoned equipment">
                </div>
                <div class="form-group">
                    <label>Penalty applied (£)</label>
                    <input type="number" name="penalty_applied" class="form-control" step="0.01" min="0" value="0">
                </div>
            </div>
            <div class="form-group">
                <label>Upload photos (optional)</label>
                <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
            </div>
            <button type="submit" name="submit_inspection" value="1" class="btn btn-warning">Save Inspection Record</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Pest Reports Table -->
<div class="card mb-2">
    <div class="card-header">Recent Pest/Disease Reports</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Plot</th><th>Pest/Disease</th><th>Severity</th><th>Transmissible?</th><th>Reported by</th><th>Date</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($reports as $r): ?>
            <tr>
                <td><?= e($r['plot_code']) ?></td>
                <td><?= e($r['pest_type']) ?></td>
                <td><span class="badge badge-<?= ['low'=>'info','medium'=>'warning','high'=>'danger'][$r['severity']]?:'secondary' ?>"><?= e($r['severity']) ?></span></td>
                <td><?= $r['is_transmissible'] ? '<span class="badge badge-danger">Yes ⚠️</span>' : 'No' ?></td>
                <td><?= e($r['full_name']) ?></td>
                <td><?= date('d M Y', strtotime($r['reported_at'])) ?></td>
                <td><span class="badge badge-<?= ['open'=>'warning','investigating'=>'info','resolved'=>'success'][$r['status']]?:'secondary' ?>"><?= e($r['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Inspections Table -->
<div class="card">
    <div class="card-header">Inspection Records</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Plot</th><th>Warden</th><th>Result</th><th>Notes</th><th>Penalty</th><th>Date</th></tr>
            </thead>
            <tbody>
            <?php foreach ($inspections as $ins): ?>
            <tr>
                <td><?= e($ins['plot_code']) ?></td>
                <td><?= e($ins['warden_name']) ?></td>
                <td><span class="badge badge-<?= ['pass'=>'success','warning'=>'warning','fail'=>'danger'][$ins['result']]?:'secondary' ?>"><?= e($ins['result']) ?></span></td>
                <td><?= e(substr($ins['notes'],0,60)) ?><?= strlen($ins['notes'])>60?'...':'' ?></td>
                <td><?= $ins['penalty_applied']>0 ? '£'.number_format($ins['penalty_applied'],2) : '—' ?></td>
                <td><?= date('d M Y', strtotime($ins['inspected_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
