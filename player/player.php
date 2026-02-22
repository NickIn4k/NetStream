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
        $stmt = $conn->prepare("
            SELECT titoloContenuto, trailerPath AS pathEpisodio, tipoContenuto
            FROM Contenuto
            WHERE idContenuto = ?
        ");
        $stmt->bind_param("i", $_SESSION['idContenuto']);
    } else if(isset($_GET['film']) && $_GET['film'] == 1) {
        $stmt = $conn->prepare("
            SELECT titoloContenuto, pathContenuto AS pathEpisodio, tipoContenuto, durataContenuto
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

    $time = null;
    $time = null;
    if (isset($_COOKIE['continuaAGuardare'])) {
        $idEpisodio = $_GET['id'] ?? null;
        $data = json_decode($_COOKIE['continuaAGuardare'], true);

        if (is_array($data)) {
            foreach ($data as $c) {
                if (isset($c['idProfilo'], $c['idContenuto'], $c['tempo']) 
                    && $c['idProfilo'] == $_SESSION['idProfilo'] && $c['idContenuto'] == $_SESSION['idContenuto']
                    && ((!isset($idEpisodio) && $c['idEpisodio'] == null) || (isset($idEpisodio) && $c['idEpisodio'] == $idEpisodio))
                ){
                    $time = $c['tempo'];
                    break;
                }    
            }
        }
    } 
    
    include __DIR__ . '/../includes/header.php';
?>

<main class="player-page" id="playerPage">

    <div class="player-header">
        <h1><?= htmlspecialchars($episodio['titoloContenuto']) ?></h1>
        <br>
        <h3>
            <?= isset($episodio['numeroStagione']) ? "Stagione: " . $episodio['numeroStagione'] : "Contenuto completo" ?>
            <?= isset($episodio['numeroEpisodio']) ? " Episodio: " . $episodio['numeroEpisodio'] . " - " : "" ?>
            <?= isset($episodio['titoloEpisodio']) ? htmlspecialchars($episodio['titoloEpisodio']) : "" ?>
        </h3>
    </div>

    <div class="video-wrapper">
        <video 
          controls autoplay class="video-player" id="videoPlayer" controlsList="nodownload"
          data-id-contenuto="<?= $_SESSION['idContenuto'] ?>"
          data-id-profilo="<?= $_SESSION['idProfilo'] ?>"
          data-tipo-contenuto="<?= $episodio['tipoContenuto'] ?>" 
          data-stagione="<?= isset($episodio['numeroStagione']) ? $episodio['numeroStagione'] : null ?>" 
          data-episodio="<?= isset($episodio['numeroEpisodio']) ? $episodio['numeroEpisodio'] : null ?>"
          data-id-episodio="<?= $idEpisodio ?? '' ?>"
          data-start-time="<?= $time !== null ? $time : 0 ?>"
        >
            <source src="<?= htmlspecialchars($episodio['pathEpisodio'])?>" type="video/mp4">
            Il tuo browser non supporta il video.
        </video>

        <div id="videoOverlay" class="video-overlay hidden">
            <button class="overlay-btn" onclick="replay()">⟲</button>
            <?php 
            if (!isset($_GET['trailer']))
                echo "<button class='overlay-btn primary' onclick='nextEpisode(" . (isset($idEpisodio) ? ($idEpisodio + 1) : 'null') . ")'>⏭</button>";
            ?>
        </div>
    </div>

    <div class="player-info">
        <?php 
            if (isset($episodio['durataEpisodio']))
                echo "<p>Durata: " . (int)$episodio['durataEpisodio'] . " min</p>";
            else if (isset($_GET['film']) && isset($episodio['durataContenuto']))
                echo "<p>Durata: " . (int)$episodio['durataContenuto'] . " min</p>";
        ?>
    </div>
</main>

<script src="/assets/js/player.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>