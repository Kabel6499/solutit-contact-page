<?php
date_default_timezone_set('Europe/Berlin');

$servername = "localhost";
$username = "messeuser";
$password = "rootofant";
$dbname = "messe_statistik";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindungsfehler: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
$conn->query("SET time_zone = '".date('P')."'");
?>
