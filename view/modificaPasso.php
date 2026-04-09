<?php
// view/modificaPasso.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../include/connessione.php';
require_once '../model/passo.php';
require_once '../model/ricetta.php';

$id_utente  = $_SESSION['user_id'];
$id_passo   = isset($_GET['id_passo'])   ? (int)$_GET['id_passo']   : null;
$id_ricetta = isset($_GET['id_ricetta']) ? (int)$_GET['id_ricetta'] : null;

if (!$id_passo || !$id_ricetta) {
    header("Location: ilMioRistorante.php");
    exit();
}

// Verifica che il passo appartenga a una ricetta dell'utente
$sql_check = "SELECT p.*, r.titolo AS titolo_ricetta
              FROM passi p
              JOIN ricette r ON p.idRicetta = r.id
              WHERE p.id = ? AND r.idCreatore = ?
              LIMIT 1";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $id_passo, $id_utente);
$stmt_check->execute();
$passo = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

if (!$passo) {
    header("Location: ilMioRistorante.php?error=non_autorizzato");
    exit();
}

// Numero del passo (posizione nella ricetta)
$sql_num = "SELECT COUNT(*) AS pos FROM passi WHERE idRicetta = ? AND id <= ?";
$stmt_num = $conn->prepare($sql_num);
$stmt_num->bind_param("ii", $id_ricetta, $id_passo);
$stmt_num->execute();
$numero_passo = (int)$stmt_num->get_result()->fetch_assoc()['pos'];
$stmt_num->close();

// Media esistenti del passo
$foto_passo = getMediaByIdPasso($conn, $id_passo);

// Ingredienti esistenti del passo
$ingredienti_passo = getIngredientiByIdPasso($conn, $id_passo);

// Anagrafiche per i select
$sql_cotture = "SELECT id, nome FROM anagCotture ORDER BY nome ASC";
$lista_cotture = $conn->query($sql_cotture)->fetch_all(MYSQLI_ASSOC);

