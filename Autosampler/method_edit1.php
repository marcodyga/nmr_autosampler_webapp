<!DOCTYPE html>
<html>
<head>
<title>Edit Peaks</title>
<meta charset="utf-8" />
</head>
<body>
<?php

include("mysql_userdata.php");
include("params.php");
include("globals.php");

$invalid = false;

function check_float_value($key) {
    if(isset($_POST[$key]) and $_POST[$key] != "") {
        $retval = trim($_POST[$key]);
        $retval = str_replace(",", ".", $retval);
        if(!is_numeric($retval)) {
            return false;
        }
    } else {
        return false;
    }
    return $retval;
}

function check_digit_value($key) {
    if(isset($_POST[$key]) and $_POST[$key] != "") {
        $retval = trim($_POST[$key]);
        if(!ctype_digit($retval)) {
            return false;
        }
    } else {
        return false;
    }
    return $retval;
}

if(isset($_POST['methodID'])) {
    if(isset($_POST['modify_method'])) {
        // Update or add the method. (From method_edit0.php)
        $User = check_digit_value("User");
        if($User === false) {
            echo "<p>User does not exist.</p>";
            $invalid = true;
        }
        $LB = check_float_value("LB");
        if($LB === false) {
            echo "<p>LB must be a number.</p>";
            $invalid = true;
        }
        if(isset($_POST["Name"]) and $_POST["Name"] != "") {
            $Name = $_POST["Name"];
        } else {
            echo "<p>Method must have a name.</p>";
            $invalid = true;
        }
		if(isset($_POST['BaseLine']) and $_POST["BaseLine"] != "") {
			$BaseLine = $_POST["BaseLine"];
			if($BaseLine == "SpAveraging") {
				$BoxHalfWidth = check_digit_value("BoxHalfWidth");
				if($BoxHalfWidth === false) {
					echo "<p>BoxHalfWidth must be a number.</p>";
					$invalid = true;
				}
				$NoiseFactor = check_digit_value("NoiseFactor");
				if($NoiseFactor === false) {
					echo "<p>NoiseFactor must be a number.</p>";
					$invalid = true;
				}
			} else if ($BaseLine == "FIDReconstruction") {
				// not applicable, put some sensible default values
				$BoxHalfWidth = 50;
				$NoiseFactor = 3;
			} else {
				echo "<p>BaseLine is invalid.</p>";
				$invalid = true;
			}
		}
        if(!$invalid) {
            if($_POST["methodID"] == "-1") {
                // new method, needs an ID first
                $stmt = $pdo->prepare("INSERT INTO methods (User, LB, Name, BaseLine, BoxHalfWidth, NoiseFactor) VALUES (?,?,?,?,?,?)");
            } else {
                // update existing method
                $stmt = $pdo->prepare("UPDATE methods SET User=?, LB=?, Name=?, BaseLine=?, BoxHalfWidth=?, NoiseFactor=? WHERE ID = " . $_POST['methodID']);
            }
            $stmt->execute([$User, $LB, $Name, $BaseLine, $BoxHalfWidth, $NoiseFactor]);
            if($_POST["methodID"] == "-1") {
                $methodID = $pdo->lastInsertId();
            } else {
                $methodID = $_POST["methodID"];
            }
        }
    } else {
        // we do not want to edit a method, maybe a peak though
        // Fetch method from db
        if(ctype_digit($_POST["methodID"])) {
            $methodID = $_POST["methodID"];
            $pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);
            $stmt = $pdo->query("SELECT * FROM methods WHERE ID = " . $methodID);
            $method = $stmt->fetch();
            $User = $method["User"];
            $LB = $method["LB"];
            $Name = $method["Name"];
            
            if(isset($_POST["modify_peak"])) {
                // we want to edit a Peak!
                // First collect the data
                // Role
                $role = check_digit_value("role");
                if($role === false) {
                    echo "<p>Role of the peak is unclear!</p>";
                    $invalid = true;
                } else {
                    // Eq.
                    $Eq = check_float_value("Eq");
					if($Eq === false or $Eq <= 0) {
						echo "<p>Equivalents must be a numeric value greater than zero!</p>";
						$invalid = true;
					}
                    // nF
                    $nF = check_digit_value("nF");
                    if($nF === false) {
                        echo "<p>Number of fluorine atoms must be a whole, positive number.</p>";
                        $invalid = true;
                    }
                    // Begin ppm
                    $begin_ppm = check_float_value("begin_ppm");
                    if($begin_ppm === false) {
                        echo "<p>Chemical shifts (ppm) must be numeric values.</p>";
                        $invalid = true;
                    }
                    // End ppm
                    $end_ppm = check_float_value("end_ppm");
                    if($end_ppm === false) {
                        echo "<p>Chemical shifts (ppm) must be numeric values.</p>";
                        $invalid = true;
                    }
                    // Reference ppm & tolerance
                    $reference_ppm = "0";
					$reference_tolerance = "0";
                    if($role === "0") {
                        $reference_ppm = check_float_value("reference_ppm");
						$reference_tolerance = check_float_value("reference_tolerance");
                        if($reference_ppm === false or $reference_tolerance === false) {
                            echo "<p>Chemical shifts (ppm) must be numeric values.</p>";
                            $invalid = true;
                        }
                    }
                    // annotation
                    if(isset($_POST["annotation"]) and $_POST["annotation"] != "") {
                        $annotation = $_POST["annotation"];
                    } else {
                        echo "<p>Peak must have an annotation.</p>";
                        $annotation = false;
                        $invalid = true;
                    }
                }
                if(!$invalid) {
                    // make sure that begin_ppm is actually smaller than end_ppm
                    if(floatval($begin_ppm) > floatval($end_ppm)) {
                        // swap begin and end if not
                        $tmp = $begin_ppm;
                        $begin_ppm = $end_ppm;
                        $end_ppm = $tmp;
                    }
                    
                    // do we create a new peak, or do we already have a peakID?
                    $peakID = check_digit_value("peakID");
                    if($peakID === false) {
                        // new peak
                        $stmt = $pdo->prepare("INSERT INTO peaks (role, method, Eq, nF, begin_ppm, end_ppm, reference_ppm, reference_tolerance, annotation) VALUES (?,?,?,?,?,?,?,?,?)");
                    } else {
                        // existing peak
                        $stmt = $pdo->prepare("UPDATE peaks SET role=?, method=?, Eq=?, nF=?, begin_ppm=?, end_ppm=?, reference_ppm=?, reference_tolerance=?, annotation=? WHERE id = " . $peakID);
                    }
                    $stmt->execute([$role, $methodID, $Eq, $nF, $begin_ppm, $end_ppm, $reference_ppm, $reference_tolerance, $annotation]);
                }
            } else if(isset($_POST['standard_from_db'])) {
				// user chose to add a preconfigured internal standard
				$std_from_db_id = check_digit_value("standard_from_db");
				if($std_from_db_id === false) {
					echo "<p>Could not parse the internal standard.</p>";
					$invalid = true;
				} 
				$Eq = check_float_value("Eq");
				if($Eq === false or $Eq <= 0) {
					echo "<p>Equivalents must be a numeric value greater than zero!</p>";
					$invalid = true;
				}
				if(!$invalid) {
					$std_from_db = $pdo->query("SELECT * FROM fnmr_standards WHERE ID = " . $std_from_db_id . " LIMIT 1")->fetch();
					$begin_ppm = $std_from_db['shift'] - ($std_from_db['peakwidth_ppm'] / 2);
					$end_ppm = $std_from_db['shift'] + ($std_from_db['peakwidth_ppm'] / 2);
					$reference_ppm = $std_from_db['shift'];
					$reference_tolerance = 5; // put a sensible default value here.
					$annotation = substr($std_from_db['name'], 0, 20); // maximum 20 characters
					$stmt = $pdo->prepare("INSERT INTO peaks (role, method, Eq, nF, begin_ppm, end_ppm, reference_ppm, reference_tolerance, annotation) VALUES (0,?,?,?,?,?,?,?,?)");
                    $stmt->execute([$methodID, $Eq, $std_from_db['fluorine_atoms'], $begin_ppm, $end_ppm, $reference_ppm, $reference_tolerance, $annotation]);
				}
			}
        }
    }
} else {
    $invalid = true;
}

