<!DOCTYPE html>
<html>
<head>
<title>User Management</title>
<meta charset="utf-8" />
<script type="text/javascript">

function cancel() {
    window.location.href = "users.php";
}

</script>
</head>
<body>
<?php

include("mysql_userdata.php");
include("params.php");
include("globals.php");
$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);

// add new user and edit existing user
if(isset($_POST['add_to_db']) and $_POST['add_to_db'] == "1") {
    $invalid = false;
    if(isset($_POST['shortname']) and $_POST['shortname'] != "") {
        $shortname = $_POST['shortname'];
    } else {
        $invalid = true;
    }
    if(isset($_POST['fullname']) and $_POST['fullname'] != "") {
        $fullname = $_POST['fullname'];
    } else {
        $invalid = true;
    }
    if(isset($_POST['email']) and $_POST['email'] != "") {
        $email = $_POST['email'];
    } else {
        $email = '';
        //$invalid = true;
    }
    if(!$invalid) {
        if(isset($_POST['id']) && is_numeric($_POST['id'])) {
            // update existing user
            $stmt = $pdo->prepare("UPDATE users SET shortname=?, fullname=?, email=? WHERE id = " . $_POST['id']);
        } else {
            // add new user
            $stmt = $pdo->prepare("INSERT INTO users (shortname, fullname, email) VALUES (?,?,?)");
        }
        $stmt->execute([$shortname, $fullname, $email]);
    } else {
        echo "Error: Not all informations were provided.";
    }
}

// remove user
if(isset($_POST['remove']) && is_numeric($_POST['remove'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_POST['remove']]);
}

$sort = "id";
if(isset($_GET['sort'])) {
    if($_GET['sort'] == "shortname") {
        $sort = "shortname";
    } elseif($_GET['sort'] == "fullname") {
        $sort = "fullname";
    } elseif($_GET['sort'] == "email") {
        $sort = "email";
    }
}
$UserData = [];
foreach($pdo->query("SELECT * FROM users ORDER BY $sort ASC") as $row) {
    array_push($UserData, $row);
}

echo "<table border='1'>";
echo "<tr><th colspan='6'>User Management</th></tr>\n";
echo "<tr>";
echo "<th><a href='users.php'>ID</a></th>";
echo "<th><a href='users.php?sort=shortname'>Short Name</a></th>";
echo "<th><a href='users.php?sort=fullname'>Full Name</a></th>";
echo "<th><a href='users.php?sort=email'>E-Mail</a></th>";
echo "<th colspan='2'>edit</th>";
echo "</tr>\n";
foreach ($UserData as $user) {
    if(isset($_POST['edit']) and $_POST['edit'] == $user['ID']) {
        echo "<form action='#' method='post'>\n";
        echo "<tr><td>";
        echo "<input type='hidden' value='$user[ID]' name='id' />";
        echo $user['ID'];
        echo "</td><td>";
        echo "<input type='text' maxlength='3' size='4' name='shortname' value='$user[shortname]' />";
        echo "</td><td>";
        echo "<input type='text' maxlength='255' name='fullname' value='$user[fullname]' />";
        echo "</td><td>";
        echo "<input type='email' maxlength='255' name='email' value='$user[email]' />";
        echo "</td><td>";
        echo "<input type='hidden' value='1' name='add_to_db' />";
        echo "<input type='submit' value='ok' />";
        echo "</td><td>";
        echo "<input type='button' value='cancel' onClick='cancel();' />";
        echo "</td></tr>\n";
        echo "</form>\n";
    } else {
        echo "<tr><td>";
        echo $user['ID'];
        echo "</td><td>";
        echo $user['shortname'];
        echo "</td><td>";
        echo $user['fullname'];
        echo "</td><td>";
        echo $user['email'];
        echo "</td><td>";
        echo "<form action='#' method='post'>";
        echo "<input type='submit' value='&#x270e;' />";
        echo "<input type='hidden' value='$user[ID]' name='edit' />";
        echo "</form>";
        echo "</td><td>";
        echo "<form action='#' method='post'>";
        echo "<input type='submit' value='&minus;' />";
        echo "<input type='hidden' value='$user[ID]' name='remove' />";
        echo "</form>";
        echo "</td></tr>\n";
    }
}
echo "<form action='#' method='post'>\n";
echo "<tr><td>";
echo "</td><td>";
echo "<input type='text' maxlength='3' size='4' name='shortname' value='' />";
echo "</td><td>";
echo "<input type='text' maxlength='255' name='fullname' value='' />";
echo "</td><td>";
echo "<input type='email' maxlength='255' name='email' value='' />";
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