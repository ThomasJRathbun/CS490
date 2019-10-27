<?php
function logInfo($status){
	 $rtype = $_POST['rtype'];
	 $info = "************************************\n[INFO $rtype][";
	 $info .= date('Y-m-d h:i:sa',time());
	 $info .= "]\n";


	 foreach( $_POST as $key => $value){
	 	  $info .= $key;
		  $info .= ": ";
		  $info .= $value;
		  $info .= "\n";
	 }

	 $info .= "[argument]:\n$status\n************************************\n";

	 $log = file_put_contents("log.txt", $info.PHP_EOL, FILE_APPEND | LOCK_EX);
	 
	 fwrite($log, $info);
	 fclose($log);
}
?>