<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$user = Auth::requireLogin();
$errors = [];
$updated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        Csrf::verify();
        $fullName = Input::name($_POST['full_name'] ?? null);
        $phone    = Input::phone($_POST['phone'] ?? null);
        $email    = Input::email($_POST['email'] ?? null);

        // Update profile using a parameterized UPDATE - user data never
        // becomes part of the SQL text.
        Database::run(
            'UPDATE users SET full_name = ?, phone = ?, email = ? WHERE id = ?',
            [$fullName, $phone, $email, $user['id']]
        );
        $_SESSION['email'] = $email;
        $updated = true;
        $user = Auth::user();
    } catch (ValidationError $e) {
        $errors = $e->errors;
    }
}
$user = Auth::user();
$pageTitle = 'My Profile';
require __DIR__ . '/../views/header.php';
?>
<div class="card">
  <h1>My Profile</h1>
  <?php if ($updated): ?><div class="alert alert-success">Profile updated.</div><?php endif; ?>
  <?php if (isset($errors['csrf_token'])): ?><div class="alert alert-error"><?= e($errors['csrf_token']) ?></div><?php endif; ?>

  <form method="post" action="/profile.php">
    <?= Csrf::hiddenField() ?>
    <label for="matric_no">Matric number (read-only)</label>
    <input type="text" id="matric_no" value="<?= e($user['matric_no']) ?>" disabled>

    <label for="full_name">Full name</label>
    <input type="text" id="full_name" name="full_name" required maxlength="100"
           value="<?= e($user['full_name']) ?>">
    <?php if (isset($errors['full_name'])): ?><p class="field-error"><?= e($errors['full_name']) ?></p><?php endif; ?>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required maxlength="254"
           value="<?= e($user['email']) ?>">
    <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>

    <label for="phone">Phone</label>
    <input type="text" id="phone" name="phone" maxlength="20"
           value="<?= e($user['phone'] ?? '') ?>">
    <?php if (isset($errors['phone'])): ?><p class="field-error"><?= e($errors['phone']) ?></p><?php endif; ?>

    <button type="submit">Save changes</button>
  </form>
</div>
<?php require __DIR__ . '/../views/footer.php'; ?>
