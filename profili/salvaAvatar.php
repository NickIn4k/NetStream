<?php
    session_start();

    if (!isset($_SESSION['idUtente'])) {
        header("Location: /auth/login.php");
        exit;
    }

    if (!isset($_POST['nomeProfilo']) || !isset($_POST['idAvatar']) || empty(trim($_POST['nomeProfilo']))) {
        header("Location: /profili/avatar.php");
        exit;
    }

    // Connessione DB
    $conn = new mysqli("localhost", "root", "", "db_NetStream");
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    // Dati
    $idUtente    = $_SESSION['idUtente'];
    $nomeProfilo = trim($_POST['nomeProfilo']);
    $idAvatar    = (int) $_POST['idAvatar'];

    // Valori di default (puoi cambiarli più avanti)
    $linguaProfilo = $_POST['lingua'] ?? 'it';
    $etaProfilo    = isset($_POST['eta']) ? (int)$_POST['eta'] : 18;

    $idProfilo = $_POST['idProfilo'] ?? null;

    if($idProfilo) {
        // Update profilo esistente
        $stmt = $conn->prepare("
            UPDATE Profilo
            SET nomeProfilo = ?, linguaProfilo = ?, etaProfilo = ?, idAvatar = ?
            WHERE idProfilo = ? AND idUtente = ?
        ");

        $stmt->bind_param(
            "ssiiii",
            $nomeProfilo,
            $linguaProfilo,
            $etaProfilo,
            $idAvatar,
            $idProfilo,
            $idUtente
        );

        if (!$stmt->execute()) {
            die("Errore aggiornamento profilo: " . $stmt->error);
        }

        // Salvataggio in sessione
        $_SESSION['idProfilo']   = $idProfilo;
        $_SESSION['nomeProfilo'] = $nomeProfilo;

        $stmt->close();
        $conn->close();

        // Redirect finale
        header("Location: /profili/profili.php");
        exit;
    }

    // Insert profilo
    $stmt = $conn->prepare("
        INSERT INTO profilo 
        (nomeProfilo, linguaProfilo, etaProfilo, idUtente, idAvatar)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssiii",
        $nomeProfilo,
        $linguaProfilo,
        $etaProfilo,
        $idUtente,
        $idAvatar
    );

    if (!$stmt->execute()) {
        die("Errore inserimento profilo: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    // Redirect finale
    header("Location: /profili/profili.php");
    exit;
?>