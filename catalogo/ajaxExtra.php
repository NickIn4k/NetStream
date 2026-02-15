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
        <div class='episode-info'>
            <h4>Trailer ufficiale</h4>
        </div>
        <a href='{$row['pathContenuto']}' class='cta'>Guarda</a>
    </div>
";

$stmt->close();
$conn->close();