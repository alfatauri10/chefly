<!-- include/header.php — usa chefly.css come stile globale -->
<header class="main-header">

    <div class="header-left">
        <a href="/index.php">
            <img src="/img/logo.png" alt="Chefly" class="header-logo">
        </a>
    </div>

    <div class="header-center">
        <form action="/ricerca.php" method="GET" class="search-form">
            <div class="search-wrapper">
                <input type="text" name="q" placeholder="Cerca ricette, ingredienti..." class="search-bar">
                <button type="submit" class="search-icon-btn" aria-label="Cerca">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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
                    <a href="/view/ilMioRistorante.php" class="dropdown-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                            <line x1="6" y1="17" x2="18" y2="17"/>
                        </svg>
                        Il mio ristorante
                    </a>
                    <a href="/controller/logOutController.php" class="dropdown-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        <?php else: ?>
            <a href="/view/login.php" class="login-link">Accedi</a>
            <a href="/view/signup.php" class="signup-button">Registrati</a>
        <?php endif; ?>
    </div>

</header>