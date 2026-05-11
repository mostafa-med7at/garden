<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>🔧 Tool Library</h1>
    <p>Reserve tools, track their status, report damage, and manage returns.</p>
</div>

<div class="page-actions">
    <?php if ($user['role_name']==='admin'): ?>
    <button class="btn btn-primary" onclick="toggleSection('add-tool-form')">+ Add Tool</button>
    <?php endif; ?>
    <a href="consumables.php" class="btn btn-secondary">📦 Consumables</a>
    <a href="penalties.php" class="btn btn-secondary">⚠️ Penalties</a>
</div>

<!-- My reservations -->
<?php if ($myRes): ?>
<div class="card mb-2">
    <div class="card-header">My Active Reservations</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tool</th><th>Date</th><th>Time</th><th>Due</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($myRes as $r): ?>
            <tr>
                <td><strong><?= e($r['tool_name']) ?></strong></td>
                <td><?= date('d M Y', strtotime($r['slot_date'])) ?></td>
                <td><?= e($r['slot_start']) ?> – <?= e($r['slot_end']) ?></td>
                <td><?= date('d M Y H:i', strtotime($r['due_date'])) ?></td>
                <td style="white-space:nowrap">
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                        <button name="return_tool" value="1" class="btn btn-sm btn-primary"
                                data-confirm="Confirm return of <?= e($r['tool_name']) ?>?">Return Tool</button>
                    </form>
                    <button class="btn btn-sm btn-secondary" onclick="toggleSection('reschedule-form-<?= $r['id'] ?>')">Reschedule</button>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                        <button name="cancel_reservation" value="1" class="btn btn-sm btn-outline-danger"
                                data-confirm="Cancel this reservation?">Cancel</button>
                    </form>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="damage_tool_id" value="<?= $r['tool_id'] ?>">
                        <button class="btn btn-sm btn-danger" onclick="document.getElementById('damage-form-<?= $r['tool_id'] ?>').style.display='block';return false">Report Damage</button>
                    </form>
                </td>
            </tr>
            <!-- Reschedule sub-form -->
            <tr id="reschedule-form-<?= $r['id'] ?>" style="display:none;background:var(--gray-100)">
                <td colspan="5">
                    <form method="POST" style="display:flex;gap:.5rem;padding:.5rem;align-items:flex-end">
                        <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                        <div class="form-group" style="margin:0">
                            <label class="small fw-semibold text-muted">New Date</label>
                            <input type="date" name="slot_date" class="form-control" value="<?= e($r['slot_date']) ?>" required>
                        </div>
                        <div class="form-group" style="margin:0">
                            <label class="small fw-semibold text-muted">Start</label>
                            <input type="time" name="slot_start" class="form-control" value="<?= e($r['slot_start']) ?>" required>
                        </div>
                        <div class="form-group" style="margin:0">
                            <label class="small fw-semibold text-muted">End</label>
                            <input type="time" name="slot_end" class="form-control" value="<?= e($r['slot_end']) ?>" required>
                        </div>
                        <button name="reschedule_reservation" value="1" class="btn btn-warning">Save New Time</button>
                    </form>
                </td>
            </tr>
            <!-- Damage report sub-form -->
            <tr id="damage-form-<?= $r['tool_id'] ?>" style="display:none;background:var(--gray-100)">
                <td colspan="5">
                    <form method="POST" style="display:flex;gap:.5rem;padding:.5rem;align-items:flex-end">
                        <input type="hidden" name="damage_tool_id" value="<?= $r['tool_id'] ?>">
                        <div class="form-group" style="margin:0;flex:1">
                            <input type="text" name="damage_desc" class="form-control" placeholder="Describe the damage..." required>
                        </div>
                        <button name="report_damage" value="1" class="btn btn-danger">Submit Report</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Pending penalties -->
<?php if ($penalties): ?>
<div class="alert alert-warning">
    ⚠️ You have <?= count($penalties) ?> pending penalty(ies).
    <?php foreach ($penalties as $p): ?>
        <strong><?= e($p['tool_name']) ?></strong>: <?= $p['days_late'] ?> days late — Fine: £<?= number_format($p['fine_amount'],2) ?> or <?= $p['service_hours'] ?>h community service.
    <?php endforeach; ?>
    <a href="penalties.php" class="btn btn-sm btn-warning" style="margin-left:.5rem">View Penalties</a>
