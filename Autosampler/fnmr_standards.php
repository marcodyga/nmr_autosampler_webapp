<!DOCTYPE html>
<html>
<head>
<title>Internal Standards for 19F-NMR</title>
<meta charset="utf-8" />
<script type="text/javascript">

function cancel() {
    window.location = "fnmr_standards.php";
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
    if(isset($_POST['fluorine_atoms']) and $_POST['fluorine_atoms'] != "") {
        $fluorine_atoms = $_POST['fluorine_atoms'];
    } else {
        $fluorine_atoms = '';
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
            $stmt = $pdo->prepare("UPDATE fnmr_standards SET name=?, shift=?, fluorine_atoms=?, peakwidth_ppm=? WHERE id = " . $_POST['id']);
        } else {
            // add new entry
            $stmt = $pdo->prepare("INSERT INTO fnmr_standards (name, shift, fluorine_atoms, peakwidth_ppm) VALUES (?,?,?,?)");
        }
        $stmt->execute([$name, $shift, $fluorine_atoms, $peakwidth_ppm]);
    } else {
        echo "Error: Not all informations were provided.";
    }
}

// remove user
if(isset($_POST['remove'])) {
    $stmt = $pdo->prepare("DELETE FROM fnmr_standards WHERE id = ?");
    $stmt->execute([$_POST['remove']]);
}

$sort = "id";
if(isset($_GET['sort'])) {
    if($_GET['sort'] == "name") {
        $sort = "name";
    } elseif($_GET['sort'] == "shift") {
        $sort = "shift";
    } elseif($_GET['sort'] == "fluorine_atoms") {
        $sort = "fluorine_atoms";
    }
}
$StandardsData = [];
foreach($pdo->query("SELECT * FROM fnmr_standards ORDER BY $sort ASC") as $row) {
    array_push($StandardsData, $row);
}

echo "<table border='1'>";
echo "<tr><th colspan='6'>Internal Standards for <sup>19</sup>F-NMR</th></tr>\n";
echo "<tr>";
echo "<th><a href='fnmr_standards.php'>ID</a></th>";
echo "<th><a href='fnmr_standards.php?sort=name'>Name</a></th>";
echo "<th><a href='fnmr_standards.php?sort=shift'>Chemical shift [ppm]</a></th>";
echo "<th><a href='fnmr_standards.php?sort=fluorine_atoms'>Number of F atoms</a></th>";
echo "<th><a href='fnmr_standards.php?sort=peakwidth_ppm'>Peak width [ppm]</a></th>";
echo "<th colspan='2'>edit</th>";
echo "</tr>\n";
foreach ($StandardsData as $standard) {
    if(isset($_POST['edit']) and $_POST['edit'] == $standard['ID']) {
        echo "<form action='#' method='post'>\n";
        echo "<tr><td>";
        echo "<input type='hidden' value='$standard[ID]' name='id' />";
        echo $standard['ID'];
        echo "</td><td>";
        echo "<input type='text' maxlength='255' size='32' name='name' value='$standard[name]' />";
        echo "</td><td>";
        echo "<input type='text' maxlength='255' size='10' name='shift' value='$standard[shift]' />";
        echo "</td><td>";
        echo "<input type='text' maxlength='255' size='10' name='fluorine_atoms' value='$standard[fluorine_atoms]' />";
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
        echo $standard['name'];
        echo "</td><td>";
        echo $standard['shift'];
        echo "</td><td>";
        echo $standard['fluorine_atoms'];
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
echo "<form action='#' method='post'>\n";
echo "<tr><td>";
echo "</td><td>";
echo "<input type='text' maxlength='255' size='32' name='name' value='' />";
echo "</td><td>";
echo "<input type='text' maxlength='255' size='10' name='shift' value='' />";
echo "</td><td>";
echo "<input type='text' maxlength='4' size='10' name='fluorine_atoms' value='' />";
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