<?php
    session_start();
    $idProfilo = $_SESSION['idProfilo'] ?? null;

    $c = $_GET['c'] ?? '';
    $searchFlag = $_GET['searchFlag'] ?? '';
    $option = $_GET['option'] ?? 'titoloContenuto';

    if ($c == '') {
        exit;
    }

    $config = require __DIR__ . '/../assets/configurations/configDB.php';
    $conn = new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);

    if ($conn->connect_error) {
        http_response_code(500);
        echo "Errore di connessione al database";
        exit;
    }

    if ($option !== 'titoloContenuto' && $option !== 'regista') {
        $option = 'titoloContenuto';
    }

    /* Casi query:
        - utente loggato -> si filtra in base all'eta profilo e al rating del contenuto
        - utente non loggato -> nessun filtro, deve essere solo nella pagina di home
        
        In ogni caso, la ricerca è basata su 'option' che viene ricavata dall'elemento <select> di html.
        Il Like serve per dare tutte le opzioni che iniziano con la serie di caratteri nella ricerca.
    */
    $sql = "
    SELECT c.idContenuto, c.titoloContenuto, c.pathCopertina
    FROM contenuto c
    WHERE $option LIKE CONCAT(?, '%') 
        AND (c.ratingEta <= (
            SELECT p.etaProfilo
            FROM profilo p
            WHERE p.idProfilo = ?
        ) OR (
            SELECT p.etaProfilo
            FROM profilo p
            WHERE p.idProfilo = ?
        ) IS NULL)
    LIMIT 10;
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo "Errore nella query";
        exit;
    }

    $stmt->bind_param("sii", $c, $idProfilo, $idProfilo);
    $stmt->execute();
    $result = $stmt->get_result();

    // Creazione elementi presenti nella lista contenuti
    if ($result->num_rows == 0) {
        echo "<div class='no-result'>Nessun risultato</div>";
    } else {
        while ($row = $result->fetch_assoc()) {
            if (isset($searchFlag) && $searchFlag == 'catalogo') {
                echo "
                    <a href='/catalogo/contenuto.php?idContenuto={$row['idContenuto']}' class='result-item'>
                        <img src='{$row['pathCopertina']}' class='result-cover'>
                        <span class='result-title'>{$row['titoloContenuto']}</span>
                    </a>
                ";
            } else {
                echo "
                    <a href='/auth/signin.php' class='result-item'>
                        <img src='{$row['pathCopertina']}' alt='' class='result-cover'>
                        <span class='result-title'>{$row['titoloContenuto']}</span>
                    </a>
                ";
            }
        }
    }

    $stmt->close();
    $conn->close();
?>