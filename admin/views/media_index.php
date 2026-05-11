<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h1>📁 File Manager</h1>
    <p>Upload, view, and manage your garden documents and images.</p>
</div>

<!-- Upload Card -->
<div class="card mb-2">
    <div class="card-header">📤 Upload New File</div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-start">
            <div class="form-group" style="flex:1;min-width:250px;margin:0">
                <label class="small fw-semibold">Select File <span class="text-muted">(JPG, PNG, PDF, DOC, TXT)</span></label>
                <input type="file" name="file" class="form-control" required style="padding: .375rem .75rem;">
            </div>
            <div class="form-group" style="flex:2;min-width:250px;margin:0">
                <label class="small fw-semibold">Description / Notes <span class="text-muted">(Optional)</span></label>
                <input type="text" name="description" class="form-control" placeholder="What is this file?">
            </div>
            <div style="margin-top:1.4rem">
                <button type="submit" name="upload_file" value="1" class="btn btn-primary" style="height:38px">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Files Gallery / Table -->
<div class="card">
    <div class="card-header">
        Uploaded Files <span class="badge badge-info" style="margin-left:.5rem"><?= count($files) ?> files</span>
    </div>
    
    <?php if ($files): ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>File Details</th>
                    <th>Description</th>
                    <th>Uploaded By</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $f): 
                    $ext = strtolower(pathinfo($f['file_name'], PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg','jpeg','png','gif']);
                    $fileUrl = APP_URL . '/assets/uploads/' . $f['file_name'];
                    $sizeKb = round($f['file_size'] / 1024, 1);
                ?>
                <tr>
                    <td style="width:80px;text-align:center">
                        <?php if ($isImage): ?>
                            <a href="<?= $fileUrl ?>" target="_blank">
                                <img src="<?= $fileUrl ?>" alt="preview" style="width:50px;height:50px;object-fit:cover;border-radius:4px;border:1px solid var(--gray-300)">
                            </a>
                        <?php else: ?>
                            <div style="width:50px;height:50px;background:var(--gray-200);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:var(--gray-600)">
                                📄
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= $fileUrl ?>" target="_blank" style="font-weight:600;text-decoration:none;color:var(--green-dark)">
                            <?= e((strlen($f['original_name']) > 30) ? substr($f['original_name'], 0, 27) . '...' : $f['original_name']) ?>
                        </a>
                        <div class="text-sm text-muted mt-1"><?= strtoupper($ext) ?> &bull; <?= $sizeKb ?> KB</div>
                    </td>
                    <td><?= e($f['description'] ?: '—') ?></td>
                    <td><?= e($f['full_name']) ?></td>
                    <td class="text-sm text-muted"><?= date('d M Y, H:i', strtotime($f['uploaded_at'])) ?></td>
                    <td>
                        <a href="<?= $fileUrl ?>" download="<?= e($f['original_name']) ?>" class="btn btn-sm btn-secondary" title="Download">⬇️</a>
                        <?php if ($user['role_name'] === 'admin' || $user['id'] === $f['user_id']): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="file_id" value="<?= $f['id'] ?>">
                            <button type="submit" name="delete_file" value="1" class="btn btn-sm btn-danger" data-confirm="Are you sure you want to delete this file permanently?" title="Delete">🗑️</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="padding:3rem;text-align:center;color:var(--gray-500)">
        <div style="font-size:3rem;margin-bottom:1rem">📂</div>
        <p>No files uploaded yet.</p>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('click', function(e) {
    var btn = e.target.closest('[data-confirm]');
    if (btn && !confirm(btn.dataset.confirm)) e.preventDefault();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
