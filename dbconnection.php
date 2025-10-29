<?php
$servername = "localhost";
$username = "root";
$password = base64_decode("Y29tcHJvZzE=");
$dbname = "machine_equipment_repair";
$port = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if($conn->connect_error)
{
die("Database connection failed: ".$conn->connect_error);
}
?> 