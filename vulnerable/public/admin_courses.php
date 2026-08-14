<?php
require __DIR__ . '/../src/config.php';

$user = require_admin();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // VULN: no CSRF; concatenated SQL.
    $code = $_POST['code'] ?? '';
    $title = $_POST['title'] ?? '';
    $units = (int) ($_POST['credit_units'] ?? 2);
    $cap = (int) ($_POST['capacity'] ?? 60);
    if (mysqli_query($conn,
        "INSERT INTO courses (code, title, credit_units, capacity) VALUES ('$code', '$title', $units, $cap)")) {
        $message = 'Course added.';
    } else {
        $message = mysqli_error($conn);
    }
}

$rows = mysqli_query($conn, 'SELECT * FROM courses ORDER BY code');
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Admin Courses - Student Registration</title></head>
<body>
<p><a href="index.php">&larr; Dashboard</a></p>
<h1>Admin: Courses</h1>
<?php if ($message): ?><p style="color:green"><?= $message ?></p><?php endif; ?>
<form method="post" action="admin_courses.php">
  <label>Code <input type="text" name="code"></label>
  <label>Title <input type="text" name="title"></label>
  <label>Units <input type="number" name="credit_units" value="3"></label>
  <label>Capacity <input type="number" name="capacity" value="60"></label>
  <button type="submit">Add course</button>
</form>
<table border="1" cellpadding="6">
<tr><th>Code</th><th>Title</th><th>Units</th><th>Capacity</th></tr>
<?php while ($c = mysqli_fetch_assoc($rows)): ?>
<tr><td><?= $c['code'] ?></td><td><?= $c['title'] ?></td><td><?= $c['credit_units'] ?></td><td><?= $c['capacity'] ?></td></tr>
<?php endwhile; ?>
</table>
</body>
</html>
