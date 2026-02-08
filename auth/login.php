<?php
    session_start();

    if(isset($_SESSION['idUtente']) && isset($_SESSION['nomeUtente'])){
        header("Location: /../profili/profili.php");
        exit;
    }

    include __DIR__ . '/../includes/header.php';

    $msg = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST)) {
        $conn = new mysqli("localhost", "root", "", "db_NetStream");

        if ($conn->connect_error) {
            die("Connessione fallita: " . $conn->connect_error);
        }

        //Recupero dati
        $login = trim($_POST['login']); //username o email
        $pwd = $_POST['password'];

        if (empty($login) || empty($pwd)) {
            $msg = "<p style='color:red'>Inserisci username/email e password.</p>";
        } else {
            //Recupero utente
            $stmt = $conn->prepare(" SELECT idUtente, nomeUtente, password FROM Utente WHERE nomeUtente = ? OR email = ?");
            $stmt->bind_param("ss", $login, $login);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $utente = $result->fetch_assoc();
                
                //Verifica password
                if (password_verify($pwd, $utente['password'])) {
                    $idUtente = $utente['idUtente'];

                    //Controllo sottoscrizione ATTIVA
                    $stmt_sub = $conn->prepare("SELECT s.idSottoscrizione FROM Sottoscrizione s WHERE s.idUtente = ? AND s.statoSottoscrizione = 'ATTIVA' AND (s.dataFine IS NULL OR s.dataFine >= CURDATE()) LIMIT 1");
                    $stmt_sub->bind_param("i", $idUtente);
                    $stmt_sub->execute();
                    $stmt_sub->store_result();

                    if ($stmt_sub->num_rows === 1) {
                        // Login
                        $_SESSION['idUtente'] = $idUtente;
                        $_SESSION['nomeUtente'] = $utente['nomeUtente'];

                        $stmt_sub->close();
                        $stmt->close();
                        $conn->close();

                        header("Location: /profili/profili.php");
                        exit;
                    } else {
                        $msg = "<p style='color:red'>Sottoscrizione non attiva o scaduta.</p>";
                    }

                    $stmt_sub->close();
                } else {
                    $msg = "<p style='color:red'>Password errata.</p>";
                }
            } else {
                $msg = "<p style='color:red'>Utente non trovato.</p>";
            }

            $stmt->close();
        }

        $conn->close();
    }

    //Messaggi di errore
    if (!empty($msg)) echo $msg;
?>

<section class="signin-main">
    <div class="signin-box">

        <h1>Accedi</h1>
        <p class="signin-subtitle">Bentornato su NetStream</p>

        <form action="" method="post">

            <div class="form-group">
                <label for="login">Username o Email</label>
                <input type="text" id="login" name="login" required maxlength="100">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required maxlength="255">
            </div>

            <div class="form-buttons">
                <input type="submit" value="Accedi" class="cta">
            </div>

        </form>

        <p class="signin-footer">
            Non hai un account?
            <a href="/auth/signin.php">Registrati</a>
        </p>

    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>