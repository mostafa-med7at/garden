<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="page-header mb-4">
    <h1 class="fw-bold" style="color: var(--green-dark);">🔔 My Notifications</h1>
    <p class="text-muted">View messages and alerts sent by the community administrators.</p>
</div>

<div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
    <div class="card-body p-0">
        <?php if ($notifications): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $note): ?>
                    <div class="list-group-item p-4 border-bottom" style="border-left: 4px solid <?= $note['type'] === 'pest_alert' ? 'var(--coral)' : ($note['type'] === 'waitlist' ? '#e6ab00' : 'var(--green-main)') ?>;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0 fw-semibold text-dark" style="font-size: 1.1rem;"><?= e($note['subject']) ?></h5>
                            <small class="text-muted fw-medium"><?= date('d M Y, h:i A', strtotime($note['sent_at'])) ?></small>
                        </div>
                        <div class="text-secondary mb-3" style="font-size: 0.95rem; line-height: 1.6;">
                            <?= $note['body'] ?>
                        </div>
                        <div class="mt-2">
                            <span class="badge text-uppercase" style="background: var(--gray-200); color: var(--gray-600); font-size: 0.75rem; letter-spacing: 0.5px;">
                                <?= e(str_replace('_', ' ', $note['type'])) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-5 text-center text-muted">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">📭</div>
                <h4 class="fw-semibold" style="color: var(--gray-500);">No notifications yet</h4>
                <p>You're all caught up! Admin broadcasts will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.list-group-item { background: #fff; transition: background 0.2s; }
.list-group-item:hover { background: #fdfdfd; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
