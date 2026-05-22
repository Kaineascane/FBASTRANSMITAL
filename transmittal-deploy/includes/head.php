<?php
/** @var string $pageTitle */
/** @var string $headerTitle */
/** @var string|null $headerActionHref */
/** @var string|null $headerActionLabel */
$pageTitle = $pageTitle ?? 'S.I Transmittal';
$headerTitle = $headerTitle ?? 'S.I TRANSMITTAL SYSTEM';
$headerActionHref = $headerActionHref ?? 'search.php';
$headerActionLabel = $headerActionLabel ?? 'Search / Reprint';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#0f2744">
  <title><?= h($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<header class="app-header">
  <div class="container app-header-inner">
    <a href="index.php" class="app-brand" aria-label="Home">
      <span class="app-brand-icon"><i class="bi bi-receipt"></i></span>
      <span class="app-brand-text"><?= h($headerTitle) ?></span>
    </a>
    <nav class="app-nav">
      <a href="<?= h($headerActionHref) ?>" class="btn btn-header-action">
        <i class="bi bi-search"></i>
        <span><?= h($headerActionLabel) ?></span>
      </a>
    </nav>
  </div>
</header>

<main class="app-main container">
