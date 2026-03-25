<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>

<header class="main-header">
    <div class="header-left">
        <form action="/ricerca.php" method="GET" class="search-form">
            <div class="search-wrapper">
                <input type="text" name="q" placeholder="Cerca ricette..." class="search-bar">
                <button type="submit" class="search-icon-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <div class="header-center">
        <a href="/index.php">
            <img src="/img/logo.png" alt="Chefly" class="header-logo">
        </a>
    </div>

    <div class="header-right">
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="profile-menu">
                <img src="/img/fotoProfilo.jpg" class="profile-pic" id="profileToggle">
                <div class="dropdown-menu" id="dropdownMenu">
                    <div class="dropdown-username"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <a href="/controller/logOutController.php" class="dropdown-item">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="/view/login.php" class="login-link">Log in</a>
            <a href="/view/registrazione.php" class="signup-button">Sign up</a>
        <?php endif; ?>
    </div>
</header>

<script>
    document.getElementById('profileToggle')?.addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('dropdownMenu').classList.toggle('show');
    });
    window.onclick = function() { document.getElementById('dropdownMenu')?.classList.remove('show'); };
</script>