$sql_ingredienti = "SELECT id, nome FROM anagIngredienti ORDER BY nome ASC";
$lista_ingredienti = $conn->query($sql_ingredienti)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Passo <?php echo $numero_passo; ?> — Chefly</title>

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
            flex-wrap: wrap;
        }
        .breadcrumb a { color: #C4622D; text-decoration: none; font-weight: 500; }
        .breadcrumb a:hover { text-decoration: underline; }

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
            background: #1A1008;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFF;
            flex-shrink: 0;
            font-family: 'DM Sans', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem;
            font-weight: 600;
            color: #1A1008;
            line-height: 1.15;
            margin-bottom: 6px;
        }
        .page-title em { font-style: italic; color: #C4622D; }

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

        /* ── CARD ────────────────────────────────────── */
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
            min-height: 120px;
        }

        select.form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23a67c52' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
        }

        /* ── INGREDIENTI ─────────────────────────────── */
        .ingredienti-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 12px;
        }

        .ingrediente-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 10px;
            align-items: center;
            background: #FAF8F5;
            border: 1px solid #EDE8E0;
            border-radius: 10px;
            padding: 10px 12px;
        }

        .ingrediente-row select,
        .ingrediente-row input[type="text"] {
            width: 100%;
            background: #FFF;
            border: 1px solid #EDE8E0;
            border-radius: 7px;
            padding: 8px 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: .85rem;
            color: #1A1008;
            appearance: none;
        }

        .ingrediente-row select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23a67c52' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 28px;
        }

        .btn-remove {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #DC2626;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s;
            flex-shrink: 0;
        }
        .btn-remove:hover { background: #FFF1F0; }

        .btn-add-ingrediente {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: 'DM Sans', sans-serif;
            font-size: .78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #C4622D;
            background: none;
            border: 1.5px dashed #E8B99A;
            border-radius: 8px;
            padding: 8px 16px;
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
        }
        .btn-add-ingrediente:hover { background: #FFF3ED; border-color: #C4622D; }

        /* ── FOTO PASSO ESISTENTI ────────────────────── */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
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
        .gallery-item input[type="checkbox"] { display: none; }
        .gallery-item .overlay {
            position: absolute;
            inset: 0;
            background: rgba(220,38,38,0);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
        }
        .gallery-item .check-mark {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255,255,255,.9);
            border: 2px solid #EDE8E0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .2s, border-color .2s;
        }
        .gallery-item .check-mark svg { color: #DC2626; opacity: 0; transition: opacity .15s; }
        .gallery-item:hover .check-mark { opacity: 1; }
        .gallery-item.marked { border-color: #DC2626; }
        .gallery-item.marked .overlay { background: rgba(220,38,38,.2); }
        .gallery-item.marked .check-mark { opacity: 1; border-color: #DC2626; }
        .gallery-item.marked .check-mark svg { opacity: 1; }

        .gallery-empty {
            text-align: center;
            padding: 24px;
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
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── FILE ZONE ───────────────────────────────── */
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
            .form-row          { grid-template-columns: 1fr; }
            .ingrediente-row   { grid-template-columns: 1fr; }
            .page-title        { font-size: 1.5rem; }
            .form-actions      { flex-direction: column; }
            .btn-secondary     { justify-content: center; }
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
        <a href="aggiungiPasso.php?id_ricetta=<?php echo $id_ricetta; ?>">
            <?php echo htmlspecialchars($passo['titolo_ricetta']); ?>
        </a>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Modifica passo <?php echo $numero_passo; ?></span>
    </nav>

    <!-- Intestazione -->
    <div class="page-header">
        <div class="page-header-icon"><?php echo $numero_passo; ?></div>
        <div>
            <h1 class="page-title">Modifica <em><?php echo htmlspecialchars($passo['titolo']); ?></em></h1>
            <p class="page-sub">Passo <?php echo $numero_passo; ?> di «<?php echo htmlspecialchars($passo['titolo_ricetta']); ?>».</p>
        </div>
    </div>

    <!-- Errore -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert--error">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php
            $errori = [
                'campi_mancanti'  => 'Compila almeno il titolo, la descrizione e la durata.',
                'errore_modifica' => 'Si è verificato un errore durante il salvataggio. Riprova.',
            ];
            echo htmlspecialchars($errori[$_GET['error']] ?? 'Errore sconosciuto.');
            ?>
        </div>
    <?php endif; ?>

    <form action="../controller/modificaPassoController.php" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="id_passo"   value="<?php echo $id_passo; ?>">
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
                    <label for="titolo">Titolo del passo *</label>
                    <input type="text"
                           id="titolo"
                           name="titolo"
                           class="form-control"
                           placeholder="Es: Preparazione del soffritto"
                           value="<?php echo htmlspecialchars($passo['titolo']); ?>"
                           required>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="descrizione">Descrizione *</label>
                    <textarea id="descrizione"
                              name="descrizione"
                              class="form-control"
                              placeholder="Spiega nel dettaglio questo passaggio..."
                              required><?php echo htmlspecialchars($passo['descrizione']); ?></textarea>
                </div>

            </div>
        </div>

        <!-- ══ SEZIONE: TEMPI ═══════════════════════════ -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <span class="card-header-title">Tempi</span>
            </div>
            <div class="card-body">

                <div class="form-row" style="margin-bottom:16px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="tempoCottura">Tempo cottura <span class="opt">(minuti)</span></label>
                        <input type="number"
                               id="tempoCottura"
                               name="tempoCottura"
                               class="form-control"
                               placeholder="Es: 10"
                               min="0"
                               value="<?php echo htmlspecialchars($passo['tempoCottura'] ?? ''); ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="tempoRiposo">Tempo riposo <span class="opt">(minuti)</span></label>
                        <input type="number"
                               id="tempoRiposo"
                               name="tempoRiposo"
                               class="form-control"
                               placeholder="Es: 5"
                               min="0"
                               value="<?php echo htmlspecialchars($passo['tempoRiposo'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="durata">Durata totale passo * <span class="opt">(minuti)</span></label>
                    <input type="number"
                           id="durata"
                           name="durata"
                           class="form-control"
                           placeholder="Es: 15"
                           min="1"
                           value="<?php echo htmlspecialchars($passo['durata']); ?>"
                           required>
                </div>

            </div>
        </div>

        <!-- ══ SEZIONE: TECNICA DI COTTURA ══════════════ -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <span class="card-header-title">Tecnica di cottura</span>
            </div>
            <div class="card-body">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="idCottura">Tipo di cottura <span class="opt">(opzionale)</span></label>
                    <select id="idCottura" name="idCottura" class="form-control">
                        <option value="">Nessuna cottura specifica</option>
                        <?php foreach ($lista_cotture as $cottura): ?>
                            <option value="<?php echo $cottura['id']; ?>"
                                <?php echo ($passo['idCottura'] == $cottura['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cottura['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- ══ SEZIONE: INGREDIENTI ══════════════════════ -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/><line x1="6" y1="17" x2="18" y2="17"/></svg>
                </div>
                <span class="card-header-title">Ingredienti del passo</span>
            </div>
            <div class="card-body">

                <div class="ingredienti-list" id="ingredienti-container">
                    <?php foreach ($ingredienti_passo as $ing): ?>
                        <div class="ingrediente-row" id="ing-row-<?php echo $ing['idIngrediente']; ?>-<?php echo uniqid(); ?>">
                            <select name="ingredienti[id][]">
                                <option value="">Scegli ingrediente</option>
                                <?php foreach ($lista_ingredienti as $li): ?>
                                    <option value="<?php echo $li['id']; ?>"
                                        <?php echo ($li['id'] == $ing['idIngrediente']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($li['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text"
                                   name="ingredienti[dose][]"
                                   placeholder="Dose (es: 200g)"
                                   value="<?php echo htmlspecialchars($ing['dose']); ?>">
                            <button type="button" class="btn-remove" onclick="rimuoviRiga(this)" title="Rimuovi">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="btn-add-ingrediente" onclick="aggiungiIngrediente()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Aggiungi ingrediente
                </button>

            </div>
        </div>

        <!-- ══ SEZIONE: FOTO DEL PASSO ══════════════════ -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <span class="card-header-title">Foto del passo</span>
            </div>
            <div class="card-body">

                <?php if (!empty($foto_passo)): ?>
                    <p class="delete-hint">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Clicca su una foto per selezionarla ed eliminarla.
                    </p>
                    <div class="gallery-grid">
                        <?php foreach ($foto_passo as $foto): ?>
                            <label class="gallery-item" id="foto-<?php echo $foto['id']; ?>">
                                <input type="checkbox"
                                       name="foto_da_eliminare[]"
                                       value="<?php echo $foto['id']; ?>"
                                       onchange="toggleFoto(this, <?php echo $foto['id']; ?>)">
                                <img src="../<?php echo htmlspecialchars($foto['urlMedia']); ?>" alt="Foto passo">
                                <div class="overlay">
                                    <div class="check-mark">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="gallery-empty">Nessuna foto caricata per questo passo.</div>
                <?php endif; ?>

                <div class="file-zone">
                    <input type="file" name="mediaPasso[]" accept="image/*" multiple>
                    <div class="file-zone-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    </div>
                    <p>Aggiungi nuove foto al passo</p>
                    <small>Tieni premuto CTRL / CMD per selezionare più immagini</small>
                </div>

            </div>
        </div>

        <!-- ══ PULSANTI ══════════════════════════════════ -->
        <div class="form-actions">
            <a href="aggiungiPasso.php?id_ricetta=<?php echo $id_ricetta; ?>" class="btn-secondary">
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
    /* ── Ingredienti dinamici ─────────────────────────────────── */
    const ingredienti = <?php echo json_encode($lista_ingredienti); ?>;
    let ingCounter = 0;

    function buildSelect(name) {
        let html = `<select name="${name}" style="width:100%;background:#FFF;border:1px solid #EDE8E0;border-radius:7px;padding:8px 12px;font-family:'DM Sans',sans-serif;font-size:.85rem;color:#1A1008;appearance:none;background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23a67c52' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right 10px center;padding-right:28px;">`;
        html += `<option value="">Scegli ingrediente</option>`;
        ingredienti.forEach(ing => {
            html += `<option value="${ing.id}">${ing.nome}</option>`;
        });
        html += `</select>`;
        return html;
    }

    function aggiungiIngrediente() {
        ingCounter++;
        const container = document.getElementById('ingredienti-container');
        const row = document.createElement('div');
        row.className = 'ingrediente-row';
        row.innerHTML = `
            ${buildSelect('ingredienti[id][]')}
            <input type="text"
                   name="ingredienti[dose][]"
                   placeholder="Dose (es: 200g)"
                   style="width:100%;background:#FFF;border:1px solid #EDE8E0;border-radius:7px;padding:8px 12px;font-family:'DM Sans',sans-serif;font-size:.85rem;color:#1A1008;">
            <button type="button" class="btn-remove" onclick="rimuoviRiga(this)" title="Rimuovi">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;
        container.appendChild(row);
    }

    function rimuoviRiga(btn) {
        btn.closest('.ingrediente-row').remove();
    }

    /* ── Galleria foto ────────────────────────────────────────── */
    function toggleFoto(checkbox, id) {
        const item = document.getElementById('foto-' + id);
        item.classList.toggle('marked', checkbox.checked);
    }
</script>
<script><?php include_once '../js/dropDownMenu.js'; ?></script>

</body>
</html>