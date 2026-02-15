<?php
    session_start();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Metodo non valido']);
        exit;
    }

    // Controllo sessione
    if(!isset($_SESSION['idProfilo'])) {
        echo json_encode(['success' => false, 'message' => 'Devi essere loggato per scrivere una recensione.']);
        exit;
    }

    $idProfilo = $_SESSION['idProfilo'];
    $idContenuto = $_POST['idContenuto'] ?? null;
    $voto = $_POST['voto'] ?? null;
    $commento = trim($_POST['commento'] ?? '');

    if(!$idContenuto || !$voto || $voto < 1 || $voto > 5 || empty($commento)){
        echo json_encode(['success' => false, 'message' => 'Compila tutti i campi correttamente.']);
        exit;
    }

    $conn = new mysqli("localhost", "root", "", "db_NetStream");

    if($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Connessione al database fallita.']);
        exit;
    }

    // Inserimento nel DB
    $sql = "INSERT INTO recensione (voto, commento, dataRecensione, idProfilo, idContenuto) 
            VALUES (?, ?, NOW(), ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isii", $voto, $commento, $idProfilo, $idContenuto);

    if($stmt->execute()){
        echo json_encode(['success' => true, 'message' => 'Recensione inviata con successo!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore durante l\'invio della recensione.']);
    }

    $stmt->close();
?>