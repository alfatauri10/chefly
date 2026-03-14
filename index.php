<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Chefly</title>

    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">

</head>

<body>

<?php include "include/header.php"; ?>

<?php if (isset($_SESSION['user_id'])): ?>
    <!-- codice se l'utente è loggato -->
<?php endif; ?>



<script> <?php include_once "js/dropDownMenu.js"?> </script>
    </body>
</html>