<?php include_once 'script/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?></title>
	<link rel="shortcut icon" type="image/png" href="<?= $pathRedirect; ?>public/images/favicon/favicon.png">
	<link rel="stylesheet" type="text/css" href="<?= $pathRedirect; ?>public/css/reset.css">
	<link rel="stylesheet" type="text/css" href="<?= $pathRedirect; ?>public/css/app.css">
    <link rel="stylesheet" media="print" type="text/css" href="<?= $pathRedirect; ?>public/css/print.css" /> 
    <script src="https://kit.fontawesome.com/118716b668.js" crossorigin="anonymous"></script>
</head>
<body>

<?php require_once 'partials/header.php'; ?>
<main><?= $content ?></main>

</body>
</html>