<?php
require __DIR__ . '/../src/config.php';

$user = require_login();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = (int) ($_POST['course_id'] ?? 0);
    $userId   = (int) $user['id'];

    // VULN: concatenated INSERT with no CSRF protection and no capacity check.
    $sql = "INSERT INTO enrolments (user_id, course_id, status) VALUES ($userId, $courseId, 'pending')";
    if (mysqli_query($conn, $sql)) {
        $message = 'Registered!';
    } else {
        $message = mysqli_error($conn); // VULN: leaks schema details
    }
}

$rows = mysqli_query($conn, 'SELECT * FROM courses ORDER BY code');
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Course Registration - Student Registration</title></head>
<body>
<p><a href="index.php">&larr; Dashboard</a></p>
<h1>Course Registration</h1>
<?php if ($message): ?><p style="color:green"><?= $message ?></p><?php endif; ?>
<table border="1" cellpadding="6">
<tr><th>Code</th><th>Title</th><th>Units</th><th></th></tr>
<?php while ($c = mysqli_fetch_assoc($rows)): ?>
<tr>
  <td><?= $c['code'] ?></td>
  <td><?= $c['title'] ?></td>
  <td><?= $c['credit_units'] ?></td>
  <td>
    <form method="post" action="courses.php">
      <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
      <button type="submit">Register</button>
    </form>
  </td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
