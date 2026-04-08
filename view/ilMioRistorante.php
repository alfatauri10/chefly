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
    <link rel="stylesheet" href="../css/timer.css">
    <script src="../js/timer.js" defer></script>

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

        /* ── AVATAR CON OVERLAY CAMBIO FOTO ──────────────────── */
        .profile-avatar-wrap {
            flex-shrink: 0;
            position: relative;
            width: 110px;
            height: 110px;
            cursor: pointer;
        }

        .profile-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #EDE8E0;
            display: block;
            transition: filter 0.25s ease;
        }

        /* Overlay scuro al hover sull'avatar */
        .avatar-overlay {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(26, 16, 8, 0.52);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.25s ease;
            pointer-events: none;
        }

        .profile-avatar-wrap:hover .avatar-overlay {
            opacity: 1;
        }

        .profile-avatar-wrap:hover .profile-avatar {
            filter: brightness(0.7);
        }

        .avatar-overlay svg {
            color: #fff;
        }

        .avatar-overlay span {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #fff;
            line-height: 1;
        }

        /* ── MODAL CAMBIO FOTO ────────────────────────────────── */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(26, 16, 8, 0.55);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        .modal-backdrop.open {
            opacity: 1;
            pointer-events: all;
        }

        .modal-box {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 36px 32px 28px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 24px 60px rgba(26, 16, 8, 0.18);
            transform: translateY(16px) scale(0.97);
            transition: transform 0.25s ease, opacity 0.25s ease;
            opacity: 0;
            position: relative;
        }

        .modal-backdrop.open .modal-box {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .modal-close {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 32px;
            height: 32px;
            border: none;
            background: #F5F2EC;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6B5C48;
            transition: background 0.15s, color 0.15s;
        }

        .modal-close:hover {
            background: #EDE8E0;
            color: #1A1008;
        }

        .modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: #1A1008;
            margin-bottom: 6px;
        }

        .modal-subtitle {
            font-size: 0.82rem;
            color: #8B7355;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        /* Anteprima foto nel modal */
        .preview-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .preview-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #EDE8E0;
            transition: border-color 0.2s;
        }

        /* Drop zone file */
        .dropzone {
            border: 2px dashed #D6CFC4;
            border-radius: 12px;
            padding: 20px 16px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
            margin-bottom: 16px;
            position: relative;
        }

        .dropzone:hover,
        .dropzone.dragover {
            border-color: #C4622D;
            background: #FFF3ED;
        }

        .dropzone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .dropzone-icon {
            width: 36px;
            height: 36px;
            background: #F5F2EC;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: #C4622D;
        }

        .dropzone p {
            font-size: 0.82rem;
            color: #6B5C48;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .dropzone small {
            font-size: 0.72rem;
            color: #A89880;
        }

        /* Nome file selezionato */
        .file-name-label {
            font-size: 0.78rem;
            color: #C4622D;
            font-weight: 600;
            text-align: center;
            margin-bottom: 16px;
            min-height: 18px;
            display: block;
        }

        /* Bottone conferma */
        .btn-modal-submit {
            width: 100%;
            padding: 13px;
            background: #1A1008;
            color: #FFF;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            letter-spacing: 0.3px;
        }

        .btn-modal-submit:hover   { background: #3a2518; }
        .btn-modal-submit:active  { transform: scale(0.98); }
        .btn-modal-submit:disabled {
            background: #D6CFC4;
            cursor: not-allowed;
            transform: none;
        }

        /* ── PROFILO INFO ─────────────────────────────────────── */
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

        .btn-toggle svg { transition: transform 0.25s ease; }
        .btn-toggle.open svg { transform: rotate(180deg); }

        /* ── SEZIONE PASSI ────────────────────────────────────── */
        .passi-section {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
        }

        .passi-section.open { max-height: 4000px; }

        .passi-inner {
            border-top: 1px solid #EDE8E0;
            background: #FDFCFA;
            padding: 0 20px;
        }

        .passo-row {
            display: flex;
            align-items: flex-start;
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
            margin-top: 4px;
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
            margin-bottom: 4px;
        }

        .passo-actions {
            display: flex;
            gap: 4px;
            flex-shrink: 0;
            margin-top: 4px;
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

        .delete-form { margin: 0; }

        /* ── RESPONSIVE ───────────────────────────────────────── */
        @media (max-width: 600px) {
            .profile-section { flex-direction: column; gap: 24px; text-align: center; }
            .profile-stats   { justify-content: center; }
            .profile-bio     { margin: 0 auto; }
            .fab             { bottom: 24px; right: 20px; }
            .fab-tooltip     { display: none; }
            .modal-box       { margin: 0 16px; }
        }
    </style>
</head>
<body>

<?php include '../include/header.php'; ?>

<!-- ═══════════════════════════════════════════════════════
     MODAL CAMBIO FOTO PROFILO
════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="modalFoto" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-box">

        <button class="modal-close" onclick="chiudiModal()" aria-label="Chiudi">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        <h2 class="modal-title" id="modalTitle">Cambia foto profilo</h2>
        <p class="modal-subtitle">Carica una nuova immagine (JPG, PNG, WEBP · max 5 MB)</p>

        <!-- Anteprima -->
        <div class="preview-wrap">
            <img id="previewImg"
                 src="<?php echo htmlspecialchars($profilo['urlFotoProfilo'] ?? '/img/fotoProfilo.jpg'); ?>"
                 alt="Anteprima"
                 class="preview-circle">
        </div>

        <form action="../controller/aggiornafotoprofiloController.php"
              method="POST"
              enctype="multipart/form-data"
              id="formFoto">

            <!-- Drop zone -->
            <div class="dropzone" id="dropzone">
                <input type="file"
                       name="foto_profilo"
                       id="inputFoto"
                       accept="image/jpeg,image/png,image/webp,image/gif"
                       onchange="onFileSelect(this)">
                <div class="dropzone-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </div>
                <p>Clicca o trascina qui la foto</p>
                <small>JPG, PNG, WEBP, GIF — max 5 MB</small>
            </div>

            <span class="file-name-label" id="fileNameLabel"></span>

            <button type="submit" class="btn-modal-submit" id="btnConferma" disabled>
                Salva foto profilo
            </button>

        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     PAGINA
════════════════════════════════════════════════════════ -->
<div class="ristorante-wrap">

    <!-- Flash messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="flash flash--success">
            <?php
            $msgs = [
                    'ricetta_creata'     => '✓ Ricetta aggiunta con successo!',
                    'ricetta_modificata' => '✓ Ricetta aggiornata.',
                    'passo_aggiunto'     => '✓ Passo aggiunto correttamente.',
                    'passo_modificato'   => '✓ Passo aggiornato.',
                    'ricetta_completata' => '✓ Ricetta completata!',
                    'foto_aggiornata'    => '✓ Foto profilo aggiornata!',
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
        <div class="flash flash--error">
            <?php
            $errori = [
                    'upload_fallito'    => 'Errore durante il caricamento. Riprova.',
                    'tipo_non_valido'   => 'Formato file non supportato. Usa JPG, PNG o WEBP.',
                    'file_troppo_grande'=> 'Il file supera i 5 MB.',
                    'salvataggio_fallito'=> 'Impossibile salvare il file sul server.',
                    'db_fallito'        => 'Errore nel salvataggio sul database.',
            ];
            echo htmlspecialchars($errori[$_GET['error']] ?? 'Si è verificato un errore. Riprova.');
            ?>
        </div>
    <?php endif; ?>

    <!-- ── SEZIONE PROFILO ────────────────────────────────── -->
    <section class="profile-section">

        <!-- Avatar cliccabile → apre modal -->
        <div class="profile-avatar-wrap" onclick="apriModal()" title="Cambia foto profilo">
            <img
                    src="<?php echo htmlspecialchars($profilo['urlFotoProfilo'] ?? '/img/fotoProfilo.jpg'); ?>"
                    alt="Foto profilo"
                    class="profile-avatar"
                    id="avatarPrincipale"
            >
            <div class="avatar-overlay">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                </svg>
                <span>Cambia</span>
            </div>
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

                    <div class="recipe-header">

                        <div class="recipe-cover">
                            <?php if (!empty($ricetta['url_copertina'])): ?>
                                <img src="../<?php echo htmlspecialchars($ricetta['url_copertina']); ?>"
                                     alt="Copertina <?php echo htmlspecialchars($ricetta['titolo']); ?>">
                            <?php else: ?>
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 11l19-9-9 19-2-8-8-2z"/>
                                </svg>
                            <?php endif; ?>
                        </div>

                        <div class="recipe-main">
                            <div class="recipe-title-text"><?php echo htmlspecialchars($ricetta['titolo']); ?></div>
                            <div class="recipe-meta">
                                <span class="badge badge--difficolta"><?php echo htmlspecialchars(ucfirst($ricetta['difficolta'])); ?></span>
                                <span class="badge badge--steps">
                                    <?php echo count($ricetta['passi']); ?> passo<?php echo count($ricetta['passi']) !== 1 ? 'i' : ''; ?>
                                </span>
                            </div>
                        </div>

                        <div class="recipe-actions">

                            <a href="modificaRicetta.php?id_ricetta=<?php echo $ricetta['id']; ?>"
                               class="btn-icon" title="Modifica ricetta">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>

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

                            <button class="btn-toggle"
                                    onclick="togglePassi(this, 'passi-<?php echo $ricetta['id']; ?>')"
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
                                            <?php if (!empty($passo['durata']) && $passo['durata'] > 0): ?>
                                                <div data-chefly-timer="<?= (int)$passo['id'] ?>"
                                                     data-durata="<?= (int)$passo['durata'] ?>"
                                                     data-label="<?= htmlspecialchars($passo['titolo']) ?>">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="passo-actions">
                                            <a href="modificaPasso.php?id_passo=<?php echo $passo['id']; ?>&id_ricetta=<?php echo $ricetta['id']; ?>"
                                               class="btn-icon--sm" title="Modifica passo">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                            </a>
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

<a href="aggiungiRicetta.php" class="fab" title="Aggiungi ricetta">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"/>
        <line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
</a>
<span class="fab-tooltip">Nuova ricetta</span>

<?php include '../include/footer.php'; ?>

<script>
    /* ── Accordion passi ─────────────────────────── */
    function togglePassi(btn, id) {
        const section = document.getElementById(id);
        const isOpen  = section.classList.toggle('open');
        btn.classList.toggle('open', isOpen);
        btn.title = isOpen ? 'Nascondi passi' : 'Mostra passi';
    }

    /* ── Modal foto profilo ──────────────────────── */
    const modal      = document.getElementById('modalFoto');
    const btnConferma = document.getElementById('btnConferma');
    const inputFoto  = document.getElementById('inputFoto');
    const previewImg = document.getElementById('previewImg');
    const fileLabel  = document.getElementById('fileNameLabel');
    const dropzone   = document.getElementById('dropzone');

    function apriModal() {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function chiudiModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }

    // Chiudi cliccando il backdrop
    modal.addEventListener('click', function(e) {
        if (e.target === modal) chiudiModal();
    });

    // Chiudi con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') chiudiModal();
    });

    // Selezione file: anteprima + abilita bottone
    function onFileSelect(input) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];

        // Validazione client-side dimensione
        if (file.size > 5 * 1024 * 1024) {
            alert('Il file è troppo grande. Massimo 5 MB.');
            input.value = '';
            return;
        }

        // Mostra nome file
        fileLabel.textContent = file.name;

        // Anteprima
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.borderColor = '#C4622D';
        };
        reader.readAsDataURL(file);

        btnConferma.disabled = false;
    }

    // Drag & drop sul dropzone
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', function() {
        dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            inputFoto.files = e.dataTransfer.files;
            onFileSelect(inputFoto);
        }
    });
</script>
<script><?php include_once '../js/dropDownMenu.js'; ?></script>

</body>
</html>