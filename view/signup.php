<?php
/* view/signup.php */
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrati — Chefly</title>
    <link rel="stylesheet" href="../css/chefly.css">
    <style>
        /* Override globale: questa pagina non ha header fisso */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0 !important; /* azzera il padding-top: 90px del body globale */
        }

        body {
            background: var(--cream);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 32px 16px !important; /* spazio verticale su mobile */
            box-sizing: border-box;
        }

        /* Card centrale */
        .auth-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 44px 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 8px 32px rgba(26,16,8,.07);
            box-sizing: border-box;
        }

        /* Logo centrato */
        .auth-logo {
            display: block;
            height: 52px;
            margin: 0 auto 28px;
        }

        .auth-title {
            font-family: var(--font-serif);
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--brown);
            text-align: center;
            margin-bottom: 6px;
        }

        .auth-subtitle {
            font-size: .88rem;
            color: var(--muted);
            text-align: center;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        /* Form row per nome/cognome */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }
        .form-group:last-of-type { margin-bottom: 0; }

        .form-label {
            display: block;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #6B5C48;
            margin-bottom: 6px;
        }
        .form-label .opt {
            font-weight: 400;
            color: var(--muted-light);
            text-transform: none;
            letter-spacing: 0;
        }

        .form-control {
            width: 100%;
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 11px 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: .92rem;
            color: var(--brown);
            box-sizing: border-box;
            transition: border-color .2s, box-shadow .2s, background .2s;
            appearance: none;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--caramel);
            box-shadow: 0 0 0 3px rgba(196,98,45,.12);
            background: var(--white);
        }
        .form-control::placeholder { color: #C4C0B8; }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* Flash messages */
        .flash {
            padding: 12px 16px;
            border-radius: 9px;
            font-size: .84rem;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .flash--error   { background: #FFF1F0; color: #991B1B; border: 1px solid #FECACA; }
        .flash a        { color: inherit; font-weight: 700; }

        /* Bottone submit */
        .btn-submit {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--caramel);
            color: #FFF;
            border: none;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 24px;
            transition: background .2s, transform .1s;
            letter-spacing: .3px;
        }
        .btn-submit:hover  { background: var(--caramel-dark); }
        .btn-submit:active { transform: scale(.98); }

        /* Footer link */
        .auth-footer {
            text-align: center;
            margin-top: 22px;
            font-size: .85rem;
            color: var(--muted);
        }
        .auth-link {
            color: var(--caramel);
            text-decoration: none;
            font-weight: 600;
        }
        .auth-link:hover { text-decoration: underline; }

        .back-home-link {
            display: block;
            text-align: center;
            margin-top: 14px;
            font-size: .75rem;
            color: var(--muted-light);
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: color .2s;
        }
        .back-home-link:hover { color: var(--brown); }

        /* ── Responsive ── */
        @media (max-width: 520px) {
            .auth-card {
                padding: 32px 20px;
                border-radius: 18px;
            }
            /* Nome e cognome in colonna su mobile stretto */
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>

<div class="auth-card">

    <a href="../index.php">
        <img src="/img/logo.png" alt="Chefly" class="auth-logo">
    </a>

    <h1 class="auth-title">Crea il tuo account</h1>
    <p class="auth-subtitle">Unisciti alla community di cuochi Chefly</p>

    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] === 'mail'): ?>
            <div class="flash flash--error">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Email già registrata. <a href="login.php">Accedi</a>
            </div>
        <?php elseif ($_GET['error'] === 'username'): ?>
            <div class="flash flash--error">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Username già in uso. Scegline un altro.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <form action="../controller/singupController.php" method="POST" novalidate>

        <div class="form-row">
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

        <div class="form-group">
            <label class="form-label">Biografia <span class="opt">(opzionale)</span></label>
            <textarea name="biografia" class="form-control" placeholder="Raccontaci qualcosa di te e della tua cucina..."></textarea>
        </div>

        <button type="submit" class="btn-submit">Crea account</button>

    </form>

    <div class="auth-footer">
        Hai già un account?
        <a href="login.php" class="auth-link">Accedi</a>
    </div>

    <a href="../index.php" class="back-home-link">← Torna alla Home</a>

</div>

</body>
</html>