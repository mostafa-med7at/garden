<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>🥕 Harvest Marketplace</h1>
    <p>Flash-trade perishable produce, donate for karma points, and rate what you receive.</p>
</div>

<div class="page-actions">
    <button class="btn btn-primary" onclick="toggleSection('post-trade')">+ Post a Trade</button>
    <button class="btn btn-secondary" onclick="toggleSection('donate-form')">❤️ Donate Produce</button>
    <a href="advice.php" class="btn btn-secondary">💬 Advice Board</a>
</div>

<!-- My karma summary -->
<div class="stats-row" style="grid-template-columns:repeat(4,1fr);margin-bottom:1rem">
    <div class="stat-card">
        <span class="stat-value">⭐ <?= (int)($_SESSION['user']['karma'] ?? 0) ?></span>
        <span class="stat-label">My karma points</span>
    </div>
    <div class="stat-card accent-blue">
        <span class="stat-value"><?= count($activeTrades) ?></span>
        <span class="stat-label">Active trades now</span>
    </div>
    <div class="stat-card accent-amber">
        <span class="stat-value"><?= $donationStats[0] ?></span>
        <span class="stat-label">My donations</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?= (int)$donationStats[1] ?></span>
        <span class="stat-label">Karma earned total</span>
    </div>
</div>

<!-- Rate claimed trades -->
<?php foreach ($claimedByMe as $ct): if (!$ct['already_rated']): ?>
<div class="alert alert-info" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem">
    <span>You claimed <strong><?= e($ct['title']) ?></strong> from <?= e($ct['seller_name']) ?>. Rate the quality:</span>
    <form method="POST" style="display:flex;gap:.5rem;align-items:center">
        <input type="hidden" name="trade_id" value="<?= $ct['id'] ?>">
        <select name="rating" class="form-control" style="width:auto;height:32px;font-size:13px">
            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
            <option value="4">⭐⭐⭐⭐ Good</option>
            <option value="3" selected>⭐⭐⭐ Average</option>
            <option value="2">⭐⭐ Poor</option>
            <option value="1">⭐ Very poor</option>
        </select>
        <input type="text" name="rating_notes" class="form-control" style="width:140px" placeholder="Notes...">
        <button name="rate_trade" value="1" class="btn btn-primary btn-sm">Submit Rating</button>
    </form>
</div>
<?php endif; endforeach; ?>

<!-- Post trade form -->
<div id="post-trade" style="display:none" class="card mb-2">
    <div class="card-header">Post a Flash Trade</div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>What are you offering?</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. 5kg ripe tomatoes">
                </div>
                <div class="form-group">
                    <label>Quantity / amount</label>
                    <input type="text" name="quantity" class="form-control" placeholder="e.g. 5kg, 3 bunches">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Allergen category (if any)</label>
                    <select name="allergen_category" class="form-control">
                        <option value="">None</option>
                        <?php foreach (ALLERGEN_CATEGORIES as $a): ?><option value="<?= e($a) ?>"><?= e($a) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Expires in (hours)</label>
                    <select name="expiry_hours" class="form-control">
                        <option value="1">1 hour</option>
                        <option value="2" selected>2 hours</option>
                        <option value="4">4 hours</option>
                        <option value="8">8 hours</option>
                        <option value="24">24 hours</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Description / pickup instructions</label>
                <textarea name="description" class="form-control" rows="2" placeholder="e.g. Pickup from my plot A-01 before 6pm today..."></textarea>
            </div>
            <button name="create_trade" value="1" class="btn btn-primary">Post Trade</button>
        </form>
    </div>
</div>

<!-- Donation form -->
<div id="donate-form" style="display:none" class="card mb-2">
    <div class="card-header">❤️ Donate Produce (Earn Karma — Fn 25)</div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Produce name</label>
                    <input type="text" name="produce_name" class="form-control" required placeholder="e.g. Courgettes">
                </div>
                <div class="form-group">
                    <label>Quantity (include unit, e.g. 2 kg)</label>
                    <input type="text" name="donate_qty" class="form-control" required placeholder="e.g. 2 kg, 10 apples">
                </div>
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-weight:normal">
                    <input type="checkbox" name="is_spoiled" value="1">
                    This produce is spoiled or unusable (no karma awarded)
                </label>
            </div>
            <button name="donate" value="1" class="btn btn-primary">Donate &amp; Earn Karma</button>
        </form>
        <p class="form-hint mt-1">You earn <?= KARMA_PER_KG ?> karma points per kg donated.</p>
    </div>
