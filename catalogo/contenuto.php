<?php 
    include __DIR__ . '/../includes/header.php';

    $conn = new mysqli("localhost", "root", "", "db_NetStream");

    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    $idContenuto = $_GET['idContenuto'] ?? null;
    if(!$idContenuto){
        header("Location: /catalogo/catalogo.php");
        exit;
    }

    // Per semplificare il redirect in altre pagine
    $_SESSION['idContenuto'] = $idContenuto;

    // Recupero contenuto
    $sql = "
        SELECT c.titoloContenuto, c.descrizioneContenuto, c.regista, 
               c.pathBanner, c.durataContenuto, c.dataUscita, 
               c.pathContenuto, c.tipoContenuto
        FROM contenuto c
        WHERE c.idContenuto = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idContenuto);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 0){
        header("Location: /catalogo/catalogo.php");
        exit;
    }

    $contenuto = $result->fetch_assoc();
    $stmt->close();

    // Recupero generi
    $sql = "
        SELECT g.nomeGenere
        FROM genere g
        JOIN contenutogenere cg ON g.idGenere = cg.idGenere
        WHERE cg.idContenuto = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idContenuto);
    $stmt->execute();
    $result = $stmt->get_result();

    $generi = [];
    while ($row = $result->fetch_assoc()) {
        $generi[] = $row['nomeGenere'];
    }
    $stmt->close();

    // Recupero stagioni
    $stagioni = [];
    if($contenuto['tipoContenuto'] === 'Serie'){
        $sql = "
            SELECT s.idStagione, s.numeroStagione
            FROM stagione s
            WHERE s.idSerie = ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idContenuto);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $stagioni[] = $row;
        }
        $stmt->close();
    }

    // Recupero preferiti
    $isPreferito = false;
    if (isset($_SESSION['idProfilo'])) {
        $sql = "
            SELECT 1
            FROM listapreferiti
            WHERE idProfilo = ? AND idContenuto = ?
            LIMIT 1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['idProfilo'], $idContenuto);
        $stmt->execute();
        $stmt->store_result();

        $isPreferito = $stmt->num_rows > 0;
        $stmt->close();
    }
?>

<main class="contenuto-page">

    <!-- BANNER -->
    <section class="contenuto-banner">
        <div class="banner-overlay"></div>
        <img src="<?= htmlspecialchars($contenuto['pathBanner']) ?>" alt="Banner di <?= htmlspecialchars($contenuto['titoloContenuto']) ?>">
    </section>

    <!-- INFO CONTENUTO -->
    <section class="contenuto-info">
        <h1 class="contenuto-title-wrapper">
            <span class="contenuto-title">
                <?= htmlspecialchars($contenuto['titoloContenuto']) ?>
            </span>

            <a href="/backend/listaPreferiti.php?idContenuto=<?= $idContenuto ?>" class="fav-btn" title="<?= $isPreferito ? 'Rimuovi dai preferiti' : 'Aggiungi ai preferiti' ?>">
                <?= $isPreferito ? '★' : '☆' ?>
            </a>
        </h1>


        <p class="contenuto-description">
            <?= htmlspecialchars($contenuto['descrizioneContenuto']) ?>
        </p>

        <div class="contenuto-meta">
            <span><strong>Regia:</strong> <?= htmlspecialchars($contenuto['regista']) ?></span>
            <span class="dot">•</span>
            <span><strong>Generi:</strong> <?= htmlspecialchars(implode(', ', $generi)) ?></span>
        </div>
    </section>

    <!-- NAV STAGIONI -->
    <section class='contenuto-nav'>
        <?php 
            if ($contenuto['tipoContenuto'] === 'Serie'){
                //Attributo data-stagione ad ogni button per id stagione
                foreach ($stagioni as $stagione){
                    echo"
                        <button class='nav-btn' data-stagione='{$stagione['idStagione']}'>
                            Stagione {$stagione['numeroStagione']}
                        </button>
                    ";
                }
                echo "
                    <button class='nav-btn' data-stagione='-1'>
                        Contenuti extra
                    </button>
                ";
            }
        ?>
    </section>

    <hr class="contenuto-divider">

    <!-- CONTENUTO DINAMICO -->
    <section class="contenuto-dynamic">
        <!-- Riempito via AJAX -->
    </section>

    <!-- RECENSIONI -->
    <section class="contenuto-review">
        <h3>Scrivi una recensione</h3>

        <form id="reviewForm">
            <!-- Campo nascosto idContenuto -->
            <input type="hidden" name="idContenuto" value="<?= htmlspecialchars($_GET['idContenuto']) ?>">

            <!-- Voto -->
            <select name="voto" id="voto" required>
                <option value="">Stelle</option>
                <?php for($i=1; $i<=5; $i++)
                    echo "<option value='$i'>$i</option>";
                ?>
            </select>

            <!-- Commento -->
            <textarea name="commento" class="review-textarea" placeholder="Scrivi cosa ne pensi..." required></textarea>

            <button type="submit" class="cta">Invia recensione</button>
        </form>

        <div id="reviewMessage" style="margin-top:12px;"></div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="/assets/js/recensione.js"></script>
<script src="/assets/js/stagioni.js"></script>