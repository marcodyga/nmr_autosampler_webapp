<!DOCTYPE html>
<html>
<head>
<title>Protocols</title>
<meta charset="utf-8" />
<script type="text/javascript">

function cancel() {
    window.location.href = "protocols.php";
}

</script>
</head>
<body>
<?php

include("mysql_userdata.php");
include("params.php");
include("globals.php");

function generateNucleusSelect($pdo, $selected="") {
	echo '<select name="Mass">';
	echo '<option value="">none</option>';
	foreach($pdo->query("SELECT * FROM nuclei") as $nucleus) {
		echo '<option value="' . $nucleus['Mass'] . '"';
		if ($nucleus['Mass'] == $selected) {
			echo ' selected="selected"';
		}
		echo '>' . $nucleus['FriendlyName'] . '</option>';
	}
	echo '</select>';
}

function generateProperties($pdo, $protocolid) {
	echo '<a href="protocol_properties.php?protocolid=' . $protocolid . '">';
	$stmt = $pdo->prepare("SELECT * FROM protocol_properties WHERE protocolid=?");
	$stmt->execute([$protocolid]);
	$props = $stmt->fetchAll();
	if (count($props) > 0) {
		$first = true;
		foreach ($props as $prop) {
			if (!$first) {
				echo ", ";
			}
			$first = false;
			echo $prop["friendlyName"];
		}
	} else {
		echo "None";
	}
	echo "</a>";
}

// add new protocol and edit existing protocol
if(isset($_POST['add_to_db']) and $_POST['add_to_db'] == "1") {
    $invalid = false;
    if(isset($_POST['Mass']) and $_POST['Mass'] != "") {
		$nucleus = $_POST['Mass'];
    } else {
        $nucleus = null;
    }
    if(isset($_POST['name']) and $_POST['name'] != "") {
        $name = $_POST['name'];
    } else {
        $invalid = true;
    }
    if(isset($_POST['xmlKey']) and $_POST['xmlKey'] != "") {
        $xmlKey = $_POST['xmlKey'];
    } else {
        $invalid = true;
    }
    if(!$invalid) {
        if(isset($_POST['protocolid']) && is_numeric($_POST['protocolid'])) {
            // update existing protocol
            $stmt = $pdo->prepare("UPDATE protocols SET nucleus=?, name=?, xmlKey=? WHERE protocolid = " . $_POST['protocolid']);
        } else {
            // add new protocol
            $stmt = $pdo->prepare("INSERT INTO protocols (nucleus, name, xmlKey) VALUES (?,?,?)");
        }
        $stmt->execute([$nucleus, $name, $xmlKey]);
    } else {
        echo "Error: Not all informations were provided.";
    }
}

// remove protocol
if(isset($_POST['remove']) && is_numeric($_POST['remove'])) {
    $stmt = $pdo->prepare("DELETE FROM protocols WHERE protocolid = ?");
    $stmt->execute([$_POST['remove']]);
}

$sort = "protocolid";
if(isset($_GET['sort'])) {
    if($_GET['sort'] == "Mass") {
        $sort = "Mass";
    } elseif($_GET['sort'] == "name") {
        $sort = "name";
    } elseif($_GET['sort'] == "xmlKey") {
        $sort = "xmlKey";
    }
}

echo "<table border='1'>";
echo "<tr><th colspan='7'>Protocol Management</th></tr>\n";
echo "<tr>";
echo "<th><a href='protocols.php'>ID</a></th>";
echo "<th><a href='protocols.php?sort=Mass'>Nucleus</a></th>";
echo "<th><a href='protocols.php?sort=name'>Name</a></th>";
echo "<th><a href='protocols.php?sort=xmlKey'>XML Key</a></th>";
echo "<th>Properties</th>";
echo "<th colspan='2'>edit</th>";
echo "</tr>\n";

foreach($pdo->query("SELECT * FROM protocols LEFT OUTER JOIN nuclei ON protocols.nucleus = nuclei.Mass ORDER BY $sort ASC") as $protocol) {
    if(isset($_POST['edit']) and $_POST['edit'] == $protocol['protocolid']) {
        echo "<form action='#' method='post'>\n";
        echo "<tr><td>";
        echo "<input type='hidden' value='$protocol[protocolid]' name='protocolid' />";
        echo $protocol['protocolid'];
        echo "</td><td>";
        generateNucleusSelect($pdo, $protocol['nucleus']);
        echo "</td><td>";
        echo "<input type='text' maxlength='255' name='name' value='$protocol[name]' />";
        echo "</td><td>";
        echo "<input type='xmlKey' maxlength='255' name='xmlKey' value='$protocol[xmlKey]' />";
        echo "</td><td>";
		generateProperties($pdo, $protocol['protocolid']);
		echo "</td><td>";
        echo "<input type='hidden' value='1' name='add_to_db' />";
        echo "<input type='submit' value='ok' />";
        echo "</td><td>";
        echo "<input type='button' value='cancel' onClick='cancel();' />";
        echo "</td></tr>\n";
        echo "</form>\n";
    } else {
        echo "<tr><td>";
        echo $protocol['protocolid'];
        echo "</td><td>";
		if ($protocol['FriendlyName']) {
			echo $protocol['FriendlyName'];
		} else {
			echo "none";
		}
        echo "</td><td>";
        echo $protocol['name'];
        echo "</td><td>";
        echo $protocol['xmlKey'];
		echo "</td><td>";
		generateProperties($pdo, $protocol['protocolid']);
        echo "</td><td>";
        echo "<form action='#' method='post'>";
        echo "<input type='submit' value='&#x270e;' />";
        echo "<input type='hidden' value='$protocol[protocolid]' name='edit' />";
        echo "</form>";
        echo "</td><td>";
        echo "<form action='#' method='post'>";
        echo "<input type='submit' value='&minus;' />";
        echo "<input type='hidden' value='$protocol[protocolid]' name='remove' />";
        echo "</form>";
        echo "</td></tr>\n";
    }
}
echo "<form action='#' method='post'>\n";
echo "<tr><td>";
echo "</td><td>";
generateNucleusSelect($pdo);
echo "</td><td>";
echo "<input type='text' maxlength='255' name='name' value='' />";
echo "</td><td>";
echo "<input type='xmlKey' maxlength='255' name='xmlKey' value='' />";
echo "</td><td>";
echo "<input type='hidden' value='1' name='add_to_db' />";
echo "</td><td>";
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