<?php
/* view/ricerca.php */
require_once '../controller/ricercaController.php';
?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $q ? 'Ricerca: ' . htmlspecialchars($q) . ' — Chefly' : 'Cerca ricette — Chefly'; ?></title>
        <link rel="stylesheet" href="../css/chefly.css">
        <style>
            /* ── HERO RICERCA ── */
            .search-hero {
                background: var(--white);
                border-bottom: 1px solid var(--border);
                padding: 36px 28px 32px;
            }
            .search-hero-inner {
                max-width: 860px;
                margin: 0 auto;
            }
            .search-hero-eyebrow {
                font-size: .68rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 2.5px;
                color: var(--caramel);
                margin-bottom: 14px;
            }
            .search-hero-title {
                font-family: var(--font-serif);
                font-size: clamp(1.4rem, 3vw, 2rem);
                font-weight: 700;
                color: var(--brown);
                margin-bottom: 22px;
                line-height: 1.2;
            }
            .search-hero-title em { font-style: italic; color: var(--caramel); }

            /* Barra di ricerca principale */
            .main-search-form { display: flex; gap: 10px; }
            .main-search-input-wrap {
                flex: 1;
                display: flex;
                align-items: center;
                background: var(--cream);
                border: 1.5px solid var(--border);
                border-radius: var(--radius-pill);
                padding: 0 20px;
                transition: border-color .2s, box-shadow .2s, background .2s;
                position: relative;
            }
            .main-search-input-wrap:focus-within {
                border-color: var(--caramel);
                box-shadow: 0 0 0 3px rgba(196,98,45,.1);
                background: var(--white);
            }
            .main-search-icon { color: var(--muted); flex-shrink: 0; margin-right: 10px; }
            .main-search-bar {
                flex: 1;
                border: none;
                background: transparent;
                outline: none;
                font-family: var(--font-sans);
                font-size: 1rem;
                color: var(--brown);
                padding: 14px 0;
            }
            .main-search-bar::placeholder { color: #C4C0B8; }
            .main-search-clear {
                background: none; border: none; cursor: pointer;
                color: var(--muted-light); padding: 0; margin-left: 8px;
                display: flex; align-items: center;
                opacity: 0; pointer-events: none; transition: opacity .15s, color .15s;
            }
            .main-search-clear.visible { opacity: 1; pointer-events: all; }
            .main-search-clear:hover { color: var(--brown); }
            .btn-search-submit {
                background: var(--brown);
                color: #FFF;
                border: none;
                border-radius: var(--radius-pill);
                padding: 0 28px;
                font-family: var(--font-sans);
                font-size: .88rem;
                font-weight: 700;
                cursor: pointer;
                white-space: nowrap;
                transition: background .2s, transform .1s;
                height: 52px;
            }
            .btn-search-submit:hover { background: #3a2518; }
            .btn-search-submit:active { transform: scale(.97); }

            /* Suggerimenti autocomplete */
            .autocomplete-dropdown {
                position: absolute;
                top: calc(100% + 8px);
                left: 0; right: 0;
                background: var(--white);
                border: 1px solid var(--border);
                border-radius: var(--radius-md);
                box-shadow: 0 8px 32px rgba(26,16,8,.12);
                z-index: 500;
                overflow: hidden;
                display: none;
            }
            .autocomplete-dropdown.open { display: block; }
            .autocomplete-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 11px 18px;
                cursor: pointer;
                transition: background .12s;
                text-decoration: none;
                color: var(--brown);
            }
            .autocomplete-item:hover, .autocomplete-item.focused {
                background: var(--cream);
            }
            .autocomplete-item-title { font-size: .9rem; font-weight: 500; flex: 1; }
            .autocomplete-item-badge { font-size: .65rem; font-weight: 700; padding: 2px 8px; border-radius: 10px; }

            /* ── LAYOUT PRINCIPALE ── */
            .search-layout {
                max-width: 1180px;
                margin: 0 auto;
                padding: 32px 28px 100px;
                display: grid;
                grid-template-columns: 260px 1fr;
                gap: 32px;
                align-items: start;
            }

            /* ── SIDEBAR FILTRI ── */
            .filters-sidebar {
                position: sticky;
                top: 110px;
            }
            .filters-card {
                background: var(--white);
                border: 1px solid var(--border);
                border-radius: var(--radius-lg);
                overflow: hidden;
            }
            .filters-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 16px 20px;
                border-bottom: 1px solid var(--border-light);
            }
            .filters-title {
                font-size: .72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 1.5px;
                color: var(--muted);
            }
            .btn-reset-filters {
                font-size: .72rem;
                font-weight: 700;
                color: var(--caramel);
                background: none;
                border: none;
                cursor: pointer;
                padding: 0;
                text-decoration: none;
                transition: opacity .15s;
            }
            .btn-reset-filters:hover { opacity: .7; }
            .filters-body { padding: 18px 20px; display: flex; flex-direction: column; gap: 20px; }

            .filter-group {}
            .filter-group-label {
                font-size: .68rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: #6B5C48;
                margin-bottom: 8px;
                display: block;
            }
            .filter-select {
                width: 100%;
                background: var(--cream);
                border: 1px solid var(--border);
                border-radius: 9px;
                padding: 9px 32px 9px 12px;
                font-family: var(--font-sans);
                font-size: .85rem;
                color: var(--brown);
                appearance: none;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%238B7355' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 12px center;
                cursor: pointer;
                transition: border-color .2s, box-shadow .2s;
            }
            .filter-select:focus {
                outline: none;
                border-color: var(--caramel);
                box-shadow: 0 0 0 3px rgba(196,98,45,.1);
            }
            .filter-select.active {
                border-color: var(--caramel);
                background-color: #FFF3ED;
                font-weight: 600;
            }

            /* Pillole difficoltà */
            .difficulty-pills { display: flex; flex-wrap: wrap; gap: 6px; }
            .diff-pill {
                padding: 5px 14px;
                border-radius: 20px;
                font-size: .72rem;
                font-weight: 700;
                cursor: pointer;
                text-decoration: none;
                border: 1.5px solid transparent;
                transition: all .15s;
            }
            .diff-pill-facile   { background: #F0FDF4; color: #166534; border-color: #BBF7D0; }
            .diff-pill-media    { background: #FFFBEB; color: #92400E; border-color: #FDE68A; }
            .diff-pill-difficile{ background: #FFF1F0; color: #991B1B; border-color: #FECACA; }
            .diff-pill-esperto  { background: var(--brown); color: #F5E6D3; border-color: var(--brown); }
            .diff-pill.active   { box-shadow: 0 0 0 2px var(--brown) inset; transform: scale(.97); }
            .diff-pill:not(.active):hover { opacity: .8; transform: scale(.97); }

            /* Contatore filtri attivi */
            .filter-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 18px; height: 18px;
                border-radius: 50%;
                background: var(--caramel);
                color: #FFF;
                font-size: .6rem;
                font-weight: 800;
                margin-left: 6px;
            }

            /* ── AREA RISULTATI ── */
            .results-area {}
            .results-topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 22px;
                flex-wrap: wrap;
                gap: 10px;
            }
            .results-count {
                font-size: .82rem;
                color: var(--muted);
            }
            .results-count strong { color: var(--brown); font-weight: 700; }

            /* Chips filtri attivi */
            .active-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                margin-bottom: 20px;
            }
            .filter-chip {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: #FFF3ED;
                border: 1px solid #E8B99A;
                border-radius: 20px;
                padding: 5px 12px;
                font-size: .72rem;
                font-weight: 600;
                color: var(--caramel);
                text-decoration: none;
            }
            .filter-chip:hover { background: #FEEEE0; }
            .filter-chip-remove {
                width: 14px; height: 14px;
                border-radius: 50%;
                background: rgba(196,98,45,.2);
                display: flex; align-items: center; justify-content: center;
            }

            /* ── GRIGLIA RISULTATI ── */
            .results-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 18px;
            }

            /* Card risultato */
            .result-card {
                background: var(--white);
                border: 1px solid var(--border);
                border-radius: var(--radius-lg);
                overflow: hidden;
                text-decoration: none;
                color: inherit;
                transition: transform .22s ease, box-shadow .22s ease;
                display: flex;
                flex-direction: column;
            }
            .result-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 12px 36px rgba(26,16,8,.1);
            }
            .result-card-cover {
                aspect-ratio: 4/3;
                background: var(--sand);
                overflow: hidden;
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #D6CFC4;
            }
            .result-card-cover img {
                width: 100%; height: 100%;
                object-fit: cover;
                display: block;
                transition: transform .3s ease;
            }
            .result-card:hover .result-card-cover img { transform: scale(1.04); }
            .result-card-body { padding: 14px 16px 16px; flex: 1; display: flex; flex-direction: column; }
            .result-card-tags { display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 8px; }
            .result-card-title {
                font-family: var(--font-serif);
                font-size: .98rem;
                font-weight: 600;
                color: var(--brown);
                line-height: 1.3;
                margin-bottom: 6px;
            }
            .result-card-desc {
                font-size: .78rem;
                color: var(--muted);
                line-height: 1.55;
                flex: 1;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                margin-bottom: 12px;
            }
            .result-card-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding-top: 10px;
                border-top: 1px solid var(--border-light);
            }
            .result-author {
                display: flex; align-items: center; gap: 6px;
            }
            .result-author-avatar {
                width: 22px; height: 22px; border-radius: 50%;
                background: var(--sand);
                display: flex; align-items: center; justify-content: center;
                font-size: .55rem; font-weight: 700; color: var(--caramel);
                text-transform: uppercase; flex-shrink: 0;
            }
            .result-author-name { font-size: .7rem; font-weight: 600; color: var(--brown); }
            .result-card-date   { font-size: .68rem; color: var(--muted-light); }

            /* ── EMPTY STATE ── */
            .empty-results {
                text-align: center;
                padding: 72px 20px;
                background: var(--white);
                border: 1.5px dashed var(--border);
                border-radius: var(--radius-lg);
            }
            .empty-results-icon { margin-bottom: 20px; color: #D6CFC4; }
            .empty-results h3 {
                font-family: var(--font-serif);
                font-size: 1.3rem; font-weight: 600;
                color: var(--brown); margin-bottom: 8px;
            }
            .empty-results p { font-size: .88rem; color: var(--muted); margin-bottom: 22px; line-height: 1.6; }

            /* ── PAGINAZIONE ── */
            .pagination {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                margin-top: 40px;
            }
            .page-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 38px; height: 38px;
                border-radius: var(--radius-sm);
                font-family: var(--font-sans);
                font-size: .85rem;
                font-weight: 600;
                text-decoration: none;
                border: 1px solid var(--border);
                color: var(--brown);
                background: var(--white);
                transition: background .15s, border-color .15s;
                padding: 0 10px;
            }
            .page-btn:hover { background: var(--cream); border-color: var(--muted-light); }
            .page-btn.active {
                background: var(--brown); color: #FFF; border-color: var(--brown);
            }
            .page-btn.disabled {
                opacity: .35; pointer-events: none;
            }
            .page-dots { color: var(--muted-light); font-size: .9rem; padding: 0 4px; }

            /* ── RESPONSIVE ── */
            @media (max-width: 860px) {
                .search-layout { grid-template-columns: 1fr; }
                .filters-sidebar { position: static; }
                .filters-card { display: none; }
                .filters-card.mobile-open { display: block; }
                .mobile-filter-toggle { display: flex !important; }
            }
            @media (max-width: 640px) {
                .main-search-form { flex-direction: column; }
                .btn-search-submit { width: 100%; justify-content: center; border-radius: var(--radius-md); height: 48px; }
                .search-layout { padding: 20px 16px 80px; }
                .results-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
            }

            .mobile-filter-toggle {
                display: none;
                align-items: center;
                gap: 8px;
                background: var(--white);
                border: 1px solid var(--border);
                border-radius: var(--radius-sm);
                padding: 9px 16px;
                font-family: var(--font-sans);
                font-size: .85rem;
                font-weight: 600;
                color: var(--brown);
                cursor: pointer;
                transition: background .15s;
            }
            .mobile-filter-toggle:hover { background: var(--cream); }
        </style>
    </head>
    <body>
    <?php include '../include/header.php'; ?>

    <main class="page-content">

        <!-- ── HERO RICERCA ── -->
        <div class="search-hero">
            <div class="search-hero-inner">
                <p class="search-hero-eyebrow">Trova la tua prossima ricetta</p>
                <h1 class="search-hero-title">
                    <?php if ($q !== ''): ?>
                        Risultati per <em>"<?php echo htmlspecialchars($q); ?>"</em>
                    <?php else: ?>
                        Cerca tra le <em>ricette</em> Chefly
                    <?php endif; ?>
                </h1>

                <form action="/ricerca.php" method="GET" class="main-search-form" id="mainSearchForm" autocomplete="off">
                    <!-- Preserva filtri attivi nei campi nascosti -->
                    <?php foreach ($filtri as $key => $val): ?>
                        <?php if (!empty($val)): ?>
                            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($val); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <div class="main-search-input-wrap" id="searchWrap">
                        <svg class="main-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text"
                               name="q"
                               id="mainSearchBar"
                               class="main-search-bar"
                               placeholder="Cerca ricette, ingredienti, piatti…"
                               value="<?php echo htmlspecialchars($q); ?>"
                               oninput="onSearchInput(this)">
                        <button type="button"
                                class="main-search-clear <?php echo $q !== '' ? 'visible' : ''; ?>"
                                id="clearBtn"
                                onclick="clearSearch()"
                                title="Cancella ricerca">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>

                        <!-- Autocomplete dropdown -->
                        <div class="autocomplete-dropdown" id="autocompleteDropdown"></div>
                    </div>

                    <button type="submit" class="btn-search-submit">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="display:inline;vertical-align:middle;margin-right:6px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Cerca
                    </button>
                </form>
            </div>
        </div>

        <!-- ── LAYOUT ── -->
        <div class="search-layout">

            <!-- SIDEBAR FILTRI -->
            <aside class="filters-sidebar">
                <!-- Toggle mobile -->
                <button class="mobile-filter-toggle" onclick="toggleMobileFilters()" id="mobileFilterBtn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
                    Filtri
                    <?php if ($ha_filtri_attivi): ?>
                        <span class="filter-badge"><?php
                            $n = 0;
                            if ($filtri['difficolta']) $n++;
                            if ($filtri['id_nazionalita']) $n++;
                            if ($filtri['id_tipologia']) $n++;
                            if ($filtri['id_ingrediente']) $n++;
                            if ($filtri['id_cottura']) $n++;
                            echo $n;
                            ?></span>
                    <?php endif; ?>
                </button>

                <div class="filters-card" id="filtersCard">
                    <div class="filters-header">
                    <span class="filters-title">
                        Filtri
                        <?php if ($ha_filtri_attivi): ?>
                            <span class="filter-badge"><?php echo $n; ?></span>
                        <?php endif; ?>
                    </span>
                        <?php if ($ha_filtri_attivi): ?>
                            <a href="/ricerca.php<?php echo $q ? '?q=' . urlencode($q) : ''; ?>"
                               class="btn-reset-filters">Reimposta</a>
                        <?php endif; ?>
                    </div>
                    <div class="filters-body">

                        <!-- Difficoltà -->
                        <div class="filter-group">
                            <span class="filter-group-label">Difficoltà</span>
                            <div class="difficulty-pills">
                                <?php foreach (['facile'=>'Facile','media'=>'Media','difficile'=>'Difficile','esperto'=>'Esperto'] as $v => $l):
                                    $isActive = ($filtri['difficolta'] === $v);
                                    $href = buildFilterUrl($q, $filtri, 'difficolta', $isActive ? '' : $v);
                                    ?>
                                    <a href="<?php echo $href; ?>"
                                       class="diff-pill diff-pill-<?php echo $v; ?> <?php echo $isActive ? 'active' : ''; ?>">
                                        <?php echo $l; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Tipologia -->
                        <div class="filter-group">
                            <span class="filter-group-label">Tipologia</span>
                            <select class="filter-select <?php echo $filtri['id_tipologia'] ? 'active' : ''; ?>"
                                    onchange="applySelectFilter('id_tipologia', this.value)">
                                <option value="">Tutte le tipologie</option>
                                <?php foreach ($lista_tipologie as $t): ?>
                                    <option value="<?php echo $t['id']; ?>"
                                        <?php echo ($filtri['id_tipologia'] == $t['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($t['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Nazionalità -->
                        <div class="filter-group">
                            <span class="filter-group-label">Cucina nazionale</span>
                            <select class="filter-select <?php echo $filtri['id_nazionalita'] ? 'active' : ''; ?>"
                                    onchange="applySelectFilter('id_nazionalita', this.value)">
                                <option value="">Tutte le nazionalità</option>
                                <?php foreach ($lista_nazionalita as $n): ?>
                                    <option value="<?php echo $n['id']; ?>"
                                        <?php echo ($filtri['id_nazionalita'] == $n['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($n['nome']); ?> (<?php echo htmlspecialchars($n['sigla']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Ingrediente -->
                        <div class="filter-group">
                            <span class="filter-group-label">Contiene ingrediente</span>
                            <select class="filter-select <?php echo $filtri['id_ingrediente'] ? 'active' : ''; ?>"
                                    onchange="applySelectFilter('id_ingrediente', this.value)">
                                <option value="">Qualsiasi ingrediente</option>
                                <?php foreach ($lista_ingredienti as $ing): ?>
                                    <option value="<?php echo $ing['id']; ?>"
                                        <?php echo ($filtri['id_ingrediente'] == $ing['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ing['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tecnica di cottura -->
                        <div class="filter-group">
                            <span class="filter-group-label">Tecnica di cottura</span>
                            <select class="filter-select <?php echo $filtri['id_cottura'] ? 'active' : ''; ?>"
                                    onchange="applySelectFilter('id_cottura', this.value)">
                                <option value="">Qualsiasi tecnica</option>
                                <?php foreach ($lista_cotture as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"
                                        <?php echo ($filtri['id_cottura'] == $c['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>
                </div>
            </aside>

            <!-- AREA RISULTATI -->
            <div class="results-area">

                <!-- Filtri attivi come chip removibili -->
                <?php if ($ha_filtri_attivi): ?>
                    <div class="active-filters">
                        <?php if ($filtri['difficolta']): ?>
                            <a class="filter-chip" href="<?php echo buildFilterUrl($q, $filtri, 'difficolta', ''); ?>">
                                Difficoltà: <?php echo ucfirst($filtri['difficolta']); ?>
                                <span class="filter-chip-remove">
                                <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </span>
                            </a>
                        <?php endif; ?>
                        <?php if ($filtri['id_tipologia']): ?>
                            <?php $tip_name = ''; foreach ($lista_tipologie as $t) { if ($t['id'] == $filtri['id_tipologia']) $tip_name = $t['nome']; } ?>
                            <a class="filter-chip" href="<?php echo buildFilterUrl($q, $filtri, 'id_tipologia', ''); ?>">
                                <?php echo htmlspecialchars($tip_name); ?>
                                <span class="filter-chip-remove"><svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span>
                            </a>
                        <?php endif; ?>
                        <?php if ($filtri['id_nazionalita']): ?>
                            <?php $naz_name = ''; foreach ($lista_nazionalita as $n) { if ($n['id'] == $filtri['id_nazionalita']) $naz_name = $n['nome']; } ?>
                            <a class="filter-chip" href="<?php echo buildFilterUrl($q, $filtri, 'id_nazionalita', ''); ?>">
                                <?php echo htmlspecialchars($naz_name); ?>
                                <span class="filter-chip-remove"><svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span>
                            </a>
                        <?php endif; ?>
                        <?php if ($filtri['id_ingrediente']): ?>
                            <?php $ing_name = ''; foreach ($lista_ingredienti as $i) { if ($i['id'] == $filtri['id_ingrediente']) $ing_name = $i['nome']; } ?>
                            <a class="filter-chip" href="<?php echo buildFilterUrl($q, $filtri, 'id_ingrediente', ''); ?>">
                                <?php echo htmlspecialchars($ing_name); ?>
                                <span class="filter-chip-remove"><svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span>
                            </a>
                        <?php endif; ?>
                        <?php if ($filtri['id_cottura']): ?>
                            <?php $cot_name = ''; foreach ($lista_cotture as $c) { if ($c['id'] == $filtri['id_cottura']) $cot_name = $c['nome']; } ?>
                            <a class="filter-chip" href="<?php echo buildFilterUrl($q, $filtri, 'id_cottura', ''); ?>">
                                <?php echo htmlspecialchars($cot_name); ?>
                                <span class="filter-chip-remove"><svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Topbar risultati -->
                <div class="results-topbar">
                    <p class="results-count">
                        <?php if ($q !== '' || $ha_filtri_attivi): ?>
                            <strong><?php echo $totale; ?></strong>
                            ricett<?php echo $totale !== 1 ? 'e trovate' : 'a trovata'; ?>
                            <?php if ($q !== ''): ?> per «<?php echo htmlspecialchars($q); ?>»<?php endif; ?>
                        <?php else: ?>
                            <strong><?php echo $totale; ?></strong> ricette disponibili
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Griglia risultati -->
                <?php if (empty($risultati)): ?>
                    <div class="empty-results">
                        <div class="empty-results-icon">
                            <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                <line x1="8" y1="11" x2="14" y2="11"/>
                            </svg>
                        </div>
                        <h3>Nessun risultato trovato</h3>
                        <p>
                            <?php if ($q !== ''): ?>
                                Non abbiamo trovato ricette per "<?php echo htmlspecialchars($q); ?>".
                                Prova con parole diverse o rimuovi alcuni filtri.
                            <?php else: ?>
                                Nessuna ricetta corrisponde ai filtri selezionati.
                            <?php endif; ?>
                        </p>
                        <a href="/ricerca.php" class="btn btn-ghost">Mostra tutte le ricette</a>
                    </div>
                <?php else: ?>
                    <div class="results-grid">
                        <?php foreach ($risultati as $r): ?>
                            <a class="result-card" href="/view/ricetta.php?id=<?php echo $r['id']; ?>">
                                <div class="result-card-cover">
                                    <?php if (!empty($r['url_copertina'])): ?>
                                        <img src="/<?php echo htmlspecialchars($r['url_copertina']); ?>"
                                             alt="<?php echo htmlspecialchars($r['titolo']); ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round">
                                            <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                                            <line x1="6" y1="17" x2="18" y2="17"/>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div class="result-card-body">
                                    <div class="result-card-tags">
                                    <span class="badge badge--<?php echo strtolower($r['difficolta']); ?>">
                                        <?php echo ucfirst($r['difficolta']); ?>
                                    </span>
                                        <?php if (!empty($r['nome_tipologia'])): ?>
                                            <span class="badge" style="background:#FFF3ED;color:var(--caramel);">
                                            <?php echo htmlspecialchars($r['nome_tipologia']); ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if (!empty($r['nome_nazionalita'])): ?>
                                            <span class="badge" style="background:var(--cream);color:var(--muted);border:1px solid var(--border);">
                                            <?php echo htmlspecialchars($r['nome_nazionalita']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="result-card-title"><?php echo htmlspecialchars($r['titolo']); ?></div>
                                    <?php if (!empty($r['descrizione'])): ?>
                                        <p class="result-card-desc"><?php echo htmlspecialchars($r['descrizione']); ?></p>
                                    <?php endif; ?>
                                    <div class="result-card-footer">
                                        <div class="result-author">
                                            <div class="result-author-avatar">
                                                <?php echo mb_substr($r['nome_autore'], 0, 2); ?>
                                            </div>
                                            <span class="result-author-name">@<?php echo htmlspecialchars($r['nome_autore']); ?></span>
                                        </div>
                                        <span class="result-card-date"><?php echo date('d M Y', strtotime($r['dataCreazione'])); ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- PAGINAZIONE -->
                    <?php if ($tot_pagine > 1): ?>
                        <nav class="pagination" aria-label="Paginazione">
                            <?php
                            // Costruisce URL di pagina mantenendo tutti i parametri
                            function buildPageUrl($pagina, $q, $filtri) {
                                $p = array_filter(['q' => $q] + $filtri);
                                $p['pagina'] = $pagina;
                                return '/ricerca.php?' . http_build_query($p);
                            }
                            ?>

                            <!-- Prev -->
                            <?php if ($pagina > 1): ?>
                                <a class="page-btn" href="<?php echo buildPageUrl($pagina - 1, $q, $filtri); ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
                                </a>
                            <?php else: ?>
                                <span class="page-btn disabled">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
                            </span>
                            <?php endif; ?>

                            <!-- Numeri di pagina -->
                            <?php
                            $range = 2; // pagine intorno alla corrente
                            for ($p = 1; $p <= $tot_pagine; $p++):
                                if ($p === 1 || $p === $tot_pagine || abs($p - $pagina) <= $range):
                                    if ($p !== 1 && $p !== $tot_pagine && abs($p - $pagina) === $range + 1):
                                        echo '<span class="page-dots">…</span>';
                                    elseif ($p > 1 && $p < $tot_pagine && ($p - 1) > 1 && abs(($p - 1) - $pagina) > $range):
                                        echo '<span class="page-dots">…</span>';
                                    endif;
                                    ?>
                                    <a class="page-btn <?php echo $p === $pagina ? 'active' : ''; ?>"
                                       href="<?php echo buildPageUrl($p, $q, $filtri); ?>">
                                        <?php echo $p; ?>
                                    </a>
                                <?php
                                endif;
                            endfor;
                            ?>

                            <!-- Next -->
                            <?php if ($pagina < $tot_pagine): ?>
                                <a class="page-btn" href="<?php echo buildPageUrl($pagina + 1, $q, $filtri); ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
                                </a>
                            <?php else: ?>
                                <span class="page-btn disabled">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
                            </span>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>

                <?php endif; ?>
            </div><!-- .results-area -->

        </div><!-- .search-layout -->

    </main>

    <?php include '../include/footer.php'; ?>

    <script>
        /* ── Autocomplete ── */
        let acTimer = null;
        const dropdown  = document.getElementById('autocompleteDropdown');
        const searchBar = document.getElementById('mainSearchBar');
        const clearBtn  = document.getElementById('clearBtn');

        function onSearchInput(input) {
            clearBtn.classList.toggle('visible', input.value.length > 0);
            clearTimeout(acTimer);
            if (input.value.trim().length < 2) { closeAutocomplete(); return; }
            acTimer = setTimeout(() => fetchSuggestions(input.value.trim()), 220);
        }

        function fetchSuggestions(q) {
            fetch('/controller/autocompleteController.php?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    if (!data.length) { closeAutocomplete(); return; }
                    dropdown.innerHTML = data.map(item => `
                <a class="autocomplete-item" href="/ricerca.php?q=${encodeURIComponent(item.titolo)}">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <span class="autocomplete-item-title">${escHtml(item.titolo)}</span>
                    <span class="autocomplete-item-badge badge badge--${item.difficolta.toLowerCase()}">${capitalize(item.difficolta)}</span>
                </a>`).join('');
                    dropdown.classList.add('open');
                })
                .catch(() => closeAutocomplete());
        }

        function closeAutocomplete() { dropdown.classList.remove('open'); dropdown.innerHTML = ''; }

        function clearSearch() {
            searchBar.value = '';
            clearBtn.classList.remove('visible');
            closeAutocomplete();
            searchBar.focus();
        }

        document.addEventListener('click', e => {
            if (!document.getElementById('searchWrap').contains(e.target)) closeAutocomplete();
        });

        searchBar.addEventListener('keydown', e => {
            const items = dropdown.querySelectorAll('.autocomplete-item');
            if (!items.length) return;
            const focused = dropdown.querySelector('.focused');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const next = focused ? (focused.nextElementSibling || items[0]) : items[0];
                focused && focused.classList.remove('focused');
                next.classList.add('focused');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prev = focused ? (focused.previousElementSibling || items[items.length - 1]) : items[items.length - 1];
                focused && focused.classList.remove('focused');
                prev.classList.add('focused');
            } else if (e.key === 'Enter' && focused) {
                e.preventDefault();
                window.location.href = focused.href;
            } else if (e.key === 'Escape') {
                closeAutocomplete();
            }
        });

        /* ── Filtri via select ── */
        function applySelectFilter(key, value) {
            const url = new URL(window.location.href);
            if (value) url.searchParams.set(key, value);
            else url.searchParams.delete(key);
            url.searchParams.delete('pagina'); // reset paginazione
            window.location.href = url.toString();
        }

        /* ── Mobile filtri ── */
        function toggleMobileFilters() {
            const card = document.getElementById('filtersCard');
            card.classList.toggle('mobile-open');
        }

        /* ── Utilities ── */
        function escHtml(s) {
            return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
    </script>

    <script><?php include_once '../js/dropDownMenu.js'; ?></script>
    </body>
    </html>

<?php
// ── Helper buildFilterUrl() ──────────────────────────────────────────────────
// Costruisce l'URL della ricerca modificando un singolo parametro filtro.
// Se $value è vuoto, rimuove il filtro.
function buildFilterUrl($q, $filtri, $chiave, $valore) {
    $p = [];
    if ($q !== '') $p['q'] = $q;
    foreach ($filtri as $k => $v) {
        if (!empty($v) && $k !== $chiave) $p[$k] = $v;
    }
    if ($valore !== '') $p[$chiave] = $valore;
    return '/ricerca.php' . ($p ? '?' . http_build_query($p) : '');
}