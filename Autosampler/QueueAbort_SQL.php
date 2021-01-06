<?php 

$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);

$stmt = $pdo->prepare("SELECT * FROM QueueAbort");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$QueueStat=$row["QueueStat"];

?>