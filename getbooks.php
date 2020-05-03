<?php
header("Content-Type: application/json;charset=utf-8");

session_start();

echo file_get_contents('http://www.librarything.com/api_getdata.php?userid=jazzleeds&key=2837672999&booksort=title&max=350&showTags=1&responseType=json&showCollections=1', true);

?>