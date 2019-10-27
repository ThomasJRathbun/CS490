<?php
include 'connect_NJIT.php';
include 'logging.php';

$dbh = connect_NJIT();

$userTable = 't_users';
$userName = $_POST['username'];
$password = $_POST['password'];
$utype    = $_POST['utype'];
$password = password_hash($password, PASSWORD_DEFAULT);

$adding = 'INSERT INTO '. $userTable . ' (user_name, user_password, user_type) VALUES (\'' . $userName . '\',\'' . $password . '\',\'' . $utype . '\')';
$select = 'SELECT user_name, user_password FROM t_users WHERE user_name = \'' . $userName . '\'';

$q = mysqli_query($dbh, $select);
$row = mysqli_fetch_array($q, MYSQLI_ASSOC);


if (is_null($row) == TRUE){
   if( mysqli_query($dbh, $adding) == TRUE){
       logINFO("SUCCESS");
       echo "New User Created";
   }
   else{
       logINFO($adding);
       echo "ERROR";
   }
}
else{
    logINFO($userName . " already exists");
}
?>