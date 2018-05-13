<?php
header("Content-Type: application/json;charset=utf-8");

session_start();

$_SESSION['Interested'] = json_decode(file_get_contents('php://input'), true);

?>