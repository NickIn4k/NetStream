<?php
    $config = require __DIR__ . '/../assets/configurations/configDB.php';
    $conn = new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);

    $idContenuto = $_GET['idContenuto'] ?? null;
    if (!$idContenuto) 
        exit;

    $stmt = $conn->prepare("
        SELECT titoloContenuto, trailerPath
        FROM contenuto
        WHERE idContenuto = ?
    ");

    $stmt->bind_param("i", $idContenuto);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    // Card per il trailer (CSS comune con episodio)
    echo "
        <div class='episode-card'>
            <div class='episode-left'>
                <div class='episode-info'>
                    <h4>Trailer ufficiale</h4>
                </div>
            </div>
            <a href='/player/player.php?id={$_GET['idContenuto']}&trailer=1' class='watch-btn'>Guarda</a>
        </div>
    ";

    $stmt->close();
    $conn->close();
?>