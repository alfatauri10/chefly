<header class="main-header">

    <div class="header-left">
        <a href="/index.php">
            <img src="/img/logo.png" alt="Chefly" class="header-logo">
        </a>
    </div>

    <div class="header-center">
        <form action="/ricerca.php" method="GET" class="search-form">
            <div class="search-wrapper">
                <input type="text" name="q" placeholder="Cerca ricette..." class="search-bar">
                <button type="submit" class="search-icon-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#000"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <div class="header-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="profile-menu">

                <img src="<?php echo htmlspecialchars($_SESSION['fotoProfilo'] ?? '/img/fotoProfilo.jpg'); ?>"
                     class="profile-pic"
                     id="profileToggle"
                     alt="Profilo">

                <div class="dropdown-menu" id="dropdownMenu">
                    <div class="dropdown-username"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></div>

                    <a href="/view/ilMioRistorante.php" class="dropdown-item">
                        Il mio ristorante
                    </a>

                    <a href="/controller/logOutController.php" class="dropdown-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        <?php else: ?>
            <a href="/view/login.php" class="login-link">Log in</a>
            <a href="/view/signup.php" class="signup-button">Sign up</a>
        <?php endif; ?>
    </div>

</header>