<?php
// view/aggiungiPasso.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../include/connessione.php';
require_once '../model/passo.php';

// L'id_ricetta DEVE arrivare dalla query string (reindirizzamento dal controller della ricetta)
$id_ricetta = isset($_GET['id_ricetta']) ? (int)$_GET['id_ricetta'] : null;

if (!$id_ricetta) {
    header("Location: listaRicetteUtente.php?error=ricetta_non_trovata");
    exit();
}

// Recuperiamo il numero del passo corrente (quanti passi esistono già + 1)
$sql_count = "SELECT COUNT(*) as tot FROM passi WHERE idRicetta = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $id_ricetta);
$stmt_count->execute();
$result_count = $stmt_count->get_result()->fetch_assoc();
$numero_passo_corrente = ($result_count['tot'] ?? 0) + 1;

// Recuperiamo le anagrafiche per i menu a tendina
$sql_cotture = "SELECT id, nome FROM anagCotture ORDER BY nome ASC";
$lista_cotture = $conn->query($sql_cotture)->fetch_all(MYSQLI_ASSOC);

$sql_ingredienti = "SELECT id, nome FROM anagIngredienti ORDER BY nome ASC";
$lista_ingredienti = $conn->query($sql_ingredienti)->fetch_all(MYSQLI_ASSOC);

