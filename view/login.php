<?php
include "../include/inizio.php";
?>

    <body class="body-login">

<div class="container d-flex justify-content-center align-items-center vh-100">

    <div class="container-form text-center">

        <h1 class="login-title">
            Accesso Utente
        </h1>

        <p class="login-subtitle">Accedi al tuo account</p>

        <?php
        if (isset($_GET['error'])) {
            $msg = ($_GET['error'] == 1) ? "Email o password errati." : "Si è verificato un errore.";
            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($msg) . '</div>';
        }
        ?>

        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'successRegister'): ?>
            <div class="alert alert-success">Benvenuto nel club, esegui il login!</div>
        <?php endif; ?>

        <form action="../controller/logInController.php" method="post">

            <div class="form-group text-start">
                <label class="form-label-custom">Email</label>

                <input type="email"
                       name="mail"
                       class="form-control-custom"
                       placeholder="Inserisci la tua email"
                       value="<?php echo htmlspecialchars($_GET['mail'] ?? ''); ?>"
                       required>

            </div>

            <div class="form-group text-start">

                <label class="form-label-custom">Password</label>

                <input type="password"
                       name="password"
                       class="form-control-custom"
                       placeholder="Inserisci la password"
                       required>

            </div>

            <button type="submit" class="btn-login-action mt-3">
                Accedi
            </button>

        </form>

        <div class="mt-4">

            <p class="small text-uppercase login-text">
                Non hai un account?
                <a href="signup.php" class="gold-link">Registrati</a>
            </p>

            <a href="../index.php" class="back-home-link">
                ← Torna alla Home
            </a>

        </div>

    </div>

</div>

<link href="../css/login.css" rel="stylesheet">

<?php
include "../include/fine.php";
?>