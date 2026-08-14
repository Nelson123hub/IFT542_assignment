<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        Csrf::verify();
        Auth::login(
            (string) ($_POST['email'] ?? ''),
            (string) ($_POST['password'] ?? '')
        );
        $next = (string) ($_POST['next'] ?? '/index.php');
        if (!str_starts_with($next, '/') || str_starts_with($next, '//')) {
            $next = '/index.php';
        }
        header('Location: ' . $next);
        exit;
    } catch (AuthException $e) {
        http_response_code($e->httpStatus);
        $error = $e->safeMessage;
    } catch (ValidationError $e) {
        http_response_code(422);
        $errors = $e->errors;
    }
}

$next = (string) ($_GET['next'] ?? '/index.php');
$user = null;
$pageTitle = 'Login';
require __DIR__ . '/../views/header.php';
?>
<div class="card login-card">
  <h1>Student Login</h1>
  <p class="muted">Fictitious accounts only - authorised lab environment.</p>

  <?php if (isset($error)): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
  <?php endif; ?>
  <?php if (isset($errors['csrf_token'])): ?>
    <div class="alert alert-error"><?= e($errors['csrf_token']) ?></div>
  <?php endif; ?>

  <form method="post" action="/login.php">
    <?= Csrf::hiddenField() ?>
    <input type="hidden" name="next" value="<?= e($next) ?>">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required maxlength="254"
           value="<?= e($_POST['email'] ?? '') ?>" autocomplete="username">
    <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required maxlength="128"
           autocomplete="current-password">
    <?php if (isset($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>

    <button type="submit">Sign in</button>
  </form>
</div>
<?php require __DIR__ . '/../views/footer.php'; ?>
