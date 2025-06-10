<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include 'config.php'; // Verbindung zur Datenbank

$messe_id = 3;  // Hier die ID der Messe, die besucht wird, anpassen
$ip_address = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare("INSERT INTO besuche (messe_id, ip_address, zeitstempel) VALUES (?, ?, NOW())");
$stmt->bind_param("is", $messe_id, $ip_address);
$stmt->execute();



// Optional: Ein transparentes 1x1-Pixel-Bild ausgeben, damit der Browser zufrieden ist
header('Content-Type: image/png');
echo base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII='
);
exit;
?>