// Recuperiamo il titolo della ricetta per mostrarlo nella UI
$sql_ricetta = "SELECT titolo FROM ricette WHERE id = ?";
$stmt_ric = $conn->prepare($sql_ricetta);
$stmt_ric->bind_param("i", $id_ricetta);
$stmt_ric->execute();
$dati_ricetta = $stmt_ric->get_result()->fetch_assoc();
$titolo_ricetta = $dati_ricetta['titolo'] ?? 'la tua ricetta';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Passo <?php echo $numero_passo_corrente; ?> - Chefly</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ==========================================
           RESET & BASE
        ========================================== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f7f3ef;
            min-height: 100vh;
            color: #2d1b10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 40px 20px 80px;
        }

        /* ==========================================
           PROGRESS HEADER
        ========================================== */
        .step-header {
            width: 100%;
            max-width: 700px;
            margin-bottom: 30px;
        }

        .step-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .recipe-context {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #a67c52;
            font-weight: 600;
        }

        .step-badge {
            background: #2d1b10;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 5px 14px;
            border-radius: 50px;
        }

        .step-progress-bar {
            width: 100%;
            height: 3px;
            background: #e8dfd5;
            border-radius: 2px;
            overflow: hidden;
        }

        .step-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #a67c52, #2d1b10);
            width: <?php echo min(100, $numero_passo_corrente * 20); ?>%;
            border-radius: 2px;
            transition: width 0.4s ease;
        }

        /* ==========================================
           MAIN CARD
        ========================================== */
        .step-card {
            background: #fff;
            width: 100%;
            max-width: 700px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(45, 27, 16, 0.08);
            overflow: hidden;
        }

        .card-top-bar {
            height: 5px;
            background: linear-gradient(90deg, #a67c52, #2d1b10 60%, #a67c52);
        }

        .card-body {
            padding: 36px 40px 40px;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d1b10;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .step-number-circle {
            width: 36px;
            height: 36px;
            background: #2d1b10;
            color: #f7f3ef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .card-subtitle {
            font-size: 0.82rem;
            color: #a67c52;
            letter-spacing: 0.5px;
            margin-bottom: 32px;
            padding-left: 48px; /* allinea sotto il titolo */
        }

        /* ==========================================
           FORM ELEMENTS
        ========================================== */
        .form-section {
            margin-bottom: 28px;
        }

        .form-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #a67c52;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0e8de;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
        }

        label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #2d1b10;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        label .opt {
            font-weight: 400;
            color: #a67c52;
            text-transform: none;
            letter-spacing: 0;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            background: #f9f5f1;
            border: 1px solid #e8dfd5;
            border-radius: 8px;
            padding: 10px 14px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.88rem;
            color: #2d1b10;
            transition: border-color 0.2s, box-shadow 0.2s;
            appearance: none;
            -webkit-appearance: none;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #a67c52;
            box-shadow: 0 0 0 3px rgba(166, 124, 82, 0.12);
            background: #fff;
        }

        textarea {
            resize: vertical;
            min-height: 110px;
        }

        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23a67c52' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
        }

        input[type="file"] {
            width: 100%;
            background: #fff;
            border: 2px dashed #e8dfd5;
            border-radius: 8px;
            padding: 12px 14px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.82rem;
            color: #a67c52;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        input[type="file"]:hover {
            border-color: #a67c52;
        }

        .file-hint {
            font-size: 0.72rem;
            color: #b8a99a;
            margin-top: 4px;
        }

        /* ==========================================
           INGREDIENTI DINAMICI
        ========================================== */
        .ingredienti-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 10px;
        }

        .ingrediente-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 10px;
            align-items: center;
            background: #f9f5f1;
            border-radius: 8px;
            padding: 10px 12px;
            border: 1px solid #e8dfd5;
        }

        .btn-remove-ing {
            background: none;
            border: none;
            cursor: pointer;
            color: #c0392b;
            padding: 4px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s;
        }

        .btn-remove-ing:hover {
            background: #fdf2f2;
        }

        .btn-add-ingrediente {
            background: none;
            border: 1px dashed #a67c52;
            color: #a67c52;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 9px 16px;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s, color 0.15s;
        }

        .btn-add-ingrediente:hover {
            background: #f7f3ef;
            color: #2d1b10;
            border-color: #2d1b10;
        }

        /* ==========================================
           DIVIDER
        ========================================== */
        .divider {
            height: 1px;
            background: #f0e8de;
            margin: 28px 0;
        }

        /* ==========================================
           CHECKBOX "ULTIMO PASSO"
        ========================================== */
        .ultimo-passo-section {
            background: linear-gradient(135deg, #fdf9f5 0%, #f7f0e8 100%);
            border: 1px solid #e8dfd5;
            border-radius: 12px;
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        .custom-checkbox-wrap {
            flex-shrink: 0;
            position: relative;
            width: 24px;
            height: 24px;
        }

        .custom-checkbox-wrap input[type="checkbox"] {
            opacity: 0;
            width: 24px;
            height: 24px;
            position: absolute;
            cursor: pointer;
            margin: 0;
        }

        .custom-checkbox-mark {
            width: 24px;
            height: 24px;
            border: 2px solid #a67c52;
            border-radius: 6px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, border-color 0.2s;
            pointer-events: none;
        }

        .custom-checkbox-wrap input[type="checkbox"]:checked ~ .custom-checkbox-mark {
            background: #2d1b10;
            border-color: #2d1b10;
        }

        .custom-checkbox-mark svg {
            display: none;
        }

        .custom-checkbox-wrap input[type="checkbox"]:checked ~ .custom-checkbox-mark svg {
            display: block;
        }

        .ultimo-passo-label {
            flex: 1;
        }

        .ultimo-passo-label strong {
            display: block;
            font-size: 0.9rem;
            font-weight: 700;
            color: #2d1b10;
            margin-bottom: 2px;
        }

        .ultimo-passo-label span {
            font-size: 0.78rem;
            color: #a67c52;
        }

        /* ==========================================
           SUBMIT BUTTONS
        ========================================== */
        .btn-group {
            display: flex;
            gap: 14px;
        }

        .btn-submit {
            flex: 1;
            background: #2d1b10;
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 14px 24px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.88rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-submit:hover { background: #4a3327; }
        .btn-submit:active { transform: scale(0.98); }

        .btn-back {
            background: transparent;
            color: #2d1b10;
            border: 1.5px solid #e8dfd5;
            border-radius: 50px;
            padding: 14px 24px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: border-color 0.2s, background 0.2s;
        }

        .btn-back:hover {
            border-color: #a67c52;
            background: #f7f3ef;
        }

        /* ==========================================
           ALERT
        ========================================== */
        .alert-error {
            background: #fdf2f2;
            color: #c0392b;
            border: 1px solid #ffc9c9;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 24px;
        }

        /* ==========================================
           RESPONSIVE
        ========================================== */
        @media (max-width: 620px) {
            .card-body { padding: 24px 22px 28px; }
            .form-row { grid-template-columns: 1fr; }
            .ingrediente-row { grid-template-columns: 1fr; }
            .btn-group { flex-direction: column; }
            .card-subtitle { padding-left: 0; }
        }
    </style>
</head>
<body>

<!-- PROGRESS HEADER -->
<div class="step-header">
    <div class="step-meta">
        <span class="recipe-context">
            <?php echo htmlspecialchars($titolo_ricetta); ?>
        </span>
        <span class="step-badge">Passo <?php echo $numero_passo_corrente; ?></span>
    </div>
    <div class="step-progress-bar">
        <div class="step-progress-fill"></div>
    </div>
</div>

<!-- CARD PRINCIPALE -->
<div class="step-card">
    <div class="card-top-bar"></div>
    <div class="card-body">

        <h1 class="card-title">
            <span class="step-number-circle"><?php echo $numero_passo_corrente; ?></span>
            Aggiungi un Passo
        </h1>
        <p class="card-subtitle">
            Descrivi questa fase della ricetta. Al termine potrai aggiungerne altri oppure completare la ricetta.
        </p>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'campi_mancanti'): ?>
            <div class="alert-error">
                Compila almeno il titolo e la descrizione del passo prima di procedere.
            </div>
        <?php endif; ?>

        <form action="../controller/aggiungiPassoController.php" method="POST" enctype="multipart/form-data">

            <!-- Campo nascosto: id ricetta -->
            <input type="hidden" name="id_ricetta" value="<?php echo $id_ricetta; ?>">
            <input type="hidden" name="numero_passo" value="<?php echo $numero_passo_corrente; ?>">

            <!-- ===== SEZIONE: INFORMAZIONI BASE ===== -->
            <div class="form-section">
                <div class="form-section-title">Informazioni Base</div>

                <div class="form-group">
                    <label for="titolo">Titolo del Passo *</label>
                    <input type="text" id="titolo" name="titolo"
                           placeholder="Es: Preparazione del soffritto" required>
                </div>

                <div class="form-group">
                    <label for="descrizione">Descrizione *</label>
                    <textarea id="descrizione" name="descrizione"
                              placeholder="Spiega nel dettaglio cosa deve fare il cuoco in questo passaggio..." required></textarea>
                </div>
            </div>

            <!-- ===== SEZIONE: TEMPI ===== -->
            <div class="form-section">
                <div class="form-section-title">Tempi</div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tempoCottura">Tempo Cottura <span class="opt">(minuti)</span></label>
                        <input type="number" id="tempoCottura" name="tempoCottura"
                               placeholder="Es: 10" min="0">
                    </div>
                    <div class="form-group">
                        <label for="tempoRiposo">Tempo Riposo <span class="opt">(minuti)</span></label>
                        <input type="number" id="tempoRiposo" name="tempoRiposo"
                               placeholder="Es: 5" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="durata">Durata Totale Passo *<span class="opt"> (minuti)</span></label>
                    <input type="number" id="durata" name="durata"
                           placeholder="Es: 15" min="1" required>
                </div>
            </div>

            <!-- ===== SEZIONE: COTTURA ===== -->
            <div class="form-section">
                <div class="form-section-title">Tecnica di Cottura</div>

                <div class="form-group">
                    <label for="idCottura">Tipo di Cottura <span class="opt">(opzionale)</span></label>
                    <select id="idCottura" name="idCottura">
                        <option value="">Nessuna cottura specifica</option>
                        <?php foreach ($lista_cotture as $cottura): ?>
                            <option value="<?php echo $cottura['id']; ?>">
                                <?php echo htmlspecialchars($cottura['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- ===== SEZIONE: INGREDIENTI ===== -->
            <div class="form-section">
                <div class="form-section-title">Ingredienti usati in questo passo</div>

                <div class="ingredienti-list" id="ingredienti-container">
                    <!-- Le righe ingrediente vengono aggiunte dinamicamente -->
                </div>

                <button type="button" class="btn-add-ingrediente" onclick="aggiungiIngrediente()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Aggiungi Ingrediente
                </button>
            </div>

            <!-- ===== SEZIONE: MEDIA ===== -->
            <div class="form-section">
                <div class="form-section-title">Foto del Passo</div>

                <div class="form-group">
                    <label>Immagini <span class="opt">(opzionale)</span></label>
                    <input type="file" name="mediaPasso[]" accept="image/*" multiple>
                    <span class="file-hint">Puoi caricare più immagini tenendo premuto CTRL / CMD.</span>
                </div>
            </div>

            <div class="divider"></div>

            <!-- ===== SEZIONE: ULTIMO PASSO? ===== -->
            <div class="ultimo-passo-section">
                <div class="custom-checkbox-wrap">
                    <input type="checkbox" name="is_ultimo_passo" id="is_ultimo_passo" value="1">
                    <div class="custom-checkbox-mark">
                        <svg width="13" height="10" viewBox="0 0 13 10" fill="none">
                            <path d="M1.5 5L5 8.5L11.5 1.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <div class="ultimo-passo-label">
                    <strong>Questo è l'ultimo passo</strong>
                    <span>Spunta questa casella solo se vuoi concludere la ricetta dopo aver salvato questo passo.</span>
                </div>
            </div>

            <!-- ===== BOTTONI ===== -->
            <div class="btn-group">
                <a href="listaRicetteUtente.php" class="btn-back">
                    ← Le mie ricette
                </a>
                <button type="submit" class="btn-submit">
                    Salva Passo →
                </button>
            </div>

        </form>
    </div>
</div>

<!-- Template JS per le righe ingrediente -->
<script>
    const ingredienti = <?php echo json_encode($lista_ingredienti); ?>;
    let ingCounter = 0;

    function buildSelectIngrediente(name) {
        let html = `<select name="${name}" style="width:100%;background:#fff;border:1px solid #e8dfd5;border-radius:6px;padding:8px 12px;font-family:Montserrat,sans-serif;font-size:0.83rem;color:#2d1b10;appearance:none;background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23a67c52' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right 10px center;padding-right:28px;">`;
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
        row.id = 'ing-row-' + ingCounter;
        row.innerHTML = `
            ${buildSelectIngrediente('ingredienti[id][]')}
            <input type="text"
                   name="ingredienti[dose][]"
                   placeholder="Dose (es: 200g)"
                   style="background:#fff;border:1px solid #e8dfd5;border-radius:6px;padding:8px 12px;font-family:Montserrat,sans-serif;font-size:0.83rem;color:#2d1b10;">
            <button type="button" class="btn-remove-ing" onclick="rimuoviIngrediente('ing-row-${ingCounter}')" title="Rimuovi">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        `;
        container.appendChild(row);
    }

    function rimuoviIngrediente(rowId) {
        const row = document.getElementById(rowId);
        if (row) row.remove();
    }
</script>

</body>
</html>