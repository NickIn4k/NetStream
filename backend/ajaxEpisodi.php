<?php
    $config = require __DIR__ . '/../assets/configurations/configDB.php';
    $conn = new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);

    if ($conn->connect_error)
        die("Errore DB");

    $idStagione = $_GET['idStagione'] ?? null;
    if (!$idStagione) 
        exit;

    $stmt = $conn->prepare("
        SELECT idEpisodio, titoloEpisodio, numeroEpisodio, durataEpisodio, pathEpisodio
        FROM episodio
        WHERE idStagione = ?
        ORDER BY numeroEpisodio
    ");

    $stmt->bind_param("i", $idStagione);
    $stmt->execute();
    $result = $stmt->get_result();

    // Ogni episodio => Card con titolo, durata e btn "Guarda" (CSS comune con episodio)
    while ($row = $result->fetch_assoc()) {
        echo "
            <div class='episode-card'>
                <div class='episode-left'>
                    <div class='episode-info'>
                        <h4>Episodio {$row['numeroEpisodio']} - {$row['titoloEpisodio']}</h4>
                        <p>{$row['durataEpisodio']} min</p>
                    </div>
                </div>
                <a href='/player/player.php?id={$row['idEpisodio']}' class='watch-btn'>Guarda</a>
            </div>
        ";
    }

    $stmt->close();
    $conn->close();

?>