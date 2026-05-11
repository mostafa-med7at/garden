<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>Welcome back, <?= e($user['full_name']) ?> 👋</h1>
    <p>Here's what's happening in your garden today.</p>
</div>

<!-- Stats row -->
<div class="stats-row">
    <div class="stat-card">
        <span class="stat-value"><?= $stats['availablePlots'] ?>/<?= $stats['totalPlots'] ?></span>
        <span class="stat-label">Plots available</span>
    </div>
    <div class="stat-card accent-amber">
        <span class="stat-value"><?= $stats['waitlistCount'] ?></span>
        <span class="stat-label">On waitlist</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?= $stats['totalMembers'] ?></span>
        <span class="stat-label">Active members</span>
    </div>
    <div class="stat-card accent-coral">
        <span class="stat-value"><?= $stats['openIncidents'] ?></span>
        <span class="stat-label">Open incidents</span>
    </div>
    <div class="stat-card accent-amber">
        <span class="stat-value"><?= $stats['lowStock'] ?></span>
        <span class="stat-label">Low stock items</span>
    </div>
    <div class="stat-card accent-blue">
        <span class="stat-value"><?= $stats['activeTrades'] ?></span>
        <span class="stat-label">Active trades</span>
    </div>
</div>

<div class="dashboard-grid">

    <!-- My account card -->
    <div class="card">
        <div class="card-header">My Account</div>
        <div class="card-body">
            <p><strong>Role:</strong> <span class="badge badge-success"><?= e($user['role_name']) ?></span></p>
            <p class="mt-1"><strong>Community points:</strong> <?= (int)$user['points'] ?></p>
            <p class="mt-1"><strong>Karma points:</strong> ⭐ <?= (int)$user['karma'] ?></p>
            <p class="mt-1"><strong>Seed credits:</strong> 🌱 <?= (int)$user['credits'] ?></p>
            <?php if ($myLease): ?>
                <hr style="margin:1rem 0; border-color:var(--gray-200)">
                <p><strong>Plot:</strong> <?= e($myLease['plot_code']) ?></p>
                <p class="mt-1"><strong>Lease expires:</strong> <?= e($myLease['end_date']) ?></p>
                <?php
                    $daysLeft = (int)((strtotime($myLease['end_date']) - time()) / 86400);
                    $pct = max(0, min(100, ($daysLeft / 365) * 100));
                ?>
                <div class="progress mt-1">
                    <div class="progress-bar <?= $daysLeft < 30 ? 'danger' : '' ?>" style="width:<?= $pct ?>%"></div>
                </div>
                <p class="text-sm text-muted mt-1"><?= $daysLeft ?> days remaining</p>
            <?php else: ?>
                <p class="text-sm text-muted mt-2">No active lease. <a href="<?= APP_URL ?>/modules/land/waitlist.php">Join the waitlist</a></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick links card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 pb-0">
            <h5 class="fw-bold text-dark mb-0">Quick Actions</h5>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:.7rem">
            <a class="btn btn-qa btn-qa-1" href="<?= APP_URL ?>/modules/land/plots.php">🗺️ View Plot Map</a>
            <a class="btn btn-qa btn-qa-2" href="<?= APP_URL ?>/modules/resources/tools.php">🔧 Reserve a Tool</a>
            <a class="btn btn-qa btn-qa-3" href="<?= APP_URL ?>/modules/marketplace/trades.php">🥕 Browse Marketplace</a>
            <a class="btn btn-qa btn-qa-4" href="<?= APP_URL ?>/modules/volunteer/tasks.php">📋 View Tasks (<?= $stats['openTasks'] ?> open)</a>
            <a class="btn btn-qa btn-qa-5" href="<?= APP_URL ?>/modules/land/pest_report.php">🐛 Report Pest/Disease</a>
            <a class="btn btn-qa btn-qa-6" href="<?= APP_URL ?>/modules/volunteer/incidents.php">⚠️ Report Incident</a>
        </div>
    </div>

    <!-- Recent broadcasts -->
    <div class="card">
        <div class="card-header">📢 Recent Announcements</div>
        <div class="card-body">
            <?php if ($broadcasts): ?>
                <?php foreach ($broadcasts as $b): ?>
                    <div style="margin-bottom:.85rem;padding-bottom:.85rem;border-bottom:1px solid var(--gray-200)">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge badge-<?= $b['site_status']==='emergency'?'danger':($b['site_status']==='warning'?'warning':'info') ?>"><?= e($b['site_status']) ?></span>
                            <strong><?= e($b['title']) ?></strong>
                        </div>
                        <p class="text-sm text-muted mt-1"><?= e(substr($b['message'],0,100)) ?>...</p>
                        <p class="text-sm text-muted">By <?= e($b['full_name']) ?> — <?= date('d M Y', strtotime($b['sent_at'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No recent announcements.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
