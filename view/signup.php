<?php
/* view/signup.php */
include "../include/inizio.php";
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrati — Chefly</title>
    <link rel="stylesheet" href="../css/chefly.css">
    <style>
        body { padding-top: 0; }
        .auth-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
        .auth-card { max-width: 520px; }
    </style>
</head>
<body class="auth-page">

<div class="auth-card fade-up">

    <a href="../index.php">
        <img src="/img/logo.png" alt="Chefly" class="auth-logo">
    </a>

    <h1 class="auth-title">Crea il tuo account</h1>
    <p class="auth-subtitle">Unisciti alla community di cuochi Chefly</p>

    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] === 'mail'): ?>
            <div class="flash flash--error">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Email già registrata. <a href="login.php" class="auth-link">Accedi</a>
            </div>
        <?php elseif ($_GET['error'] === 'username'): ?>
            <div class="flash flash--error">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Username già in uso. Scegline un altro.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <form action="../controller/singupController.php" method="POST">

        <div class="form-row" style="margin-bottom:20px;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" placeholder="Mario" required>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Cognome</label>
                <input type="text" name="cognome" class="form-control" placeholder="Rossi" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="mariorossi_chef" required>
        </div>

        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="mail" class="form-control" placeholder="mario@esempio.it" required>
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <div class="form-group" style="margin-bottom:28px;">
            <label class="form-label">Biografia <span class="opt">(opzionale)</span></label>
            <textarea name="biografia" class="form-control" placeholder="Raccontaci qualcosa di te e della tua cucina..." style="min-height:80px;"></textarea>
        </div>

        <button type="submit" class="btn btn-caramel" style="width:100%; padding:14px; font-size:.95rem; border-radius:12px;">
            Crea account
        </button>

    </form>

    <div class="auth-footer">
        Hai già un account?
        <a href="login.php" class="auth-link">Accedi</a>
    </div>

    <a href="../index.php" class="back-home-link">← Torna alla Home</a>

</div>

</body>
</html>