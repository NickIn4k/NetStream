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

    // Eliminzaione profilo
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteProfile'])) {
        $idProfilo = (int) $_POST['idProfilo'];

        $stmt = $conn->prepare("
            DELETE FROM Profilo
            WHERE idProfilo = ? AND idUtente = ?
        ");
        $stmt->bind_param("ii", $idProfilo, $idUtente);
        $stmt->execute();
        $stmt->close();

        header("Location: profili.php");
        exit;
    }

    // Seleziona profilo
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
    while ($row = $result->fetch_assoc()) {
        $profili[] = $row;
    }
    $numProfili = count($profili);
    $stmt->close();

    // Limite
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
        <?php
            foreach ($profili as $profilo) {
                echo '
                <div class="profile-card selectable" data-id="' . $profilo['idProfilo'] . '">
                    <img src="' . $profilo['pathAvatar'] . '" alt="Avatar">
                    <span>' . htmlspecialchars($profilo['nomeProfilo']) . '</span>
                </div>';
            }
        ?>
    </div>

    <div class="profiles-actions" id="profilesActions">
        <?php
            if ($puoCreareProfilo) 
                echo '<a href="/profili/avatar.php" class="btn primary">Crea nuovo profilo</a>';
        ?>

        <button class="btn secondary" id="editProfileBtn" disabled>
            Modifica profilo
        </button>

        <form method="post" id="deleteForm" style="display:inline; z-index: 1;">
            <input type="hidden" name="idProfilo" id="deleteProfileId">
            <button type="submit" name="deleteProfile" class="btn secondary" id="deleteBtn" disabled>
                Elimina profilo
            </button>
        </form>
    </div>
</main>

<script>
    let selectedProfileId = null;

    const cards = document.querySelectorAll('.profile-card');
    const editBtn = document.getElementById('editProfileBtn');
    const deleteBtn = document.getElementById('deleteBtn'); 
    const deleteInput = document.getElementById('deleteProfileId');
    const actionsBar = document.getElementById('profilesActions');

    cards.forEach(card => {
        card.addEventListener('click', () => {
            cards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');

            selectedProfileId = card.dataset.id;
            deleteInput.value = selectedProfileId;

            deleteBtn.disabled = false;
            editBtn.disabled = false;

            actionsBar.classList.add('active');
        });

        card.addEventListener('dblclick', () => {
            const id = card.dataset.id;
            if (id) {
                window.location.href = "/catalogo/catalogo.php?idProfilo=" + encodeURIComponent(id);
            }
        });
    });

    editBtn.addEventListener('click', () => {
        if (!selectedProfileId) return;

        window.location.href = "/profili/avatar.php?idProfilo=" + encodeURIComponent(selectedProfileId);
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>