<?php

$conn = new mysqli("localhost", "root", "", "db_NetStream");

$idContenuto = $_GET['idContenuto'] ?? null;
if (!$idContenuto) 
    exit;

$stmt = $conn->prepare("
    SELECT titoloContenuto, pathContenuto
    FROM contenuto
    WHERE idContenuto = ?
");

$stmt->bind_param("i", $idContenuto);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();

echo "
    <div class='episode-card'>
        <div class='episode-left'>
            <div class='episode-info'>
                <h4>Trailer ufficiale</h4>
            </div>
        </div>
        <a href='/player/player.php?id={$_GET['idContenuto']}&trailer=1' class='watch-btn'>Guarda</a>
    </div>
";

$stmt->close();
$conn->close();