</div>
<?php endif; ?>

<!-- Add tool form (admin) -->
<div id="add-tool-form" style="display:none" class="card mb-2">
    <div class="card-header">Add Tool to Library</div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Tool name</label>
                    <input type="text" name="tool_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Maintenance threshold (hours)</label>
                    <input type="number" name="threshold" class="form-control" value="50" min="1" required>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="tool_desc" class="form-control">
            </div>
            <div class="form-group">
                <label>Media links (or Upload Image)</label>
                <input type="file" name="tool_image" class="form-control mb-2" accept="image/*">
                <input type="text" name="media_links" class="form-control" placeholder="Or paste YouTube URL...">
            </div>
            <button name="add_tool" value="1" class="btn btn-primary">Add Tool</button>
        </form>
    </div>
</div>

<!-- Tool cards -->
<div class="card">
    <div class="card-header">All Tools (<?= count($tools) ?>)</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Tool</th><th>Status</th><th>Usage hrs</th><th>Maintenance</th><th>Reserve</th><th>State change (admin)</th></tr>
            </thead>
            <tbody>
            <?php foreach ($tools as $t):
                $statusBadge = ['available'=>'success','checked_out'=>'warning','in_repair'=>'info','decommissioned'=>'secondary','missing'=>'danger'][$t['status']]??'secondary';
                $links = $t['media_links'] ? array_filter(array_map('trim', explode(',', $t['media_links']))) : [];
            ?>
            <tr>
                <td>
                    <strong><?= e($t['name']) ?></strong><br>
                    <span class="text-sm text-muted"><?= e($t['description'] ?: '') ?></span>
                    <?php if ($links): ?>
                        <br>
                        <?php foreach ($links as $link): ?>
                            <a href="<?= e($link) ?>" target="_blank" class="btn btn-sm btn-secondary" style="margin-top:3px;font-size:11px">📎 Guide</a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </td>
                <td><span class="badge badge-<?= $statusBadge ?>"><?= e(str_replace('_',' ',$t['status'])) ?></span></td>
                <td>
                    <?= number_format($t['total_usage_hours'],1) ?> / <?= number_format($t['maintenance_threshold_hours'],0) ?>h
                    <div class="progress mt-1" style="width:80px">
                        <?php $pct = min(100, ($t['total_usage_hours']/$t['maintenance_threshold_hours'])*100); ?>
                        <div class="progress-bar <?= $pct>=100?'danger':($pct>=80?'warning':'') ?>" style="width:<?= $pct ?>%"></div>
                    </div>
                </td>
                <td><?= $t['needs_maintenance'] ? '<span class="badge badge-danger">⚠️ Service needed</span>' : '<span class="badge badge-success">OK</span>' ?></td>
                <td>
                    <?php if ($t['status']==='available'): ?>
                    <button class="btn btn-sm btn-primary" onclick="toggleSection('res-<?= $t['id'] ?>')">Reserve</button>
                    <div id="res-<?= $t['id'] ?>" style="display:none;margin-top:.5rem">
                        <form method="POST">
                            <input type="hidden" name="tool_id" value="<?= $t['id'] ?>">
                            <input type="date" name="slot_date" class="form-control" style="margin-bottom:4px" value="<?= date('Y-m-d') ?>" required>
                            <div style="display:flex;gap:4px">
                                <input type="time" name="slot_start" class="form-control" value="09:00" required>
                                <input type="time" name="slot_end"   class="form-control" value="12:00" required>
                            </div>
                            <button name="reserve" value="1" class="btn btn-sm btn-primary" style="margin-top:4px;width:100%">Confirm</button>
                        </form>
                    </div>
                    <?php else:
                        $notAvailLabels = [
                            'checked_out'    => '<span class="badge badge-warning" style="color:black">Checked Out</span>',
                            'in_repair'      => '<span class="badge badge-info" style="color:black">In Repair</span>',
                            'decommissioned' => '<span class="badge badge-secondary" style="color:black">Decommissioned</span>',
                            'missing'        => '<span class="badge badge-danger" style="color:black">⚠️ Missing</span>',
                        ];
                        echo $notAvailLabels[$t['status']] ?? '<span class="text-muted text-sm">Not available</span>';
                    endif; ?>
                </td>
                <td>
                    <?php if ($user['role_name']==='admin'): ?>
                    <button class="btn btn-sm btn-secondary" onclick="toggleSection('state-<?= $t['id'] ?>')">Change State</button>
                    <?php if (!empty($stateLog[$t['id']])): ?>
                        <button class="btn btn-sm btn-outline-secondary" style="margin-top:3px" onclick="toggleSection('statelog-<?= $t['id'] ?>')">📋 History</button>
                    <?php endif; ?>
                    <div id="state-<?= $t['id'] ?>" style="display:none;margin-top:.5rem">
                        <form method="POST">
                            <input type="hidden" name="tool_id" value="<?= $t['id'] ?>">
                            <select name="new_state" class="form-control" style="margin-bottom:4px">
                                <option value="available">Available</option>
                                <option value="checked_out">Checked Out</option>
                                <option value="in_repair">In Repair</option>
                                <option value="decommissioned">Decommissioned</option>
                                <option value="missing">Missing</option>
                            </select>
                            <input type="text" name="state_notes" class="form-control" placeholder="Notes (required for missing/repair)..." style="margin-bottom:4px">
                            <button name="change_state" value="1" class="btn btn-sm btn-warning" style="width:100%">Update</button>
                        </form>
                    </div>
                    <?php if (!empty($stateLog[$t['id']])): ?>
                    <div id="statelog-<?= $t['id'] ?>" style="display:none;margin-top:.5rem;font-size:12px;max-height:140px;overflow-y:auto;background:var(--gray-100);padding:.4rem;border-radius:4px">
                        <?php foreach ($stateLog[$t['id']] as $sl): ?>
                            <div style="border-bottom:1px solid var(--gray-200);padding:3px 0">
                                <span class="badge badge-secondary"><?= e($sl['old_status']) ?></span>
                                → <span class="badge badge-success"><?= e($sl['new_status']) ?></span>
                                <span class="text-muted" style="margin-left:4px"><?= date('d M y H:i', strtotime($sl['changed_at'])) ?></span>
                                <?php if ($sl['changed_by_name']): ?><em style="margin-left:4px">by <?= e($sl['changed_by_name']) ?></em><?php endif; ?>
                                <?php if (!empty($sl['notes'])): ?>
                                    <div style="color:#555;margin-top:2px">📝 <?= e($sl['notes']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Damage reports (admin) -->
<?php if ($user['role_name']==='admin' && $damageRep): ?>
<div class="card mt-2">
    <div class="card-header">🔨 Damage Reports (Admin Review)</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tool</th><th>Reported by</th><th>Description</th><th>Status</th><th>Review</th></tr></thead>
            <tbody>
            <?php foreach ($damageRep as $dr): ?>
            <tr>
                <td><?= e($dr['tool_name']) ?></td>
                <td><?= e($dr['reporter']) ?></td>
                <td><?= e(substr($dr['description'],0,60)) ?></td>
                <td><span class="badge badge-<?= ['pending'=>'warning','reviewed'=>'success','resolved'=>'info'][$dr['status']]??'secondary' ?>"><?= e($dr['status']) ?></span></td>
                <td>
                    <?php if ($dr['status']==='pending'): ?>
                    <form method="POST" style="display:flex;gap:4px;align-items:center;flex-wrap:wrap">
                        <input type="hidden" name="report_id" value="<?= $dr['id'] ?>">
                        <select name="damage_type" class="form-control" style="width:auto;height:32px;font-size:13px">
                            <option value="natural_wear">Natural wear (exempt)</option>
                            <option value="negligence">Negligence (charge fee)</option>
                        </select>
                        <input type="number" name="repair_fee" placeholder="Fee £" step="0.01" min="0" value="0" style="width:80px;padding:4px;border:1px solid var(--gray-400);border-radius:4px;font-size:13px">
                        <button name="review_damage" value="1" class="btn btn-sm btn-primary">Review</button>
                    </form>
                    <?php else: ?>
                        <span class="text-muted text-sm"><?= e($dr['damage_type']??'—') ?> / £<?= number_format($dr['repair_fee'],2) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
function toggleSection(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = el.style.display === 'none' ? '' : 'none';
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
