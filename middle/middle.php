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
    //$questionCount = count(array_slice($_POST,0,-3));
    //$totalGrade = $questionCount*9;
    //logInfo("TOTAL GRADE: " .$totalGrade);
    $currentGrade = 0;
    //Create Array Above loop
    $final = array('rtype' => 'cgrade', 'user_name' => $user_name,
        'test_id' => $test_id);
    $finalScore = 0;
    //logInfo(json_encode($final));
    foreach($backendData as $value){
        $value = (array)$value;
        // logInfo("1) Final: ". json_encode($final));
        //logInfo("VALUE: " . json_encode($value['question_id']));
        //add additional testcases up to six test cases
        $testcaseCount = 0;
        foreach($value as $testcase => $test){
            if(strcmp(($word = substr($testcase,0,8)), "testcase") === 0){
            logInfo("TESTCASE " . $testcase . " $test");
                continue;
            }
            $testcaseCount++;
        }
        $testcaseCount /= 2;
        $question_ID = $value['question_id'];
        $question_name = $value['question_name'];
        /*$testcase_input_1 = $value['testcase_input_1'];
        $testcase_result_1 = $value['testcase_result_1'];
        $testcase_input_2 = $value['testcase_input_2'];
        $testcase_result_2 = $value['testcase_result_2'];
        $testcase_input_3 = $value['testcase_input_3'];
        $testcase_result_3 = $value['testcase_result_3'];
        $testcase_input_4 = $value['testcase_input_4'];
        $testcase_result_4 = $value['testcase_result_4'];
        $testcase_input_5 = $value['testcase_input_5'];
        $testcase_result_5 = $value['testcase_result_5'];
        $testcase_input_6 = $value['testcase_input_6'];
        $testcase_result_6 = $value['testcase_result_6'];
        //$question_points = $value['question_points'];*/
        //logInfo("QUESTION ID " . $question_ID);

        //will need to get custom score
        $totalScore = $value['question_points'];
        logInfo("Total Score: " . $totalScore);
        $question_constraint = $value['question_constraint'];

        logInfo("Before If");
        $caseCount = 2;
        if($question_constraint){
            $caseCount++;
        }
        logInfo("Case Count After If: " . $caseCount);
        $caseCount = $caseCount + $testcaseCount;
        logInfo("Case Count + TestCase Count: " . $caseCount);
        $points4Else = (int)($totalScore/ $caseCount);
        logInfo("points4Else " . $points4Else);
        $points4Name = $points4Else + ($totalScore % $caseCount);
        logInfo("Points4Name: " . $points4Name);
        $namePoints = 0;
        $colonPoints = 0;
        $constraintPoints = 0;
        $forLoop = "for";
        $whileLoop = "while";
        $printStatement = "print";
        $testcasePoints = 0;
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

        //Check for Constraints
        //$name = explode(" ",$answer);

        $file = fopen("answer.py", "r+");
        $constraintFlag = false;
        while(!feof($file)){
            $line = fgets($file);
            $splitLine = explode(" ", $line);
            $i = 0;
            foreach($splitLine as $token){
                if ( strcmp($token, "def") == 0 ){
                    $functionName = explode("(",$splitLine[$i+1])[0];
                    //logInfo("FUNCTION NAME: " . $functionName);
                    $colonSearch = explode(")", $line)[1];
                    if(strcmp(trim($colonSearch), ":") !== 0){
                        $colonPoints = $points4Else;
                        $question = array_merge($question, array("pass_colon"=>$colonPoints));
                    }
                }
                $i++;
            }
            logInfo("Question COnstraint: " . $question_constraint);
            switch ($question_constraint){
                case 'for':
                    logInfo(json_encode($line));
                    if(strpos($line, $forLoop) <> 0){
                        //$constraintPoints = $points4Else;
                        $constraintFlag = true;
                        //$question = array_merge($question, array("pass_for"=>$constraintPoints));
                    }
                    break;
                case 'while':
                    if (strpos($line, $whileLoop) <> 0){
                        //$constraintPoints = $points4Else;
                        $constraintFlag = true;
                        //$question = array_merge($question, array("pass_while"=>$constraintPoints));
                    }
                    break;
                case 'print':
                    if (strpos($line, $printStatement) <> 0){
                        //$constraintPoints = $points4Else;
                        $constraintFlag = true;
                        //$question = array_merge($question, array("pass_print"=>$constraintPoints));
                    }
                    break;
                case 'null':
                    break;
            }
        }
        if($constraintFlag == false){
            $constraintPoints = $points4Else;
            $question = array_merge($question, array("pass_constraint"=>$constraintPoints));
                
        }
        
        
        
        fclose($file);
        //Get Function Name

        //Check if colon is at the end of function line
        /*if(strcmp(end(substr($name, -1)), ":") !== 0){
            $totalScore-= 5;
        }*/
        //logInfo("$answer : *************************************************************************************************************");
        //logInfo("NAME****************************************************\n".json_encode($name));
        //Need to use this for loop below to chekc for the name of the function and the check if the colon is at the end of the function
        /*$i = 0;
        foreach($name as $token){
            //logInfo("TOKEN: $token");
            if ( strcmp($token, "def") == 0 ){
                $functionName = explode("(",$name[$i+1])[0];
                //logInfo("FUNCTION NAME: " . $functionName);
                break;
            }
            $i++;
        }*/
        //logInfo("FUNCTION NAME: " .json_encode($functionName));
        //$functionName = exec('python resultCheck.py');
        //logInfo("********************************\n $functionName \n****************************");
        //Check if function name is set as instructed on exam
        logInfo($functionName . " : " . $question_name);
        if(strcmp($functionName,$question_name) <> 0 ){
            logInfo("LOST POINTS FOR NAME");
            $namePoints = $points4Name;
            $question = array_merge($question, array("pass_name"=>$namePoints));
            //logInfo("NO QUESTION SCORE");
        }

        //Writes Function to file test.py
        //$functionCall = "    print(answer." . substr($functionName,0,-6) . "((*sys.argv[1:])))";
        $functionCall = "    print(answer." . $functionName . "((*sys.argv[1:])))";
        //logInfo("HERE:" . $functionCall);
        exec("/bin/cp -rf tempTest.py test.py");
        $lines = file("test.py", FILE_IGNORE_NEW_LINES);
        $lines[4] = $functionCall;
        file_put_contents("test.py", implode("\n", $lines));

        //Execution of File and Comparison of Results
        $i = 1;
        $testcasePoints = 0;
        for($i = 1;$i < $testcaseCount+1;$i++){
            $testcasePointDeductions = 0;
            $testcase_input = $value["testcase_input_$i"];
            $testcase_result = $value["testcase_result_$i"];
            $toExec = escapeshellcmd("/afs/cad/linux/anaconda3.6/anaconda/bin/python test.py $testcase_input");
            //$toExec = escapeshellcmd("/afs/cad/linux/anaconda3.6/anaconda/bin/python test.py 1");
            //logInfo("PLEASE MAKE SENSE:::::::\n" . $toExec);
            $output = exec($toExec);
            //logInfo("*************************************\n************************************\n********************************************************\n" . $output1);
            if(strcmp($output, $testcase_result) <> 0){ //might have to do strcmp
                logInfo("LOST POINTS FOR testcase_" . $i);
                logInfo($output . " " . $testcase_result);
                $testcasePoints += $points4Else;
                logInfo("FIRST TIME:: $output : $testcase_result");
                $question = array_merge($question, array("pass_testcase_$i"=>$testcasePoints));
            }
        }

        //Place all array_merges here


        //Execute the File and Compare Results
        /*$toExec = escapeshellcmd("/afs/cad/linux/anaconda3.6/anaconda/bin/python test.py $testcase_input_1");
        //$toExec = escapeshellcmd("/afs/cad/linux/anaconda3.6/anaconda/bin/python test.py 1");
        //logInfo("PLEASE MAKE SENSE:::::::\n" . $toExec);
        $output1 = exec($toExec);
        //logInfo("*************************************\n************************************\n********************************************************\n" . $output1);
        if(strcmp($output1, $testcase_result_1) <> 0){ //might have to do strcmp
            $totalScore -= 2;
            logInfo("FIRST TIME:: $output1 : $testcase_result_1");
        }
        $toExec = escapeshellcmd("/afs/cad/linux/anaconda3.6/anaconda/bin/python test.py $testcase_input_2");
        $output2 = exec($toExec);
        //logInfo("************************************\n" . $testcase_input_2 .":". $output2);
        if( strcmp($output2, $testcase_result_2) <> 0){
            $totalScore -= 2;
            logInfo("FIRST TIME:: $output2 : $testcase_result_2");
        }
        $toExec = escapeshellcmd("/afs/cad/linux/anaconda3.6/anaconda/bin/python test.py $testcase_input_3");
        $output3 = exec($toExec);
        //logInfo("************************************\n" . $testcase_input_2 .":". $output2);
        if( strcmp($output3, $testcase_result_3) <> 0){
            $totalScore -= 2;
            logInfo("FIRST TIME:: $output3 : $testcase_result_3");
        }
        $toExec = escapeshellcmd("/afs/cad/linux/anaconda3.6/anaconda/bin/python test.py $testcase_input_4");
        $output4 = exec($toExec);
        //logInfo("************************************\n" . $testcase_input_2 .":". $output2);
        if( strcmp($output4, $testcase_result_4) <> 0){
            $totalScore -= 2;
            logInfo("FIRST TIME:: $output4 : $testcase_result_4");
        }
        $toExec = escapeshellcmd("/afs/cad/linux/anaconda3.6/anaconda/bin/python test.py $testcase_input_5");
        $output5 = exec($toExec);
        //logInfo("************************************\n" . $testcase_input_2 .":". $output2);
        if( strcmp($output5, $testcase_result_5) <> 0){
            $totalScore -= 2;
            logInfo("FIRST TIME:: $output5 : $testcase_result_5");
        }
        $toExec = escapeshellcmd("/afs/cad/linux/anaconda3.6/anaconda/bin/python test.py $testcase_input_6");
        $output6 = exec($toExec);
        //logInfo("************************************\n" . $testcase_input_2 .":". $output2);
        if( strcmp($output6, $testcase_result_6) <> 0){
            $totalScore -= 2;
            logInfo("FIRST TIME:: $output6 : $testcase_result_6");
        }*/

        //Incremement questionCount
        $totalScore -= ($namePoints + $colonPoints + $testcasePoints + $constraintPoints);
        logInfo($totalScore . " " . $namePoints . " " . $colonPoints .  " " . $testcasePoints . " " . $constraintPoints);
        $question = array_merge($question, array("question_id"=>$question_ID,"answer_points"=>$totalScore));

        $final = array_merge($final, array($question));

        $finalScore += $totalScore;
    }

    //Calculate Grade

    $final = array_merge( array("test_grade" => "$finalScore"),$final);
    //logInfo(json_encode($final));
    $grade2Front = array("test_grade" => "$finalScore");
    //logInfo("FINAL: " . json_encode($final));

    //will
    logInfo(json_encode("poop"));
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
