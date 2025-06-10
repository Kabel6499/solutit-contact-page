<?php
include 'config.php';

$messe_id = $_GET['messe_id'] ?? 1; // Standard-Messe-ID
$ip = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare("INSERT INTO besuche (messe_id, ip_address, zeitstempel) VALUES (?, ?, NOW())");
$stmt->bind_param("is", $messe_id, $ip);
$stmt->execute();

// Nur für Testzwecke - Im Produktivbetrieb entfernen
echo "Besuch wurde registriert!";
?>
