<?php
    $secret = "CHIAVE_SUPER_SEGRETA"; //Chiave condivisa con Bet271

    if ($_POST['secret'] !== $secret) {
        http_response_code(403);
        die("Accesso negato");
    }

    $email = $_POST['email'];
    $value = $_POST['value'];

    $conn = new mysqli("localhost", "root", "", "db_NetStream");

    //controlla se esiste l'utente con la stessa email
    $stmt = $conn->prepare("SELECT idUtente, bet271Points FROM utente WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0)
        die("Utente non presente su NetStream");

    $user = $result->fetch_assoc();

    //aggiorna la tabella
    $newBet271Points = $user['bet271Points'] + $value;
    $update = $conn->prepare("UPDATE utente SET bet271Points = ? WHERE idUtente = ?");
    $update->bind_param("si", $newBet271Points, $user['idUtente']);
    $update->execute();

    echo "OK: DB NetStream aggiornato";
?>