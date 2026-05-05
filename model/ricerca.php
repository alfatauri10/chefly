<?php
// model/ricerca.php

/**
 * cercaRicette()
 *
 * Ricerca full-text con filtri avanzati.
 *
 * @param $conn
 * @param string $q              Testo libero (ricerca su titolo + descrizione ricetta)
 * @param array  $filtri         Associativo:
 *                               - difficolta      string|null  ('facile','media','difficile','esperto')
 *                               - id_nazionalita  int|null
 *                               - id_tipologia    int|null
 *                               - id_ingrediente  int|null     (almeno un passo deve usare quell'ingrediente)
 *                               - id_cottura      int|null     (almeno un passo deve usare quella cottura)
 * @param int    $limit
 * @param int    $offset
 * @return array  ['ricette' => [...], 'totale' => int]
 */
function cercaRicette($conn, $q = '', $filtri = [], $limit = 20, $offset = 0) {

    $where  = ["1=1"];
    $params = [];
    $types  = "";

    // ── Testo libero ──────────────────────────────────────────
    $q = trim($q);
    if ($q !== '') {
        $like = '%' . $q . '%';
        $where[]  = "(r.titolo LIKE ? OR r.descrizione LIKE ?)";
        $params[] = $like;
        $params[] = $like;
        $types   .= "ss";
    }

    // ── Difficoltà ────────────────────────────────────────────
    $difficolta_valide = ['facile', 'media', 'difficile', 'esperto'];
    if (!empty($filtri['difficolta']) && in_array($filtri['difficolta'], $difficolta_valide)) {
        $where[]  = "r.difficolta = ?";
        $params[] = $filtri['difficolta'];
        $types   .= "s";
    }

    // ── Nazionalità ───────────────────────────────────────────
    if (!empty($filtri['id_nazionalita'])) {
        $where[]  = "r.idNazionalita = ?";
        $params[] = (int)$filtri['id_nazionalita'];
        $types   .= "i";
    }

    // ── Tipologia ─────────────────────────────────────────────
    if (!empty($filtri['id_tipologia'])) {
        $where[]  = "r.idTipologia = ?";
        $params[] = (int)$filtri['id_tipologia'];
        $types   .= "i";
    }

    // ── Ingrediente ───────────────────────────────────────────
    // Almeno un passo della ricetta deve usare quell'ingrediente
    if (!empty($filtri['id_ingrediente'])) {
        $where[] = "EXISTS (
            SELECT 1 FROM passi p2
            JOIN passiIngredienti pi ON pi.idPasso = p2.id
            WHERE p2.idRicetta = r.id AND pi.idIngrediente = ?
        )";
        $params[] = (int)$filtri['id_ingrediente'];
        $types   .= "i";
    }

    // ── Tecnica di cottura ────────────────────────────────────
    // Almeno un passo deve usare quella tecnica
    if (!empty($filtri['id_cottura'])) {
        $where[] = "EXISTS (
            SELECT 1 FROM passi p3
            WHERE p3.idRicetta = r.id AND p3.idCottura = ?
        )";
        $params[] = (int)$filtri['id_cottura'];
        $types   .= "i";
    }

    $where_sql = implode(" AND ", $where);

    // ── Count totale (per paginazione) ────────────────────────
    $sql_count = "SELECT COUNT(DISTINCT r.id) AS tot
                  FROM ricette r
                  WHERE {$where_sql}";

    $stmt_c = $conn->prepare($sql_count);
    $totale = 0;
    if (!empty($params)) {
        $stmt_c->bind_param($types, ...$params);
    }
    $stmt_c->execute();
    $totale = (int)$stmt_c->get_result()->fetch_assoc()['tot'];
    $stmt_c->close();

    // ── Risultati ─────────────────────────────────────────────
    $sql = "SELECT r.id, r.titolo, r.descrizione, r.difficolta, r.dataCreazione,
                   r.idNazionalita, r.idTipologia,
                   u.username AS nome_autore,
                   u.urlFotoProfilo AS foto_autore,
                   n.nome AS nome_nazionalita,
                   t.nome AS nome_tipologia,
                   m.urlMedia AS url_copertina
            FROM ricette r
            JOIN utenti u ON r.idCreatore = u.id
            LEFT JOIN anagNazionalita n    ON r.idNazionalita = n.id
            LEFT JOIN anagTipologiePiatti t ON r.idTipologia  = t.id
            LEFT JOIN mediaRicette m ON r.id = m.idRicetta AND m.isCopertina = 1
            WHERE {$where_sql}
            GROUP BY r.id
            ORDER BY r.dataCreazione DESC
            LIMIT ? OFFSET ?";

    $all_params = array_merge($params, [$limit, $offset]);
    $all_types  = $types . "ii";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($all_types, ...$all_params);
    $stmt->execute();
    $result  = $stmt->get_result();
    $ricette = [];
    while ($row = $result->fetch_assoc()) {
        $ricette[] = $row;
    }
    $stmt->close();

    return ['ricette' => $ricette, 'totale' => $totale];
}

/**
 * getSuggerimentiRicerca()
 * Restituisce titoli di ricette che iniziano con la stringa passata.
 * Usata per l'autocomplete live (AJAX).
 * Max 8 risultati.
 */
function getSuggerimentiRicerca($conn, $q, $limit = 8) {
    if (trim($q) === '') return [];
    $like = '%' . trim($q) . '%';
    $sql = "SELECT id, titolo, difficolta FROM ricette
            WHERE titolo LIKE ?
            ORDER BY titolo ASC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $like, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}