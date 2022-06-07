<?php

// get the UserData from the Database first!
include("mysql_userdata.php");
$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);
foreach($pdo->query("SELECT * FROM users") as $row) {
    $UserData[$row['ID']] = $row;
}
if(!isset($UserData)) {
    // this catches the case of when no users exist in the database
    $UserData = [];
}
$Users = array();
foreach($UserData as $user) {
    array_push($Users, $user['shortname']);
}
$Users_FullNames = array();
foreach($UserData as $user) {
    array_push($Users_FullNames, $user['fullname']);
}

// get standards from DB
$Standards = array("");
foreach($pdo->query("SELECT name FROM nmr_standards ORDER BY ID ASC") as $name) {
	array_push($Standards, $name[0]);
}

// get nuclei from DB
$Nuclei = array();
foreach($pdo->query("SELECT * FROM nuclei ORDER BY Mass ASC") as $nucleus) {
    array_push($Nuclei, $nucleus);
}

// get methods from DB
$Methods = array();
$DefaultMethod = array();
$DefaultMethod["ID"] = NULL;
$DefaultMethod["User"] = 0;
$DefaultMethod["Name"] = "None";
$DefaultMethod["Nucleus"] = 0;
array_push($Methods, $DefaultMethod);
foreach($pdo->query("SELECT ID, User, Name, Nucleus FROM methods ORDER BY ID ASC") as $data) {
	array_push($Methods, $data);
}

//$Parameters defines all columns of the table; $Solvents and $Protocols should be kept up to date with everything that is 
//available in the magritek software (pay attention to correct spelling!); new solvents/protocols can simply be appended 
//at the end of the array as they are sorted into alphabetic order afterwards anyway; 
$Parameters = array("#", "Holder", "User", "Solvent", "Protocol", "Properties", "Processing Method", "Sample Name", "Submitted", "Start Date", "Status", "Progress", "Result");
$Solvents = array("None","Acetone","Acetonitrile","Benzene","Chloroform","Cyclohexane","DMSO","Ethanol","Methanol","Pyridine","TMS","THF","Toluene","TFA","Water","Other");
// Fetch protocols from DB
$Protocols = array();
$ProtocolNuclei = array();
$ProtocolProperties = array();
foreach($pdo->query("SELECT * FROM protocols ORDER BY name ASC") as $protocol) {
	$Protocols[$protocol["protocolid"]] = $protocol["name"];
	$ProtocolNuclei[$protocol["protocolid"]] = $protocol["nucleus"];
	// properties
	$stmt = $pdo->prepare("SELECT * FROM protocol_properties WHERE protocolid = ? ORDER BY propid ASC");
	$stmt->execute([$protocol["protocolid"]]);
	$ProtocolProperties[$protocol["protocolid"]] = array();
	while($prop = $stmt->fetch()) {
		$ProtocolProperties[$protocol["protocolid"]][$prop["propid"]] = $prop;
		//print_r($prop);
		//print_r(json_decode($prop["options"]));
	}
}
sort ($Solvents);
sort ($Standards);
sort ($Users);
//$NumberOfHolders is the number of usable holders in the autosampler.
$NumberOfHolders = 30;
// All parameters can be summarized in one Array, to facilitate transfer of the information.
$ParamData["Users"] = $Users;
$ParamData["Solvents"] = $Solvents;
$ParamData["Protocols"] = $Protocols;
$ParamData["Standards"] = $Standards;
$ParamData["Methods"] = $Methods;
$ParamData["NumberOfHolders"] = $NumberOfHolders;

?>