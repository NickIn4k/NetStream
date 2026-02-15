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
?>

<main class="contenuto-page">

    <!-- BANNER -->
    <section class="contenuto-banner">
        <div class="banner-overlay"></div>
        <img src="<?= htmlspecialchars($contenuto['pathBanner']) ?>" 
             alt="Banner di <?= htmlspecialchars($contenuto['titoloContenuto']) ?>">
    </section>

    <!-- INFO CONTENUTO -->
    <section class="contenuto-info">
        <h1 class="contenuto-title">
            <?= htmlspecialchars($contenuto['titoloContenuto']) ?>
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
    <?php if ($contenuto['tipoContenuto'] === 'Serie'): ?>
        <section class="contenuto-nav">
            <?php foreach ($stagioni as $stagione): ?>
                <button class="nav-btn" data-stagione="<?= $stagione['idStagione'] ?>">
                    Stagione <?= $stagione['numeroStagione'] ?>
                </button>
            <?php endforeach; ?>

            <button class="nav-btn" data-stagione="0">
                Tutte le stagioni
            </button>
        </section>
    <?php endif; ?>

    <hr class="contenuto-divider">

    <!-- CONTENUTO DINAMICO -->
    <section class="contenuto-dynamic">
        <!-- Riempito via AJAX -->
    </section>

    <!-- RECENSIONI -->
    <section class="contenuto-review">
        <h3>Scrivi una recensione</h3>

        <textarea class="review-textarea"
                  placeholder="Scrivi cosa ne pensi..."></textarea>

        <button class="cta">Invia recensione</button>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>