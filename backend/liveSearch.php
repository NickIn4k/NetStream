<?php
    header('Content-Type: text/html; charset=utf-8');

    $c = $_GET['c'] ?? '';
    $option = $_GET['option'] ?? 'titoloContenuto';

    if ($c == '') {
        exit;
    }

    $conn = new mysqli("localhost", "root", "", "db_NetStream");
    if ($conn->connect_error) {
        http_response_code(500);
        echo "Errore di connessione al database";
        exit;
    }

    if ($option !== 'titoloContenuto' && $option !== 'regista') {
        $option = 'titoloContenuto';
    }

    $sql = "
        SELECT titoloContenuto, pathCopertina
        FROM contenuto
        WHERE $option LIKE CONCAT(?, '%')
        LIMIT 10
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo "Errore nella query";
        exit;
    }

    $stmt->bind_param("s", $c);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<div class='no-result'>Nessun risultato</div>";
    } else {
        while ($row = $result->fetch_assoc()) {
            echo "
                <div class='result-item'>
                    <img src='{$row['pathCopertina']}' alt='' class='result-cover'>
                    <span class='result-title'>{$row['titoloContenuto']}</span>
                </div>
            ";
        }
    }

    $stmt->close();
    $conn->close();
?>