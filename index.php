<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Chefly</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<div class="container">
    <h1>Ricette</h1>
    <p class="subtitle">ricette</p>

    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Bentornato in cucina, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></p>
        <div class="button-group">
            <a href="controller/logOutController.php" class="btn btn-logout">Esci</a>
        </div>
    <?php else: ?>
        <p>Sei pronto per una nuova ricetta?</p>
        <div class="button-group">
            <a href="view/login.php" class="btn">Login</a>
            <a href="view/signup.php" class="btn">Registrati</a>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg'])): ?>
        <div class="message success">Bentornato nel club! Operazione riuscita.</div>
    <?php endif; ?>
</div>
</body>
</html>