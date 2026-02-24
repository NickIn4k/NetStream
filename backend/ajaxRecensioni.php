<?php
    // Connessione DB
    $conn = new mysqli("localhost", "root", "", "db_NetStream");

    if ($conn->connect_error) {
        http_response_code(500);
        exit("Errore di connessione al database");
    }

    // Recupero idContenuto
    $idContenuto = $_GET['idContenuto'] ?? null;

    if (!$idContenuto || !is_numeric($idContenuto)) {
        http_response_code(400);
        exit("Parametro non valido");
    }

    // Query recensioni + nome profilo
    $sql = "
        SELECT r.voto, r.commento, r.dataRecensione, p.nomeProfilo
        FROM recensione r
        JOIN profilo p ON r.idProfilo = p.idProfilo
        WHERE r.idContenuto = ?
        ORDER BY r.dataRecensione DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idContenuto);
    $stmt->execute();
    $result = $stmt->get_result();

    // Nessuna recensione
    if ($result->num_rows === 0) {
        echo "<p class='no-reviews'>Nessuna recensione disponibile.</p>";
        exit;
    }

    // Output HTML => Lista di card con Nome, commento, data e stelle
    while ($row = $result->fetch_assoc()) {
        $nomeProfilo = htmlspecialchars($row['nomeProfilo']);
        $commento    = htmlspecialchars($row['commento']);
        $voto        = $row['voto'];
        $data        = htmlspecialchars($row['dataRecensione']);

        echo "
            <div class='review-item'>
                <div class='review-header'>
                    <strong class='review-user'>{$nomeProfilo}</strong>
                    <span class='review-stars'>{$voto} ★</span>
                </div>
                <p class='review-comment'>{$commento}</p>
                <small class='review-date'>{$data}</small>
            </div>
        ";
    }

    $stmt->close();
    $conn->close();
?>