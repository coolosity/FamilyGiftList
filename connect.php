<?php
$host = "localhost";
$db = "familygiftlist";
$user = "root";
$pass = "";

if (true) {
    $host = "localhost";
    $db = "familygiftlist";
    $user = "root";
    $pass = "";
}

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_errno) {
    echo "Failed to connect to MySQL: " . $conn->connect_error;
}
?>
