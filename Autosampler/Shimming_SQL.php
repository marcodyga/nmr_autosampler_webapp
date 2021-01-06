<?php 

$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);

$stmt = $pdo->prepare("SELECT * FROM shimming");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$Shimming = $row["Shimming"];
$LastShim = $row["LastShim"];
$ShimProgress = $row["ShimProgress"];

 ?>