<?php require_once __DIR__ . '/../includes/header.php'; ?>

<!-- ── Page Header ─────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 fw-bold text-success mb-0">📋 Plot Waitlist</h1>
        <p class="text-muted small mb-0">
            Ranked by priority score (60% community points + 40% residency months).
            <?php if ($availableCount > 0): ?>
                <span class="badge text-bg-success ms-1"><?= $availableCount ?> plot<?= $availableCount > 1 ? 's' : '' ?> available now!</span>
            <?php endif; ?>
        </p>
    </div>
    <a href="plots.php" class="btn btn-outline-secondary btn-sm">← Back to Map</a>
</div>

<!-- ── My Status Banner ───────────────────────────────────────── -->
<?php if (!$myEntry): ?>
    <!-- Not on waitlist → offer to join -->
    <div class="card border-success shadow-sm mb-4">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h6 class="fw-bold mb-1">Not on the waitlist yet?</h6>
                <p class="text-muted small mb-0">Join now and you'll be notified when a plot becomes available.</p>
            </div>
            <form method="POST">
                <button type="submit" name="join" value="1" class="btn btn-success">
                    📋 Join Waitlist
                </button>
            </form>
        </div>
    </div>

<?php elseif ($myEntry['status'] === 'notified'): ?>
    <!-- Notified — let them accept or decline -->
    <div class="alert alert-success shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            🎉 <strong>A plot is available for you!</strong> Please respond before your offer expires.
        </div>
        <div class="d-flex gap-2">
            <form method="POST">
                <button type="submit" name="respond" value="accepted" class="btn btn-success btn-sm">✅ Accept Plot</button>
            </form>
            <form method="POST">
                <button type="submit" name="respond" value="declined" class="btn btn-outline-secondary btn-sm">✗ Decline</button>
            </form>
        </div>
    </div>

<?php else: ?>
    <!-- Already waiting -->
    <div class="card border-info shadow-sm mb-4">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                You are on the waitlist at position
                <strong>#<?= $myPos ?? '—' ?></strong>
                &nbsp;|&nbsp; Priority score: <strong><?= $myEntry['priority_score'] ?></strong>
                &nbsp;|&nbsp; Status: <span class="badge text-bg-info"><?= e($myEntry['status']) ?></span>
            </div>
            <form method="POST">
                <button type="submit" name="leave" value="1" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to leave the waitlist? You will lose your current priority score.');">
                    Leave Waitlist
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- ── Admin Actions ──────────────────────────────────────────── -->
<?php if ($user['role_name'] === 'admin'): ?>
<div class="mb-4">
    <form method="POST" class="d-inline">
        <button type="submit" name="notify_top" value="1" class="btn btn-warning btn-sm">
            📣 Notify Highest-Priority Member
        </button>
    </form>
</div>
<?php endif; ?>

<!-- ── Waitlist Table ─────────────────────────────────────────── -->
<div class="card shadow-sm">
    <div class="card-header fw-semibold">
        Waitlist
        <span class="badge bg-secondary ms-1"><?= count($waitlistItems) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Community Points</th>
                    <th>Residency (mo.)</th>
                    <th>Priority Score</th>
                    <th>Joined</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($waitlistItems): ?>
                <?php foreach ($waitlistItems as $i => $w): ?>
                <tr class="<?= $w['user_id'] == $user['id'] ? 'table-success' : '' ?>">
                    <td><strong><?= $i + 1 ?></strong></td>
                    <td>
                        <?= e($w['full_name']) ?>
                        <?= $w['user_id'] == $user['id'] ? '<span class="badge text-bg-success ms-1">You</span>' : '' ?>
                    </td>
                    <td><?= e($w['community_points']) ?></td>
                    <td><?= e($w['residency_months']) ?></td>
                    <td>
                        <strong><?= e($w['priority_score']) ?></strong>
                        <?= $i === 0 ? '<span class="badge text-bg-warning ms-1">Top</span>' : '' ?>
                    </td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($w['joined_at'])) ?></td>
                    <td>
                        <?php
                        $sb = ['waiting'=>'secondary','notified'=>'warning','accepted'=>'success','declined'=>'danger'][$w['status']] ?? 'secondary';
                        ?>
                        <span class="badge text-bg-<?= $sb ?>"><?= e($w['status']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No members on the waitlist yet.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
