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
                if($key == "Date") {
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
				} else {
                    echo $attr;
                }
            } else {
                echo " ";
            }
            echo "</$key>\n";
        }
    }
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