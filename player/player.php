<?php
    session_start();

    // Controllo accesso
    if (!isset($_SESSION['idProfilo'])) {
        header("Location: /profili/profili.php");
        exit;
    }

    // Connessione DB
    $conn = new mysqli("localhost", "root", "", "db_NetStream");
    if ($conn->connect_error) {
        die("Errore DB: " . $conn->connect_error);
    }

    // Variabile episodio
    $episodio = null;

    // Trailer o episodio
    if (isset($_GET['trailer']) && $_GET['trailer'] == 1) {
        if (!isset($_SESSION['idContenuto'])) {
            header("Location: /catalogo/catalogo.php");
            exit;
        }

        $stmt = $conn->prepare("
            SELECT titoloContenuto, pathContenuto AS pathEpisodio, tipoContenuto
            FROM Contenuto
            WHERE idContenuto = ?
        ");

        $stmt->bind_param("i", $_SESSION['idContenuto']);
    } else {
        if (!isset($_GET['id'])) {
            header("Location: /catalogo/catalogo.php");
            exit;
        }

        $idEpisodio = $_GET['id'];

        $stmt = $conn->prepare("
            SELECT e.titoloEpisodio, e.numeroEpisodio, e.durataEpisodio, e.pathEpisodio, 
                s.numeroStagione, c.titoloContenuto, c.tipoContenuto
            FROM Episodio e
            INNER JOIN Stagione s ON e.idStagione = s.idStagione
            INNER JOIN Contenuto c ON s.idSerie = c.idContenuto
            WHERE e.idEpisodio = ?
        ");

        $stmt->bind_param("i", $idEpisodio);
    }

    // Esecuzione
    $stmt->execute();
    $result = $stmt->get_result();
    $episodio = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if (!$episodio) {
        header("Location: /catalogo/contenuto.php?idContenuto=".$_SESSION['idContenuto']);
        exit;
    }

    include __DIR__ . '/../includes/header.php';
?>

<main class="player-page" id="playerPage">

    <div class="player-header">
        <h1><?= htmlspecialchars($episodio['titoloContenuto']) ?></h1>
        <br>
        <h3>
            <?= isset($episodio['numeroStagione']) ? "Stagione: " . $episodio['numeroStagione'] : "Trailer" ?>
            <?= isset($episodio['numeroEpisodio']) ? " Episodio: " . $episodio['numeroEpisodio'] . " - " : "" ?>
            <?= isset($episodio['titoloEpisodio']) ? htmlspecialchars($episodio['titoloEpisodio']) : "" ?>
        </h3>
    </div>

    <div class="video-wrapper">
        <video 
          controls autoplay 
          class="video-player" 
          id="videoPlayer" 
          controlsList="nodownload"
          data-id-contenuto="<?= $_SESSION['idContenuto'] ?>"
          data-id-profilo="<?= $_SESSION['idProfilo'] ?>"
          data-tipo-contenuto="<?= $episodio['tipoContenuto'] ?>" 
          data-stagione="<?= isset($episodio['numeroStagione']) ? $episodio['numeroStagione'] : null ?>" 
          data-episodio="<?= isset($episodio['numeroEpisodio']) ? $episodio['numeroEpisodio'] : null ?>"
        >
            <source src="<?= htmlspecialchars($episodio['pathEpisodio']) ?>" type="video/mp4">
            Il tuo browser non supporta il video.
        </video>

        <div id="videoOverlay" class="video-overlay hidden">
            <button class="overlay-btn" onclick="replay()">⟲</button>
            <?php 
            if (!isset($_GET['trailer']))
                echo "<button class='overlay-btn primary' onclick='nextEpisode(" . ($idEpisodio + 1) . ")'>⏭</button>";
            ?>
        </div>
    </div>

    <div class="player-info">
        <p>Durata: <?= isset($episodio['durataEpisodio']) ? $episodio['durataEpisodio'] : "N/A" ?> min</p>
    </div>
</main>

<script src="/assets/js/player.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>