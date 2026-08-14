<?php
/** @var array|null $user current authenticated user (optional) */
$current = $user ?? null;
$pageTitle = $pageTitle ?? 'Dashboard';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> &middot; <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header class="site-header">
  <div class="wrap">
    <span class="brand"><?= e(APP_NAME) ?></span>
    <?php if ($current): ?>
    <nav>
      <a href="/index.php">Dashboard</a>
      <a href="/profile.php">Profile</a>
      <a href="/courses.php">Register Courses</a>
      <a href="/my_courses.php">My Courses</a>
      <a href="/upload.php">Documents</a>
      <a href="/import_preview.php">Import Preview</a>
      <?php if (($current['role'] ?? '') === 'admin'): ?>
      <a href="/admin/courses.php">Admin</a>
      <?php endif; ?>
      <a href="/logout.php">Logout</a>
    </nav>
    <?php endif; ?>
  </div>
</header>
<main class="wrap">
