<header class="main-header">

    <div class="header-left">
        <img src="img/logo.png" class="logo">

        <a href="index.php" class="site-title">
            Chefly
        </a>
    </div>


    <div class="header-center">
        <form action="ricerca.php" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Cerca ricette..." class="search-bar">
            <button type="submit" class="search-button">🔍</button>
        </form>
    </div>


    <div class="header-right">

        <?php if(isset($_SESSION['user_id'])): ?>

            <a href="profilo.php">  <!-- TODO -->
                <img src="img/fotoProfilo.jpg" class="profile-pic">
            </a>

            <!--
       senza spazio: < ?php echo $_SESSION['fotoProfilo']; ?> da ficcare dentro l'src
    -->

        <?php else: ?>

            <a href="view/login.php" class="login-button">
                <img src="img/fotoProfilo.jpg" class="user-icon">
                <span>Accedi</span>
            </a>

        <?php endif; ?>

    </div>

</header>