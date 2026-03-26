<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Chefly</title>

    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/footer.css">

</head>

<body>

<?php include "include/header.php"; ?>

<main style="min-height: 70vh;">
    <?php if (isset($_SESSION['user_id'])): ?>
        <h1>Benvenuto su Chefly!</h1>
    <?php endif; ?>
</main>

<?php include "include/footer.php"; ?>

<script> <?php include_once "js/dropDownMenu.js"?> </script>
</body>
</html>