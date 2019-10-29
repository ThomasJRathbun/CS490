
<?php
/*
	$ch = curl_init();

	$user = array('rtype' => $_POST['rtype'], 'user_name' => $_POST['user_name'],'user_password' => $_POST['user_password']);
	
//	echo json_encode($user);	
	// REMINDER: UPDATE LINK FROM TEST TO PROD
	$middle_url = "https://web.njit.edu/~tjr44/CS490/backend.php";
//	var_dump($user);	

	curl_setopt($ch, CURLOPT_URL, $middle_url);                           
	curl_setopt($ch, CURLOPT_POST, true);                                   
	curl_setopt($ch, CURLOPT_POSTFIELDS, $user);       
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

	$result = curl_exec($ch);
	
		
	//Error Checking curl
//	echo curl_getinfo($ch) . '<br/>';
//	echo curl_errno($ch) . '<br/>';
//	echo curl_error($ch) . '<br/>';	
	
	
	curl_close($ch);
	echo $result;
*/

   $requestType = $_POST['rtype'];
   
   switch($requestType){
      case 'ruser':
          $user_name = $_POST['user_name'];
          $user_password = $_POST['user_password'];
          echo login($user_name, $user_password);   
          break;

      default:
	  echo passthrough();
	  break;		
   }
	
   function passthrough(){
       //$back_url = "https://web.njit.edu/~tjr44/CS490/backend.php";
       $middle_url = "https://web.njit.edu/~ard47/middle.php";
       $PostRequest =array();
       foreach($_POST as $key=>$value){
            $PostRequest = array_merge($PostRequest, array( "$key" => "$value"));
       }

       $channel = curl_init();
       curl_setopt($channel, CURLOPT_URL, $middle_url);
       curl_setopt($channel, CURLOPT_POST, true);
       curl_setopt($channel, CURLOPT_POSTFIELDS, $PostRequest);
       curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
       $result = curl_exec($channel);
       return $result;
   }

   function login($username, $password){
       $user = array('rtype' => $_POST['rtype'], 'user_name' => $username,'user_password' => $password);
       //echo json_encode($user);
       //$back_url = "https://web.njit.edu/~tjr44/CS490/backend.php";
       $middle_url = "https://web.njit.edu/~ard47/middle.php";

       $channel = curl_init();
       curl_setopt($channel, CURLOPT_URL, $middle_url);
       curl_setopt($channel, CURLOPT_POST, true);
       curl_setopt($channel, CURLOPT_POSTFIELDS, $user);
       curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
       $result = curl_exec($channel);
       return $result;
   }	
  	

?>

