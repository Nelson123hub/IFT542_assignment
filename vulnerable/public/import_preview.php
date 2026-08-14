<?php
require __DIR__ . '/../src/config.php';

$user = require_login();
$preview = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // VULN: SSRF - arbitrary URL fetched server-side with no allowlist.
    $url = $_POST['url'] ?? '';
    $preview = @file_get_contents($url);
    if ($preview === false) {
        $preview = 'Could not fetch ' . $url;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Import Preview - Student Registration</title></head>
<body>
<p><a href="index.php">&larr; Dashboard</a></p>
<h1>URL Preview / Import (starter build)</h1>
<form method="post" action="import_preview.php">
  <label>URL <input type="text" name="url" placeholder="http://catalog.ftminna.internal/"></label>
  <button type="submit">Preview</button>
</form>
<pre><?= $preview ?></pre>
</body>
</html>
