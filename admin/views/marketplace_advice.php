<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
    <h1>💬 Gardening Advice Board</h1>
    <p>Ask questions, share expertise, and earn seed bank credits for helpful answers.</p>
</div>

<div class="page-actions">
    <button class="btn btn-primary" onclick="toggleSection('ask-form')">❓ Ask a Question</button>
    <a href="trades.php" class="btn btn-secondary">← Marketplace</a>
    <div style="margin-left:auto;display:flex;align-items:center;gap:.5rem">
        <span class="text-muted text-sm">My seed credits:</span>
        <span class="badge badge-success" style="font-size:14px">🌱 <?= (int)($_SESSION['user']['credits'] ?? 0) ?></span>
    </div>
</div>

<!-- Ask question form -->
<div id="ask-form" style="display:none" class="card mb-2">
    <div class="card-header">Ask the Community</div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <label>Your question</label>
                <textarea name="question" class="form-control" rows="3" required
                    placeholder="e.g. What's the best companion plant for tomatoes? How do I fix clay-heavy soil?"></textarea>
            </div>
            <button name="ask_question" value="1" class="btn btn-primary">Post Question</button>
        </form>
    </div>
</div>

<!-- Questions list -->
<?php if ($questions): ?>
    <?php foreach ($questions as $q):
        $answers = $q['answers'];
        $isAsker = ($q['asker_id'] == $user['id']);
        $isOpen  = ($q['status'] === 'open');
    ?>
    <div class="card mb-2" id="q<?= $q['id'] ?>" style="<?= !$isOpen ? 'opacity:.85' : '' ?>">
        <div class="card-header" style="gap:.75rem;flex-wrap:wrap">
            <div style="flex:1">
                <span class="badge badge-<?= ['open'=>'success','answered'=>'info','closed'=>'secondary'][$q['status']]??'secondary' ?>"><?= e($q['status']) ?></span>
                <strong style="margin-left:.5rem"><?= e($q['question']) ?></strong>
            </div>
            <div style="display:flex;align-items:center;gap:.75rem;flex-shrink:0">
                <span class="text-sm text-muted">By <?= e($q['asker_name']) ?> — <?= date('d M Y', strtotime($q['asked_at'])) ?></span>
                <span class="badge badge-secondary"><?= $q['answer_count'] ?> answer(s)</span>
                <?php if ($isAsker && $isOpen): ?>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                    <button name="close_question" value="1" class="btn btn-sm btn-secondary">Close</button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body" style="padding:.75rem 1.25rem">
            <!-- Answers -->
            <?php if ($answers): ?>
            <div style="margin-bottom:.75rem">
                <?php foreach ($answers as $a): $isBest = ($q['best_answer_id'] == $a['id']); ?>
                <div style="padding:.6rem .8rem;border-radius:6px;margin-bottom:.5rem;background:<?= $isBest ? 'var(--green-pale)' : 'var(--gray-100)' ?>;border:1px solid <?= $isBest ? 'var(--green-light)' : 'var(--gray-200)' ?>">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;flex-wrap:wrap">
                        <div style="flex:1">
                            <?php if ($isBest): ?><span class="badge badge-success" style="margin-bottom:4px">✅ Best Answer</span><br><?php endif; ?>
                            <span class="text-sm"><strong><?= e($a['answerer_name']) ?></strong> — <?= date('d M Y', strtotime($a['answered_at'])) ?></span>
                            <?php if ($a['credits_awarded']): ?> <span class="badge badge-info">🌱 +<?= $a['credits_awarded'] ?> credits</span><?php endif; ?>
                            <p style="margin:.4rem 0 0"><?= nl2br(e($a['answer'])) ?></p>
                        </div>
                        <?php if ($isAsker && $isOpen && !$q['best_answer_id'] && $a['answerer_id'] != $user['id']): ?>
                        <form method="POST" style="flex-shrink:0">
                            <input type="hidden" name="answer_id"   value="<?= $a['id'] ?>">
                            <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                            <button name="select_best" value="1" class="btn btn-sm btn-primary" data-confirm="Select this as the best answer and award 5 seed credits?">✓ Best</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <p class="text-muted text-sm" style="margin-bottom:.75rem">No answers yet. Be the first to help!</p>
            <?php endif; ?>

            <!-- Post answer form (not for asker, not for closed) -->
            <?php if ($isOpen && !$isAsker): ?>
            <form method="POST" style="display:flex;gap:.5rem;align-items:flex-end">
                <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                <div class="form-group" style="margin:0;flex:1">
                    <textarea name="answer" class="form-control" rows="2" placeholder="Share your gardening knowledge..." required></textarea>
                </div>
                <button name="post_answer" value="1" class="btn btn-primary" style="flex-shrink:0">Post Answer</button>
            </form>
            <?php elseif ($isAsker && $isOpen): ?>
                <p class="text-sm text-muted">You asked this question. Select the best answer above to award seed credits.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-info">No questions yet. Ask the community something!</div>
<?php endif; ?>

<script>function toggleSection(id){var el=document.getElementById(id);if(el)el.style.display=el.style.display==='none'?'':'none';}</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
