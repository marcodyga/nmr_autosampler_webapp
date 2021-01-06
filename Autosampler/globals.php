<?php
// Reads important global variables from the config table in the database.

$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);

$stmt = $pdo->prepare("SELECT * FROM config");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$NMRFolder = $row['NMRFolder'];    // folder to save NMRs
$NMRIP = $row['NMRIP'];            // ip of computer which is connected to the NMR spectrometer
$NMRPort = $row['NMRPort'];        // spectrometer port
$ASPort = $row['ASPort'];		   // COM port of autosampler
$ACDFolder = $row['ACDFolder'];    // Folder of ACD NMR processor

?>