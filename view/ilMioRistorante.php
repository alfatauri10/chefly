<?php
// view/ilMioRistorante.php
require_once '../controller/ilMioRistoranteController.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il Mio Ristorante — Chefly</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">

    <style>
        /* ── RESET & BASE ─────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #FAF8F5;
            color: #1A1008;
            min-height: 100vh;
        }

        .ristorante-wrap {
            max-width: 760px;
            margin: 0 auto;
            padding: 48px 20px 120px;
        }

        /* ── FLASH MESSAGES ───────────────────────────────────── */
        .flash {
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 28px;
            letter-spacing: 0.3px;
        }
        .flash--success { background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; }
        .flash--error   { background: #FFF1F0; color: #991B1B; border: 1px solid #FECACA; }
        .flash--deleted { background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A; }

        /* ── PROFILO ──────────────────────────────────────────── */
        .profile-section {
            display: flex;
            align-items: center;
            gap: 48px;
            padding-bottom: 36px;
            border-bottom: 1px solid #EDE8E0;
            margin-bottom: 40px;
        }

        .profile-avatar-wrap { flex-shrink: 0; position: relative; }

        .profile-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #EDE8E0;
            display: block;
        }

        .profile-info { flex: 1; }

        .profile-stats {
            display: flex;
            gap: 36px;
            margin-bottom: 16px;
        }

        .stat-item { text-align: center; }

        .stat-num {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1A1008;
            line-height: 1;
        }

        .stat-label {
            display: block;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #8B7355;
            margin-top: 4px;
        }

        .profile-username {
            font-size: 1rem;
            font-weight: 600;
            color: #1A1008;
            margin-bottom: 6px;
        }

        .profile-username span { color: #C4622D; }

        .profile-bio {
            font-size: 0.9rem;
            color: #6B5C48;
            line-height: 1.55;
            max-width: 380px;
        }

        /* ── INTESTAZIONE SEZIONE RICETTE ─────────────────────── */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: #1A1008;
        }

        /* ── EMPTY STATE ──────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #FFFFFF;
            border: 1.5px dashed #D6CFC4;
            border-radius: 16px;
        }

        .empty-state p {
            color: #8B7355;
            font-size: 0.95rem;
            margin-bottom: 20px;
        }

        /* ── RECIPE CARD ──────────────────────────────────────── */
        .recipe-list { display: flex; flex-direction: column; gap: 16px; }

        .recipe-card {
            background: #FFFFFF;
            border: 1px solid #EDE8E0;
            border-radius: 16px;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }

        .recipe-card:hover { box-shadow: 0 4px 20px rgba(26,16,8,0.07); }

        /* Riga principale ricetta */
        .recipe-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
        }

        .recipe-cover {
            width: 64px;
            height: 64px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            background: #F5F2EC;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #C4C0B8;
        }

        .recipe-cover img {
            width: 64px;
            height: 64px;
            border-radius: 10px;
            object-fit: cover;
        }

        .recipe-main { flex: 1; min-width: 0; }

        .recipe-title-text {
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
            font-weight: 600;
            color: #1A1008;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }

        .recipe-meta {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .badge {
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 2px 9px;
            border-radius: 20px;
        }

        .badge--difficolta { background: #F5F2EC; color: #6B5C48; }
        .badge--steps      { background: #FFF3ED; color: #C4622D; }

        /* Azioni ricetta (destra) */
        .recipe-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8B7355;
            transition: background 0.15s, color 0.15s;
            text-decoration: none;
        }

        .btn-icon:hover { background: #F5F2EC; color: #1A1008; }
        .btn-icon--danger:hover { background: #FFF1F0; color: #DC2626; }

        /* Toggle passi */
        .btn-toggle {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid #EDE8E0;
            background: #FAF8F5;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8B7355;
            transition: all 0.15s;
        }

        .btn-toggle:hover { background: #F0EBE3; }

        .btn-toggle svg {
            transition: transform 0.25s ease;
        }

        .btn-toggle.open svg { transform: rotate(180deg); }

        /* ── SEZIONE PASSI ────────────────────────────────────── */
        .passi-section {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
        }

        .passi-section.open { max-height: 2000px; }

        .passi-inner {
            border-top: 1px solid #EDE8E0;
            background: #FDFCFA;
            padding: 0 20px;
        }

        .passo-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 0;
            border-bottom: 1px solid #F0ECE6;
        }

        .passo-row:last-child { border-bottom: none; }

        .passo-num {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #C4622D;
            color: #FFF;
            font-size: 0.72rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .passo-info { flex: 1; min-width: 0; }

        .passo-title {
            font-size: 0.88rem;
            font-weight: 500;
            color: #1A1008;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .passo-durata {
            font-size: 0.75rem;
            color: #8B7355;
            margin-top: 2px;
        }

        .passo-actions {
            display: flex;
            gap: 4px;
            flex-shrink: 0;
        }

        .btn-icon--sm {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            border: none;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8B7355;
            transition: background 0.15s, color 0.15s;
        }

        .btn-icon--sm:hover { background: #F5F2EC; color: #1A1008; }
        .btn-icon--sm.danger:hover { background: #FFF1F0; color: #DC2626; }

        .passi-footer {
            padding: 12px 0 14px;
            display: flex;
            justify-content: center;
        }

        .btn-add-passo {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #C4622D;
            text-decoration: none;
            padding: 7px 16px;
            border-radius: 20px;
            border: 1.5px dashed #E8B99A;
            background: transparent;
            transition: background 0.15s;
        }

        .btn-add-passo:hover { background: #FFF3ED; }

        /* ── FAB ──────────────────────────────────────────────── */
        .fab {
            position: fixed;
            bottom: 36px;
            right: 36px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #C4622D;
            color: #FFF;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 24px rgba(196,98,45,0.38);
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            z-index: 100;
        }

        .fab:hover {
            transform: scale(1.08) translateY(-2px);
            box-shadow: 0 10px 30px rgba(196,98,45,0.45);
        }

        .fab-tooltip {
            position: fixed;
            bottom: 44px;
            right: 106px;
            background: #1A1008;
            color: #FFF;
            font-size: 0.78rem;
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 20px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        .fab:hover + .fab-tooltip { opacity: 1; }

        /* ── DELETE FORM INLINE ───────────────────────────────── */
        .delete-form { margin: 0; }

        /* ── RESPONSIVE ───────────────────────────────────────── */
        @media (max-width: 600px) {
            .profile-section { flex-direction: column; gap: 24px; text-align: center; }
            .profile-stats   { justify-content: center; }
            .profile-bio     { margin: 0 auto; }
            .fab             { bottom: 24px; right: 20px; }
            .fab-tooltip     { display: none; }
        }
    </style>
</head>
<body>

<?php include '../include/header.php'; ?>

<div class="ristorante-wrap">

    <!-- Flash messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="flash flash--success">
            <?php
            $msgs = [
                    'ricetta_creata'    => '✓ Ricetta aggiunta con successo!',
                    'ricetta_modificata'=> '✓ Ricetta aggiornata.',
                    'passo_aggiunto'    => '✓ Passo aggiunto correttamente.',
                    'passo_modificato'  => '✓ Passo aggiornato.',
            ];
            echo htmlspecialchars($msgs[$_GET['success']] ?? 'Operazione completata.');
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="flash flash--deleted">
            <?php echo $_GET['deleted'] === 'passo' ? '⚑ Passo eliminato.' : '⚑ Ricetta eliminata.'; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="flash flash--error">Si è verificato un errore. Riprova.</div>
    <?php endif; ?>

    <!-- ── SEZIONE PROFILO ────────────────────────────────── -->
    <section class="profile-section">

        <div class="profile-avatar-wrap">
            <img
                    src="<?php echo htmlspecialchars($profilo['urlFotoProfilo'] ?? '/img/fotoProfilo.jpg'); ?>"
                    alt="Foto profilo"
                    class="profile-avatar"
            >
        </div>

        <div class="profile-info">
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-num"><?php echo $statistiche['num_ricette']; ?></span>
                    <span class="stat-label">Post</span>
                </div>
                <div class="stat-item">
                    <span class="stat-num"><?php echo $statistiche['num_follower']; ?></span>
                    <span class="stat-label">Follower</span>
                </div>
                <div class="stat-item">
                    <span class="stat-num"><?php echo $statistiche['num_seguiti']; ?></span>
                    <span class="stat-label">Seguiti</span>
                </div>
            </div>

            <p class="profile-username">
                <span>@</span><?php echo htmlspecialchars($profilo['userName']); ?>
            </p>

            <?php if (!empty($profilo['biografia'])): ?>
                <p class="profile-bio"><?php echo nl2br(htmlspecialchars($profilo['biografia'])); ?></p>
            <?php endif; ?>
        </div>

    </section>

    <!-- ── LISTA RICETTE ──────────────────────────────────── -->
    <div class="section-header">
        <h2 class="section-title">Le mie ricette</h2>
    </div>

    <?php if (empty($ricette)): ?>
        <div class="empty-state">
            <p>Non hai ancora aggiunto nessuna ricetta.</p>
            <a href="aggiungiRicetta.php" class="btn-add-passo" style="display:inline-flex; border-style:solid; background:#C4622D; color:#FFF; border-color:#C4622D;">
                + Aggiungi la prima ricetta
            </a>
        </div>
    <?php else: ?>
        <div class="recipe-list">
            <?php foreach ($ricette as $i => $ricetta): ?>
                <article class="recipe-card">

                    <!-- Riga principale ricetta -->
                    <div class="recipe-header">

                        <!-- Copertina -->
                        <div class="recipe-cover">
                            <?php if (!empty($ricetta['url_copertina'])): ?>
                                <img src="../<?php echo htmlspecialchars($ricetta['url_copertina']); ?>"
                                     alt="Copertina <?php echo htmlspecialchars($ricetta['titolo']); ?>">
                            <?php else: ?>
                                <!-- Placeholder icona piatto -->
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 11l19-9-9 19-2-8-8-2z"/>
                                </svg>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="recipe-main">
                            <div class="recipe-title-text"><?php echo htmlspecialchars($ricetta['titolo']); ?></div>
                            <div class="recipe-meta">
                                <span class="badge badge--difficolta"><?php echo htmlspecialchars(ucfirst($ricetta['difficolta'])); ?></span>
                                <span class="badge badge--steps">
                                    <?php echo count($ricetta['passi']); ?> passo<?php echo count($ricetta['passi']) !== 1 ? 'i' : ''; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Azioni -->
                        <div class="recipe-actions">

                            <!-- Modifica ricetta -->
                            <a href="modificaRicetta.php?id_ricetta=<?php echo $ricetta['id']; ?>"
                               class="btn-icon"
                               title="Modifica ricetta">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>

                            <!-- Elimina ricetta -->
                            <form class="delete-form"
                                  action="../controller/cancellaRicettaController.php"
                                  method="POST"
                                  onsubmit="return confirm('Eliminare la ricetta «<?php echo addslashes($ricetta['titolo']); ?>» e tutti i suoi passi? L\'operazione è irreversibile.');">
                                <input type="hidden" name="id_ricetta" value="<?php echo $ricetta['id']; ?>">
                                <button type="submit" class="btn-icon btn-icon--danger" title="Elimina ricetta">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6M14 11v6"/>
                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                </button>
                            </form>

                            <!-- Toggle passi -->
                            <button class="btn-toggle" onclick="togglePassi(this, 'passi-<?php echo $ricetta['id']; ?>')"
                                    title="Mostra/nascondi passi">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>

                        </div>
                    </div>

                    <!-- Sezione passi (accordion) -->
                    <div class="passi-section" id="passi-<?php echo $ricetta['id']; ?>">
                        <div class="passi-inner">

                            <?php if (empty($ricetta['passi'])): ?>
                                <div style="padding: 18px 0; text-align:center; color:#8B7355; font-size:0.85rem;">
                                    Nessun passo ancora. Aggiungine uno!
                                </div>
                            <?php else: ?>
                                <?php foreach ($ricetta['passi'] as $idx => $passo): ?>
                                    <div class="passo-row">

                                        <div class="passo-num"><?php echo $idx + 1; ?></div>

                                        <div class="passo-info">
                                            <div class="passo-title"><?php echo htmlspecialchars($passo['titolo']); ?></div>
                                            <div class="passo-durata">
                                                <?php if ($passo['durata']): ?>⏱ <?php echo $passo['durata']; ?> min<?php endif; ?>
                                                <?php if ($passo['nome_cottura']): ?> · <?php echo htmlspecialchars($passo['nome_cottura']); ?><?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="passo-actions">
                                            <!-- Modifica passo -->
                                            <a href="modificaPasso.php?id_passo=<?php echo $passo['id']; ?>&id_ricetta=<?php echo $ricetta['id']; ?>"
                                               class="btn-icon--sm"
                                               title="Modifica passo">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                            </a>

                                            <!-- Elimina passo -->
                                            <form class="delete-form"
                                                  action="../controller/cancellaPassoController.php"
                                                  method="POST"
                                                  onsubmit="return confirm('Eliminare il passo «<?php echo addslashes($passo['titolo']); ?>»?');">
                                                <input type="hidden" name="id_passo"   value="<?php echo $passo['id']; ?>">
                                                <input type="hidden" name="id_ricetta" value="<?php echo $ricetta['id']; ?>">
                                                <button type="submit" class="btn-icon--sm danger" title="Elimina passo">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"/>
                                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                        <path d="M10 11v6M14 11v6"/>
                                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Aggiungi passo inline -->
                            <div class="passi-footer">
                                <a href="aggiungiPasso.php?id_ricetta=<?php echo $ricetta['id']; ?>" class="btn-add-passo">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Aggiungi passo
                                </a>
                            </div>

                        </div>
                    </div>

                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div><!-- /.ristorante-wrap -->

<!-- FAB aggiungi ricetta -->
<a href="aggiungiRicetta.php" class="fab" title="Aggiungi ricetta">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"/>
        <line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
</a>
<span class="fab-tooltip">Nuova ricetta</span>

<?php include '../include/footer.php'; ?>

<script>
    function togglePassi(btn, id) {
        const section = document.getElementById(id);
        const isOpen  = section.classList.toggle('open');
        btn.classList.toggle('open', isOpen);
        btn.title = isOpen ? 'Nascondi passi' : 'Mostra passi';
    }
</script>
<script><?php include_once '../js/dropDownMenu.js'; ?></script>

</body>
</html>