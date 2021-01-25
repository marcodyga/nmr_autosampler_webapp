<!DOCTYPE html>
<html>
<head>
<title>Configuration</title>
<meta charset="utf-8" />
</head>
<body>
<?php

include("mysql_userdata.php");
include("globals.php");

// update existing data
$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);
if(isset($_POST['NMRFolder'])) {
    $last_char = substr($_POST['NMRFolder'], -1);
    if($last_char != "/" and $last_char != "\\") {
        $_POST['NMRFolder'] .= "/";
    }
    $stmt = $pdo->prepare("UPDATE config SET NMRFolder = ?");
    $success = $stmt->execute([$_POST['NMRFolder']]);
    if($success) $NMRFolder = $_POST['NMRFolder'];
}
if(isset($_POST['NMRIP'])) {
    $stmt = $pdo->prepare("UPDATE config SET NMRIP = ?");
    $success = $stmt->execute([$_POST['NMRIP']]);
    if($success) $NMRIP = $_POST['NMRIP'];
}
if(isset($_POST['NMRPort'])) {
    $stmt = $pdo->prepare("UPDATE config SET NMRPort = ?");
    $success = $stmt->execute([$_POST['NMRPort']]);
    if($success) $NMRPort = $_POST['NMRPort'];
}
if(isset($_POST['ASPort'])) {
    $stmt = $pdo->prepare("UPDATE config SET ASPort = ?");
    $success = $stmt->execute([$_POST['ASPort']]);
    if($success) $ASPort = $_POST['ASPort'];
}
if(isset($_POST['ACDFolder'])) {
    $last_char = substr($_POST['ACDFolder'], -1);
    if($last_char != "/" and $last_char != "\\") {
        $_POST['ACDFolder'] .= "/";
    }
    $stmt = $pdo->prepare("UPDATE config SET ACDFolder = ?");
    $success = $stmt->execute([$_POST['ACDFolder']]);
    if($success) $ACDFolder = $_POST['ACDFolder'];
}

// create form
echo "<h2>Autosampler configuration</h2>";
echo "<form action='#' method='post'>";
echo "<table>";
echo "<tr><th colspan='2'>Spinsolve spectrometer</th></tr>";

// NMRFolder
echo "<tr>";
echo "<td title='Folder to save NMRs in.'>NMR folder</td>";
echo "<td><input type='text' value='$NMRFolder' name='NMRFolder' /></td>";
echo "</tr>";
// NMRIP
echo "<tr>";
echo "<td title='IP address of computer which is connected to the NMR spectrometer.'>NMR IP</td>";
echo "<td><input type='text' value='$NMRIP' name='NMRIP' /></td>";
echo "</tr>";
// NMRPort
echo "<tr>";
echo "<td title='Spectrometer port (Standard: 12997).'>NMR port</td>";
echo "<td><input type='text' value='$NMRPort' name='NMRPort' /></td>";
echo "</tr>";

echo "<tr><th colspan='2'>Autosampler</th></tr>";
// ASPort
echo "<tr>";
echo "<td title='COM port of Autosampler Arduino.'>Autosampler port</td>";
echo "<td><input type='text' value='$ASPort' name='ASPort' /></td>";
echo "</tr>";

echo "<tr><th colspan='2'>Automatic Processing</th></tr>";
// ACDFolder
echo "<tr>";
echo "<td title='Directory of the ACD NMR Processor.'>ACD folder</td>";
echo "<td><input type='text' value='$ACDFolder' name='ACDFolder' /></td>";
echo "</tr>";

echo "<tr><td></td><td>";
echo "<input type='submit' value='Save'/>";
echo "</td></tr>";

echo "</table>";
echo "</form>";

?>
<p><a href="index.php"><< return to table</a></p>
</body>
</html>