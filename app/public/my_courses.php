<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$user = Auth::requireLogin();

$rows = Database::run(
    'SELECT c.code, c.title, c.credit_units, e.status, e.registered_at
     FROM enrolments e JOIN courses c ON c.id = e.course_id
     WHERE e.user_id = ? ORDER BY e.registered_at DESC',
    [$user['id']]
)->fetchAll();

$pageTitle = 'My Courses';
require __DIR__ . '/../views/header.php';
?>
<div class="card">
  <h1>My Courses</h1>
  <?php if (!$rows): ?>
    <p class="muted">You have not registered for any courses yet.</p>
  <?php else: ?>
  <table>
    <tr><th>Code</th><th>Title</th><th>Units</th><th>Status</th><th>Registered</th></tr>
    <?php foreach ($rows as $row): ?>
    <tr>
      <td><?= e($row['code']) ?></td>
      <td><?= e($row['title']) ?></td>
      <td><?= (int) $row['credit_units'] ?></td>
      <td><?= e($row['status']) ?></td>
      <td><?= e($row['registered_at']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../views/footer.php'; ?>
