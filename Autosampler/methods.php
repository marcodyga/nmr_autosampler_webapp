<!DOCTYPE html>
<html>
<head>
<title>Method Management</title>
<meta charset="utf-8" />
</head>
<body>
<?php

include("mysql_userdata.php");
include("params.php");
include("globals.php");
$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);

// remove method
if(isset($_POST['remove'])) {
    $stmt = $pdo->prepare("DELETE FROM methods WHERE ID = ?");
    $stmt->execute([$_POST['remove']]);
}

$sort = "id";
if(isset($_GET['sort'])) {
    if($_GET['sort'] == "User") {
        $sort = "User";
    } elseif($_GET['sort'] == "LB") {
        $sort = "LB";
    } elseif($_GET['sort'] == "Name") {
        $sort = "Name";
    }
}
$Methods = [];
foreach($pdo->query("SELECT * FROM methods ORDER BY $sort ASC") as $row) {
    array_push($Methods, $row);
}

echo "<table border='1'>";
echo "<tr><th colspan='9'>Method Management</th></tr>\n";

$filterUser = "0";
if(isset($_POST["filterUser"]) and ctype_digit($_POST["filterUser"])) {
	$filterUser = $_POST["filterUser"];
}
echo "<tr><td colspan='9'><form action='#' method='post'>";
echo "Only show methods for User ";
echo "<select name='filterUser'>";
echo "<option value='0'>any</option>";
foreach($pdo->query("SELECT * FROM users ORDER BY shortname ASC") as $User) {
	echo "<option value='".$User['ID']."'";
	if($filterUser === strval($User["ID"])) {
		echo " selected='selected'";
	}
	echo ">".$User['shortname']."</option>";
}
echo "</select>";
echo "<input type='submit' value='>>'/>";
echo "</form></td></tr>";

echo "<tr>";
echo "<th><a href='methods.php'>ID</a></th>";
echo "<th><a href='methods.php?sort=User'>User</a></th>";
echo "<th><a href='methods.php?sort=Name'>Name</a></th>";
echo "<th><a href='methods.php?sort=LB'>LB [Hz]</a></th>";
echo "<th><a href='methods.php?sort=BaseLine'>BaseLine</a></th>";
echo "<th><a href='methods.php?sort=BoxHalfWidth'>BoxHalfWidth</a></th>";
echo "<th><a href='methods.php?sort=NoiseFactor'>NoiseFactor</a></th>";
echo "<th colspan='2'>edit</th>";
echo "</tr>\n";
foreach ($Methods as $method) {
	if($filterUser === "0" or $filterUser == $method['User']) {
		echo "<tr><td>";
		echo $method['ID'];
		echo "</td><td>";
		echo $UserData[$method['User']]["shortname"];
		echo "</td><td>";
		echo $method['Name'];
		echo "</td><td>";
		echo $method['LB'];
		echo "</td><td>";
		echo $method['BaseLine'];
		echo "</td><td>";
		if($method["BaseLine"] == "SpAveraging") {
			echo $method['BoxHalfWidth'];
		}
		echo "</td><td>";
		if($method["BaseLine"] == "SpAveraging") {
			echo $method['NoiseFactor'];
		}
		echo "</td><td>";
		echo "<form action='method_edit0.php' method='post'>";
		echo "<input type='submit' value='&#x270e;' />";
		echo "<input type='hidden' value='$method[ID]' name='edit' />";
		echo "</form>";
		echo "</td><td>";
		echo "<form action='#' method='post'>";
		echo "<input type='submit' value='&minus;' />";
		echo "<input type='hidden' value='$method[ID]' name='remove' />";
		echo "</form>";
		echo "</td></tr>\n";
	}
}
echo "<form action='method_edit0.php' method='post'>\n";
echo "<tr><td colspan='8'>";
echo "<input type='hidden' value='1' name='add_to_db' />";
echo "</td><td>";
echo "<input type='submit' value='+' />";
echo "</td></tr>\n";
echo "</form>\n";
echo "</td>";
echo "</table>";

?>
<p><a href="index.php"><< return to table</a></p>
</body>
</html>