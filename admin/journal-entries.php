<?php

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$adminUser = require_login();

$entries = get_db()->query(
    'SELECT id, slug, title, kicker, published_on, featured_sort_order, status FROM journal_entries ORDER BY archive_sort_order DESC'
)->fetchAll();

$pageTitle = 'Journal entries';
require __DIR__ . '/includes/header.php';
?>
<h1>Journal entries</h1>
<p><a href="/admin/journal-entry-edit.php" class="btn btn-primary">+ New entry</a></p>

<div class="admin-table-wrap">
<table class="table">
<thead><tr><th>Title</th><th>Kicker</th><th>Date</th><th>Featured</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($entries as $entry): ?>
<tr>
<td><?= e($entry['title']) ?><br><span class="help-text"><?= e($entry['slug']) ?></span></td>
<td><?= e($entry['kicker']) ?></td>
<td><?= e(date('M Y', strtotime($entry['published_on']))) ?></td>
<td><?= $entry['featured_sort_order'] !== null ? 'Yes' : '—' ?></td>
<td><?= e(ucfirst($entry['status'])) ?></td>
<td class="admin-table-actions">
<a href="/admin/journal-entry-edit.php?id=<?= (int) $entry['id'] ?>">Edit</a>
<a href="/journal-detail.php?slug=<?= urlencode($entry['slug']) ?>" target="_blank" rel="noopener">View</a>
<form method="post" action="/admin/journal-entry-delete.php" data-confirm="Delete &quot;<?= e($entry['title']) ?>&quot;? This can't be undone.">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int) $entry['id'] ?>">
<button type="submit" class="admin-link-button">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if ($entries === []): ?>
<tr><td colspan="6">No entries yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
