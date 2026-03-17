<?php
include "../include/inizio.php";
?>

    <body class="body-login">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="container-form text-center">

        <h1 class="login-title">
            Crea il tuo account
        </h1>

        <p class="login-subtitle">Unisciti alla piattaforma</p>

        <?php if (isset($_GET['error'])): ?>

            <?php if ($_GET['error']==='mail'): ?>
                <div class="alert alert-login-error">
                    Utente già registrato, esegui il
                    <a href="login.php" class="gold-link">Login</a>
                </div>

            <?php elseif ($_GET['error']==='username'): ?>
                <div class="alert alert-login-error">
                    Username già esistente!
                </div>
            <?php endif; ?>

        <?php endif; ?>


        <form action="../controller/singupController.php" method="post">

            <div class="row">

                <div class="col-md-6 text-start">
                    <label class="form-label-custom">Nome</label>

                    <input type="text"
                           name="nome"
                           class="form-control-custom"
                           placeholder="Inserisci il nome"
                           required>

                </div>

                <div class="col-md-6 text-start">
                    <label class="form-label-custom">Cognome</label>

                    <input type="text"
                           name="cognome"
                           class="form-control-custom"
                           placeholder="Inserisci il cognome"
                           required>

                </div>

            </div>


            <div class="form-group text-start">

                <label class="form-label-custom">Username</label>

                <input type="text"
                       name="username"
                       class="form-control-custom"
                       placeholder="Scegli uno username"
                       required>

            </div>


            <div class="form-group text-start">

                <label class="form-label-custom">Email</label>

                <input type="email"
                       name="mail"
                       class="form-control-custom"
                       placeholder="Inserisci la tua email"
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


            <div class="form-group text-start">

                <label class="form-label-custom">Biografia</label>

                <textarea
                        name="biografia"
                        class="form-control-custom"
                        placeholder="Scrivi una breve biografia"
                ></textarea>

            </div>


            <button type="submit" class="btn-login-action mt-3">
                Registrati
            </button>

        </form>


        <div class="mt-4">

            <p class="small text-uppercase login-text">

                Hai già un account?
                <a href="login.php" class="gold-link">Accedi</a>

            </p>

            <a href="../index.php" class="back-home-link">
                ← Torna alla Home
            </a>

        </div>

    </div>
</div>

<link href="../css/signup.css" rel="stylesheet">

<?php
include "../include/fine.php";
?>