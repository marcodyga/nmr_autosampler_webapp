<!DOCTYPE html>
<html>
<head>
<title>Edit Method</title>
<meta charset="utf-8" />
<script type="text/javascript">

function cancel() {
    window.location.href = "methods.php";
}

function checkBaseLine() {
	var value = document.getElementById("BaseLine").value;
	if (value == "SpAveraging") {
		document.getElementById("tr_BoxHalfWidth").style.visibility = 'visible';
		document.getElementById("tr_NoiseFactor").style.visibility = 'visible';
	} else {
		document.getElementById("tr_BoxHalfWidth").style.visibility = 'hidden';
		document.getElementById("tr_NoiseFactor").style.visibility = 'hidden';
	}
}

</script>
</head>
<body onload="checkBaseLine();">
<form method="post" action="method_edit1.php">
<?php

include("mysql_userdata.php");
include("params.php");
include("globals.php");

// default values are set here
$method = Array();
$method["ID"] = -1;
$method["User"] = NULL;
$method["Name"] = "New Method";
$method["LB"] = 1.0;
$method["BaseLine"] = "SpAveraging";
$method["BoxHalfWidth"] = 50;
$method["NoiseFactor"] = 3;

$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);
if(isset($_POST["edit"])) {
    $stmt = $pdo->query("SELECT * FROM methods WHERE ID = " . $_POST["edit"]);
    $method = $stmt->fetch();
}

echo "<table border=\"1\">";
echo "<tr><th colspan=\"2\">";
if(isset($_POST["edit"])) {
    echo "Editing Method with ID = " . strval($_POST["edit"]);
} else {
    echo "Create New Method";
}
echo "</th></tr>";
echo "<tr><td>User</td><td>";
echo "<select id='User' name='User' required='required'>";
$StandardUser = False;
foreach($pdo->query("SELECT * FROM users ORDER BY shortname ASC") as $User) {
    if ($method["User"] == $User["ID"]) {
        echo "<option value='" . $User["ID"] . "' selected=\"selected\";>" . $User["shortname"] . "</option>";
        $StandardUser = True;
    } else {
        echo "<option value='" . $User["ID"] . "'>" . $User["shortname"] . "</option>";
    }
}
if($StandardUser == False) {
    echo "<option disabled='disabled' selected='selected'></option>";
}
echo "</td></tr>";
echo "<tr><td>Name</td><td>";
echo "<input type='text' maxlength='100' name='Name' value='$method[Name]'/>";
echo "</td></tr>";
echo "<tr><td>LB [Hz]</td><td>";
$lb_display_value = strval($method['LB']);
if(strpos($lb_display_value, ".") === false) {
    $lb_display_value .= ".0";
}
echo "<input type='text' maxlength='255' name='LB' value='" . $lb_display_value . "'/>";
echo "</td></tr>";
echo "<tr><td>BaseLine</td><td>";
echo "<select id='BaseLine' name='BaseLine' required='required' onchange='checkBaseLine();'>";
echo "<option value='SpAveraging'";
if ($method["BaseLine"] == "SpAveraging") {
	echo " selected='selected'";
}
echo ">SpAveraging</option>";
echo "<option value='FIDReconstruction'";
if ($method["BaseLine"] == "FIDReconstruction") {
	echo " selected='selected'";
}
echo ">FIDReconstruction</option>";
echo "</td></tr>";
echo "<tr id='tr_BoxHalfWidth'><td>BoxHalfWidth</td><td>";
echo "<input type='number' size='4' id='BoxHalfWidth' name='BoxHalfWidth' value='" . $method["BoxHalfWidth"]. "'/>";
echo "</td></tr>";
echo "<tr id='tr_NoiseFactor'><td>NoiseFactor</td><td>";
echo "<input type='number' size='4' id='NoiseFactor' name='NoiseFactor' value='" . $method["NoiseFactor"]. "'/>";
echo "</td></tr>";
echo "</table>";
echo "<input type='hidden' name='methodID' value='" . strval($method["ID"]) . "'/>";
echo "<input type='hidden' name='modify_method' value='1'/>";

?>
<input type="button" onClick="cancel();" value="&lt; back"/> <input type="submit" value="save & continue &gt;"/>
</form>
</body>
</html>