<?php
/* view/login.php */
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accedi — Chefly</title>
    <link rel="stylesheet" href="../css/chefly.css">
    <style>
        body { padding-top: 0; }
        .auth-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
    </style>
</head>
<body class="auth-page">

<div class="auth-card fade-up">

    <a href="../index.php">
        <img src="/img/logo.png" alt="Chefly" class="auth-logo">
    </a>

    <h1 class="auth-title">Bentornato</h1>
    <p class="auth-subtitle">Accedi al tuo account Chefly</p>

    <?php if (isset($_GET['error'])): ?>
        <div class="flash flash--error">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php echo $_GET['error'] == 1 ? 'Email o password errati.' : 'Si è verificato un errore.'; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'successRegister'): ?>
        <div class="flash flash--success">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
            Registrazione completata! Accedi ora.
        </div>
    <?php endif; ?>

    <form action="../controller/logInController.php" method="POST">

        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email"
                   name="mail"
                   class="form-control"
                   placeholder="la-tua@email.com"
                   value="<?php echo htmlspecialchars($_GET['mail'] ?? ''); ?>"
                   required>
        </div>

        <div class="form-group" style="margin-bottom:28px;">
            <label class="form-label">Password</label>
            <input type="password"
                   name="password"
                   class="form-control"
                   placeholder="••••••••"
                   required>
        </div>

        <button type="submit" class="btn btn-caramel" style="width:100%; padding:14px; font-size:.95rem; border-radius:12px;">
            Accedi
        </button>

    </form>

    <div class="auth-footer">
        Non hai un account?
        <a href="signup.php" class="auth-link">Registrati gratis</a>
    </div>

    <a href="../index.php" class="back-home-link">← Torna alla Home</a>

</div>

</body>
</html>