if($invalid) {
    echo "<p>The data was invalid. <a href='javascript:window.history.back();'>Back</a></p>";
} else {
    if(isset($_POST["remove"]) and ctype_digit($_POST["remove"])) {
        // remove if requested.
        $stmt = $pdo->prepare("DELETE FROM peaks WHERE id = ?");
        $stmt->execute([$_POST['remove']]);
        $edit = -1;
    } else if(isset($_POST["edit"]) and ctype_digit($_POST["edit"])) {
        // prepare for editing.
        $edit = $_POST["edit"];
    } else {
        $edit = -1;
    }
    
    echo "<table border=\"1\">";
    echo "<tr><th colspan=\"8\">";
    echo "Editing Integration Events for Method \"" . $Name . "\" (ID = " . strval($methodID) . ")";
    echo "</th></tr>";
    
    // Helper function for generating the rows. READONLY
    function create_row_readonly($peak, $methodID) {
        $peakID = $peak["ID"];
        // create readonly row
        echo "<tr>";
        // Equiv.
        echo "<td>" . $peak["Eq"] . "</td>";
        // nF
        echo "<td>" . $peak["nF"] . "</td>";
        // Begin ppm
        echo "<td>" . $peak["begin_ppm"] . "</td>";
        // End ppm
        echo "<td>" . $peak["end_ppm"] . "</td>";
        if($peak["role"] == 0) {
            // reference ppm
            echo "<td>" . $peak["reference_ppm"] . " &#177; " . $peak["reference_tolerance"] . "</td>";
        }
        // Annotation
        echo "<td";
        if($peak["role"] != 0) {
            echo " colspan='2'";
        }
        echo ">" . $peak["annotation"] . "</td>";
        echo "<td>";
        echo "<form action='#' method='post'>";
        echo "<input type='submit' value='&#x270e;' />";
        echo "<input type='hidden' value='$peakID' name='edit' />";
        echo "<input type='hidden' value='$methodID' name='methodID' />";
        echo "</form>";
        echo "</td><td>";
        echo "<form action='#' method='post'>";
        echo "<input type='submit' value='&minus;' />";
        echo "<input type='hidden' value='$peakID' name='remove' />";
        echo "<input type='hidden' value='$methodID' name='methodID' />";
        echo "</form>";
        echo "</td></tr>";
    }
    // Helper function for generating the rows. With form & input fields.
    function create_row_writable($peak, $methodID) {
        // create form
        echo "<tr>";
        echo "<form action='#' method='post'>";
        // Equiv.
        echo "<td>";
        echo "<input type='text' name='Eq' value='" . $peak["Eq"] . "' size='4' maxlength='25' title='Equivalents of standard in the sample.'/>";
        echo "</td>";
        // nF
        echo "<td>";
        echo "<input type='number' name='nF' value='" . $peak["nF"] . "' size='4' maxlength='2' min='1' max='99' title='The number of equivalent fluorine atoms in your target molecule that you use for calculating the yield.'/>";
        echo "</td>";
        // Begin ppm
        echo "<td>";
        echo "<input type='text' name='begin_ppm' value='" . $peak["begin_ppm"] . "' size='10' maxlength='25' title='Starting point of the integration event.'/>";
        echo "</td>";
        // End ppm
        echo "<td>";
        echo "<input type='text' name='end_ppm' value='" . $peak["end_ppm"] . "' size='10' maxlength='25' title='End point of the integration event.'/>";
        echo "</td>";
        if($peak["role"] == 0) {
            // reference ppm            
            echo "<td>";
            echo "<input type='text' name='reference_ppm' value='" . $peak["reference_ppm"] . "' size='8' maxlength='25' title='Reference point for the NMR spectrum.'/>";
			echo " &#177; "; // plus-minus
			echo "<input type='text' name='reference_tolerance' value='" . $peak["reference_tolerance"] . "' size='5' maxlength='25' title='Tolerance for finding the reference peak.'/>";
            echo "</td>";
        }
        // Annotation
        $annotation_size = 10;
        echo "<td";
        if($peak["role"] != 0) {
            echo " colspan='2'";
            $annotation_size = 32;
        }
        echo ">";
        echo "<input type='text' name='annotation' value='" . $peak["annotation"] . "' size='$annotation_size' maxlength='20' title='The name of the peak.'/>";
        echo "</td><td>";
        echo "<input type='hidden' value='1' name='modify_peak'/>";
        echo "<input type='hidden' value='" . strval($peak["role"]) . "' name='role'/>";
        if(isset($peak["ID"])) {
            // if not defined then we generate a new peak
            echo "<input type='hidden' value='" . $peak["ID"] . "' name='peakID' />"; 
            echo "<input type='hidden' value='$methodID' name='methodID' />";
            echo "<input type='submit' value='ok'/>";
            echo "</form>";
            echo "</td><td>";
            echo "<form action='#' method='post'>";
            echo "<input type='hidden' value='$methodID' name='methodID' />";
            echo "<input type='submit' value='cancel'/>";
        } else {
            echo "</td><td>";
            echo "<input type='hidden' value='$methodID' name='methodID' />";
            echo "<input type='submit' value='+'/>";
        }
        echo "</form></td></tr>\n";
        
        echo "</tr>";
    }
    // define default values for a new peak
    $new_peak = Array();
    $new_peak["Eq"] = "";
    $new_peak["nF"] = "";
    $new_peak["begin_ppm"] = "";
    $new_peak["end_ppm"] = "";
    $new_peak["reference_ppm"] = "";
	$new_peak["reference_tolerance"] = "";
    $new_peak["annotation"] = "";
    
    // INTERNAL STANDARD
    $new_peak["role"] = 0;
    echo "<tr><th colspan='8' style='padding-top:10px'>Internal Standard</th></tr>";
    echo "<tr><th>Equiv.</th><th>Number of F atoms</th><th>Begin [ppm]</th><th>End [ppm]</th><th>Reference value [ppm]</th><th>Annotation</th><th colspan='2'>edit</th></tr>";
    $any_int_stand = false;
    foreach($pdo->query("SELECT * FROM peaks WHERE role = 0 AND method = $methodID ORDER BY ID ASC") as $peak) {
        if($edit == $peak['ID']) {
            create_row_writable($peak, $methodID);
        } else {
            create_row_readonly($peak, $methodID);
        }
        $any_int_stand = true;
    }
    if(!$any_int_stand) {
        create_row_writable($new_peak, $methodID);
		// or use one of the preconfigured standards - create a select
		echo "<form action='' method='post'><tr><td colspan='7' style='text-align:right;'>load preconfigured standard: ";
		echo "<input type='text' value='1.0' size='4' maxlength='8' name='Eq'/> equiv. of ";
		echo "<select name='standard_from_db'>";
		foreach($pdo->query("SELECT ID,name,shift FROM fnmr_standards ORDER BY shift ASC") as $std_from_db) {
			echo "<option value='" . $std_from_db['ID'] . "'>";
			echo $std_from_db['name'] . " @ " . number_format($std_from_db['shift'], 1) . " ppm";
			echo "</option>";
		}
		echo "</select>";
		echo "</td><td>";
		echo "<input type='hidden' value='$methodID' name='methodID'/>";
		echo "<input type='submit' value='+' /></td></tr></form>";
    }
    
    // STARTING MATERIALS
    $new_peak["role"] = 1;
    echo "<tr><th colspan='8' style='padding-top:10px'>Starting Materials</th></tr>";
    echo "<tr><th>Equiv.</th><th>Number of F atoms</th><th>Begin [ppm]</th><th>End [ppm]</th><th colspan='2'>Annotation</th><th colspan='2'>edit</th></tr>";
    foreach($pdo->query("SELECT * FROM peaks WHERE role = 1 AND method = $methodID ORDER BY ID ASC") as $peak) {
        if($edit == $peak['ID']) {
            create_row_writable($peak, $methodID);
        } else {
            create_row_readonly($peak, $methodID);
        }
    }
    create_row_writable($new_peak, $methodID);
    
    // PRODUCTS
    $new_peak["role"] = 2;
    echo "<tr><th colspan='8' style='padding-top:10px'>Products</th></tr>";
    echo "<tr><th>Equiv.</th><th>Number of F atoms</th><th>Begin [ppm]</th><th>End [ppm]</th><th colspan='2'>Annotation</th><th colspan='2'>edit</th></tr>";
    foreach($pdo->query("SELECT * FROM peaks WHERE role = 2 AND method = $methodID ORDER BY ID ASC") as $peak) {
        if($edit == $peak['ID']) {
            create_row_writable($peak, $methodID);
        } else {
            create_row_readonly($peak, $methodID);
        }
    }
    create_row_writable($new_peak, $methodID);
    
    echo "</table>";
    echo '<form style="display:inline;" method="post" action="method_edit0.php">';
    echo '<input type="hidden" value="' . strval($methodID) . '" name="edit" />';
    echo '<input type="submit" value="&lt; back"/>';
    echo '</form> ';
    echo '<form style="display:inline;" method="post" action="methods.php">';
    echo '<input type="submit" value="finish &gt;"/>';
    echo '</form>';
}
?>
</body>
</html>