<?php
ini_set('display_errors', 'on');

$server = "localhost";
$username = "dikuland_dikulan";
$password = "YxgrDDdlhGsATbLsQT9x";
$database = "dikuland_dikulan";

$dbcon = new PDO("mysql:dbname=" . $database . ";host=" . $server, $username, $password);

print_r($dbcon);

?>

