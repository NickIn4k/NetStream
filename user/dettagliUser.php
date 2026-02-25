<?php
    session_start();
    include __DIR__ . '/../includes/header.php';

    if (!isset($_SESSION['idUtente'])) {
        header("Location: /auth/login.php");
        exit;
    }

    $config = require __DIR__ . '/../assets/configurations/configDB.php';
    $conn = new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME']);

    if ($conn->connect_error)
        die("Connessione fallita: " . $conn->connect_error);

    $idUtente = $_SESSION['idUtente'];
    $msg = "";

    //Recupero dati utente e ultima sottoscrizione 
    $stmt = $conn->prepare("
        SELECT u.nomeUtente, u.email, 
            a.tipoAbbonamento, a.prezzo, a.qualitaMassima, a.maxProfili,
            s.dataInizio, s.dataFine, s.statoSottoscrizione
        FROM Utente u
        INNER JOIN Sottoscrizione s ON u.idUtente = s.idUtente
        INNER JOIN Abbonamento a ON s.idAbbonamento = a.idAbbonamento
        WHERE u.idUtente = ?
        ORDER BY s.dataFine DESC
        LIMIT 1
    ");

    $stmt->bind_param("i", $idUtente);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1)
        die("Errore nel recupero dati utente.");

    $dati = $result->fetch_assoc();
    $stmt->close();

    //Boolean sottoscrizione attiva
    $sottoscrizioneAttiva = ($dati['statoSottoscrizione'] === 'ATTIVA');

    //RINNOVO ABBONAMENTO (SOLO SE SCADUTO)
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['rinnovaAbbonamento'])) {

        $stmt_rinnovo = $conn->prepare("
            UPDATE Sottoscrizione
            SET 
                statoSottoscrizione = 'ATTIVA',
                dataInizio = CURDATE(),
                dataFine = DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
            WHERE idUtente = ?
            ORDER BY dataFine DESC
            LIMIT 1
        ");

        $stmt_rinnovo->bind_param("i", $idUtente);

        if ($stmt_rinnovo->execute()) {
            //Aggiorno anche la sessione
            $_SESSION['statoSottoscrizione'] = 'ATTIVA';

            $msg = "<div class='msg success'>Abbonamento rinnovato con successo.</div>";
            header("Refresh:1");
        } else {
            $msg = "<div class='msg error'>Errore nel rinnovo dell'abbonamento.</div>";
        }

        $stmt_rinnovo->close();
    }

    //CAMBIO PIANO (SOLO SE ATTIVO)
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nuovoAbbonamento'])) {

        $nuovoAbbonamento = $_POST['nuovoAbbonamento'];

        //Recupero idAbbonamento e maxProfili del nuovo piano
        $stmt_abbon = $conn->prepare("
            SELECT idAbbonamento, maxProfili 
            FROM Abbonamento 
            WHERE tipoAbbonamento = ?
        ");
        $stmt_abbon->bind_param("s", $nuovoAbbonamento);
        $stmt_abbon->execute();
        $stmt_abbon->bind_result($idAbbonamentoNuovo, $maxProfiliNuovo);
        $stmt_abbon->fetch();
        $stmt_abbon->close();

        //Conteggio profili attuali
        $stmt_prof = $conn->prepare("
            SELECT COUNT(*) 
            FROM Profilo 
            WHERE idUtente = ?
        ");
        $stmt_prof->bind_param("i", $idUtente);
        $stmt_prof->execute();
        $stmt_prof->bind_result($numeroProfili);
        $stmt_prof->fetch();
        $stmt_prof->close();

        //Controllo limite profili
        if ($numeroProfili > $maxProfiliNuovo) {
            $msg = "<div class='msg error'>
                Impossibile cambiare piano: hai $numeroProfili profili, 
                ma il nuovo abbonamento ne consente massimo $maxProfiliNuovo.
            </div>";
        } else {
            //Aggiorno sottoscrizione attiva
            $stmt_upd = $conn->prepare("
                UPDATE Sottoscrizione
                SET idAbbonamento = ?
                WHERE idUtente = ? AND statoSottoscrizione = 'ATTIVA'
            ");
            $stmt_upd->bind_param("ii", $idAbbonamentoNuovo, $idUtente);

            if ($stmt_upd->execute()) {
                $msg = "<div class='msg success'>Abbonamento aggiornato con successo.</div>";
                header("Refresh:1");
            } else {
                $msg = "<div class='msg error'>Errore nel cambio abbonamento.</div>";
            }

            $stmt_upd->close();
        }
    }

    $conn->close();
?>

<?php 
    if (!empty($msg)) echo $msg; 
?>

<section class="signin-main">
    <div class="signin-box">

        <h1>Il tuo account</h1>
        <p class="signin-subtitle">Dettagli profilo e abbonamento</p>

        <!-- AVVISO ABBONAMENTO SCADUTO -->
        <?php 
            if (!$sottoscrizioneAttiva){
                echo "
                    <div class='msg error' style='margin-bottom:16px;'>
                        Il tuo abbonamento è <strong>SCADUTO</strong>.  
                        Rinnovalo per continuare a usare NetStream.
                    </div>
                ";
            }    
        ?>

        <!-- DATI ACCOUNT -->
        <div class="card-box">
            <p class="card-title">Dati account</p>

            <p><strong>Username:</strong> <?= htmlspecialchars($dati['nomeUtente']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($dati['email']) ?></p>
            <p><strong>Stato:</strong> <?= $dati['statoSottoscrizione'] ?></p>
            <p><strong>Attivo dal:</strong> <?= $dati['dataInizio'] ?></p>
            <p><strong>Scadenza:</strong> <?= $dati['dataFine'] ?></p>
        </div>

        <!-- ABBONAMENTO ATTUALE -->
        <div class="card-box">
            <p class="card-title">Abbonamento attuale</p>

            <p><strong>Piano:</strong> <?= $dati['tipoAbbonamento'] ?></p>
            <p><strong>Prezzo:</strong> €<?= $dati['prezzo'] ?></p>
            <p><strong>Qualità:</strong> <?= $dati['qualitaMassima'] ?></p>
            <p><strong>Profili max:</strong> <?= $dati['maxProfili'] ?></p>
        </div>

        <!-- CAMBIO PIANO (SOLO SE ATTIVO) -->
        <?php if ($sottoscrizioneAttiva): ?>
        <form method="post" class="card-box">
            <p class="card-title">Cambia abbonamento</p>

            <div class="form-group">
                <label for="nuovoAbbonamento">Nuovo piano</label>
                <select name="nuovoAbbonamento" id="nuovoAbbonamento" required>
                    <?= $dati['tipoAbbonamento'] !== 'Base' ? "<option value='Base'>Base - 6.99€</option>" : "" ?>
                    <?= $dati['tipoAbbonamento'] !== 'Medium' ? "<option value='Medium'>Medium - 9.99€</option>" : "" ?>
                    <?= $dati['tipoAbbonamento'] !== 'Pro' ? "<option value='Pro'>Pro - 12.99€</option>" : "" ?>
                </select>
            </div>

            <div class="form-buttons">
                <input type="submit" value="Aggiorna piano" class="cta">
            </div>
        </form>
        <?php endif; ?>

        <!-- RINNOVO ABBONAMENTO (SOLO SE SCADUTO) -->
        <?php if (!$sottoscrizioneAttiva): ?>
        <form method="post" class="card-box">
            <p class="card-title">Rinnova abbonamento</p>

            <p>
                Piano precedente: <strong><?= $dati['tipoAbbonamento'] ?></strong><br>
                Prezzo: €<?= $dati['prezzo'] ?> / mese
            </p>

            <div class="form-buttons">
                <button type="submit" name="rinnovaAbbonamento" class="cta">
                    Rinnova ora
                </button>
            </div>
        </form>
        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>