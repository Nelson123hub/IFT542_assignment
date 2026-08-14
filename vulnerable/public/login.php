<?php
require __DIR__ . '/../src/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';

    // VULN: raw input concatenated into SQL - classic SQL injection.
    // The first row of the result is trusted, so payloads like
    // "email=' OR '1'='1' -- " authenticate as the first user (admin).
    $sql = "SELECT * FROM users WHERE email = '$email' AND password_hash = '$pass'";
    $result = mysqli_query($conn, $sql);

    if ($result && ($row = mysqli_fetch_assoc($result))) {
        $_SESSION['user_id'] = $row['id'];   // VULN: no session-ID regeneration
        $_SESSION['role'] = $row['role'];
        header('Location: index.php');
        exit;
    }
    $error = 'Login failed. ' . mysqli_error($conn); // VULN: leaks DB details
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Login - Student Registration</title></head>
<body>
<h1>Student Login (starter build)</h1>
<?php if ($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
<form method="post" action="login.php">
  <label>Email <input type="text" name="email" value="<?= $_POST['email'] ?? '' ?>"></label>
  <label>Password <input type="text" name="password"></label>
  <button type="submit">Sign in</button>
</form>
<p><small>Demo: admin@ftminna.edu.ng / admin</small></p>
</body>
</html>
