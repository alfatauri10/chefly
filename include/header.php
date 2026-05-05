<!-- include/header.php — Responsive header con brand name CHEFLY -->
<header class="main-header">

    <!-- SINISTRA: Logo + Brand name -->
    <div class="header-left">
        <a href="/index.php" class="header-brand">
            <img src="/img/logo.png" alt="Chefly" class="header-logo">
            <span class="header-brand-name">CHEFLY</span>
        </a>
    </div>

    <!-- CENTRO: Search bar (desktop) -->
    <div class="header-center">
        <form action="/view/ricerca.php" method="GET" class="search-form">
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

    <!-- DESTRA: Azioni + mobile toggle -->
    <div class="header-right">
        <!-- Search toggle (solo mobile) -->
        <button class="mobile-search-btn" id="mobileSearchToggle" aria-label="Cerca">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </button>

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

<!-- Search bar espandibile su mobile -->
<div class="mobile-search-bar" id="mobileSearchBar">
    <form action="/view/ricerca.php" method="GET">
        <div class="mobile-search-inner">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="mobile-search-icon">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="q" placeholder="Cerca ricette, ingredienti..." class="mobile-search-input" id="mobileSearchInput" autocomplete="off">
            <button type="button" class="mobile-search-close" id="mobileSearchClose" aria-label="Chiudi">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </form>
</div>

<style>
    /* ── BRAND NAME ─────────────────────────────────────────────── */
    .header-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    .header-brand-name {
        font-family: 'DM Sans', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: 3px;
        color: #1A1008;
        text-transform: uppercase;
        line-height: 1;
        /* Nasconde su schermi molto piccoli per non sovraccaricare */
        transition: opacity 0.2s;
    }

    /* ── MOBILE SEARCH BUTTON ───────────────────────────────────── */
    .mobile-search-btn {
        display: none; /* visibile solo mobile */
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border: none;
        background: #F7F7F7;
        border-radius: 10px;
        cursor: pointer;
        color: #555;
        transition: background 0.2s, color 0.2s;
    }
    .mobile-search-btn:hover { background: #EEEAE3; color: #1A1008; }

    /* ── MOBILE SEARCH BAR ──────────────────────────────────────── */
    .mobile-search-bar {
        position: fixed;
        top: 90px; /* altezza header */
        left: 0;
        right: 0;
        background: #fff;
        border-bottom: 1px solid #EDE8E0;
        padding: 10px 16px;
        z-index: 999;
        transform: translateY(-100%);
        opacity: 0;
        pointer-events: none;
        transition: transform 0.25s ease, opacity 0.25s ease;
        box-shadow: 0 4px 16px rgba(26,16,8,0.07);
    }
    .mobile-search-bar.open {
        transform: translateY(0);
        opacity: 1;
        pointer-events: all;
    }
    .mobile-search-inner {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #F7F7F7;
        border-radius: 10px;
        padding: 10px 14px;
        border: 1px solid #EDE8E0;
    }
    .mobile-search-icon { color: #8B7355; flex-shrink: 0; }
    .mobile-search-input {
        flex: 1;
        border: none;
        background: transparent;
        outline: none;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.95rem;
        color: #1A1008;
    }
    .mobile-search-close {
        background: none;
        border: none;
        cursor: pointer;
        color: #8B7355;
        display: flex;
        align-items: center;
        padding: 0;
        flex-shrink: 0;
        transition: color 0.2s;
    }
    .mobile-search-close:hover { color: #1A1008; }

    /* ── RESPONSIVE HEADER ──────────────────────────────────────── */
    @media (max-width: 900px) {
        /* Riduci gap laterali */
        .main-header { padding: 0 1.5rem; }
        /* Logo leggermente più piccolo */
        .header-logo { height: 56px; }
    }

    @media (max-width: 680px) {
        /* Layout: logo | [spazio] | icone */
        .main-header {
            grid-template-columns: auto 1fr auto;
            padding: 0 1rem;
        }
        /* Nascondi search bar desktop */
        .header-center { display: none; }
        /* Mostra bottone search mobile */
        .mobile-search-btn { display: flex; }
        /* Logo ancora più compatto */
        .header-logo { height: 46px; }
        /* Riduci spaziatura destra */
        .header-right { gap: 10px; }
        /* Nasconde i bottoni testo su schermi molto piccoli */
        .login-link { display: none; }
        .signup-button {
            padding: 8px 14px;
            font-size: .8rem;
            border-radius: 7px;
        }
    }

    @media (max-width: 400px) {
        /* Su schermi minuscoli togli anche il nome brand */
        .header-brand-name { display: none; }
        .header-logo { height: 42px; }
    }
</style>

<script>
    (function() {
        const toggleBtn   = document.getElementById('mobileSearchToggle');
        const searchBar   = document.getElementById('mobileSearchBar');
        const closeBtn    = document.getElementById('mobileSearchClose');
        const searchInput = document.getElementById('mobileSearchInput');

        if (!toggleBtn || !searchBar) return;

        toggleBtn.addEventListener('click', function() {
            searchBar.classList.add('open');
            setTimeout(() => searchInput && searchInput.focus(), 120);
        });

        closeBtn && closeBtn.addEventListener('click', function() {
            searchBar.classList.remove('open');
        });

        // Chiudi premendo Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchBar.classList.contains('open')) {
                searchBar.classList.remove('open');
            }
        });
    })();
</script>