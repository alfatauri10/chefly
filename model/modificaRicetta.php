<?php
// view/modificaRicetta.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../include/connessione.php';
require_once '../model/ricetta.php';

$id_utente  = $_SESSION['user_id'];
$id_ricetta = isset($_GET['id_ricetta']) ? (int)$_GET['id_ricetta'] : null;

if (!$id_ricetta) {
    header("Location: ilMioRistorante.php");
    exit();
}

// Recupera la ricetta e verifica che appartenga all'utente
$ricetta = getRicettaByIdDB($conn, $id_ricetta);
if (!$ricetta || $ricetta['idCreatore'] != $id_utente) {
    header("Location: ilMioRistorante.php?error=non_autorizzato");
    exit();
}

// Anagrafica per i select
$lista_nazionalita = getTutteLeNazionalita($conn);
$lista_tipologie   = getTutteLeTipologie($conn);

// Foto galleria esistenti (senza copertina)
$sql_gallery = "SELECT id, urlMedia, isCopertina FROM mediaRicette WHERE idRicetta = ? ORDER BY id ASC";
$stmt_gallery = $conn->prepare($sql_gallery);
$stmt_gallery->bind_param("i", $id_ricetta);
$stmt_gallery->execute();
$result_gallery = $stmt_gallery->get_result();
$foto_esistenti = [];
$copertina_esistente = null;
while ($row = $result_gallery->fetch_assoc()) {
    if ($row['isCopertina']) {
        $copertina_esistente = $row;
    } else {
        $foto_esistenti[] = $row;
    }
}
$stmt_gallery->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Ricetta — Chefly</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,500&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #FAF8F5;
            color: #1A1008;
            min-height: 100vh;
        }

        /* ── LAYOUT ───────────────────────────────────── */
        .edit-wrap {
            max-width: 680px;
            margin: 0 auto;
            padding: 52px 20px 120px;
        }

        /* ── BREADCRUMB ──────────────────────────────── */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .78rem;
            color: #8B7355;
            margin-bottom: 32px;
        }
        .breadcrumb a { color: #C4622D; text-decoration: none; font-weight: 500; }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb svg { flex-shrink: 0; }

        /* ── INTESTAZIONE ────────────────────────────── */
        .page-header {
            display: flex;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 40px;
        }

        .page-header-icon {
            width: 52px;
            height: 52px;
            background: #C4622D;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFF;
            flex-shrink: 0;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem;
            font-weight: 600;
            color: #1A1008;
            line-height: 1.15;
            margin-bottom: 6px;
        }

        .page-title em {
            font-style: italic;
            color: #C4622D;
        }

        .page-sub {
            font-size: .85rem;
            color: #8B7355;
            line-height: 1.5;
        }

        /* ── ALERT ───────────────────────────────────── */
        .alert {
            padding: 13px 18px;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert--error { background: #FFF1F0; color: #991B1B; border: 1px solid #FECACA; }

        /* ── CARD / SEZIONE ──────────────────────────── */
        .card {
            background: #FFF;
            border: 1px solid #EDE8E0;
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .card-header {
            padding: 18px 24px 16px;
            border-bottom: 1px solid #F5F2EC;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #FFF3ED;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #C4622D;
            flex-shrink: 0;
        }

        .card-header-title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #6B5C48;
        }

        .card-body { padding: 22px 24px 26px; }

        /* ── FORM ────────────────────────────────────── */
        .form-group { margin-bottom: 20px; }
        .form-group:last-child { margin-bottom: 0; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        label {
            display: block;
            font-size: .77rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #6B5C48;
            margin-bottom: 7px;
        }

        label .opt {
            font-weight: 400;
            color: #A89880;
            text-transform: none;
            letter-spacing: 0;
        }

        .form-control {
            width: 100%;
            background: #FAF8F5;
            border: 1px solid #EDE8E0;
            border-radius: 10px;
            padding: 11px 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: .92rem;
            color: #1A1008;
            transition: border-color .2s, box-shadow .2s, background .2s;
            appearance: none;
        }

        .form-control:focus {
            outline: none;
            border-color: #C4622D;
            box-shadow: 0 0 0 3px rgba(196,98,45,.12);
            background: #FFF;
        }

        .form-control::placeholder { color: #C4C0B8; }

        textarea.form-control {
            resize: vertical;
            min-height: 110px;
        }

        select.form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23a67c52' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
        }

        /* ── FILE INPUT ──────────────────────────────── */
        .file-zone {
            border: 2px dashed #D6CFC4;
            border-radius: 10px;
            padding: 18px 16px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            position: relative;
        }
        .file-zone:hover { border-color: #C4622D; background: #FFF3ED; }
        .file-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        .file-zone-icon {
            width: 36px; height: 36px;
            background: #F5F2EC;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 10px;
            color: #C4622D;
        }
        .file-zone p   { font-size: .82rem; color: #6B5C48; margin-bottom: 3px; font-weight: 500; }
        .file-zone small { font-size: .72rem; color: #A89880; }

        /* ── COPERTINA ATTUALE ───────────────────────── */
        .current-cover {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #FAF8F5;
            border: 1px solid #EDE8E0;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 14px;
        }
        .current-cover img {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
            border: 1px solid #EDE8E0;
        }
        .current-cover-placeholder {
            width: 72px;
            height: 72px;
            background: #F5F2EC;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #C4C0B8;
            flex-shrink: 0;
        }
        .current-cover-info { flex: 1; min-width: 0; }
        .current-cover-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #8B7355;
            margin-bottom: 3px;
        }
        .current-cover-name {
            font-size: .82rem;
            color: #1A1008;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .current-cover-hint {
            font-size: .75rem;
            color: #A89880;
            margin-top: 3px;
        }

        /* ── GALLERIA ESISTENTE ──────────────────────── */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .gallery-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid transparent;
            transition: border-color .2s;
            cursor: pointer;
        }
        .gallery-item img {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            display: block;
        }
        /* Overlay checkbox */
        .gallery-item input[type="checkbox"] {
            display: none;
        }
        .gallery-item .overlay {
            position: absolute;
            inset: 0;
            background: rgba(220,38,38,.0);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
        }
        .gallery-item .check-mark {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: rgba(255,255,255,.9);
            border: 2px solid #EDE8E0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .2s, border-color .2s, background .2s;
        }
        .gallery-item .check-mark svg { color: #DC2626; opacity: 0; transition: opacity .15s; }

        /* Stato: marcato per eliminazione */
        .gallery-item.marked {
            border-color: #DC2626;
        }
        .gallery-item.marked .overlay { background: rgba(220,38,38,.2); }
        .gallery-item.marked .check-mark { opacity: 1; border-color: #DC2626; background: #FFF; }
        .gallery-item.marked .check-mark svg { opacity: 1; }

        /* Hover sempre visibile la check */
        .gallery-item:hover .check-mark { opacity: 1; }

        .gallery-empty {
            text-align: center;
            padding: 28px;
            color: #A89880;
            font-size: .82rem;
            background: #FAF8F5;
            border: 1px dashed #D6CFC4;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .delete-hint {
            font-size: .75rem;
            color: #A89880;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .delete-hint svg { flex-shrink: 0; }

        /* ── PULSANTI FOOTER ─────────────────────────── */
        .form-actions {
            display: flex;
            gap: 14px;
            padding-top: 8px;
        }

        .btn-primary {
            flex: 1;
            padding: 14px;
            background: #C4622D;
            color: #FFF;
            border: none;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: .92rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-primary:hover  { background: #A8511F; }
        .btn-primary:active { transform: scale(.98); }

        .btn-secondary {
            padding: 14px 22px;
            background: transparent;
            color: #6B5C48;
            border: 1.5px solid #EDE8E0;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: .88rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 7px;
            transition: border-color .2s, background .2s, color .2s;
        }
        .btn-secondary:hover { border-color: #C4C0B8; background: #F5F2EC; color: #1A1008; }

        /* ── RESPONSIVE ──────────────────────────────── */
        @media (max-width: 600px) {
            .form-row   { grid-template-columns: 1fr; }
            .page-title { font-size: 1.5rem; }
            .form-actions { flex-direction: column; }
            .btn-secondary { justify-content: center; }
        }
    </style>
</head>
<body>

<?php include '../include/header.php'; ?>

<div class="edit-wrap">

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="ilMioRistorante.php">Il mio ristorante</a>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Modifica ricetta</span>
    </nav>

    <!-- Intestazione -->
    <div class="page-header">
        <div class="page-header-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
        </div>
        <div>
            <h1 class="page-title">Modifica <em><?php echo htmlspecialchars($ricetta['titolo']); ?></em></h1>
            <p class="page-sub">Aggiorna le informazioni della tua ricetta. Le modifiche saranno subito visibili.</p>
        </div>
    </div>

    <!-- Errore -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert--error">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php
            $errori = [
                'campi_mancanti'  => 'Compila tutti i campi obbligatori.',
                'errore_modifica' => 'Si è verificato un errore durante il salvataggio. Riprova.',
            ];
            echo htmlspecialchars($errori[$_GET['error']] ?? 'Errore sconosciuto.');
            ?>
        </div>
    <?php endif; ?>

    <form action="../controller/modificaRicettaController.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_ricetta" value="<?php echo $id_ricetta; ?>">

        <!-- ══ SEZIONE: INFORMAZIONI BASE ═══════════════ -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
                </div>
                <span class="card-header-title">Informazioni base</span>
            </div>
            <div class="card-body">

                <div class="form-group">
                    <label for="titolo">Titolo *</label>
                    <input type="text"
                           id="titolo"
                           name="titolo"
                           class="form-control"
                           placeholder="Es: Spaghetti alla Carbonara originale"
                           value="<?php echo htmlspecialchars($ricetta['titolo']); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="descrizione">Descrizione *</label>
                    <textarea id="descrizione"
                              name="descrizione"
                              class="form-control"
                              placeholder="Una breve introduzione alla tua ricetta..."
                              required><?php echo htmlspecialchars($ricetta['descrizione']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="difficolta">Difficoltà *</label>
                        <select id="difficolta" name="difficolta" class="form-control" required>
                            <?php
                            $opzioni = ['facile' => 'Facile', 'media' => 'Media', 'difficile' => 'Difficile', 'esperto' => 'Esperto'];
                            foreach ($opzioni as $val => $label): ?>
                                <option value="<?php echo $val; ?>" <?php echo ($ricetta['difficolta'] === $val) ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <!-- Placeholder per allineamento -->
                    </div>
                </div>

                <div class="form-row" style="margin-bottom:0;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="id_nazionalita">Nazionalità <span class="opt">(opzionale)</span></label>
                        <select id="id_nazionalita" name="id_nazionalita" class="form-control">
                            <option value="">Nessuna</option>
                            <?php foreach ($lista_nazionalita as $naz): ?>
                                <option value="<?php echo $naz['id']; ?>"
                                    <?php echo ($ricetta['idNazionalita'] == $naz['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($naz['nome']); ?>
                                    <?php if (!empty($naz['sigla'])) echo '(' . htmlspecialchars($naz['sigla']) . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="id_tipologia">Tipologia <span class="opt">(opzionale)</span></label>
                        <select id="id_tipologia" name="id_tipologia" class="form-control">
                            <option value="">Nessuna</option>
                            <?php foreach ($lista_tipologie as $tip): ?>
                                <option value="<?php echo $tip['id']; ?>"
                                    <?php echo ($ricetta['idTipologia'] == $tip['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tip['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

            </div>
        </div>

        <!-- ══ SEZIONE: COPERTINA ════════════════════════ -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <span class="card-header-title">Foto di copertina</span>
            </div>
            <div class="card-body">

                <!-- Copertina attuale -->
                <div class="current-cover">
                    <?php if ($copertina_esistente): ?>
                        <img src="../<?php echo htmlspecialchars($copertina_esistente['urlMedia']); ?>" alt="Copertina attuale">
                    <?php else: ?>
                        <div class="current-cover-placeholder">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="current-cover-info">
                        <div class="current-cover-label">Copertina attuale</div>
                        <div class="current-cover-name">
                            <?php echo $copertina_esistente ? basename($copertina_esistente['urlMedia']) : 'Nessuna copertina'; ?>
                        </div>
                        <div class="current-cover-hint">Carica una nuova immagine per sostituirla</div>
                    </div>
                </div>

                <div class="file-zone">
                    <input type="file" name="copertina" accept="image/*">
                    <div class="file-zone-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    </div>
                    <p>Clicca per scegliere una nuova copertina</p>
                    <small>JPG, PNG, WEBP — consigliata almeno 800×600 px</small>
                </div>

            </div>
        </div>

        <!-- ══ SEZIONE: GALLERIA ═════════════════════════ -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
                <span class="card-header-title">Galleria fotografica</span>
            </div>
            <div class="card-body">

                <?php if (!empty($foto_esistenti)): ?>
                    <p class="delete-hint">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Clicca su una foto per selezionarla ed eliminarla.
                    </p>
                    <div class="gallery-grid">
                        <?php foreach ($foto_esistenti as $foto): ?>
                            <label class="gallery-item" id="item-<?php echo $foto['id']; ?>">
                                <input type="checkbox"
                                       name="foto_da_eliminare[]"
                                       value="<?php echo $foto['id']; ?>"
                                       onchange="toggleMarcatura(this, <?php echo $foto['id']; ?>)">
                                <img src="../<?php echo htmlspecialchars($foto['urlMedia']); ?>"
                                     alt="Foto galleria">
                                <div class="overlay">
                                    <div class="check-mark">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="gallery-empty">
                        Nessuna foto nella galleria.
                    </div>
                <?php endif; ?>

                <div class="file-zone">
                    <input type="file" name="gallery[]" accept="image/*" multiple>
                    <div class="file-zone-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    </div>
                    <p>Aggiungi nuove foto alla galleria</p>
                    <small>Tieni premuto CTRL / CMD per selezionare più immagini</small>
                </div>

            </div>
        </div>

        <!-- ══ PULSANTI ══════════════════════════════════ -->
        <div class="form-actions">
            <a href="ilMioRistorante.php" class="btn-secondary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Annulla
            </a>
            <button type="submit" class="btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Salva modifiche
            </button>
        </div>

    </form>
</div>

<?php include '../include/footer.php'; ?>

<script>
    function toggleMarcatura(checkbox, id) {
        const item = document.getElementById('item-' + id);
        if (checkbox.checked) {
            item.classList.add('marked');
        } else {
            item.classList.remove('marked');
        }
    }
</script>
<script><?php include_once '../js/dropDownMenu.js'; ?></script>

</body>
</html>