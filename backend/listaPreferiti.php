<?php
    session_start();

    if (!isset($_SESSION['idUtente'])) {
        header("Location: /auth/login.php");
        exit;
    }

    $idUtente = $_SESSION['idUtente'];

    if (!isset($_GET['idContenuto'])) {
        die("Contenuto non specificato");
    }

    $idContenuto = $_GET['idContenuto'];

    $conn = new mysqli("localhost", "root", "", "db_NetStream");
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    // Prima prendiamo l'idProfilo attivo (o il primo profilo dell'utente)
    $stmt = $conn->prepare("SELECT idProfilo FROM Profilo WHERE idUtente = ? LIMIT 1");
    $stmt->bind_param("i", $idUtente);
    $stmt->execute();
    $result = $stmt->get_result();
    $profilo = $result->fetch_assoc();
    $stmt->close();

    if (!$profilo) {
        die("Nessun profilo trovato per l'utente");
    }

    $idProfilo = $profilo['idProfilo'];

    // Controllo se il contenuto è già nei preferiti
    $stmt = $conn->prepare("SELECT 1 FROM ListaPreferiti WHERE idProfilo = ? AND idContenuto = ?");
    $stmt->bind_param("ii", $idProfilo, $idContenuto);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Esiste: rimuovi
        $stmt->close();
        $stmt = $conn->prepare("DELETE FROM ListaPreferiti WHERE idProfilo = ? AND idContenuto = ?");
        $stmt->bind_param("ii", $idProfilo, $idContenuto);
        $stmt->execute();
        $stmt->close();
    } else {
        // Non esiste: aggiungi
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO ListaPreferiti (idProfilo, idContenuto) VALUES (?, ?)");
        $stmt->bind_param("ii", $idProfilo, $idContenuto);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    // Torna indietro alla pagina precedente
    header("Location: /catalogo/contenuto.php?idContenuto=" . $idContenuto);
    exit;
?>