<header class="main-header">
    <div class="header-left">
        <!-- Percorsi aggiornati con / iniziale -->
        <img src="img/logo.png" class="logo">
        <a href="index.php" class="site-title">
            Chefly
        </a>
    </div>

    <div class="header-center">
        <form action="/ricerca.php" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Cerca ricette..." class="search-bar">
            <button type="submit" class="search-button">🔍</button>
        </form>
    </div>

    <div class="header-right">
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="/view/listaRicetteUtente.php" class="restaurant-link">
                Il mio Ristorante
            </a>

            <div class="profile-menu">
                <img src="/img/fotoProfilo.jpg" class="profile-pic" id="profileToggle">
                <div class="dropdown-menu" id="dropdownMenu">
                    <div class="dropdown-username">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </div>
                    <hr>
                    <a href="/controller/logOutController.php" class="dropdown-item">
                        Logout
                    </a>
                </div>
            </div>

        <?php else: ?>
            <a href="view/login.php" class="login-button">
                <img src="img/fotoProfilo.jpg" class="user-icon">
                <span>Accedi</span>
            </a>
        <?php endif; ?>
    </div>
</header>
