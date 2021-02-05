<!DOCTYPE html>
<html>
<head>
<title>Internal Standards</title>
<meta charset="utf-8" />
<script type="text/javascript">

function cancel() {
    window.location = "nmr_standards.php";
}

</script>
</head>
<body>
<?php

include("mysql_userdata.php");
include("params.php");
include("globals.php");
$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);

// add new entry and edit existing entry
if(isset($_POST['add_to_db']) and $_POST['add_to_db'] == "1") {
    $invalid = false;
    if(isset($_POST['nucleus']) and $_POST['nucleus'] != "") {
        $nucleus = $_POST['nucleus'];
    } else {
        $invalid = true;
    }
    if(isset($_POST['name']) and $_POST['name'] != "") {
        $name = $_POST['name'];
    } else {
        $invalid = true;
    }
    if(isset($_POST['shift']) and $_POST['shift'] != "") {
        $shift = str_replace(',', '.', $_POST['shift']);
    } else {
        $invalid = true;
    }
    if(isset($_POST['number_of_atoms']) and $_POST['number_of_atoms'] != "") {
        $number_of_atoms = $_POST['number_of_atoms'];
    } else {
        $number_of_atoms = '';
        $invalid = true;
    }
    if(isset($_POST['peakwidth_ppm']) and $_POST['peakwidth_ppm'] != "") {
        $peakwidth_ppm = str_replace(',', '.', $_POST['peakwidth_ppm']);
    } else {
        $invalid = true;
    }
    if(!$invalid) {
        if(isset($_POST['id'])) {
            // update existing entry
            $stmt = $pdo->prepare("UPDATE nmr_standards SET nucleus=?, name=?, shift=?, number_of_atoms=?, peakwidth_ppm=? WHERE id = " . $_POST['id']);
        } else {
            // add new entry
            $stmt = $pdo->prepare("INSERT INTO nmr_standards (nucleus, name, shift, number_of_atoms, peakwidth_ppm) VALUES (?,?,?,?,?)");
        }
        $stmt->execute([$nucleus, $name, $shift, $number_of_atoms, $peakwidth_ppm]);
    } else {
        echo "Error: Not all informations were provided.";
    }
}

// remove user
if(isset($_POST['remove'])) {
    $stmt = $pdo->prepare("DELETE FROM nmr_standards WHERE id = ?");
    $stmt->execute([$_POST['remove']]);
}

$sort = "id";
if(isset($_GET['sort'])) {
    if($_GET['sort'] == "name") {
        $sort = "name";
    } elseif($_GET['sort'] == "shift") {
        $sort = "shift";
    } elseif($_GET['sort'] == "number_of_atoms") {
        $sort = "number_of_atoms";
    }
}
$StandardsData = [];
foreach($pdo->query("SELECT * FROM nmr_standards ORDER BY $sort ASC") as $row) {
    array_push($StandardsData, $row);
}

