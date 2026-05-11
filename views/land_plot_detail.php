<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>🌿 Plot <?= e($plot['plot_code']) ?></h1>
    <p>Full details, history, and management for this allotment plot.</p>
</div>

<div class="page-actions">
    <a href="plots.php" class="btn btn-secondary">← Back to Map</a>
    <?php if ($plot['status']==='available' && hasPermission('land','create')): ?>
        <a href="lease_create.php?plot_id=<?= $id ?>" class="btn btn-primary">🌱 Rent This Plot</a>
    <?php endif; ?>
    <?php if (in_array($user['role_name'],['admin','warden'])): ?>
        <a href="pest_report.php" class="btn btn-warning">📋 Inspect Plot</a>
    <?php endif; ?>
    <button id="ajaxPingBtn" class="btn btn-info" onclick="pingSensor(<?= $id ?>)">📡 Ping Sensor (AJAX)</button>
    <span id="ajaxPingResult" class="ms-2 fw-bold text-success"></span>
</div>

<script>
function pingSensor(plotId) {
    const btn = document.getElementById('ajaxPingBtn');
    const res = document.getElementById('ajaxPingResult');
    btn.disabled = true;
    res.textContent = 'Pinging...';
    
    fetch('ping_sensor.php?id=' + plotId)
        .then(response => response.json())
        .then(data => {
            res.textContent = data.message;
            setTimeout(() => { res.textContent = ''; btn.disabled = false; }, 4000);
        })
        .catch(err => {
            res.textContent = 'Ping failed.';
            btn.disabled = false;
        });
}
</script>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
    <div class="card">
        <div class="card-header">Plot Information</div>
        <div class="card-body">
            <table style="width:100%;font-size:14px">
                <tr><td class="text-muted" style="padding:.4rem 0;width:45%">Code</td><td><strong><?= e($plot['plot_code']) ?></strong></td></tr>
                <tr><td class="text-muted" style="padding:.4rem 0">Area</td><td><?= e($plot['area_sqm']) ?> m²</td></tr>
                <tr><td class="text-muted" style="padding:.4rem 0">Sunlight</td><td><?= e($plot['sunlight_level']) ?></td></tr>
                <tr><td class="text-muted" style="padding:.4rem 0">Soil quality</td><td><span class="badge badge-<?= ['premium'=>'success','standard'=>'info','poor'=>'warning'][$plot['soil_quality']]??'secondary' ?>"><?= e($plot['soil_quality']) ?></span></td></tr>
                <tr><td class="text-muted" style="padding:.4rem 0">Status</td><td><span class="badge badge-<?= ['available'=>'success','occupied'=>'danger','maintenance'=>'warning'][$plot['status']]??'secondary' ?>"><?= e($plot['status']) ?></span></td></tr>
                <tr><td class="text-muted" style="padding:.4rem 0">Compliance</td><td><span class="badge badge-<?= ['compliant'=>'success','warning'=>'warning','violation'=>'danger'][$plot['compliance_status']]??'secondary' ?>"><?= e($plot['compliance_status']) ?></span></td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Lease Info</div>
        <div class="card-body">
            <?php if ($plot['owner_name']): ?>
            <table style="width:100%;font-size:14px">
                <tr><td class="text-muted" style="padding:.4rem 0;width:45%">Owner</td><td><strong><?= e($plot['owner_name']) ?></strong></td></tr>
                <tr><td class="text-muted" style="padding:.4rem 0">Lease status</td><td><span class="badge badge-success"><?= e($plot['lease_status']) ?></span></td></tr>
                <tr><td class="text-muted" style="padding:.4rem 0">Expires</td><td><?= date('d M Y', strtotime($plot['end_date'])) ?></td></tr>
                <tr><td class="text-muted" style="padding:.4rem 0">Annual fee</td><td>£<?= number_format((float)$plot['total_fee'],2) ?></td></tr>
            </table>
            <?php else: ?>
                <p class="text-muted">No active lease.</p>
                <?php if ($plot['status']==='available'): ?>
                    <a href="lease_create.php?plot_id=<?= $id ?>" class="btn btn-primary btn-sm mt-1">Rent this plot</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Soil history -->
<div class="card mb-2">
    <div class="card-header">🌱 Recent Soil Events <a href="soil.php?plot_id=<?= $id ?>" class="btn btn-sm btn-secondary" style="float:right">View all</a></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Event</th><th>pH</th><th>Risk</th><th>By</th></tr></thead>
            <tbody>
            <?php if ($soilEvents): foreach ($soilEvents as $se): ?>
            <tr>
                <td><?= date('d M Y', strtotime($se['recorded_at'])) ?></td>
                <td><?= e(str_replace('_',' ',$se['event_type'])) ?></td>
                <td><?= $se['ph_level'] !== null ? $se['ph_level'] : '—' ?></td>
                <td><?= $se['is_at_risk'] ? '<span class="badge badge-danger">⚠️ At risk</span>' : '<span class="badge badge-success">OK</span>' ?></td>
                <td><?= e($se['full_name']) ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-muted" style="text-align:center;padding:1rem">No soil events recorded.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pest reports -->
<div class="card mb-2">
    <div class="card-header">🐛 Recent Pest Reports</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Pest</th><th>Severity</th><th>Transmissible</th><th>Status</th></tr></thead>
            <tbody>
            <?php if ($pestReports): foreach ($pestReports as $pr): ?>
            <tr>
                <td><?= date('d M Y', strtotime($pr['reported_at'])) ?></td>
                <td><?= e($pr['pest_type']) ?></td>
                <td><span class="badge badge-<?= ['low'=>'info','medium'=>'warning','high'=>'danger'][$pr['severity']]??'secondary' ?>"><?= e($pr['severity']) ?></span></td>
                <td><?= $pr['is_transmissible'] ? '⚠️ Yes' : 'No' ?></td>
                <td><span class="badge badge-<?= ['open'=>'warning','resolved'=>'success'][$pr['status']]??'secondary' ?>"><?= e($pr['status']) ?></span></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-muted" style="text-align:center;padding:1rem">No pest reports.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Inspections -->
<div class="card">
    <div class="card-header">📋 Recent Inspections</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Warden</th><th>Result</th><th>Notes</th><th>Penalty</th></tr></thead>
            <tbody>
            <?php if ($inspections): foreach ($inspections as $ins): ?>
            <tr>
                <td><?= date('d M Y', strtotime($ins['inspected_at'])) ?></td>
                <td><?= e($ins['warden']) ?></td>
                <td><span class="badge badge-<?= ['pass'=>'success','warning'=>'warning','fail'=>'danger'][$ins['result']]??'secondary' ?>"><?= e($ins['result']) ?></span></td>
                <td><?= e(substr($ins['notes'],0,60)) ?>...</td>
                <td><?= $ins['penalty_applied']>0 ? '£'.number_format($ins['penalty_applied'],2) : '—' ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-muted" style="text-align:center;padding:1rem">No inspections recorded.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
