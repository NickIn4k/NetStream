<?php
    session_start();

    if (isset($_GET['idProfilo']))
        $_SESSION['idProfilo'] = $_GET['idProfilo'];

    if (!isset($_SESSION['idProfilo'])) {
        header("Location: /profili/profili.php");
        exit;
    }

    $idProfilo = $_SESSION['idProfilo'];

    $conn = new mysqli("localhost", "root", "", "db_NetStream");
    if ($conn->connect_error) 
        die('Connessione fallita: ' . $conn->connect_error);

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

    $stmt = $conn->prepare("
        SELECT DISTINCT g.nomeGenere, c.idContenuto, c.titoloContenuto, c.pathCopertina, c.ratingEta
        FROM Genere g
        JOIN ContenutoGenere cg ON g.idGenere = cg.idGenere
        JOIN Contenuto c ON cg.idContenuto = c.idContenuto
        WHERE c.ratingEta <= ?
        ORDER BY g.nomeGenere, c.dataUscita DESC
    ");

    $stmt->bind_param("i", $etaProfilo);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc())
        $contenutiPerGenere[$row['nomeGenere']][] = $row;

    $stmt->close();

    // Recupera lista preferiti del profilo
    $preferiti = [];
    $stmt = $conn->prepare("
        SELECT c.idContenuto, c.titoloContenuto, c.pathCopertina, c.ratingEta
        FROM ListaPreferiti lp
        JOIN Contenuto c ON lp.idContenuto = c.idContenuto
        WHERE lp.idProfilo = ? AND c.ratingEta <= ?
        ORDER BY c.dataUscita DESC
    ");

    $stmt->bind_param("ii", $idProfilo, $etaProfilo);
    $stmt->execute();
    $result = $stmt->get_result();

    $contenutiPreferiti = [];
    while ($row = $result->fetch_assoc()) 
        $contenutiPreferiti[] = $row;

    $stmt->close();
    
    include __DIR__ . '/../includes/checkSubscription.php';
    include __DIR__ . '/../includes/header.php';
?>

<section class="catalogue-search">
    <br><br>
    <?php 
        $searchFlag = "catalogo";
        include __DIR__ . '/../includes/cercaCatalogo.php'; 
    ?>
</section>

<br>

<main class="catalogue-page">
    <?php
        // "Stampa" i preferiti in cima
        if (!empty($contenutiPreferiti)){
            echo '<section class="catalogue-row">';
            echo '<h2>I tuoi preferiti</h2>';
            echo '<div class="catalogue-slider">';
                foreach ($contenutiPreferiti as $contenuto){
                    echo '
                        <a href="/catalogo/contenuto.php?idContenuto=' . $contenuto['idContenuto'] . '" class="catalogue-card">
                            <img src="' . htmlspecialchars($contenuto['pathCopertina']) . '" alt="' . htmlspecialchars($contenuto['titoloContenuto']) . '">
                        </a>
                    ';
                }
            echo '</div>';
            echo '</section>';
        }

        // Leggi cookie "continua a guardare" 
        $continuaCookie = $_COOKIE['continuaAGuardare'] ?? '[]';
        $continuaAll = json_decode($continuaCookie, true) ?: [];

        // Filtra per il profilo corrente -> Più profili per lo stesso utente!
        $continuaProfilo = [];
        foreach ($continuaAll as $c) {
            if (isset($c['idProfilo']) && $c['idProfilo'] == $idProfilo)
                $continuaProfilo[] = $c;
        }

        // "Stampa" i contenuti da continuare a guardare
        if ($continuaProfilo){
            echo '<section class="catalogue-row">';
            echo '<h2>Continua a guardare</h2>';
            echo '<div class="catalogue-slider">';
            
            // Per ogni contenuto nella lista da continuare, "stampa" la card
            foreach ($continuaProfilo as $c){
                $idContenuto = $c['idContenuto'];

                $stmt = $conn->prepare("
                    SELECT titoloContenuto, pathCopertina 
                    FROM Contenuto 
                    WHERE idContenuto = ?
                ");
                
                $stmt->bind_param("i", $idContenuto);
                $stmt->execute();
                $result = $stmt->get_result();
                $contenuto = $result->fetch_assoc();
                $stmt->close();

                // ogni card (copertina è un link alla pagina contenuto.php?idContenuto=x)
                if ($contenuto) {
                    echo '
                        <a href="/catalogo/contenuto.php?idContenuto=' . $idContenuto . '" class="catalogue-card">
                            <img src="' . htmlspecialchars($contenuto['pathCopertina']) . '" alt="' . htmlspecialchars($contenuto['titoloContenuto']) . '">
                        </a>
                    ';
                }
            }
            echo '</div>';
            echo '</section>';
        }

        // "Stampa" i contenuti per ogni genere
        foreach ($contenutiPerGenere as $genere => $contenuti) {
            // Nome contenuto
            echo '<section class="catalogue-row">';
            echo '<h2>' . htmlspecialchars($genere) . '</h2>';
            echo '<div class="catalogue-slider">';

            // Ogni card (link) al contenuto di quel genere (1:N)
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
