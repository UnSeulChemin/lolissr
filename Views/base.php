<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?></title>
    <link rel="shortcut icon" href="<?= $basePath; ?>public/images/favicon/favicon.png">
    <link rel="stylesheet" href="<?= $basePath; ?>public/css/app.css">
    <script src="https://kit.fontawesome.com/118716b668.js" crossorigin="anonymous"></script>
</head>
<body>

<?php require_once 'partials/header.php'; ?>
<main><?= $content ?></main>

</body>
</html>