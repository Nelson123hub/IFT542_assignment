<?php
require __DIR__ . '/../src/config.php';

$user = require_login();

// VULN: session cookie lacks HttpOnly/SameSite; no lockout; no audit log.
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Dashboard - Student Registration</title></head>
<body>
<h1>Student Registration (starter build)</h1>
<p>Signed in as <?= $user['full_name'] ?> (<?= $user['role'] ?>)</p>
<ul>
  <li><a href="profile.php">Profile</a></li>
  <li><a href="courses.php">Register courses</a></li>
  <li><a href="my_courses.php">My courses</a></li>
  <li><a href="upload.php">Upload document</a></li>
  <li><a href="import_preview.php">Import preview</a></li>
  <?php if ($user['role'] === 'admin'): ?>
    <li><a href="admin_courses.php">Admin: courses</a></li>
    <li><a href="admin_users.php">Admin: users</a></li>
  <?php endif; ?>
  <li><a href="logout.php">Logout</a></li>
</ul>
</body>
</html>
