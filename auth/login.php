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

        if ($conn->connect_error)
            die("Connessione fallita: " . $conn->connect_error);

        //Recupero dati
        $login = trim($_POST['login']); //username o email
        $pwd = $_POST['password'];

        if (empty($login) || empty($pwd)) {
            $msg = "<div class='msg error'>Inserisci username/email e password.</div>";
        } else {
            //Recupero utente
            $stmt = $conn->prepare("SELECT idUtente, email, nomeUtente, password FROM Utente WHERE nomeUtente = ? OR email = ?");
            $stmt->bind_param("ss", $login, $login);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $utente = $result->fetch_assoc();
                
                //Verifica password
                if (password_verify($pwd, $utente['password'])) {
                    $idUtente = $utente['idUtente'];

                    // Controllo sottoscrizione
                    $stmt_sub = $conn->prepare("
                        SELECT statoSottoscrizione
                        FROM Sottoscrizione
                        WHERE idUtente = ?
                        ORDER BY dataFine DESC
                        LIMIT 1
                    ");

                    $stmt_sub->bind_param("i", $idUtente);
                    $stmt_sub->execute();
                    $stmt_sub->bind_result($stato);
                    
                    if ($stmt_sub->fetch()) {
                        $_SESSION['idUtente'] = $idUtente;
                        $_SESSION['nomeUtente'] = $utente['nomeUtente'];
                        $_SESSION['email'] = $utente['email'];
                        $_SESSION['statoSottoscrizione'] = $stato;

                        $stmt_sub->close();
                        $stmt->close();
                        $conn->close();

                        header("Location: /profili/profili.php");
                        exit;

                    } else {
                        $msg = "<div class='msg error'>Devi avere almeno una sottoscrizione.</div>";
                    }

                    $stmt_sub->close();
                } else {
                    $msg = "<div class='msg error'>Password errata.</div>";
                }
            } else {
                $msg = "<div class='msg error'>Utente non trovato.</div>";
            }
            $stmt->close();
        }
        $conn->close();
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

        <h1>Accedi</h1>
        <p class="signin-subtitle">Bentornato su NetStream</p>

        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
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