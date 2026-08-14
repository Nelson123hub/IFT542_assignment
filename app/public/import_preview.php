<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$user = Auth::requireLogin();
$preview = null;
$error = '';
$url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        Csrf::verify();
        $url = Input::trimmed($_POST['url'] ?? '');
        if ($url === '') {
            throw new ValidationError(['url' => 'URL is required.']);
        }
        $preview = Ssrf::fetchPreview($url);
        Logger::log('url_preview', ['outcome' => 'allowed', 'url_host' => parse_url($url, PHP_URL_HOST) ?? '']);
    } catch (SsrfError $e) {
        $error = $e->getMessage();
        Logger::log('url_preview', ['outcome' => 'blocked', 'url_host' => parse_url($url, PHP_URL_HOST) ?? ''], 'warning');
    } catch (ValidationError $e) {
        $error = reset($e->errors);
    }
}

$pageTitle = 'Import Preview';
require __DIR__ . '/../views/header.php';
?>
<div class="card">
  <h1>URL Preview / Import</h1>
  <p class="muted">Fetch a preview from the allowed course-catalogue service
  only. Loopback, private and cloud-metadata addresses are blocked.</p>

  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="/import_preview.php">
    <?= Csrf::hiddenField() ?>
    <label for="url">Destination URL</label>
    <input type="url" id="url" name="url" required
           placeholder="http://catalog.ftminna.internal/"
           value="<?= e($url) ?>">
    <button type="submit">Preview</button>
  </form>

  <?php if ($preview !== null): ?>
    <h2>Preview</h2>
    <pre class="muted"><?= e($preview) ?></pre>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../views/footer.php'; ?>
