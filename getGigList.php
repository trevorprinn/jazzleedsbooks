<?php
header("Content-Type: application/json;charset=utf-8");

session_start();

echo file_get_contents('./giglist.json', true);

?>