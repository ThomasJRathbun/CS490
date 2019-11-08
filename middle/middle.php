<?php

//echo json_encode(passthroughFake());
//logInfo("HI");
//logInfo($_POST['rtype']);
$requestType = $_POST['rtype'];
switch ($requestType)
{
    case 'ruser':
        $user_name = $_POST['user_name'];
        $user_password = $_POST['user_password'];
        echo login($user_name, $user_password);
        //call login
        break;
    case 'cuser':
        $user_name = $_POST['user_name'];
        $user_password = $_POST['user_password'];
        //echo ($user_name, $user_password);
        //call login
        break;
    case 'canswer':
        echo questionResultCheck();
        break;
    default:
        echo passthrough();
        break;
}

function login($username, $password){
    $user = array('rtype' => $_POST['rtype'], 'user_name' => $username,
        'user_password' => $password);
    //echo json_encode($user);
    $back_url = "https://web.njit.edu/~tjr44/CS490/backend.php";

    $channel = curl_init();
    curl_setopt($channel, CURLOPT_URL, $back_url);
    curl_setopt($channel, CURLOPT_POST, true);
    curl_setopt($channel, CURLOPT_POSTFIELDS, $user);
    curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($channel);
    return $result;
}

function questionResultCheck(){
    //logInfo("HELLO");
    $back_url = "https://web.njit.edu/~tjr44/CS490/backend.php";
    $test_id = $_POST['test_id'];
    $user_name = $_POST['user_name'];
    
    $quikPass = array("rtype"=>"rtesting","test_id" => $test_id);
    
    //logInfo($quikPass);
    $channel = curl_init();
    curl_setopt($channel, CURLOPT_URL, $back_url);
    curl_setopt($channel, CURLOPT_POST, true);
    curl_setopt($channel, CURLOPT_POSTFIELDS, $quikPass);
    curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
    $backendData = curl_exec($channel);
    
    //logInfo("RETURNED DATA: $backendData");
    $backendData = json_decode($backendData);
    $questionCount = count(array_slice($_POST,0,-3));
    $totalGrade = $questionCount*9;
    //logInfo("TOTAL GRADE: " .$totalGrade);
    $currentGrade = 0;
    //Create Array Above loop
    $final = array('rtype' => 'cgrade', 'user_name' => $user_name,
        'test_id' => $test_id);
    foreach($backendData as $value){
        $value = (array)$value;
       // logInfo("1) Final: ". json_encode($final));
        //logInfo("VALUE: " . json_encode($value['question_id']));
        $question_ID = $value['question_id'];
        $question_name = $value['question_name'];
        $testcase_input_1 = $value['testcase_input_1'];
        $testcase_result_1 = $value['testcase_result_1'];
        $testcase_input_2 = $value['testcase_input_2'];
        $testcase_result_2 = $value['testcase_result_2'];
        //logInfo("QUESTION ID " . $question_ID);
        
        
        $questionScore = 9;

       // $final = array_merge($final, array('question_id' => $question_ID));
       // logInfo("2) Final: " . json_encode($final));
        //Write Student Code into answer.py
        $answer = $_POST["q$question_ID"];
        //logInfo("answer: $answer");
        $question = array("answer_code"=>$answer);
        //logInfo("3) Answer: " . json_encode($final));
        $writeStudentCode = fopen("answer.py", "w+");
        fwrite($writeStudentCode, $answer);
        fclose($writeStudentCode);

        //Get Function Name
        $name = explode(" ",$answer);
        //logInfo("$answer : *************************************************************************************************************");
        //logInfo("NAME****************************************************\n".json_encode($name));
        $i = 0;
        foreach($name as $token){
          //logInfo("TOKEN: $token");
          if ( strcmp($token, "def") == 0 ){
             $functionName = explode("(",$name[$i+1])[0];
             //logInfo("FUNCTION NAME: " . $functionName);
             break;
          }
          $i++;
        }
        //logInfo("FUNCTION NAME: " .json_encode($functionName));
        //$functionName = exec('python resultCheck.py');
        //logInfo("********************************\n $functionName \n****************************");
        //Check if function name is set as instructed on exam
        logInfo($functionName . " : " . $question_name);
        if(strcmp($functionName,$question_name) <> 0 ){
            $questionScore -= 5;
            //logInfo("NO QUESTION SCORE");
        }

        //Writes Function to file test.py
        //$functionCall = "    print(answer." . substr($functionName,0,-6) . "((*sys.argv[1:])))";
        $functionCall = "    answer." . $functionName . "((*sys.argv[1:]))";
        //logInfo("HERE:" . $functionCall);
        exec("/bin/cp -rf tempTest.py test.py");
        $lines = file("test.py", FILE_IGNORE_NEW_LINES);
        $lines[4] = $functionCall;
        file_put_contents("test.py", implode("\n", $lines));

        //Execute the File and Compare Results
        $toExec = escapeshellcmd("/afs/cad/linux/anaconda3.6/anaconda/bin/python test.py $testcase_input_1");
        //$toExec = escapeshellcmd("/afs/cad/linux/anaconda3.6/anaconda/bin/python test.py 1");
        //logInfo("PLEASE MAKE SENSE:::::::\n" . $toExec);
        $output1 = exec($toExec);
        //logInfo("*************************************\n************************************\n********************************************************\n" . $output1);
        if(strcmp($output1, $testcase_result_1) <> 0){ //might have to do strcmp
            $questionScore -= 2;
            logInfo("FIRST TIME:: $output1 : $testcase_result_1");
        }
        $toExec = escapeshellcmd("/afs/cad/linux/anaconda3.6/anaconda/bin/python test.py $testcase_input_2");
        $output2 = exec($toExec);
        //logInfo("************************************\n" . $testcase_input_2 .":". $output2);
        if( strcmp($output2, $testcase_result_2) <> 0){
            $questionScore -= 2;
            logInfo("FIRST TIME:: $output2 : $testcase_result_2");
        }

        //Incremement questionCount
        $currentGrade += $questionScore;
        $question = array_merge($question, array("question_id"=>$question_ID,"answer_points"=>$questionScore));

        $final = array_merge($final, array($question));
    }
    
    //Calculate Grade
    $finalScore = "$currentGrade out of $totalGrade";

    $final = array_merge( array("test_grade" => "$finalScore"),$final);
    //logInfo(json_encode($final));
    $grade2Front = array("test_grade" => "$finalScore");
    //logInfo("FINAL: " . json_encode($final));

    $channel = curl_init();
    curl_setopt($channel, CURLOPT_URL, $back_url);
    curl_setopt($channel, CURLOPT_POST, true);
    curl_setopt($channel, CURLOPT_POSTFIELDS, serialize($final));
    curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($channel);
    return json_encode($grade2Front);
}

function passthrough(){
    $back_url = "https://web.njit.edu/~tjr44/CS490/backend.php";
    $PostRequest =array();
    foreach($_POST as $key=>$value){
        $PostRequest = array_merge($PostRequest, array( "$key" => "$value"));
    }


    $channel = curl_init();
    curl_setopt($channel, CURLOPT_URL, $back_url);
    curl_setopt($channel, CURLOPT_POST, true);
    curl_setopt($channel, CURLOPT_POSTFIELDS, $PostRequest);
    curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($channel);
    return $result;
}



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

	 $info .= "[argument]:\n $status \n************************************\n";

	 $log = file_put_contents("log.txt", $info.PHP_EOL, FILE_APPEND | LOCK_EX);
	 
	 fwrite($log, $info);
	 fclose($log);
}
?>
