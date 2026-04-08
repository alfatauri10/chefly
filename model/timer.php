<?php
// model/timer.php

/**
 * getTimerByUtente()
 * Recupera il timer associato all'utente loggato.
 * Se l'utente non ha ancora un timer personalizzato restituisce i valori di default.
 */
function getTimerByUtente($conn, $id_utente) {
    $sql = "SELECT t.id, t.coloreSfondo, t.coloreLancetta, t.coloreNumeri
            FROM timer t
            JOIN utenti u ON u.idTimer = t.id
            WHERE u.id = ?
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $result = $stmt->get_result();
    $timer  = $result->fetch_assoc();
    $stmt->close();

    // Fallback ai valori di default se per qualche motivo non trovato
    if (!$timer) {
        return [
            'id'             => 1,
            'coloreSfondo'   => '#FFFFFF',
            'coloreLancetta' => '#000000',
            'coloreNumeri'   => '#000000',
        ];
    }
    return $timer;
}

/**
 * getTimerById()
 * Recupera un timer per ID diretto.
 */
function getTimerById($conn, $id_timer) {
    $sql = "SELECT id, coloreSfondo, coloreLancetta, coloreNumeri FROM timer WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_timer);
    $stmt->execute();
    $result = $stmt->get_result();
    $timer  = $result->fetch_assoc();
    $stmt->close();
    return $timer;
}

/**
 * aggiornaTimerUtente()
 * Aggiorna i colori del timer dell'utente.
 *
 * Strategia: ogni utente ha il proprio record nella tabella timer
 * (il timer con id = 1 è solo il default di sistema, non va mai modificato).
 * Se l'utente punta ancora al timer di default (id = 1), ne creiamo uno nuovo
 * personale e aggiorniamo utenti.idTimer. Altrimenti aggiorniamo quello esistente.
 *
 * @return bool
 */
function aggiornaTimerUtente($conn, $id_utente, $coloreSfondo, $coloreLancetta, $coloreNumeri) {

    // Recupera l'id_timer attuale dell'utente
    $sql_u = "SELECT idTimer FROM utenti WHERE id = ?";
    $stmt_u = $conn->prepare($sql_u);
    $stmt_u->bind_param("i", $id_utente);
    $stmt_u->execute();
    $row_u = $stmt_u->get_result()->fetch_assoc();
    $stmt_u->close();

    $id_timer_corrente = (int)($row_u['idTimer'] ?? 1);

    if ($id_timer_corrente === 1) {
        // L'utente punta ancora al timer di default → crea un timer personale
        $sql_ins = "INSERT INTO timer (coloreSfondo, coloreLancetta, coloreNumeri) VALUES (?, ?, ?)";
        $stmt_ins = $conn->prepare($sql_ins);
        $stmt_ins->bind_param("sss", $coloreSfondo, $coloreLancetta, $coloreNumeri);

        if (!$stmt_ins->execute()) {
            $stmt_ins->close();
            return false;
        }
        $id_nuovo_timer = $conn->insert_id;
        $stmt_ins->close();

        // Aggiorna utenti.idTimer con il nuovo id
        $sql_upd_u = "UPDATE utenti SET idTimer = ? WHERE id = ?";
        $stmt_upd_u = $conn->prepare($sql_upd_u);
        $stmt_upd_u->bind_param("ii", $id_nuovo_timer, $id_utente);
        $esito = $stmt_upd_u->execute();
        $stmt_upd_u->close();
        return $esito;

    } else {
        // L'utente ha già un timer personale → aggiornalo
        $sql_upd = "UPDATE timer SET coloreSfondo = ?, coloreLancetta = ?, coloreNumeri = ? WHERE id = ?";
        $stmt_upd = $conn->prepare($sql_upd);
        $stmt_upd->bind_param("sssi", $coloreSfondo, $coloreLancetta, $coloreNumeri, $id_timer_corrente);
        $esito = $stmt_upd->execute();
        $stmt_upd->close();
        return $esito;
    }
}

/**
 * validaColoreHex()
 * Semplice sanitizzazione: accetta solo stringhe #RRGGBB valide.
 */
function validaColoreHex($colore) {
    if (preg_match('/^#[0-9A-Fa-f]{6}$/', $colore)) {
        return strtoupper($colore);
    }
    return null;
}