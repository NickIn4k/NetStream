<?php
    session_start();

    if (!isset($_SESSION['idUtente'])) {
        header("Location: /Progetti/NetStream/auth/login.php");
        exit;
    }

    $conn = new mysqli("localhost", "root", "", "db_NetStream");
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    $idUtente = $_SESSION['idUtente'];

    // Punti 
    $stmt = $conn->prepare("SELECT bet271Points FROM Utente WHERE idUtente = ?");
    $stmt->bind_param("i", $idUtente);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $puntiUtente = $user['bet271Points'] ?? 0;
    $stmt->close();

    // Avatar
    $stmt = $conn->prepare("SELECT * FROM Avatar ORDER BY puntiSblocco ASC");
    $stmt->execute();
    $result = $stmt->get_result();

    $avatarStandard = [];
    $avatarSpeciali = [];

    while ($row = $result->fetch_assoc()) {
        if ($row['puntiSblocco'] == 0) {
            $avatarStandard[] = $row;
        } else {
            $avatarSpeciali[] = $row;
        }
    }
    $stmt->close();

    include __DIR__ . '/../includes/header.php';
?>

<main class="avatar-page">

    <h1>Crea il tuo profilo</h1>
    <p class="points-info">
        Punti utente: <strong><?= $puntiUtente ?></strong>
    </p>

    <form method="post" action="salvaAvatar.php" class="create-profile-form">
        <!-- AVATAR STANDARD -->
        <section class="avatar-section">
            <h2>Avatar standard</h2>

            <div class="avatar-grid">
                <?php
                    foreach ($avatarStandard as $avatar) {
                        echo '
                        <label class="avatar-card selectable">
                            <input type="radio" name="idAvatar" value="' . $avatar['idAvatar'] . '" required>
                            <img src="' . $avatar['pathAvatar'] . '" alt="' . $avatar['descrittoreAvatar'] . '">
                        </label>';
                    }
                ?>
            </div>
        </section>

        <!-- AVATAR SPECIALI -->
        <section class="avatar-section">
            <h2>Icone speciali</h2>

            <div class="avatar-grid">
                <?php
                    foreach ($avatarSpeciali as $avatar) {

                        $bloccato = $puntiUtente < $avatar['puntiSblocco'];

                        if ($bloccato) {
                            echo '
                            <div class="avatar-card locked">
                                <img src="' . $avatar['pathAvatar'] . '">
                                <span class="lock-label">' . $avatar['puntiSblocco'] . ' punti </span>
                            </div>';
                        } else {
                            echo '
                            <label class="avatar-card selectable">
                                <input type="radio" name="idAvatar" value="' . $avatar['idAvatar'] . '">
                                <img src="' . $avatar['pathAvatar'] . '" alt="' . $avatar['descrittoreAvatar'] . '">
                            </label>';
                        }
                    }
                ?>
            </div>
        </section>

        <div class="profile-actions-bar">
            <div class="profile-name-wrapper">
                <input type="text" id="nomeProfilo" name="nomeProfilo" maxlength="50" required placeholder="Nome profilo">
            </div>

            <div class="profile-name-wrapper">
                <input type="number" id="eta" name="eta" min="8" max="120" required>
            </div>

            <select name="lingua" id="lingua" required>
                <option value="">Lingua</option>
                <option value="it">Italiano</option>
                <option value="en">Inglese</option>
                <option value="es">Spagnolo</option>
                <option value="fr">Francese</option>
            </select>

            <?=isset($_GET['idProfilo']) ? '<input type="hidden" name="idProfilo" value="' . htmlspecialchars($_GET['idProfilo']) . '">' : '';?>
            
            <button type="submit" class="btnAvatar">
                Crea profilo
            </button>
        </div>

    </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>