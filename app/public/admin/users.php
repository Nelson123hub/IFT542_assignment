<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

$user = Auth::requireAdmin();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        Csrf::verify();
        $targetId = Input::intRange($_POST['user_id'] ?? null, 1, 1000000, 'user_id');

        if ($action === 'unlock') {
            Database::run(
                'UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?',
                [$targetId]
            );
            $success = 'Account unlocked.';
        } elseif ($action === 'reset_password') {
            $newPass = bin2hex(random_bytes(9));
            Database::run(
                'UPDATE users SET password_hash = ? WHERE id = ?',
                [password_hash($newPass, PASSWORD_ARGON2ID), $targetId]
            );
            $success = "Password reset to: $newPass (note it once; it will not be shown again).";
            Logger::log('admin_password_reset', ['outcome' => 'success']);
        } else {
            throw new ValidationError(['action' => 'Unknown action.']);
        }
    } catch (ValidationError $e) {
        $errors = $e->errors;
    }
}

$users = Database::run(
    'SELECT id, matric_no, full_name, email, role, failed_attempts,
            locked_until, created_at
     FROM users ORDER BY created_at DESC'
)->fetchAll();

$pageTitle = 'Manage Users';
require __DIR__ . '/../../views/header.php';
?>
<div class="card">
  <h1>Manage Users</h1>
  <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
  <?php if (isset($errors['csrf_token'])): ?><div class="alert alert-error"><?= e($errors['csrf_token']) ?></div><?php endif; ?>

  <table>
    <tr>
      <th>ID</th><th>Matric</th><th>Name</th><th>Email</th><th>Role</th>
      <th>Failed</th><th>Locked until</th><th>Actions</th>
    </tr>
    <?php foreach ($users as $u): ?>
    <tr>
      <td><?= (int) $u['id'] ?></td>
      <td><?= e($u['matric_no']) ?></td>
      <td><?= e($u['full_name']) ?></td>
      <td><?= e($u['email']) ?></td>
      <td><?= e($u['role']) ?></td>
      <td><?= (int) $u['failed_attempts'] ?></td>
      <td><?= $u['locked_until'] ? e($u['locked_until']) : '-' ?></td>
      <td>
        <form method="post" action="/admin/users.php" style="display:inline">
          <?= Csrf::hiddenField() ?>
          <input type="hidden" name="action" value="unlock">
          <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
          <button type="submit">Unlock</button>
        </form>
        <form method="post" action="/admin/users.php" style="display:inline">
          <?= Csrf::hiddenField() ?>
          <input type="hidden" name="action" value="reset_password">
          <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
          <button type="submit" class="ghost">Reset password</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php require __DIR__ . '/../../views/footer.php'; ?>
