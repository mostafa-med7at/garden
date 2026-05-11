<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>♻️ Compost Contribution Tracker</h1>
    <p>Log green waste you bring to the communal compost pile.</p>
</div>

<div class="stats-row" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card">
        <span class="stat-value"><?= number_format($myTotal,1) ?> kg</span>
        <span class="stat-label">My total contribution</span>
    </div>
    <div class="stat-card accent-amber">
        <span class="stat-value"><?= count($history) ?></span>
        <span class="stat-label">My log entries</span>
    </div>
    <div class="stat-card accent-blue">
        <span class="stat-value"><?= number_format($grandTotal,1) ?> kg</span>
        <span class="stat-label">Garden total</span>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">

<div class="card">
    <div class="card-header">Log Contribution</div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <label>Amount of green waste (kg)</label>
                <input type="number" name="amount_kg" class="form-control" step="0.1" min="0" value="0" required>
                <p class="form-hint">Enter 0 to record a visit with no contribution.</p>
            </div>
            <div class="form-group">
                <label>Notes (optional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Hedge clippings, vegetable scraps..."></textarea>
            </div>
            <button type="submit" name="log_compost" value="1" class="btn btn-primary">Log Contribution</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">🏆 Leaderboard (Top 10)</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>Member</th><th>Total (kg)</th><th>Entries</th></tr></thead>
            <tbody>
            <?php foreach ($leaderboard as $i => $row): ?>
            <tr <?= $row['full_name']===$user['full_name']?'style="background:var(--green-pale)"':'' ?>>
                <td><?= $i+1 ?><?= $i===0?' 🥇':($i===1?' 🥈':($i===2?' 🥉':'')) ?></td>
                <td><?= e($row['full_name']) ?></td>
                <td><strong><?= number_format((float)$row['total_kg'],1) ?></strong></td>
                <td><?= $row['entries'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<div class="card mt-2">
    <div class="card-header">My Contribution History</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Amount (kg)</th><th>Notes</th></tr></thead>
            <tbody>
            <?php if ($history): ?>
                <?php foreach ($history as $h): ?>
                <tr>
                    <td><?= date('d M Y H:i', strtotime($h['contributed_at'])) ?></td>
                    <td><?= $h['amount_kg'] > 0 ? number_format((float)$h['amount_kg'],2).' kg' : '<span class="text-muted">0 (visit logged)</span>' ?></td>
                    <td><?= e($h['notes'] ?: '—') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3" style="text-align:center;padding:2rem;color:var(--gray-600)">No contributions yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