</div>

<!-- Active trades -->
<div class="card mb-2">
    <div class="card-header">🔥 Active Flash Trades</div>
    <?php if ($activeTrades): ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Produce</th><th>Qty</th><th>Seller</th><th>Allergen</th><th>Expires in</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($activeTrades as $t):
                $minsLeft = (int)$t['mins_left'];
                $urgent   = $minsLeft <= 30;
            ?>
            <tr>
                <td>
                    <strong><?= e($t['title']) ?></strong>
                    <?php if ($t['description']): ?><br><span class="text-sm text-muted"><?= e(substr($t['description'],0,60)) ?></span><?php endif; ?>
                </td>
                <td><?= e($t['quantity'] ?: '—') ?></td>
                <td><?= e($t['seller_name']) ?></td>
                <td>
                    <?php if ($t['allergen_flag']): ?>
                        <span class="badge badge-warning">⚠️ <?= e($t['allergen_category']) ?></span>
                    <?php else: echo '<span class="text-muted">—</span>'; endif; ?>
                </td>
                <td>
                    <span class="badge badge-<?= $urgent?'danger':'warning' ?>">
                        <?= $minsLeft >= 60 ? floor($minsLeft/60).'h '.($minsLeft%60).'m' : $minsLeft.'m' ?>
                    </span>
                </td>
                <td>
                    <?php if ($t['seller_id'] != $user['id']): ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="trade_id" value="<?= $t['id'] ?>">
                        <button name="claim_trade" value="1" class="btn btn-sm btn-primary"
                                data-confirm="Claim this trade? You agree to collect it promptly.">Claim</button>
                    </form>
                    <?php else: ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="trade_id" value="<?= $t['id'] ?>">
                        <button name="cancel_trade" value="1" class="btn btn-sm btn-secondary"
                                data-confirm="Cancel this trade?">Cancel</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="card-body"><p class="text-muted">No active trades right now. Be the first to post!</p></div>
    <?php endif; ?>
</div>

<!-- My trades history -->
<div class="card mb-2">
    <div class="card-header">My Posted Trades</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Qty</th><th>Posted</th><th>Claimed by</th><th>Status</th><th>Rating</th></tr></thead>
            <tbody>
            <?php if ($myTrades): foreach ($myTrades as $t): ?>
            <tr>
                <td><?= e($t['title']) ?></td>
                <td><?= e($t['quantity'] ?: '—') ?></td>
                <td><?= date('d M Y H:i', strtotime($t['created_at'])) ?></td>
                <td><?= e($t['claimer_name'] ?? '—') ?></td>
                <td><span class="badge badge-<?= ['active'=>'success','claimed'=>'info','expired'=>'secondary','cancelled'=>'secondary'][$t['status']]??'secondary' ?>"><?= e($t['status']) ?></span></td>
                <td><?= $t['avg_rating'] ? str_repeat('⭐',round($t['avg_rating'])).' ('.number_format($t['avg_rating'],1).')' : '—' ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6" class="text-muted" style="text-align:center;padding:1.5rem">No trades posted yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Donations history -->
<div class="card">
    <div class="card-header">My Donations</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Produce</th><th>Quantity</th><th>Karma awarded</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php if ($donations): foreach ($donations as $d): ?>
            <tr>
                <td><?= e($d['produce_name']) ?></td>
                <td><?= e($d['quantity']) ?></td>
                <td><?= $d['is_rejected'] ? '<span class="text-muted">0</span>' : '<span class="badge badge-success">+'.e($d['karma_points_awarded']).'</span>' ?></td>
                <td><?= $d['is_rejected'] ? '<span class="badge badge-danger">Rejected</span>' : '<span class="badge badge-success">Accepted</span>' ?></td>
                <td><?= date('d M Y', strtotime($d['donated_at'])) ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-muted" style="text-align:center;padding:1.5rem">No donations yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>function toggleSection(id){var el=document.getElementById(id);if(el)el.style.display=el.style.display==='none'?'':'none';}</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
