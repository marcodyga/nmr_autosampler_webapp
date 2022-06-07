<?php

// prints out a bunch of information on all samples

include("mysql_userdata.php");
include("Samples_SQL.php");
include("Shimming_SQL.php");
include("QueueAbort_SQL.php");
include("autosampler.php");
include("globals.php");

$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<SampleList>\n";
foreach($Samples as $sample) {
    echo "  <sample>\n";
    foreach($sample as $key=>$attr) {
        if(is_numeric($key) == false) {
            echo "    <$key>";
            if($attr) {
                if($key == "Date" or $key == "StartDate") {
                    echo date("d.m.Y H:i:s", $attr);
                } elseif($key == "Progress") {
                    echo $attr . " %";
                } elseif($key == "Method") {
                    $stmt = $pdo->query("SELECT name FROM methods WHERE ID = $attr");
                    $method = $stmt->fetch();
                    echo $method["name"];
                } elseif($key == "User") {
                    $stmt = $pdo->query("SELECT shortname FROM users WHERE ID = $attr");
                    $user = $stmt->fetch();
                    echo $user["shortname"];
				} elseif ($key == "Protocol") {
					$stmt = $pdo->query("SELECT name FROM protocols WHERE protocolid = $attr");
                    $protocol = $stmt->fetch();
                    echo $protocol["name"];
				} else {
                    echo $attr;
                }
            } else {
                echo " ";
            }
            echo "</$key>\n";
        }
    }
	# properties
	echo "    <sampleProperties>\n";
	foreach($pdo->query("SELECT sample_properties.samplepropid, sample_properties.propid, protocol_properties.friendlyName, sample_properties.strvalue FROM sample_properties INNER JOIN protocol_properties ON sample_properties.propid=protocol_properties.propid WHERE sample_properties.sampleid=" . $sample["ID"]) as $prop) {
		echo "      <property>\n";
		echo "        ";
		echo "<samplepropid>";
		echo $prop["samplepropid"];
		echo "</samplepropid>";
		echo "\n        ";
		echo "<propid>";
		echo $prop["propid"];
		echo "</propid>";
		echo "\n        ";
		echo "<friendlyName>";
		echo $prop["friendlyName"];
		echo "</friendlyName>";
		echo "\n        ";
		echo "<strvalue>";
		echo $prop["strvalue"];
		echo "</strvalue>";
		echo "\n";
		echo "      </property>\n";
	}
	echo "    </sampleProperties>\n";
	echo "  </sample>\n";
}
echo "  <Shimming>$Shimming</Shimming>\n";
echo "  <LastShim>$LastShim</LastShim>\n";
echo "  <ShimProgress>$ShimProgress</ShimProgress>\n";
echo "  <QueueStat>$QueueStat</QueueStat>\n";
echo "  <Timestamp>".time()."</Timestamp>\n";

$as = new Autosampler();
$as_status = $as->getStatus();
echo "  <AutosamplerStatus>$as_status</AutosamplerStatus>\n";

echo "</SampleList>";


?>