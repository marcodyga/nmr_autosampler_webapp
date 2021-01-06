<?php

// Recieves data from the table and does stuff like clearing table,
// removing samples and stopping queue.
// Adding new samples is handled by the add_sample.php !

echo "Update mode is " . $_POST['mode'] . " !<br />\n";

include("mysql_userdata.php");
include("QueueAbort_SQL.php");
include("globals.php");

$mode = $_POST['mode'];
$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);

// Clearing queue
if($mode == "clear") {
    $pdo->query("DELETE FROM samples");
    $pdo->query("UPDATE QueueAbort SET QueueStat = 0");
    $pdo->query("UPDATE shimming SET Shimming = 0, LastShim = 0");
}

// Create new database entry for a new Sample.
if($mode == "submit") {
    foreach($pdo->query("SELECT ID FROM samples ORDER BY ID DESC LIMIT 1") as $sample) {}
    $Holder=$_POST['Holder'];
    $User=$_POST['User'];
    $Name=$_POST['Name'];
    $m = 2;
    while (file_exists($NMRFolder . $Name . "/")) {
        $fname = $_POST['Name'] . "_" . strval($m);
        $m++;
    }
    $Solvent=$_POST['Solvent'];
    $Protocol=$_POST['Protocol'];
    $Measurements=$_POST['Measurements'];
    $Number=$_POST['Number'];
    $RepTime=$_POST['RepetitionTime'];
    $Standard=$_POST['Standard'];
    $Eq=$_POST['Eq'];
    $nF=$_POST['nF'];
    $LastID=$_POST['LastID'];
    $Date=time();
    $NewSample = array($LastID, $Holder, $User, $Name, $Solvent, $Protocol, $Measurements, $Number, $RepTime, $Standard, $Eq, $nF, $Date, "Queued");
    if ($LastID < $sample['ID']) {
        foreach(array_reverse($Samples) as $sample) {
            if ($sample['ID'] >= $LastID) {
                $newid = $sample['ID'] + 1;
                $pdo->query("UPDATE samples SET `ID` = '" . $newid . "' WHERE `ID` = " . $sample['ID']);
            }
        }
    }
    $statement = $pdo->prepare("INSERT INTO samples (ID, Holder, User, Name, Solvent, Protocol, Measurements, Number, RepTime, Standard, Eq, nF, Date, Status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $statement->execute($NewSample);
}

function addInitialShim() {
    // Adds an initial Shimming sample to the database, if this is required.
    $pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);
    $result = $pdo->query("SELECT * FROM samples WHERE Status = 'Queued' ORDER BY ID ASC");
    $queued_samples = $result->fetchAll();
    $Date=time();
    $SampleType = "CheckShim";
    if ($queued_samples != [] and $queued_samples[0]["SampleType"] == "Sample") {
        $FirstID = $queued_samples[0]["ID"];
        foreach(array_reverse($queued_samples) as $sample) {
            $newid = $sample['ID'] + 1;
            $pdo->query("UPDATE samples SET `ID` = '" . $newid . "' WHERE `ID` = " . $sample['ID']);
        }
    } else {
        $FirstID = 1;
    }
    if($queued_samples[0]["SampleType"] == "Sample") {
        $NewSample = array($FirstID, $Date, "Queued", $SampleType);
        $statement = $pdo->prepare("INSERT INTO samples (ID, Holder, Date, Status, SampleType) VALUES (?,31,?,?,?)");
        $statement->execute($NewSample);
    }
}

// Starts queue
if($QueueStat == 0 and $mode == "start") {
    $pdo->query("UPDATE QueueAbort SET QueueStat = 1");
}

// Aborts queue
if($mode == "abort" and $QueueStat == 1) {
	// send abort signal, it will be picked up by queue daemon in python which will handle everything else
    $pdo->query("UPDATE QueueAbort SET QueueStat = 0");
}

// Starts Shimming and Queue.
if($QueueStat == 0 and $mode == "shimstart") {
    addInitialShim();
    $pdo->query("UPDATE QueueAbort SET QueueStat = 1");
}

// delete a sample
if($mode == "delete") {
    echo "deleting sample with id ".$_POST['ID'];
    // only delete sample if it is not currently running
    foreach($pdo->query("SELECT * FROM samples WHERE id = '" . $_POST['ID'] . "'") as $row) {
        if($row['Status'] == 'Running') {
            echo "<script>alert(\"Warning, you are deleting a running sample. If the queue is still running, this may cause problems.\");</script>";
        }
		$pdo->query("DELETE FROM samples WHERE ID = '" . $_POST['ID'] . "'");
    }
}

// move a Sample
if($mode == "moveUp") {
    $id = $_POST['ID'];
    foreach($pdo->query("SELECT ID FROM samples WHERE ID < " . $id . " ORDER BY ID DESC LIMIT 1") as $sample) {}
    if(isset($sample)) {
        echo "moving sample with id ".$id." up to position ".$sample['ID'];
        $query =  "UPDATE samples SET ID = -1 WHERE ID = $id;";
        $query .= "UPDATE samples SET ID = $id WHERE ID = ".$sample['ID'].";";
        $query .= "UPDATE samples SET ID = ".$sample['ID']." WHERE ID = -1";
        $pdo->query($query);
    }
}
if($mode == "moveDown") {
    $id = $_POST['ID'];
    foreach($pdo->query("SELECT ID FROM samples WHERE ID > " . $id . " ORDER BY ID ASC LIMIT 1") as $sample) {}
    if(isset($sample)) {
        echo "moving sample with id ".$id." down to position ".$sample['ID'];
        $query =  "UPDATE samples SET ID = -1 WHERE ID = $id;";
        $query .= "UPDATE samples SET ID = $id WHERE ID = ".$sample['ID'].";";
        $query .= "UPDATE samples SET ID = ".$sample['ID']." WHERE ID = -1";
        $pdo->query($query);
    }
}


?>