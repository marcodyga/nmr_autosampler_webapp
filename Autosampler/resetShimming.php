<?php

include("mysql_userdata.php");
$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);
$pdo->query("UPDATE shimming SET Shimming = 0, LastShim = 0, ShimProgress = 0");

?>