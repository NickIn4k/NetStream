<?php
session_start();

if(isset($_SESSION['idUtente']) && isset($_SESSION['nomeUtente'])){
    header("Location: /../profili/profili.php");
    exit;
}

include __DIR__ . '/../includes/header.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST)) {
    $msg = "<div class='msg error'>";

    $conn = new mysqli("localhost", "root", "", "db_NetStream");
    if ($conn->connect_error)
        die("Connessione fallita: " . $conn->connect_error);

    //Gestione dati del form e validazione
    $nomeUtente = trim($_POST['username']);
    $email = trim($_POST['email']);
    $pwd = $_POST['password'];
    $numeroCarta = str_replace(' ', '', $_POST['card']);
    $scadenza = $_POST['scadenza'];
    $cvv = $_POST['cvv'];
    $abbonamento = $_POST['abbonamento'];

    if (empty($nomeUtente) || empty($email) || empty($pwd) || empty($numeroCarta) || empty($scadenza) || empty($cvv)) {
        $msg = $msg . "Compila tutti i campi obbligatori";
    } elseif (strlen($nomeUtente) > 50) {
        $msg = $msg . "Username troppo lungo (max 50 caratteri).";
    } elseif (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = $msg . "Email non valida o troppo lunga.";
    } elseif (strlen($pwd) < 6 || strlen($pwd) > 255) {
        $msg = $msg . "Password non valida (6–255 caratteri).";
    } elseif (!preg_match('/^[0-9]{13,19}$/', $numeroCarta)) {
        $msg = $msg . "Numero carta non plausibile.";
    } elseif (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $scadenza)) {
        $msg = $msg . "Scadenza non valida (MM/AA).";
    } elseif (!preg_match('/^[0-9]{3}$/', $cvv)) {
        $msg = $msg . "CVV non valido (3 cifre).";
    } else {
        //Controllo duplicati
        $stmt = $conn->prepare("SELECT idUtente FROM Utente WHERE nomeUtente = ? OR email = ?");
        $stmt->bind_param("ss", $nomeUtente, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $msg = $msg . "Username o email già registrati.";
        } else {
            //Inserimento utente
            $pwd_hash = password_hash($pwd, PASSWORD_DEFAULT);
            $dataRegistrazione = date('Y-m-d');

            $stmt_insert = $conn->prepare("INSERT INTO Utente (nomeUtente, email, password, dataRegistrazione) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $nomeUtente, $email, $pwd_hash, $dataRegistrazione);

            if ($stmt_insert->execute()) {
                $idUtente = $conn->insert_id; //ID utente appena creato

                $_SESSION['idUtente'] = $idUtente;
                $_SESSION['nomeUtente'] = $nomeUtente;
                $_SESSION['email'] = $email;

                //ID abbonamento
                $stmt_abbon = $conn->prepare("SELECT idAbbonamento FROM Abbonamento WHERE tipoAbbonamento = ?");
                $stmt_abbon->bind_param("s", $abbonamento);
                $stmt_abbon->execute();
                $stmt_abbon->bind_result($idAbbonamento);
                $stmt_abbon->fetch();
                $stmt_abbon->close();

                //Inserimento Sottoscrizione
                $dataInizio = date('Y-m-d');
                $dataFine = date('Y-m-d', strtotime('+1 month'));
                $stato = 'ATTIVA';

                $stmt_sott = $conn->prepare("INSERT INTO Sottoscrizione (dataInizio, dataFine, statoSottoscrizione, idUtente, idAbbonamento) VALUES (?, ?, ?, ?, ?)");
                $stmt_sott->bind_param("sssii", $dataInizio, $dataFine, $stato, $idUtente, $idAbbonamento);
                $stmt_sott->execute();
                $stmt_sott->close();
                $stmt_insert->close();
                $conn->close();
                
                header("Location: /../profili/profili.php");
                exit;
            } else {
                $msg = $msg . "Errore durante la registrazione.";
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
    $conn->close();
    $msg = $msg . "</div>";
}

//Messaggi di errore
if (!empty($msg)) 
    echo $msg;
?>

<section class="signin-main">
    <div class="signin-box">
        <?php 
            if (!empty($msg)){
                echo "
                    <div class='msg-wrapper'>
                        $msg
                    </div>
                ";
            }
        ?>

        <h1>Registrati</h1>
        <p class="signin-subtitle">Crea il tuo account NetStream</p>

        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required minlength="3" maxlength="50">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required maxlength="100">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6" maxlength="255">
            </div>

            <div class="form-group">
                <label for="abbonamento">Tipo di abbonamento</label>
                <select id="abbonamento" name="abbonamento" required>
                    <option value="Base">Base - 6.99€</option>
                    <option value="Medium">Medium - 9.99€</option>
                    <option value="Pro">Pro - 12.99€</option>
                </select>
            </div>

            <div class="card-box">
                <p class="card-title">Dati carta</p>

                <div class="form-group">
                    <label for="card">Numero carta</label>
                    <input type="text" id="card" name="card" minlength="13" maxlength="19" inputmode="numeric" placeholder="1234567812345678" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="scadenza">Scadenza</label>
                        <input type="text" id="scadenza" name="scadenza" minlength="5" maxlength="5" placeholder="MM/AA" required>
                    </div>

                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" minlength="3" maxlength="3" inputmode="numeric" required>
                    </div>
                </div>

                <p class="note">Questi dati non verranno salvati.</p>
            </div>

            <div class="form-buttons">
                <input type="submit" value="Registrati" class="cta">
                <input type="reset" value="Reset" class="btn-reset">
            </div>
        </form>

        <p class="signin-footer">
            Hai già un account?
            <a href="/auth/login.php">Accedi</a>
        </p>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>