<?php
    session_start();

    if (isset($_GET['idProfilo'])) {
        $_SESSION['idProfilo'] = $_GET['idProfilo'];
    }

    if (!isset($_SESSION['idProfilo'])) {
        header("Location: /profili/profili.php");
        exit;
    }

    $idProfilo = $_SESSION['idProfilo'];

    $conn = new mysqli("localhost", "root", "", "db_NetStream");
    if ($conn->connect_error) {
        die('Connessione fallita: ' . $conn->connect_error);
    }

    // Prendi età profilo
    $stmt = $conn->prepare("
        SELECT etaProfilo
        FROM Profilo
        WHERE idProfilo = ?
    ");
    $stmt->bind_param("i", $idProfilo);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$row = $result->fetch_assoc()) {
        header("Location: /profili/profili.php");
        exit;
    }

    $etaProfilo = $row['etaProfilo'];
    $stmt->close();

    // Contenuti in base al genere
    $contenutiPerGenere = [];

    // Funzione CAST() mysql per rimuovere subito il simbolo + e converire in numero intero
    $stmt = $conn->prepare("
        SELECT DISTINCT g.nomeGenere, c.idContenuto, c.titoloContenuto, c.pathCopertina, CAST(REPLACE(c.ratingEta, '+', '') AS UNSIGNED) AS ratingMinimo
        FROM Genere g
        JOIN ContenutoGenere cg ON g.idGenere = cg.idGenere
        JOIN Contenuto c ON cg.idContenuto = c.idContenuto
        WHERE CAST(REPLACE(c.ratingEta, '+', '') AS UNSIGNED) <= ?
        ORDER BY g.nomeGenere, c.dataUscita DESC
    ");

    $stmt->bind_param("i", $etaProfilo);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $contenutiPerGenere[$row['nomeGenere']][] = $row;
    }

    $stmt->close();

    include __DIR__ . '/../includes/header.php';
?>

<section class="catalogue-search">
    <br><br>
    <?php include __DIR__ . '/../includes/cercaCatalogo.php'; ?>
</section>
<br>
<main class="catalogue-page">
    <?php
        foreach ($contenutiPerGenere as $genere => $contenuti) {
            echo '<section class="catalogue-row">';
            echo '<h2>' . htmlspecialchars($genere) . '</h2>';
            echo '<div class="catalogue-slider">';

            foreach ($contenuti as $contenuto) {
                echo '
                    <a href="/catalogo/contenuto.php?idContenuto=' . $contenuto['idContenuto'] . '" class="catalogue-card">
                        <img src="' . htmlspecialchars($contenuto['pathCopertina']) . '" alt="' . htmlspecialchars($contenuto['titoloContenuto']) . '">
                    </a>
                ';
            }

            echo '</div>';
            echo '</section>';
        }
    ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
