<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>📢 Emergency Site Broadcaster</h1>
    <p>Send immediate alerts to all garden members. Use responsibly.</p>
</div>

<div class="card mb-2" style="border-color:var(--coral)">
    <div class="card-header" style="background:#faece7">Send Broadcast</div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Alert title</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Water Main Burst">
                </div>
                <div class="form-group">
                    <label>Site status</label>
                    <select name="site_status" class="form-control">
                        <option value="warning">⚠️ Warning</option>
                        <option value="emergency">🚨 Emergency</option>
                        <option value="closed">🔒 Site Closed</option>
                        <option value="normal">✅ Back to Normal</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" class="form-control" rows="3" required placeholder="Describe the situation and any instructions..."></textarea>
            </div>
            <div class="form-group">
                <label>Affected plots (comma-separated codes, optional)</label>
                <input type="text" name="affected_plots" class="form-control" placeholder="e.g. A-01, A-02, B-01">
            </div>
            <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap">
                <button name="broadcast" value="1" class="btn btn-danger">🚨 Send to All Members</button>
                <label style="display:flex;align-items:center;gap:.5rem;font-weight:normal;cursor:pointer">
                    <input type="checkbox" name="is_false_alarm" value="1">
                    This was a false alarm — log but don't send
                </label>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Broadcast History</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Title</th><th>Status</th><th>Message</th><th>Sent by</th><th>False alarm?</th></tr></thead>
            <tbody>
            <?php foreach ($broadcasts as $b): ?>
            <tr>
                <td><?= date('d M Y H:i', strtotime($b['sent_at'])) ?></td>
                <td><strong><?= e($b['title']) ?></strong></td>
                <td><span class="badge badge-<?= ['normal'=>'success','warning'=>'warning','closed'=>'secondary','emergency'=>'danger'][$b['site_status']]??'secondary' ?>"><?= e($b['site_status']) ?></span></td>
                <td><?= e(substr($b['message'],0,80)) ?><?= strlen($b['message'])>80?'...':'' ?></td>
                <td><?= e($b['full_name']) ?></td>
                <td><?= $b['is_false_alarm'] ? '<span class="badge badge-warning">False alarm</span>' : '—' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
