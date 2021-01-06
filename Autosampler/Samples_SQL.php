<?php

$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);

$Samples = Array();

foreach($pdo->query("SELECT * FROM samples ORDER BY ID ASC") as $row) {
    $Samples[$row['ID']] = $row;
}

?>