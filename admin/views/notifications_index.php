<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>📧 Email &amp; Notifications</h1>
    <p>Send emails to members, manage waitlist alerts, and track notification history.</p>
</div>

<!-- Quick Action Cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;margin-bottom:1.5rem">
    <div class="card" style="border-left:4px solid var(--green-dark)">
        <div class="card-body">
            <strong>📢 Custom Broadcast</strong>
            <p class="text-sm text-muted" style="margin:.5rem 0">Send a custom message to all or selected members.</p>
            <button class="btn btn-primary btn-sm" onclick="toggleSection('custom-form')">Compose</button>
        </div>
    </div>
    <div class="card" style="border-left:4px solid #e6ab00">
        <div class="card-body">
            <strong>⏳ Waitlist Alerts</strong>
            <p class="text-sm text-muted" style="margin:.5rem 0"><?= count($waitlistItems) ?> member(s) waiting. Notify when a plot opens.</p>
            <button class="btn btn-warning btn-sm" onclick="toggleSection('waitlist-form')">Notify</button>
        </div>
    </div>
    <div class="card" style="border-left:4px solid var(--coral)">
        <div class="card-body">
            <strong>🐛 Pest Alerts</strong>
            <p class="text-sm text-muted" style="margin:.5rem 0"><?= count($pestAlerts) ?> open transmissible pest report(s).</p>
            <button class="btn btn-danger btn-sm" onclick="toggleSection('pest-form')">Send Alert</button>
        </div>
    </div>
</div>

<!-- Custom Notification Form -->
<div id="custom-form" style="display:none" class="card mb-2">
    <div class="card-header">📢 Compose Custom Notification</div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Send To</label>
                    <select name="recipients" id="recipient-type" class="form-control" onchange="updateRecipientFields()">
                        <option value="all">All active members</option>
                        <option value="role">By role</option>
                        <option value="single">Single user</option>
                    </select>
                </div>
                <div class="form-group" id="role-field" style="display:none">
                    <label>Role</label>
                    <select name="role_filter" class="form-control">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" id="user-field" style="display:none">
                    <label>User</label>
                    <select name="single_user_id" class="form-control">
                        <?php foreach ($allUsers as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= e($u['full_name']) ?> &lt;<?= e($u['email']) ?>&gt;</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Subject <span class="text-danger">*</span></label>
                <input type="text" name="subject" class="form-control" placeholder="Email subject…" required>
            </div>
            <div class="form-group">
                <label>Message Body <span class="text-danger">*</span></label>
                <textarea name="body" class="form-control" rows="5" placeholder="Write your message here…" required></textarea>
            </div>
            <button name="send_custom" value="1" class="btn btn-primary" data-confirm="Send this notification?">📤 Send Now</button>
            <button type="button" class="btn btn-secondary" onclick="toggleSection('custom-form')">Cancel</button>
        </form>
    </div>
</div>

<!-- Waitlist Notification Form -->
<div id="waitlist-form" style="display:none" class="card mb-2">
    <div class="card-header">⏳ Notify Waitlist Member of Available Plot</div>
    <div class="card-body">
        <?php if ($waitlistItems): ?>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Waitlist Member</label>
                    <select name="waitlist_user_id" class="form-control">
                        <?php foreach ($waitlistItems as $w): ?>
                            <option value="<?= $w['user_id'] ?>">#<?= $w['id'] ?> — <?= e($w['full_name']) ?> (Score: <?= $w['priority_score'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Available Plot Code</label>
                    <input type="text" name="plot_code" class="form-control" placeholder="e.g. A-01" required>
                </div>
            </div>
            <button name="notify_waitlist" value="1" class="btn btn-warning">📧 Send Waitlist Email</button>
            <button type="button" class="btn btn-secondary" onclick="toggleSection('waitlist-form')">Cancel</button>
        </form>
        <?php else: ?>
            <p class="text-muted">No members currently on the waitlist.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Pest Alert Form -->
<div id="pest-form" style="display:none" class="card mb-2">
    <div class="card-header">🐛 Send Pest Alert to All Plot Owners</div>
    <div class="card-body">
        <?php if ($pestAlerts): ?>
        <form method="POST">
            <div class="form-group">
                <label>Pest Report</label>
                <select name="report_id" class="form-control">
                    <?php foreach ($pestAlerts as $p): ?>
                        <option value="<?= $p['id'] ?>">Plot <?= e($p['plot_code']) ?> — <?= e($p['pest_type']) ?> (<?= $p['severity'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button name="send_pest_alert" value="1" class="btn btn-danger" data-confirm="Send pest alert to all active plot owners?">🚨 Send Alert</button>
            <button type="button" class="btn btn-secondary" onclick="toggleSection('pest-form')">Cancel</button>
        </form>
        <?php else: ?>
            <p class="text-muted">No open transmissible pest reports.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Notification Log -->
<div class="card">
    <div class="card-header">📋 Notification Log <span class="badge badge-info" style="margin-left:.5rem"><?= count($recentLogs) ?> recent</span></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Time</th><th>Recipient</th><th>Type</th><th>Subject</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($recentLogs as $log): ?>
            <tr>
                <td class="text-sm"><?= date('d M Y H:i', strtotime($log['sent_at'])) ?></td>
                <td><?= e($log['full_name'] ?? '—') ?></td>
                <td><span class="badge badge-info"><?= e($log['type']) ?></span></td>
                <td><?= e(substr($log['subject'], 0, 60)) ?></td>
                <td>
                    <?php if ($log['status'] === 'sent'): ?>
                        <span class="badge badge-success">✅ Sent</span>
                    <?php elseif ($log['status'] === 'failed'): ?>
                        <span class="badge badge-danger">❌ Failed</span>
                    <?php else: ?>
                        <span class="badge badge-warning">⏳ Pending</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$recentLogs): ?>
                <tr><td colspan="5" class="text-center text-muted" style="padding:2rem">No notifications sent yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="alert alert-info" style="margin-top:1rem">
    <strong>ℹ️ Email Setup:</strong> Emails are sent using PHP's built-in <code>mail()</code>. For real delivery on XAMPP:
    open <code>php.ini</code>, set <code>SMTP = smtp.gmail.com</code>, <code>smtp_port = 587</code>, and install
    <a href="https://github.com/PHPMailer/PHPMailer" target="_blank">PHPMailer</a> for authenticated SMTP.
</div>

<script>
function toggleSection(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = el.style.display === 'none' ? '' : 'none';
}
function updateRecipientFields() {
    var type = document.getElementById('recipient-type').value;
    document.getElementById('role-field').style.display = type === 'role'   ? '' : 'none';
    document.getElementById('user-field').style.display = type === 'single' ? '' : 'none';
}
document.addEventListener('click', function(e) {
    var btn = e.target.closest('[data-confirm]');
    if (btn && !confirm(btn.dataset.confirm)) e.preventDefault();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
