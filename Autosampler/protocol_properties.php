<?php

include("mysql_userdata.php");
include("params.php");
include("globals.php");

if (!isset($_GET["protocolid"]) || !is_numeric($_GET["protocolid"])) {
	die("Error: No protocol id has been provided.");
}
$protocolid = $_GET["protocolid"];

$stmt = $pdo->prepare("SELECT * FROM protocols LEFT OUTER JOIN nuclei ON protocols.nucleus = nuclei.Mass WHERE protocolid=? LIMIT 1");
$stmt->execute([$protocolid]);
$protocol = $stmt->fetch();
if (!$protocol) {
	die("Error: Protocol not found.");
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Properties - <?php echo $protocol['name']; ?></title>
<meta charset="utf-8" />
<script type="text/javascript">

function cancel() {
    window.location.href = "protocol_properties.php?protocolid=" + <?php echo "'" . $protocolid . "'"; ?>;
}

</script>
</head>
<body>
<?php
// add new property and edit existing property
if(isset($_POST['add_to_db']) and $_POST['add_to_db'] == "1") {
	$message = "";
    $invalid = false;
    if(isset($_POST['xmlKey']) and $_POST['xmlKey'] != "") {
		$xmlKey = $_POST['xmlKey'];
    } else {
        $invalid = true;
		$message .= "<p>No XML key was provided.</p>";
    }
    if(isset($_POST['friendlyName']) and $_POST['friendlyName'] != "") {
        $friendlyName = $_POST['friendlyName'];
    } else {
        $invalid = true;
		$message .= "<p>No friendly name was provided.</p>";
    }
    if(isset($_POST['options']) and $_POST['options'] != "") {
        $options = $_POST['options'];
		if (!is_array(json_decode($options))) {
			$invalid = true;
			$message .= "<p>The options must be provided in a JSON array, e.g. [123, 456, \"789\"].</p>";
		}
    } else {
        $options = null;
    }
    if(isset($_POST['freeText'])) {
		$freeText = 1;
	} else {
		$freeText = 0;
	}
    if(isset($_POST['defaultValue']) and $_POST['defaultValue'] != "") {
        $defaultValue = $_POST['defaultValue'];
    } else {
        $defaultValue = null;
    }
	if($invalid) {
		$message .= "<p>The data was invalid. <a href='javascript:window.history.back();'>Back</a></p>";
		$message .= "</body></html>";
		die($message);
	} else {
		$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        if(isset($_POST['propid']) && is_numeric($_POST['propid'])) {
			if ($_POST['propid'] > 0) {
				// update existing property
				$stmt = $pdo->prepare("UPDATE protocol_properties SET xmlKey=?, friendlyName=?, options=?, freeText=?, defaultValue=? WHERE propid = ?");
				$stmt->execute([$xmlKey, $friendlyName, $options, $freeText, $defaultValue, $_POST['propid']]);
			} else {
				// add new property
				$stmt = $pdo->prepare("INSERT INTO protocol_properties (protocolid, xmlKey, friendlyName, options, freeText, defaultValue) VALUES (?,?,?,?,?,?)");
				$stmt->execute([$protocolid, $xmlKey, $friendlyName, $options, $freeText, $defaultValue]);
			}
		}
    }
}

// remove property
if(isset($_POST['remove']) && is_numeric($_POST['remove'])) {
    $stmt = $pdo->prepare("DELETE FROM protocol_properties WHERE propid = ?");
    $stmt->execute([$_POST['remove']]);
}


echo "<table border=\"1\">";
echo "<tr><th colspan=\"8\">";
echo "Editing Properties of Protocol " . $protocol['name'];
echo "</th></tr>";
echo "<tr><th>";
echo "XML Key";
echo "</th><th>";
echo "Friendly Name";
echo "</th><th>";
echo "Options (JSON)";
echo "</th><th>";
echo "Free Text?";
echo "</th><th>";
echo "Default Value";
echo "</th><th colspan=\"2\">";
echo "edit";
echo "</th></tr>";

function generatePropertyRow($prop, $edit=false) {
	if ($edit) {
		echo "<form action='#' method='post'>\n";
		echo "<tr>";
		// XML Key
		echo "<td>";
		echo '<input type="text" name="xmlKey" value="' . $prop['xmlKey'] . '"/>';
		echo "</td>";
		// Friendly name
		echo "<td>";
		echo '<input type="text" name="friendlyName" value="' . $prop['friendlyName'] . '"/>';
		echo "</td>";
		// options JSON
		echo "<td>";
		echo '<textarea name="options" rows="2" cols="60">';
		echo $prop['options'];
		echo "</textarea>";
		echo "</td>";
		// free text checkmark
		echo "<td style='text-align:center;'>";
		echo '<input type="checkbox" name="freeText"';
		if ($prop["freeText"]) {
			echo ' checked="checked"';
		}
		echo '/>';
		echo "</td>";
		// default value
		echo "<td>";
		echo '<input type="text" name="defaultValue" value="' . $prop['defaultValue'] . '"/>';
		echo "</td>";
		// ok/cancel buttons
		echo "<td>";
		echo "<input type='hidden' value='$prop[propid]' name='propid' />";
		echo "<input type='hidden' value='$prop[protocolid]' name='protocolid' />";
        echo "<input type='hidden' value='1' name='add_to_db' />";
		if ($prop["propid"] == "0") {
			echo "</td><td>";
			echo "<input type='submit' value='+'/>";
        } else {
			echo "<input type='submit' value='ok'/>";
			echo "</td><td>";
			echo "<input type='button' value='cancel' onClick='cancel();' />";
		}
        echo "</td></tr>\n";
		echo "</tr>";
        echo "</form>\n";
	} else {
		echo "<tr>";
		// XML Key
		echo "<td>";
		echo $prop['xmlKey'];
		echo "</td>";
		// Friendly name
		echo "<td>";
		echo $prop['friendlyName'];
		echo "</td>";
		// options JSON
		echo "<td>";
		echo $prop['options'];
		echo "</td>";
		// free text checkmark
		echo "<td style='text-align:center;'>";
		echo '<input type="checkbox" name="freeText" disabled="disabled"';
		if ($prop["freeText"]) {
			echo ' checked="checked"';
		}
		echo '/>';
		echo "</td>";
		// default value
		echo "<td>";
		echo $prop['defaultValue'];
		echo "</td>";
		// button for editing
		echo "<td>";
		echo "<form action='#' method='post'>";
        echo "<input type='submit' value='&#x270e;' />";
        echo "<input type='hidden' value='$prop[propid]' name='edit' />";
        echo "</form>";
		echo "</td>";
		// button for removing
		echo "<td>";
		echo "<form action='#' method='post'>";
        echo "<input type='submit' value='&minus;' />";
        echo "<input type='hidden' value='$prop[propid]' name='remove' />";
        echo "</form>";
		echo "</td></tr>";
	}
}

$stmt = $pdo->prepare("SELECT * FROM protocol_properties WHERE protocolid = ?");
$stmt->execute([$protocolid]);
$props = $stmt->fetchAll();
$editAny = false;
foreach ($props as $prop) {
	$edit = false;
	if(isset($_POST['edit']) and $_POST['edit'] == $prop['propid']) {
		$editAny = true;
		$edit = true;
	}
	generatePropertyRow($prop, $edit);
}
$emptyProp = array("propid" => "0", "protocolid" => $protocolid, "xmlKey" => "", "friendlyName" => "", "options" => "", "freeText" => "", "defaultValue" => "");
if (!$editAny) {
	generatePropertyRow($emptyProp, true);
}

echo "</table>";

?>
<p><a href="protocols.php"><< return to protocols</a></p>
</body>
</html>