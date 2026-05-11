<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>🌿 Rent a Plot</h1>
    <p>Select an available plot and complete the rental agreement.</p>
</div>

<div class="card" style="max-width:600px">
    <div class="card-header">New Lease</div>
    <div class="card-body">
        <form method="POST" id="lease-form">

            <?php if ($user['role_name'] === 'admin' && $members): ?>
            <div class="form-group">
                <label>Assign to member</label>
                <select name="member_id" class="form-control" required>
                    <option value="">-- select member --</option>
                    <?php foreach ($members as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= e($m['full_name']) ?> (<?= e($m['membership_status']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Select plot</label>
                <select name="plot_id" id="plot-select" class="form-control" required>
                    <option value="">-- select available plot --</option>
                    <?php foreach ($available as $p): ?>
                        <option value="<?= $p['id'] ?>"
                                data-area="<?= $p['area_sqm'] ?>"
                                data-soil="<?= $p['soil_quality'] ?>"
                                data-sunlight="<?= $p['sunlight_level'] ?>"
                                <?= $p['id']==$plotId?'selected':'' ?>>
                            <?= e($p['plot_code']) ?> — <?= $p['area_sqm'] ?>m² (<?= $p['soil_quality'] ?> soil, <?= $p['sunlight_level'] ?> sun)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="plot-info" style="display:none" class="alert alert-info mb-2">
                <strong>Plot details:</strong>
                <span id="info-text"></span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Duration</label>
                    <select name="duration_years" class="form-control">
                        <option value="1">1 year</option>
                        <option value="2">2 years</option>
                        <option value="3">3 years</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment method</label>
                    <select name="payment_method" class="form-control">
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank transfer</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Confirm Rental</button>
        </form>
    </div>
</div>

<script>
// Fee preview using PHP constants passed as JS
const RATE     = <?= BILLING_RATE_PER_SQM ?>;
const MULT     = <?= json_encode(SOIL_MULTIPLIER) ?>;
const DISC     = <?= json_encode(MEMBERSHIP_DISCOUNT) ?>;
const MEM      = '<?= $user['membership'] ?>';

document.getElementById('plot-select').addEventListener('change', function() {
    const opt  = this.options[this.selectedIndex];
    const area = parseFloat(opt.dataset.area) || 0;
    const soil = opt.dataset.soil;
    if (!area) { document.getElementById('plot-info').style.display='none'; return; }
    const base  = area * RATE;
    const after = base * (MULT[soil] || 1);
    const final = after * (1 - (DISC[MEM] || 0));
    document.getElementById('info-text').textContent =
        ` Area: ${area}m², Soil: ${soil}, Sunlight: ${opt.dataset.sunlight}. Estimated fee: £${final.toFixed(2)}/year`;
    document.getElementById('plot-info').style.display='';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
