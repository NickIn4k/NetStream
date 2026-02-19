<?php
    session_start();
    header('Content-Type: application/json');

    // lettura dati JSON dalla richiesta nel body => Non è un file!
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['idContenuto'], $data['tipo'], $data['tempo'])) {
        echo json_encode(['success' => false, 'message' => 'Dati mancanti']);
        exit;
    }

    // leggi cookie esistente
    $continua = [];
    if (isset($_COOKIE['continuaAGuardare'])) {
        $continua = json_decode($_COOKIE['continuaAGuardare'], true) ?? [];
    }

    // aggiorna o aggiungi nuovo contenuto
    $found = false;
    foreach ($continua as &$item) {
        if ($item['idContenuto'] == $data['idContenuto'] && $item['idProfilo'] == $data['idProfilo']) {
            $item = $data; // sovrascrivi i dati esistenti
            $found = true;
            break;
        }
    }
    if (!$found) {
        $continua[] = $data; // aggiungi nuovo contenuto
    }

    // salva cookie per 2 mesi
    setcookie('continuaAGuardare', json_encode($continua), time() + 60*24*60*60, "/");

    echo json_encode(['success' => true]);
?>