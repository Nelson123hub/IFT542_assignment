<?php
require __DIR__ . '/../src/config.php';

$user = require_login();

// VULN: concatenated SQL.
$id = (int) $user['id'];
$r = mysqli_query($conn,
    "SELECT c.code, c.title, c.credit_units, e.status
     FROM enrolments e JOIN courses c ON c.id = e.course_id
     WHERE e.user_id = $id ORDER BY e.registered_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>My Courses - Student Registration</title></head>
<body>
<p><a href="index.php">&larr; Dashboard</a></p>
<h1>My Courses</h1>
<table border="1" cellpadding="6">
<tr><th>Code</th><th>Title</th><th>Units</th><th>Status</th></tr>
<?php while ($row = mysqli_fetch_assoc($r)): ?>
<tr>
  <td><?= $row['code'] ?></td>
  <td><?= $row['title'] ?></td>
  <td><?= $row['credit_units'] ?></td>
  <td><?= $row['status'] ?></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
