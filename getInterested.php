<?php
header("Content-Type: application/json;charset=utf-8");

session_start();

if (!isset($_SESSION['Interested'])) $_SESSION['Interested'] = [];

echo json_encode($_SESSION['Interested']);

?>