echo "<table border='1'>";
echo "<tr><th colspan='8'>Internal Standards</th></tr>\n";
echo "<tr>";
# Filter by Nucleus
$filterNucleus = "0";
if(isset($_POST["filterNucleus"]) and ctype_digit($_POST["filterNucleus"])) {
	$filterNucleus = $_POST["filterNucleus"];
}
echo "<tr><td colspan='8'><form action='#' method='post'>";
echo "Only show standards for Nucleus ";
echo "<select name='filterNucleus'>";
echo "<option value='0'>any</option>";
foreach($pdo->query("SELECT Mass, FriendlyName FROM nuclei ORDER BY Mass ASC") as $nucleus) {
    echo "<option value='" . $nucleus["Mass"] . "'";
    if($filterNucleus == $nucleus["Mass"]) {
        echo " selected='selected'";
    }
    echo ">" . $nucleus["FriendlyName"] . "</option>";
}
echo "</select>";
echo "<input type='submit' value='>>'/>";
echo "</form></td></tr>";
echo "<tr>";
echo "<th><a href='nmr_standards.php'>ID</a></th>";
echo "<th><a href='nmr_standards.php?sort=nucleus'>Nucleus</a></th>";
echo "<th><a href='nmr_standards.php?sort=name'>Name</a></th>";
echo "<th><a href='nmr_standards.php?sort=shift'>Chemical shift [ppm]</a></th>";
echo "<th><a href='nmr_standards.php?sort=number_of_atoms'>Number of atoms</a></th>";
echo "<th><a href='nmr_standards.php?sort=peakwidth_ppm'>Peak width [ppm]</a></th>";
echo "<th colspan='2'>edit</th>";
echo "</tr>\n";
foreach ($StandardsData as $standard) {
    if($filterNucleus === "0" or $filterNucleus == $standard['nucleus']) {
        if(isset($_POST['edit']) and $_POST['edit'] == $standard['ID']) {
            echo "<form action='#' method='post'>\n";
            echo "<tr><td>";
            echo "<input type='hidden' value='$standard[ID]' name='id' />";
            echo $standard['ID'];
            echo "</td><td>";
            echo "<select id='nucleus' name='nucleus' required='required'>";
            foreach($pdo->query("SELECT Mass, FriendlyName FROM nuclei ORDER BY Mass ASC") as $nucleus) {
                echo "<option value='" . $nucleus["Mass"] . "'";
                if ($standard["nucleus"] == $nucleus["Mass"]) {
                    echo " selected='selected'";
                }
                echo ">" . $nucleus["FriendlyName"] . "</option>";
            }
            echo "</select>";
            echo "</td><td>";
            echo "<input type='text' maxlength='255' size='32' name='name' value='$standard[name]' />";
            echo "</td><td>";
            echo "<input type='text' maxlength='255' size='10' name='shift' value='$standard[shift]' />";
            echo "</td><td>";
            echo "<input type='text' maxlength='255' size='10' name='number_of_atoms' value='$standard[number_of_atoms]' />";
            echo "</td><td>";
            echo "<input type='text' maxlength='255' size='10' name='peakwidth_ppm' value='$standard[peakwidth_ppm]' />";
            echo "</td><td>";
            echo "<input type='hidden' value='1' name='add_to_db' />";
            echo "<input type='submit' value='ok' />";
            echo "</td><td>";
            echo "<input type='button' value='cancel' onClick='cancel();' />";
            echo "</td></tr>\n";
            echo "</form>\n";
        } else {
            echo "<tr><td>";
            echo $standard['ID'];
            echo "</td><td>";
            $nucleusDbEntry = $pdo->query("SELECT Mass, FriendlyName FROM nuclei WHERE Mass = " . $standard["nucleus"] . " LIMIT 1;")->fetch();
            echo $nucleusDbEntry["FriendlyName"];
            echo "</td><td>";
            echo $standard['name'];
            echo "</td><td>";
            echo $standard['shift'];
            echo "</td><td>";
            echo $standard['number_of_atoms'];
            echo "</td><td>";
            echo $standard['peakwidth_ppm'];
            echo "</td><td>";
            echo "<form action='#' method='post'>";
            echo "<input type='submit' value='&#x270e;' />";
            echo "<input type='hidden' value='$standard[ID]' name='edit' />";
            echo "</form>";
            echo "</td><td>";
            echo "<form action='#' method='post'>";
            echo "<input type='submit' value='&minus;' />";
            echo "<input type='hidden' value='$standard[ID]' name='remove' />";
            echo "</form>";
            echo "</td></tr>\n";
        }
    }
}
echo "<form action='#' method='post'>\n";
echo "<tr><td>";
echo "</td><td>";
echo "<select id='nucleus' name='nucleus' required='required'>";
foreach($pdo->query("SELECT Mass, FriendlyName FROM nuclei ORDER BY Mass ASC") as $nucleus) {
    echo "<option value='" . $nucleus["Mass"] . "'";
    if($filterNucleus !== "0" and $filterNucleus == $nucleus["Mass"]) {
        echo "selected='selected'";
    }
    echo ">" . $nucleus["FriendlyName"] . "</option>";
}
echo "</td><td>";
echo "<input type='text' maxlength='255' size='32' name='name' value='' />";
echo "</td><td>";
echo "<input type='text' maxlength='255' size='10' name='shift' value='' />";
echo "</td><td>";
echo "<input type='text' maxlength='4' size='10' name='number_of_atoms' value='' />";
echo "</td><td>";
echo "<input type='text' maxlength='255' size='10' name='peakwidth_ppm' value='' />";
echo "</td><td>";
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