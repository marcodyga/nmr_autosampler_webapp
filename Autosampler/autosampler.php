<?php

class Autosampler {

    function getStatus() {
        // get the current status (error code) from the Arduino. (Deposited in database by python)
		$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=autosampler', MYSQL_UNAME, MYSQL_PASSWD);

		$stmt = $pdo->prepare("SELECT * FROM as_status");
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$status = intval($row["as_status"]);
		$last_contact = intval($row["last_contact"]);
		if($status >= 0 and (time() - $last_contact) > 2) {
            // If the last contact is more than 2 seconds ago, the python part of the Autosampler is most likely dead. This will cause the status to become -2.
            $status = -2;
        }
        return intval($status);
    }
    
}


?>