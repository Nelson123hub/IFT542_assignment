<?php
require __DIR__ . '/../src/config.php';

$user = require_login();
$error = '';
$updated = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $id    = $user['id'];

    // VULN: concatenated UPDATE; also unsafe if run on GET-based input.
    $sql = "UPDATE users SET full_name = '$name', phone = '$phone', email = '$email' WHERE id = $id";
    mysqli_query($conn, $sql);
    if (mysqli_error($conn)) {
        $error = mysqli_error($conn);
    } else {
        $updated = 'Profile updated.';
        $user = current_user();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Profile - Student Registration</title></head>
<body>
<p><a href="index.php">&larr; Dashboard</a></p>
<h1>Profile</h1>
<?php if ($updated): ?><p style="color:green"><?= $updated ?></p><?php endif; ?>
<?php if ($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>

<!-- VULN: reflected user-controlled value echoed unescaped (XSS) -->
<p>Matric: <?= $user['matric_no'] ?></p>
<form method="post" action="profile.php">
  <label>Full name <input type="text" name="full_name" value="<?= $_POST['full_name'] ?? $user['full_name'] ?>"></label>
  <label>Email <input type="text" name="email" value="<?= $_POST['email'] ?? $user['email'] ?>"></label>
  <label>Phone <input type="text" name="phone" value="<?= $_POST['phone'] ?? $user['phone'] ?>"></label>
  <button type="submit">Save</button>
</form>
</body>
</html>
