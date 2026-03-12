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
    <h1>CHEFLY</h1>
    <p class="subtitle">ricette per tutti</p>

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
        <?php if ($_GET['msg']==='successLogin'): ?>
        <div class="message success">Bentornato nel club! Operazione riuscita.</div>
        <?php elseif ($_GET['msg']==='successRegister'): ?>
            <div class="message success">Benvenuto nel club, esegui il login ! .</div>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>