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
//$NumberOfHolders is the number of usable holders in the autosampler.
$Parameters = array("#", "Holder", "User", "Solvent", "Protocol", "Scans", "Repetition Time", "Processing Method", "Sample Name", "Submitted", "Status", "Progress", "Result");
$Solvents = array("None","Acetone","Acetonitrile","Benzene","Chloroform","Cyclohexane","DMSO","Ethanol","Methanol","Pyridine","TMS","THF","Toluene","TFA","Water","Other");
$Protocols = array("1D PROTON+", "1D FLUORINE+");
$NumberOfScans=array();
$i=1;
$j=1;
while ($i<=16384) {
    $NumberOfScans[$j]=$i;
    $i=$i*2;
    $j++;
}
$RepetitionTime=array(1,2,4,7,10,15,30,60,120);
sort ($Solvents);
sort ($Protocols);
sort ($Standards);
sort ($Users);
$NumberOfHolders = 30;
// All parameters can be summarized in one Array, to facilitate transfer of the information.
$ParamData["Users"] = $Users;
$ParamData["Solvents"] = $Solvents;
$ParamData["Protocols"] = $Protocols;
$ParamData["Standards"] = $Standards;
$ParamData["Methods"] = $Methods;
$ParamData["NumberOfScans"] = $NumberOfScans;
$ParamData["RepetitionTime"] = $RepetitionTime;
$ParamData["NumberOfHolders"] = $NumberOfHolders;

?>