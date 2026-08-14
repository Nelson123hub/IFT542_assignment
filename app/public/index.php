<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$user = Auth::requireLogin();
$stats = [];

// Summary numbers for the student dashboard.
$enrolled = Database::run(
    'SELECT COUNT(*) FROM enrolments WHERE user_id = ? AND status = "enrolled"',
    [$user['id']]
)->fetchColumn();
$docs = Database::run(
    'SELECT COUNT(*) FROM documents WHERE user_id = ?',
    [$user['id']]
)->fetchColumn();
$pending = Database::run(
    'SELECT COUNT(*) FROM enrolments WHERE user_id = ? AND status = "pending"',
    [$user['id']]
)->fetchColumn();

$pageTitle = 'Dashboard';
require __DIR__ . '/../views/header.php';
?>
<div class="card">
  <h1>Welcome, <?= e($user['full_name']) ?></h1>
  <p class="muted">Matric number: <?= e($user['matric_no']) ?> &middot; Role: <?= e($user['role']) ?></p>
</div>

<div class="card">
  <h2>Your summary</h2>
  <table>
    <tr><th>Enrolled courses</th><td><?= (int) $enrolled ?></td></tr>
    <tr><th>Pending registrations</th><td><?= (int) $pending ?></td></tr>
    <tr><th>Uploaded documents</th><td><?= (int) $docs ?></td></tr>
  </table>
  <p>
    <a class="btn" href="/courses.php">Register for courses</a>
    <a class="btn ghost" href="/upload.php">Upload document</a>
  </p>
</div>

<?php if (($user['role'] ?? '') === 'admin'): ?>
<div class="card">
  <h2>Administration</h2>
  <p><a href="/admin/users.php">Manage users</a> &middot;
     <a href="/admin/courses.php">Manage courses</a> &middot;
     <a href="/admin/enrolments.php">Manage enrolments</a></p>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../views/footer.php'; ?>
