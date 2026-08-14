<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

$user = Auth::requireAdmin();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        Csrf::verify();
        $id = Input::intRange($_POST['enrolment_id'] ?? null, 1, 1000000, 'enrolment_id');
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['enrolled', 'dropped'], true)) {
            throw new ValidationError(['status' => 'Invalid status.']);
        }
        Database::run(
            'UPDATE enrolments SET status = ? WHERE id = ?',
            [$status, $id]
        );
        $success = 'Enrolment updated.';
        Logger::log('enrolment_updated', ['outcome' => $status]);
    } catch (ValidationError $e) {
        $errors = $e->errors;
    }
}

$rows = Database::run(
    'SELECT e.id, e.status, e.registered_at, u.matric_no, u.full_name, u.email, c.code, c.title
     FROM enrolments e
     JOIN users u   ON u.id = e.user_id
     JOIN courses c ON c.id = e.course_id
     ORDER BY e.registered_at DESC'
)->fetchAll();

$pageTitle = 'Manage Enrolments';
require __DIR__ . '/../../views/header.php';
?>
<div class="card">
  <h1>Manage Enrolments</h1>
  <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
  <?php if (isset($errors['csrf_token'])): ?><div class="alert alert-error"><?= e($errors['csrf_token']) ?></div><?php endif; ?>

  <table>
    <tr><th>Student</th><th>Matric</th><th>Course</th><th>Status</th><th>Registered</th><th>Actions</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['full_name']) ?></td>
      <td><?= e($r['matric_no']) ?></td>
      <td><?= e($r['code']) ?> - <?= e($r['title']) ?></td>
      <td><?= e($r['status']) ?></td>
      <td><?= e($r['registered_at']) ?></td>
      <td>
        <?php if ($r['status'] !== 'enrolled'): ?>
        <form method="post" action="/admin/enrolments.php" style="display:inline">
          <?= Csrf::hiddenField() ?>
          <input type="hidden" name="enrolment_id" value="<?= (int) $r['id'] ?>">
          <input type="hidden" name="status" value="enrolled">
          <button type="submit">Approve</button>
        </form>
        <?php endif; ?>
        <?php if ($r['status'] !== 'dropped'): ?>
        <form method="post" action="/admin/enrolments.php" style="display:inline">
          <?= Csrf::hiddenField() ?>
          <input type="hidden" name="enrolment_id" value="<?= (int) $r['id'] ?>">
          <input type="hidden" name="status" value="dropped">
          <button type="submit" class="ghost">Drop</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php require __DIR__ . '/../../views/footer.php'; ?>
