<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "coffee_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi Error: " . mysqli_connect_error());
}
?>