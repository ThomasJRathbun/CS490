<?php

function connect_NJIT(){
	$dbhost = "sql1.njit.edu";
	$dbuser = "tjr44";
	$dbpass = "25OWbi3Qs";
	$dbname   = "tjr44";	
	$dbh =  mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die("UNABLE TO CONNECT TO MYSQL");
	return $dbh;
}



?>
