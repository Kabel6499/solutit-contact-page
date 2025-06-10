<?php

$servername = "localhost";
$username = "messeuser";
$password = "rootofant"; // Leer lassen, wenn kein Passwort gesetzt ist
$dbname = "messe_statistik";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Verbindungsfehler: " . $conn->connect_error);
}
?>
