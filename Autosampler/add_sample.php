<?php

include("mysql_userdata.php");
include("Samples_SQL.php");
include("params.php");
include("globals.php");
$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);
$sample = end($Samples);


// Create new database entry for a new Sample.
reset($Samples);
if(isset($_POST['submit'])) {
	$message = "";
	$invalid = false;
    if(isset($_POST['User'])) {
		$User=intval($_POST['User']);
    } else {
        $invalid = true;
        $message .= "Please select a user.";
    }
	// to catch the error in the qt browser where seconds are ignored if they are xx:xx:00
    $StartDate = date_create_from_format("Y-m-d H:i", $_POST['StartDateDate'] . " " . $_POST['StartDateTime'])
		or $StartDate = date_create_from_format("Y-m-d H:i:s", $_POST['StartDateDate'] . " " . $_POST['StartDateTime'])
		or $StartDate = NULL;
	if($StartDate !== NULL) {
        $StartDate = $StartDate->getTimestamp();
    }
	$SampleType=$_POST['SampleType'];
	if($SampleType == "Shimming") {
		$SampleType = $_POST['ShimType'];
		$Holder = strval($NumberOfHolders + 1);  // is 31 for 30 usable holders.
		$Name = "";
		$Solvent = "";
		$Protocol = "";
		$ProtocolID = NULL;
		$Method = NULL;
		$Standard = "";
		$Eq = "";
		$nF = "";
	}
	if($SampleType == "Sample") {
		$Holder=$_POST['Holder'];
		if($Holder > $ParamData['NumberOfHolders']) {
			$invalid = true;
			$message .= "<p>Sample could not be added (Holder $Holder does not exist).</p>";
		}
		$Name=trim($_POST['Name']);
		if(strpos($Name, " ") !== false) {
			$invalid = true;
			$message .= "<p>Sample name must not contain whitespace.</p>";
		}
		// Check if the sample name already exists in the queue or the NMRFolder.
		$SampleNames = scandir($NMRFolder);
		foreach($Samples as $s) {
			array_push($SampleNames, $s["Name"]);
		}
		if(in_array($Name, $SampleNames)) {
			$invalid = true;
			$message .= "<p>A sample with the name \"" . $Name . "\" already exists!</p>";
		}
        if(isset($_POST['Solvent'])) {
            $Solvent = $_POST['Solvent'];
        } else {
            $invalid = true;
            $message .= "<p>Please select a solvent.</p>";
        }
        if(isset($_POST['Protocol'])) {
            $Protocol = $_POST['Protocol'];
			// get properties
			$found = false;
			$SampleProperties = array();
			foreach($Protocols as $thisProtocolID => $protocolname) {
				// find the protocol which matches the data
				if($Protocol === $protocolname) {
					$found = true;
					$ProtocolID = $thisProtocolID;
					$props = $ProtocolProperties[$thisProtocolID];
					foreach($props as $prop) {
						if(isset($_POST["prop_" . $prop["xmlKey"]])) {
							$SampleProperties[$prop["propid"]] = $_POST["prop_" . $prop["xmlKey"]];
						} else {
							$invalid = true;
							$message .= "<p>The property " . $prop["xmlKey"] . " is not set.</p>";
						}
					}
				}
			}
			if (!$found) {
				$invalid = true;
				$message .= "<p>Protocol not found in database.</p>";
			}
        } else {
            $invalid = true;
            $message .= "<p>Please select a protocol.</p>";
        }
        if($_POST['Method'] != '') {
            $Method = $_POST['Method'];
        } else {
            $Method = NULL;
        }
		$Standard = "";
		$Eq = "";
		$nF = "";
	}
	if($invalid) {
		$message .= "<p>The data was invalid. <a href='javascript:window.history.back();'>Back</a></p>";
		$message .= "</body></html>";
		die($message);
	} else {
		// add sample
		$LastID=$_POST['LastID'];
		$Date=time();
		$NewSample = array($LastID, $Holder, $User, $Name, $Solvent, $ProtocolID, $Method, $Standard, $Eq, $nF, $Date, "Queued", $SampleType, $StartDate);
		if ($sample and $LastID <= $sample['ID']) {
			foreach(array_reverse($Samples) as $sample) {
				if ($sample['ID'] >= $LastID) {
					$newid = $sample['ID'] + 1;
					$pdo->query("UPDATE samples SET `ID` = '" . $newid . "' WHERE `ID` = " . $sample['ID']);
				}
			}
		}
		$statement = $pdo->prepare("INSERT INTO samples (ID, Holder, User, Name, Solvent, Protocol, Method, Standard, Eq, nF, Date, Status, SampleType, StartDate) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
		$statement->execute($NewSample);
		// add sample properties
		$lastId = $pdo->lastInsertId();
		foreach($SampleProperties as $propid => $data) {
			$stmt = $pdo->prepare("INSERT INTO sample_properties (sampleid, propid, strvalue) VALUES (?,?,?)");
			$stmt->execute([$lastId, $propid, $data]);
		}
		$message = "Sample $Name (#$LastID) has been added to the Queue. ";
	}
}

// reload to ensure correct display of properties later
include("Samples_SQL.php");
$sample = end($Samples);

?>
<!DOCTYPE html>
<html>
<head>
<title>Add Sample</title>
<meta charset="utf-8" />
<script type="text/javascript">
var closeCountdown = 0;

var protocols = 
	<?php
	echo json_encode($Protocols);
	?>
;
var protocolsFlipped = {
	<?php
	$first = true;
	foreach($Protocols as $protocolid => $protocolname) {
		if(!$first) {
			echo ", ";
		}
		echo "\"" . $protocolname . "\": \"" . $protocolid . "\"";
		$first = false;
	}
	?>
};
var protocolNuclei = 
	<?php
	$protocolNucleiJson = json_encode($ProtocolNuclei);
	if ($protocolNucleiJson === false) {
		echo "{}";
	} else {
		echo $protocolNucleiJson;
	}
	?>
;
var protocolProperties = 
	<?php
	$ProtocolPropertiesJson = json_encode($ProtocolProperties);
	if ($ProtocolPropertiesJson === false) {
		echo "{}";
	} else {
		echo $ProtocolPropertiesJson;
	}
	?>
;
var lastSample = 
	<?php
	if ($sample) {
		echo json_encode($sample);
	} else {
		echo "{}";
	}
	?>
;
var lastSampleProps = 
	<?php
	if ($sample) {
		$stmt = $pdo->prepare("SELECT sample_properties.samplepropid, sample_properties.propid, protocol_properties.friendlyName, protocol_properties.xmlKey, sample_properties.strvalue FROM sample_properties INNER JOIN protocol_properties ON sample_properties.propid=protocol_properties.propid WHERE sample_properties.sampleid=?");
		$stmt->execute([$sample["ID"]]);
		$lastSampleProps = $stmt->fetchAll();
		if(count($lastSampleProps) > 0) {
			echo json_encode($lastSampleProps);
		} else {
			echo "{}";
		}
	} else {
		echo "{}";
	}
	?>
;


function checkType() {
    var SampleType = document.getElementById("SampleType").value;
    
    var tr_Holder = document.getElementById("tr_Holder");
    var tr_Solvent = document.getElementById("tr_Solvent");
    var tr_Protocol = document.getElementById("tr_Protocol");
    var tr_Method = document.getElementById("tr_Method");
    var tr_ShimType = document.getElementById("tr_ShimType");
    var tr_StartDate = document.getElementById("tr_StartDate");
    var tr_Name = document.getElementById("tr_Name");
    
    var Holder = document.getElementById("Holder");
    var Solvent = document.getElementById("Solvent");
    var Protocol = document.getElementById("Protocol");
    var Name = document.getElementById("Name");
    
    if(SampleType == "Sample") {
        tr_Holder.style.visibility = 'visible';
        tr_Solvent.style.visibility = 'visible';
        tr_Protocol.style.visibility = 'visible';
		tr_Properties.style.visibility = 'visible';
        tr_Method.style.visibility = 'visible';
        tr_StartDate.style.visibility = 'visible';
        tr_ShimType.style.visibility = 'hidden';
        tr_Name.style.visibility = 'visible';
        
        Holder.setAttribute("required", "required");
        Solvent.setAttribute("required", "required");
        Protocol.setAttribute("required", "required");
        Name.setAttribute("required", "required");
    }
    if(SampleType == "Shimming") {
        tr_Holder.style.visibility = 'hidden';
        tr_Solvent.style.visibility = 'hidden';
        tr_Protocol.style.visibility = 'hidden';
		tr_Properties.style.visibility = 'hidden';
        tr_Method.style.visibility = 'hidden';
        tr_ShimType.style.visibility = 'visible';
        tr_StartDate.style.visibility = 'visible';
        tr_Name.style.visibility = 'hidden';
        
        Holder.removeAttribute("required");
        Solvent.removeAttribute("required");
        Protocol.removeAttribute("required");
        Name.removeAttribute("required");
    }
}

function checkMethods() {
	// only display methods of the user and protocol
	var selected_user = document.getElementById("User").value;
    var selected_protocol = document.getElementById("Protocol").value;
	var protocolid = protocolsFlipped[selected_protocol];
	Array.from(document.getElementById("select_Method").options).forEach(function(option_method) {
        var display = false;
		var method_user = option_method.getAttribute("data-user");
        var method_nucleus = option_method.getAttribute("data-nucleus");
        if(method_user === "0" || selected_user == method_user) {
            // ok user matches, OR method_user is 0 which is the "none" method
            // could in the future be expanded to user- or nucleus-independent methods... 
            // match "1D FLUORINE+" to 19, "1D PROTON+" to 1.
            var selected_nucleus = protocolNuclei[protocolid];
            if(method_nucleus === "0" || method_nucleus == selected_nucleus) {
                // ok either the nucleus matches, or method_nucleus is 0 which is the "none" method
                // so we can display, written with flag for more readable code
                display = true;
            }
        }
        if(display) {
            option_method.removeAttribute("hidden");
        } else {
            option_method.setAttribute("hidden", "hidden");	
            option_method.selected = false;
        }
	});
}

function getLastSampleProp(key) {
	for (let idx in lastSampleProps) {
		if (lastSampleProps[idx]["xmlKey"] === key) {
			return lastSampleProps[idx];
		}
	}
	return null;
}

function checkProperties() {
	var td_Properties = document.getElementById("td_Properties");
	var selectedProtocol = document.getElementById("Protocol").value;
	var protocolid = protocolsFlipped[selectedProtocol];
	var props = protocolProperties[protocolid];
	var table = document.createElement("table");
	for (let propid in props) {
		prop = props[propid];
		// get preset from last sample
		var preset = getLastSampleProp(prop["xmlKey"]);
		// if not present, get from defaultValue
		if (preset === null) {
			preset = {"strvalue": prop["defaultValue"]};
		}
		let tr = document.createElement("tr");
		table.appendChild(tr);
		let td1 = document.createElement("td");
		td1.innerHTML = prop["friendlyName"];
		table.appendChild(td1);
		let td2 = document.createElement("td");
		if (prop["freeText"] == "0") {
			let select = document.createElement("select");
			select.setAttribute("id", "prop_" + prop["xmlKey"]);
			select.setAttribute("name", "prop_" + prop["xmlKey"]);
			var options = JSON.parse(prop["options"]);
			options.forEach(function(value) {
				let option = document.createElement("option");
				option.setAttribute("value", value);
				if (preset["strvalue"] === value.toString()) {
					option.setAttribute("selected", "selected");
				}
				option.innerHTML = value;
				select.appendChild(option);
			});
			td2.appendChild(select);
		} else {
			let inputElement = document.createElement("input");
			inputElement.setAttribute("id", "prop_" + prop["xmlKey"]);
			inputElement.setAttribute("name", "prop_" + prop["xmlKey"]);
			if (preset !== null) {
				inputElement.setAttribute("value", preset["strvalue"]);
			}
			inputElement.setAttribute("size", "10");
			td2.appendChild(inputElement);
		}
		table.appendChild(td2);
	}
	// clear old table
	while(td_Properties.firstChild) {
		td_Properties.removeChild(td_Properties.firstChild);
	}
	// add new one
	td_Properties.appendChild(table);
	// adjust size of the iframe
	adjustSize();
}

function adjustSize() {
    var frame = window.parent.document.getElementById("addSampleFrame");
    frame.width = "400px";
    frame.height = (frame.contentWindow.document.body.scrollHeight+20) + "px";
    frame.style.visibility = 'visible';
}

function closeFrame() {
    var frame = window.parent.document.getElementById("addSampleFrame");
    frame.width = 0;
    frame.height = 0;
    frame.style.visibility = 'hidden';
}

function autoClose() {
    // Automatically close the "Add sample" window after 5 minutes of inactivity.
    if(closeCountdown > 300) {
        closeFrame();
    } else {
        closeCountdown++;
    }
}
setInterval(autoClose, 1000);
</script>
<style>
input {
    text-align: center;
}
select {
    text-align: center;
}
</style>
</head>
<body onload="checkType(); checkMethods(); checkProperties(); adjustSize();" onmousemove="closeCountdown=0;">
<?php

if(isset($_GET['ID'])) {
    $id = $_GET['ID'];
} elseif(isset($_POST['ID'])) {
    $id = $_POST['ID'];
} else {
    $id = -1;
}
echo "<form action='add_sample.php' method='post'>";
echo "<table>";

if(isset($message)) {
    echo "<tr><th colspan='2'>";
    echo $message;
    echo "</th></tr>";
}

// the sample list has to be refreshed here, or else the script will use the wrong template information.
include("Samples_SQL.php");

echo "<tr><th colspan='2'>";
// this page should recieve an id of -1 if the sample should be added to the bottom.
// We need the information on the sample, which should serve as template for our new sample.
$id_already_in_db = false;
$key_of_id = -1;
$holderList = [];
foreach($Samples as $key=>$lastSample) {
    // loop over all samples and if the ID which we got from the table is found, write down its key
    if($lastSample['ID'] == $id) {
        $id_already_in_db = true;
        $key_of_id = $key;
    }
    array_push($holderList, $lastSample['Holder']);
}
if($key_of_id < 0) {
    // if the key is -1, it means that the ID couldn't be found in the database
    // so we will use the last Sample in the database as template.
    // if there are no Samples in the database, just make a blank table.
    if(isset($lastSample)) {
        $sample = $lastSample;
    } else {
        $sample = [];
    }
} elseif($key_of_id == 0) {
    // if the key is 0, we want to add a new sample at the very beginning of the table.
    // since we cannot use any template here, just make an empty array.
    $sample = [];
} else {
    // and finally if the key is a positive number, just use the sample above this one as template.
    // however, if the sample with the key $key_of_id-1 doesn't exist, we need to create a blank table.
    if(isset($Samples[$key_of_id-1])) {
        $sample = $Samples[$key_of_id-1];
    } else {
        $sample = [];
    }
}
if($id == -1) {
    echo "Add sample to the end of the queue";
} elseif($id_already_in_db) {
    echo "Add sample above sample $id";
} else {
    echo "Add sample as sample $id";
}
echo "</th></tr>";

// Type of sample (Shimming or Sample?)
echo "<tr>";
echo "<td>Type of sample</td>";
echo "<td><select onchange='checkType();' id='SampleType' name='SampleType'>";
echo "<option selected=\"selected\" value=\"Sample\">Sample</option>";
echo "<option value=\"Shimming\">Shimming</option>";
echo "</select></td>";
echo "</tr>";

// Holder
echo "<tr id='tr_Holder'>";
echo "<td>Holder</td>";
echo "<td>";
if($sample!=[] and $id==-1) {
    // this will automatically increment the holder. We have to be careful not to increment to
    // any holder, which already exists (comment on that comment: no, we don't). Also, we only do this if we add to the bottom of the table,
    // so people will have to put in the holder number manually if they aren't adding at the end.
    $NewHolder = $sample['Holder'];
    //while(in_array($NewHolder, $holderList)) {
        if($NewHolder<$ParamData["NumberOfHolders"]) {
            $NewHolder++;
        } else {
            $NewHolder = 1;
        }
    //}
} else {
    $NewHolder = "";
}
echo "<input type='number' id='Holder' name='Holder' min='1' max='" . $ParamData["NumberOfHolders"] . "' value='" . strval($NewHolder) ."' required='required' />";
echo "</td>";
echo "</tr>";

// User
echo "<tr>";
echo "<td>User</td>";
echo "<td>";
echo "<select id='User' name='User' required='required' onchange='checkMethods();'>";
$StandardUser = False;
foreach($pdo->query("SELECT * FROM users ORDER BY shortname ASC") as $User) {
	echo "<option value='" . $User['ID'] . "'";
    if ($sample != [] and $User['ID'] == $sample['User']) {
        echo " selected=\"selected\"";
        $StandardUser = True;
    }
	echo ">" . $User['shortname'] . "</option>";
}
if($StandardUser == False) {
    echo "<option disabled='disabled' selected='selected'></option>";
}
echo "</select>";
echo "</td>";
echo "</tr>";

// Solvent
echo "<tr id='tr_Solvent'>";
echo "<td>Solvent</td>";
echo "<td>";
echo "<select id='Solvent' name='Solvent' required='required'>";
echo "<option disabled='disabled'";
if($sample==[]) {
    echo " selected='selected'";
}
echo ">Select a Solvent!</option>";
foreach ($ParamData["Solvents"] as $LoeMi) {
    echo "<option value='$LoeMi'";
    if ($sample!=[] and $LoeMi == $sample['Solvent']) {
        echo " selected='selected'";
    }
    echo ">$LoeMi</option>";
}
echo "</select>";
echo "</td>";
echo "</tr>";

// Protocol
echo "<tr id='tr_Protocol'>";
echo "<td>Protocol</td>";
echo "<td>";
echo "<select id='Protocol' name='Protocol' required='required' onchange='checkMethods(); checkProperties();'>";
echo "<option disabled='disabled'";
if($sample==[]) {
    echo " selected='selected'";
}
echo ">Select a Protocol!</option>";
foreach ($ParamData["Protocols"] as $ProtocolID => $Protocol) {
    echo "<option value='$Protocol'";
    if ($sample != [] and $ProtocolID == $sample['Protocol']) {
		echo " selected='selected'";
    }
    echo ">$Protocol</option>";
}
echo "</select>";
echo "</td>";
echo "</tr>";

// Properties
echo "<tr id='tr_Properties'>";
echo "<td>Properties</td>";
echo "<td id='td_Properties'>";
echo "</td>";
echo "</tr>";

// Method 
echo "<tr id='tr_Method'>";
echo "<td>Processing Method</td>";
echo "<td>";
echo "<select name='Method' id='select_Method'>";
foreach($ParamData["Methods"] as $Method) {
    echo "<option value='" . $Method["ID"] . "'";
    if($sample==[]) {
        if($Method["ID"] == NULL) {
            echo " selected='selected'";
        }
    } else {
        if($Method["ID"] == $sample["Method"]) {
            echo " selected='selected'";
        }
    }
	echo " data-user='" . $Method["User"] . "'";
    echo " data-nucleus='" . $Method["Nucleus"] . "'";
    echo ">" . $Method["Name"] . "</option>";
}
echo "</select>";
echo "</td>";
echo "</tr>";

// ShimType
echo "<tr id='tr_ShimType'>";
echo "<td>Shim&nbsp;Type</td>";
echo "<td>";
echo "<select id='ShimType' name='ShimType'>";
echo "<option value='CheckShim'>CheckShim</option>";
echo "<option value='QuickShim'>QuickShim</option>";
echo "<option value='PowerShim'>PowerShim</option>";
echo "</select>";
echo "</td>";
echo "</tr>";

// StartDate
echo "<tr id='tr_StartDate'>";
echo "<td>Start&nbsp;Date&nbsp;&&nbsp;Time</td>";
echo "<td>";
echo "<input type='date' id='StartDateDate' name='StartDateDate' />";
echo "<input type='time' id='StartDateTime' name='StartDateTime' step='1'/>";
echo "</td>";
echo "</tr>";

// SampleName
echo "<tr id='tr_Name'>";
echo "<td>Sample&nbsp;Name</td>";
echo "<td>";
echo "<input type='text' id='Name' name='Name' required='required' value='";
if($sample!=[]) {
	// for checking if the sample name already exists, take both the queue, and the content of the NMRFolder.
    $SampleNames = scandir($NMRFolder);
    foreach($Samples as $s) {
        array_push($SampleNames, $s["Name"]);
    }
	
    $Name = $sample["Name"];
	// This while loop will increment any number at the end of the name automatically, if that name already exists.
	if($Name !== "") {
		while(in_array($Name, $SampleNames)) {
			$m = -1;
			$lastChar = "0"; // this is just so the program always goes into the next loop. the value will be overwritten there
			$numberOnEnd = "";
			while(ctype_digit($lastChar)) {
				// as long as $lastChar consists of only digits, this loop will run.
				// $lastChar will be the last $m characters of the $Name.
				$lastChar = substr($Name, $m);
				if(ctype_digit($lastChar)) {
					$NameWithoutLastChar = substr($Name, 0, $m);
					$numberOnEnd = $lastChar;
				}
				$m--;
			}
			// Check if the first digits of $numberOnEnd are zeros.
			$n_zeros_at_beginning = 0;
			while(substr($numberOnEnd, $n_zeros_at_beginning, 1) == "0") {
				$n_zeros_at_beginning++;
			}
			if(ctype_digit($numberOnEnd)) {
				$Name = $NameWithoutLastChar;
				$n_digits = strlen($numberOnEnd);
				$newNumberOnEnd = strval(intval($numberOnEnd) + 1);
				$length_diff = $n_digits - strlen($newNumberOnEnd); // only positive if new number of digits is smaller than old number of digits.
				for($i=0; $i<$length_diff; $i++) {
					// In this case, add leading zeros until the original number of digits is reached.
					$Name .= "0";
				}
				$Name .= $newNumberOnEnd;
			} else {
				$Name = $Name . "_2";
			}
		}
	}
    echo $Name;
}
echo "' />";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td colspan='2' style='text-align:right'>";
if($id != -1) {
    $next_id = $id + 1;
    $LastID = $id;
} else {
    $next_id = -1;
    if(isset($lastSample)) {
        $LastID = $lastSample['ID']+1;
    } else {
        $LastID = 1;
    }
}
echo "<input type='hidden' name='ID' value='" . strval($next_id) . "' />";
echo "<input type='hidden' name='LastID' value='" . $LastID . "' />";
echo "<input type='submit' name='submit' value='Add sample' />";
echo "<input type='button' value='Close' onClick='closeFrame();' />";
echo "</td>";
echo "</tr>";

echo "</table>";
echo "</form>";

?>
</body>
</html>