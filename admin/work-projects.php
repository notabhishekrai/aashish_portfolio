<?php

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$adminUser = require_login();

$projects = get_db()->query(
    'SELECT id, slug, title, tag, year, is_featured, status FROM work_projects ORDER BY archive_sort_order DESC'
)->fetchAll();

$pageTitle = 'Work projects';
require __DIR__ . '/includes/header.php';
?>
<h1>Work projects</h1>
<p><a href="/admin/work-project-edit.php" class="btn btn-primary">+ New project</a></p>

<div class="admin-table-wrap">
<table class="table">
<thead><tr><th>Title</th><th>Tag</th><th>Year</th><th>Featured</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($projects as $project): ?>
<tr>
<td><?= e($project['title']) ?><br><span class="help-text"><?= e($project['slug']) ?></span></td>
<td><?= e($project['tag']) ?></td>
<td><?= e($project['year']) ?></td>
<td><?= $project['is_featured'] ? 'Yes' : '—' ?></td>
<td><?= e(ucfirst($project['status'])) ?></td>
<td class="admin-table-actions">
<a href="/admin/work-project-edit.php?id=<?= (int) $project['id'] ?>">Edit</a>
<a href="/work-detail.php?slug=<?= urlencode($project['slug']) ?>" target="_blank" rel="noopener">View</a>
<form method="post" action="/admin/work-project-delete.php" data-confirm="Delete &quot;<?= e($project['title']) ?>&quot;? This can't be undone.">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int) $project['id'] ?>">
<button type="submit" class="admin-link-button">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if ($projects === []): ?>
<tr><td colspan="6">No projects yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
