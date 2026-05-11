<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>🛡️ Admin Panel</h1>
    <p>Manage user roles (Fn 29) and review the permanent system audit trail (Fn 30).</p>
</div>

<!-- Stats -->
<div class="stats-row" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.5rem">
    <div class="stat-card"><span class="stat-value"><?= count($allUsers) ?></span><span class="stat-label">Total users</span></div>
    <div class="stat-card accent-blue"><span class="stat-value"><?= number_format($stats['totalLogs']) ?></span><span class="stat-label">Audit log entries</span></div>
    <div class="stat-card accent-amber"><span class="stat-value"><?= $stats['logsToday'] ?></span><span class="stat-label">Actions today</span></div>
    <div class="stat-card accent-coral"><span class="stat-value"><?= $stats['failedLogins'] ?></span><span class="stat-label">Failed gate attempts</span></div>
</div>

<!-- ── Fn 29: RBAC — User Role Management ─────────────────── -->
<div class="card mb-2">
    <div class="module-header">
        <span class="module-icon">👥</span>
        <h2>Role-Based Access Control (Fn 29)</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Member</th><th>Email</th><th>Current Role</th><th>Gate Code</th><th>Status</th><th>Change Role</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($allUsers as $u): ?>
            <tr <?= !$u['is_active'] ? 'style="opacity:.55"' : '' ?>>
                <td><strong><?= e($u['full_name']) ?></strong></td>
                <td><?= e($u['email']) ?></td>
                <td>
                    <span class="badge role-<?= e($u['role_name']) ?> user-badge"><?= e($u['role_name']) ?></span>
                </td>
                <td><code><?= e($u['gate_code'] ?? '—') ?></code></td>
                <td>
                    <span class="badge badge-<?= $u['is_active'] ? 'success' : 'secondary' ?>">
                        <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td>
                    <?php if ($u['id'] !== $user['id']): ?>
                    <form method="POST" style="display:flex;gap:4px">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <select name="role_id" class="form-control" style="height:30px;font-size:13px;width:auto">
                            <?php foreach ($allRoles as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $r['id']==$u['role_id']?'selected':'' ?>><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button name="change_role" value="1" class="btn btn-sm btn-primary">Save</button>
                    </form>
                    <?php else: echo '<span class="text-muted text-sm">You</span>'; endif; ?>
                </td>
                <td>
                    <?php if ($u['id'] !== $user['id']): ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <button name="toggle_active" value="1" class="btn btn-sm btn-<?= $u['is_active']?'danger':'secondary' ?>"
                                data-confirm="<?= $u['is_active']?'Deactivate':'Activate' ?> this user?">
                            <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                        </button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Fn 30: Audit Trail ─────────────────────────────────── -->
<div class="card">
    <div class="module-header">
        <span class="module-icon">📋</span>
        <h2>System Audit Trail (Fn 30)</h2>
    </div>

    <!-- Filters -->
    <div class="card-body" style="border-bottom:1px solid var(--gray-200);padding:.75rem 1.25rem">
        <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label style="font-size:12px">Action type</label>
                <input type="text" name="action" class="form-control" value="<?= e($filters['action']) ?>" placeholder="e.g. login, trade_created">
            </div>
            <div class="form-group" style="margin:0;flex:0 0 130px">
                <label style="font-size:12px">Module</label>
                <select name="module" class="form-control">
                    <option value="">All modules</option>
                    <?php foreach (['land','resources','volunteer','marketplace','auth','admin'] as $m): ?>
                    <option value="<?= $m ?>" <?= $m===$filters['module']?'selected':'' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;flex:0 0 160px">
                <label style="font-size:12px">User ID</label>
                <input type="number" name="user_id" class="form-control" value="<?= e($filters['user_id']) ?>" placeholder="User ID">
            </div>
            <div class="form-group" style="margin:0;flex:0 0 150px">
                <label style="font-size:12px">Date</label>
                <input type="date" name="date" class="form-control" value="<?= e($filters['date']) ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="audit.php" class="btn btn-secondary btn-sm">Clear</a>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Timestamp</th><th>User</th><th>Module</th><th>Action</th><th>Target</th><th>Description</th><th>IP</th></tr>
            </thead>
            <tbody>
            <?php if ($auditLogs): ?>
                <?php foreach ($auditLogs as $log): ?>
                <tr>
                    <td class="text-muted text-sm"><?= $log['id'] ?></td>
                    <td class="text-sm" style="white-space:nowrap"><?= date('d M Y H:i:s', strtotime($log['logged_at'])) ?></td>
                    <td class="text-sm"><?= e($log['full_name'] ?? 'System') ?></td>
                    <td><span class="badge badge-secondary" style="font-size:11px"><?= e($log['module'] ?? '—') ?></span></td>
                    <td>
                        <code style="font-size:12px;background:var(--gray-100);padding:1px 5px;border-radius:3px"><?= e($log['action_type']) ?></code>
                    </td>
                    <td class="text-sm text-muted">
                        <?= $log['target_table'] ? e($log['target_table']).'#'.e($log['target_id'] ?? '?') : '—' ?>
                    </td>
                    <td class="text-sm"><?= e(substr($log['description'] ?? '', 0, 60)) ?></td>
                    <td class="text-sm text-muted"><?= e($log['ip_address'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--gray-600)">No log entries match your filters.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (count($auditLogs) >= 200): ?>
    <div class="card-body" style="border-top:1px solid var(--gray-200);text-align:center;color:var(--gray-600);font-size:13px">
        Showing latest 200 entries. Use filters to narrow results.
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
