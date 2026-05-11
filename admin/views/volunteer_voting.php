<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>🗳️ Communal Fund Voting</h1>
    <p>Vote on how garden fees are spent. Each member gets one vote per proposal.</p>
</div>

<div class="page-actions">
    <?php if ($user['role_name']==='admin'): ?>
    <button class="btn btn-primary" onclick="toggleSection('create-prop')">+ Create Proposal</button>
    <?php endif; ?>
    <a href="tasks.php" class="btn btn-secondary">← Back</a>
</div>

<!-- Create proposal (admin) -->
<div id="create-prop" style="display:none" class="card mb-2">
    <div class="card-header">New Proposal</div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" required placeholder="e.g. New Greenhouse vs Beehives"></div>
            <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2" placeholder="Explain the proposal and cost estimate..."></textarea></div>
            <div class="form-group"><label>Voting closes at</label><input type="datetime-local" name="voting_ends_at" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime('+7 days')) ?>"></div>
            <button name="create_proposal" value="1" class="btn btn-primary">Create Proposal</button>
        </form>
    </div>
</div>

<!-- Proposals -->
<?php foreach ($proposals as $p):
    $pct   = $totalVoters > 0 ? round(($p['vote_count']/$totalVoters)*100, 1) : 0;
    $open  = $p['status']==='open' && strtotime($p['voting_ends_at']) > time();
?>
<div class="card mb-2">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <div>
            <strong><?= e($p['title']) ?></strong>
            <span class="badge badge-<?= $open?'success':'secondary' ?>" style="margin-left:.5rem"><?= $open?'Open':e($p['status']) ?></span>
        </div>
        <span class="text-sm text-muted">By <?= e($p['creator']) ?> — closes <?= date('d M Y H:i', strtotime($p['voting_ends_at'])) ?></span>
    </div>
    <div class="card-body">
        <?php if ($p['description']): ?><p class="mb-2"><?= e($p['description']) ?></p><?php endif; ?>
        <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap">
            <div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--green-dark)"><?= $p['vote_count'] ?> <span style="font-size:1rem;font-weight:400;color:var(--gray-600)">votes</span></div>
                <div class="text-sm text-muted"><?= $pct ?>% of active members</div>
            </div>
            <div style="flex:1;min-width:150px">
                <div class="progress"><div class="progress-bar" style="width:<?= $pct ?>%"></div></div>
            </div>
            <?php if ($open && !$p['i_voted']): ?>
            <form method="POST">
                <input type="hidden" name="proposal_id" value="<?= $p['id'] ?>">
                <button name="cast_vote" value="1" class="btn btn-primary">🗳️ Vote for this</button>
            </form>
            <?php elseif ($p['i_voted']): ?>
                <span class="badge badge-success">✅ You voted</span>
            <?php endif; ?>
            <?php if ($user['role_name']==='admin' && $open): ?>
            <form method="POST" style="display:inline">
                <input type="hidden" name="proposal_id" value="<?= $p['id'] ?>">
                <button name="close_voting" value="1" class="btn btn-secondary btn-sm" data-confirm="Close voting on this proposal?">Close voting</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php if (!$proposals): ?>
    <div class="alert alert-info">No proposals yet. <?= $user['role_name']==='admin'?'Create one above.':'Check back soon.' ?></div>
<?php endif; ?>
<script>function toggleSection(id){var el=document.getElementById(id);if(el)el.style.display=el.style.display==='none'?'':'none';}</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
