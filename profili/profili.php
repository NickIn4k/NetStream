<?php
    session_start();

    if (!isset($_SESSION['idUtente']) || !isset($_SESSION['nomeUtente'])) {
        header("Location: /../auth/login.php");
        exit;
    }

    $conn = new mysqli("localhost", "root", "", "db_NetStream");

    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    $idUtente = $_SESSION['idUtente'];

    // Selezione profili
    $stmt = $conn->prepare("
        SELECT p.idProfilo, p.nomeProfilo, a.pathAvatar
        FROM Profilo p
        LEFT JOIN Avatar a ON p.idAvatar = a.idAvatar
        WHERE p.idUtente = ?
    ");
    $stmt->bind_param("i", $idUtente);
    $stmt->execute();
    $result = $stmt->get_result();

    $profili = [];
    while ($row = $result->fetch_assoc()) 
        $profili[] = $row;

    $numProfili = count($profili);
    $stmt->close();

    // Controllo per aggiungere profili
    $stmt = $conn->prepare("
        SELECT ab.maxProfili
        FROM Sottoscrizione s
        JOIN Abbonamento ab ON s.idAbbonamento = ab.idAbbonamento
        WHERE s.idUtente = ?
        AND s.statoSottoscrizione = 'ATTIVA'
        LIMIT 1
    ");
    $stmt->bind_param("i", $idUtente);
    $stmt->execute();
    $result = $stmt->get_result();

    $maxProfili = 0;
    if ($row = $result->fetch_assoc()) {
        $maxProfili = $row['maxProfili'];
    }

    $puoCreareProfilo = ($numProfili < $maxProfili);
    $stmt->close();

    include __DIR__ . '/../includes/header.php';
?>

<main class="profiles-page">

    <h1>Chi sta guardando?</h1>

    <div class="profiles-grid">
        <?php foreach ($profili as $profilo): ?>
            <a href="/Progetti/NetStream/catalogo/catalogo.php?idProfilo=<?= $profilo['idProfilo'] ?>" class="profile-card">
                <img src="<?=$profilo['pathAvatar']?>" alt="Avatar">
                <span><?=$profilo['nomeProfilo'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="profiles-actions">
        <?php 
            if ($puoCreareProfilo) 
                echo "<a href=\"/profili/avatar.php\" class=\"btn primary\">Crea nuovo profilo</a>";
            else
                echo "<p class=\"limit-warning\">Hai raggiunto il numero massimo di profili per il tuo abbonamento</p>";
        ?>

        <button class="btn secondary" id="editProfileBtn" disabled>
            Modifica profilo
        </button>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>