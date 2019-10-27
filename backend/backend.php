<?php
include 'connect_NJIT.php';
include 'logging.php';


$post = array_keys($_POST);
$p = implode(" ",$post);
$p = unserialize($p);

//logInfo($p);
//logInfo("KEY: " . unserialize($p));
$dbh = connect_NJIT();

logInfo("TEST: " . $p['rtype']);
$rtype = $_POST['rtype'];

if(strcmp($p,"cgrade") == 0){
  $rtype = "cgrade";
}


switch($rtype){
//USER
//***********************
case "ruser":	 
    echo ruser($dbh);
    break;
case "cuser":
    break;
    

//EXAM
//***********************
case "ctest":
    echo ctest($dbh);
    break;

case "rtest":
    echo rtest($dbh);
    break;

    case "utest":
    break;
case "rtesting":
     echo rtesting($dbh);
     break;
case "dtest":
    echo dtest($dbh);
    break;
case "rexams":
    echo rexams($dbh);
    break;
//QUESTION
//***********************
    case "cquestion":
        echo cquestion($dbh);
    break;
	case "rquestion":
    echo rquestion($dbh);
	     break;
    case "uquestion":
        echo uquestion($dbh);
    break;

	case "dquestion":
       echo dquestion($dbh);
	     break;
//QUESTION
//***********************

case "cgrade":
     echo cgrade($dbh,$p);
     break;
case "rgrades":
     echo rgrades($dbh);
     break;
case "rstudentgrades":
    echo rstudentgrades($dbh);
    break;
     default:
     logInfo("info");
     break;
}
//rgrades($dbh)

//GENERAL FUNCTIONS
//delete row
function deleterow($dbh, $table, $condition){
   logInfo("$condition :: $table");
   $q = substr($table,2,-1). "_id = '$condition'";

   $query = "DELETE FROM $table WHERE $q";
   logInfo($query);
   if(mysqli_query($dbh,$query)){
   logInfo(json_encode(array("Row $condition deleted from $table")));
       return json_encode(array("Row $condition deleted from $table"));   
   }
   else{
    logInfo(json_encode(array("FAILED to delete Row $condition from $table")));
       return json_encode(array("FAILED to delete Row $condition from $table"));
   }
}

//clear table
function cleartable($dbh, $table){
    $query = "DELETE * FROM $table";
   if(mysqli_query($dbh,$query)){
       $query = "ALTER TABLE $table AUTO_INCREMENT = 1";
       mysqli_query($dbh,$query);
       return json_encode(arry("All Rows deleted from $table"));   
   }
   else{
       return json_encode(array("FAILED to delete Rows from $table"));
   }
}

function clearDB($dbh){
    cleartable($dbh, "t_users");
    cleartable($dbh, "t_questions");
    cleartable($dbh, "t_testcases");
    cleartable($dbh, "t_tests");
    cleartable($dbh, "b_testquestions");
    cleartable($dbh, "b_assignedtests");
    
}


//USER FUNCTIONS
function ruser($dbh){
	$table = "t_users";
	$userName = $_POST['user_name'];	
	$password = $_POST['user_password'];
	$password = password_hash($password, PASSWORD_DEFAULT);

	$select = "SELECT user_name, user_password, user_type FROM t_users WHERE user_name =  '$userName' ";
	$q = mysqli_query($dbh, $select);
  
	$row = mysqli_fetch_array($q, MYSQLI_ASSOC);
	logInfo( is_null($row) . " : " . password_verify($_POST['user_password'], $row['user_password']));
	if (is_null($row) == FALSE && password_verify($_POST['user_password'], $row['user_password']) == TRUE){
		$json = array('rtype' => 'ruser', 'user_type' => $row['user_type']);
    logInfo("ERROR " . mysqli_error($dbh) . json_encode($json));
		return json_encode($json); 
	}
	else{
		logInfo("fail");
		$json = array('rtype' => 'ruser', 'user_type' => -1);
		return json_encode($json);
	}	 

}


//QUESTION FUNCTIONS
function cquestion($dbh){
	 $t_questions = "t_questions";
	 $t_testcases = "t_testcases";
	 $question_name = $_POST['question_name'];
	 $question_body = $_POST['question_body'];
	 $question_difficulty = $_POST['question_difficulty'];
	 $testcase_input1 = $_POST['input_one'];
	 $testcase_result1 = $_POST['result_one'];
	 $testcase_input2 = $_POST['input_two'];
	 $testcase_result2 = $_POST['result_two'];

//QUESTION INSERT	 
	 $query = "INSERT INTO $t_questions (question_name, question_body, question_difficulty) VALUES ( '$question_name' , '$question_body' , '$question_difficulty' )";
		  
	 if(mysqli_query($dbh, $query)){
       logInfo($query);
       $testcase_question_id = mysqli_insert_id($dbh);
		   logInfo($testcase_question_id);
   }
	 else{
       logInfo($query . " DIDNT WORK 1");
		   $json = array('rtype' => 'cquestion', 'question_name' => 'FAILED');
		   return json_encode($json);
	 }

//TESTCASE INSERT
	 $query = "INSERT INTO $t_testcases (testcase_question_id, testcase_input, testcase_result) VALUES ( '$testcase_question_id' , '$testcase_input1' , '$testcase_result1')";

	 if(mysqli_query($dbh, $query)){
	     logInfo($query);
   }
	 else{
	     logInfo($query . " DIDNT WORK 2");
		   $json = array("QUERY FAIL::\n $query");
		   logInfo(json_encode($json));
		   return json_encode($json);
	 }


	 $query = "INSERT INTO $t_testcases (testcase_question_id, testcase_input, testcase_result) VALUES ( '$testcase_question_id' ,'$testcase_input2' , '$testcase_result2')";

	 if(mysqli_query($dbh, $query)){
	     logInfo($query);
	 	   $json = array('rtype' => 'cquestion', 'question_name' => $question_name);
		   return json_encode($json);
   }
	 else{
       logInfo($query . " DIDNT WORK 3");
		   $json = array("QUERY FAIL::\n $query");
		   logInfo(json_encode($json));
		   return json_encode($json);
	 }
}


function rquestion($dbh){
//return json_encode(array("hello"=> "hi"));
	 $t_questions = "t_questions";
	 $t_testcases = "t_testcases";
  
   $questions = "SELECT * FROM $t_questions";
   $questionrows = mysqli_query($dbh, $questions);
	 
	 
	 $json = array();
	 while( ($qrow = mysqli_fetch_array($questionrows)) <> NULL ){
  //BUILD Question array [name,body,difficulty] for each question
  $questionArray = array();
  $q_id   = array('question_id' => $qrow['question_id']);
  $q_name = array('question_name' => $qrow['question_name']);
  $q_body = array('question_body' => $qrow['question_body']);
  $q_diff = array('question_difficulty' => $qrow['question_difficulty']);
       $questionArray = array_merge($questionArray,$q_id);
       $questionArray = array_merge($questionArray,$q_name);
       $questionArray = array_merge($questionArray,$q_body);
       $questionArray = array_merge($questionArray,$q_diff);
       $id = $qrow['question_id'];
	 	   $testcases = "SELECT * FROM $t_testcases WHERE testcase_question_id = '$id'";
		   $testcaserows = mysqli_query($dbh, $testcases);  
       $i = 1;
   while( ($trow = mysqli_fetch_array($testcaserows)) <> NULL){
   //ADD to Question array [name,body,difficulty,testcase_input, testcase_result]  for each testcase
           $t_in = array("testcase_input_$i" => $trow['testcase_input']);
           $t_out = array("testcase_result_$i" =>  $trow['testcase_result']);
           //array_push($questionArray, $t_in, $t_out);
           $questionArray = array_merge($questionArray, $t_in);
           $questionArray = array_merge($questionArray, $t_out);
           $i += 1;
   }
       //create $json array with [question_id => Question array]
       //logInfo(json_encode($questionArray));
       $result .= json_encode($questionArray) . "\n";
	     array_push($json, $questionArray);
   }
   logInfo($result);
	 return json_encode($json); 
}

function uquestion($dbh){
    $q_name = $_POST['question_name'];
    $q_body = $_POST['question_body'];
    $q_id   = $_POST['question_id'];
    $q_diff = $_POST['question_difficulty'];

    $query = "UPDATE t_questions SET question_name = '$q_name', question_body = '$q_body', question_difficulty = '$q_diff' WHERE question_id = $q_id";

    mysqli_query($dbh, $query);
    $testcases = "SELECT * FROM t_testcases WHERE testcase_question_id = '$q_id'";
   	$testcaserows = mysqli_query($dbh, $testcases);  
       $i = 1;
   while( ($trow = mysqli_fetch_array($testcaserows)) <> NULL){
           //array_push($questionArray, $t_in, $t_out);
           $t_in = "testcase_input_$i";
           $t_out = "testcase_result_$i";
           //$query = "UPDATE t_testcases SET testcase_input='$_POST[$t_in]', testcase_result = '$_POST[$t_out] WHERE testcase_question_id = $_POST['question_id']";
           mysqli_query($dbh, $query);
           $i += 1;
   }
   logInfo(json_encode(array("Successfully edited the row")));
   return json_encode(array("Successfully edited the row"));
}



function dquestion($dbh){
    $question_id = $_POST['question_id'];
    logInfo("CHECKING THIS");
    deleterow($dbh, "t_questions", $question_id);
    $query = "SELECT testcase_id FROM t_testcases WHERE testcase_question_id = '$question_id'";
    $rows = mysqli_query($dbh, $query);
    foreach( $rows as $row){
      deleterow($dbh, "t_testcases", $row['testcase_id']);
    }
    logInfo("Delete Success");
    return json_encode(array("SUCCESS"));
}



//TEST FUNCTIONS
function ctest($dbh){

  $t_tests = "t_tests";
  $b_testquestions = "b_testquestions ";
  $test_name = $_POST['exam_name'];
  $test_questions = count($_POST) - 2;
  
  $query = "INSERT INTO $t_tests (test_name, test_questions) VALUES ( '$test_name' , '$test_questions')";
  if(mysqli_query($dbh, $query)){
      logInfo("CREATING A TEST");
  }
else{
      logInfo("FAILED to create test: $test_questions");
}
  $test_id = mysqli_insert_id($dbh);
  $question_ids = $_POST;
  logInfo("QUESTION IDS::::". json_encode($question_ids));
  $i =0;
  
    foreach( $question_ids as $question_id=>$question){
    if($i == $test_questions){
        break;
    }
    logInfo("QUESTION ID: $question");
    
    $query2 = "INSERT INTO $b_testquestions (test_id, question_id) VALUES ( '$test_id', '$question');";
    mysqli_query($dbh, $query2);
    $i++;
  }


  $query3 = "SELECT user_id FROM t_users WHERE user_type = '0'";

if(  ($students = mysqli_query($dbh, $query3)) <> NULL){
     logInfo("yes");
}
else{
     logInfo(mysqli_error());
}

  while( ($student = mysqli_fetch_array($students))){
      $student_id = $student['user_id'];
      logInfo("STUDENT ID: $student_id");
      $query4 = "INSERT INTO b_assignedtests (user_id, test_id, test_complete, test_grade) VALUES ( '$student_id' , '$test_id' , '0','0')";
      logInfo($query4);
      if(mysqli_query($dbh, $query4))
        continue;
      else
        logInfo(mysqli_error($dbh));
  }
    return json_encode(array("SUCCESS"));  

}


////////////////////////////////////////




function rtest($dbh){
   $t_tests = "t_tests";
   $b_testquestions = "b_testquestions";
   $t_questions = "t_questions";
   $t_testcases = "t_testcases";
   $arr = array_keys($_POST);
//   $test_id = $arr[0];
   $test_id = $_POST['test_id'];

//logInfo(json_encode($test_id));

   $questions = "SELECT * FROM $t_questions WHERE question_id  IN ( SELECT question_id FROM $b_testquestions WHERE test_id = $test_id)";



   $q = mysqli_query($dbh, $questions);
   logInfo(mysqli_error());
   $questionrows = array();
   while( ($question =  mysqli_fetch_array($q)) <> NULL){
      array_push($questionrows, $question);
  }
  logInfo(mysqli_error());
   
   $json = array();
   foreach($questionrows as $questionbody){

       $question_id = $questionbody['question_id'];
       $question_body = $questionbody['question_body'];
       $questionToBeSent = array("question_id"=>"$question_id", "question_body"=>"$question_body");
       array_push($json, $questionToBeSent);
   }
   logInfo(json_encode($json));
   return json_encode($json);
}
///////////////////////////////////////









/*
function rtest($dbh){
   $t_tests = "t_tests";
   $b_testquestions = "b_testquestions";
	 $t_questions = "t_questions";
	 $t_testcases = "t_testcases";
   $test_id = $_POST['test_id'];
   
  
   $questions = "SELECT * FROM $t_questions WHERE question_id  IN ( SELECT question_id FROM $b_testquestions WHERE test_id = $test_id)";
	 $questionrows = mysqli_query($dbh, $questions);
	 
	 
	 $json = array();
	 while( ($qrow = mysqli_fetch_array($questionrows)) <> NULL ){
  //BUILD Question array [name,body,difficulty] for each question
  $questionArray = array();
  $q_id   = array('question_id' => $qrow['question_id']);
  $q_name = array('question_name' => $qrow['question_name']);
  $q_body = array('question_body' => $qrow['question_body']);
  $q_diff = array('question_difficulty' => $qrow['question_difficulty']);
       //array_push($questionArray, $q_id, $q_name, $q_body, $q_diff);
       $questionArray = array_merge($questionArray,$q_id);
       $questionArray = array_merge($questionArray,$q_name);
       $questionArray = array_merge($questionArray,$q_body);
       $questionArray = array_merge($questionArray,$q_diff);
       $id = $qrow['question_id'];
       
       $id = $qrow['question_id'];
       $testcases = "SELECT * FROM $t_testcases WHERE testcase_question_id = '$id'";
       $testcaserows = mysqli_query($dbh, $testcases);  
   while( ($trow = mysqli_fetch_array($testcaserows)) <> NULL){
   //ADD to Question array [name,body,difficulty,testcase_input, testcase_result]  for each testcase
           $t_in = array("testcase_input_$i" => $trow['testcase_input']);
           $t_out = array("testcase_result_$i" =>  $trow['testcase_result']);
           //array_push($questionArray, $t_in, $t_out);
           $questionArray = array_merge($questionArray, $t_in);
           $questionArray = array_merge($questionArray, $t_out);
           $i += 1;
   }
	     array_push($json, $newVal);
   }

	 return json_encode($json); 
}
*/

function dtest($dbh){
    deleterow($dbh, "t_tests", $_POST['test_id']);
    $query = "DELETE FROM b_assignedtests WHERE test_id = " . $_POST['test_id'];
    if(mysqli_query($dbh, $query)){
        return json_encode(array("Test successfully removed from the DB"));
    }
    else{
	return json_encode(array("FAILED to delete row from DB"));
    }
    
}

function rexams($dbh){
  $query = "SELECT test_id, test_name FROM t_tests";
  $exams = mysqli_query($dbh, $query);

  $json = array();
  while( ($exam = mysqli_fetch_array($exams)) <> NULL){
    $test_id = array('test_id' => $exam['test_id']);
    $test_name = array('test_name' => $exam['test_name']);
    $test = array_merge($test_id,$test_name);
    array_push($json, $test);    
  }
  logInfo(json_encode($json));
  return json_encode($json);
}
///////////////////////////////


function rtesting($dbh){
logInfo("rtesting");
   $t_tests = "t_tests";
   $b_testquestions = "b_testquestions";
   $t_questions = "t_questions";
   $t_testcases = "t_testcases";
   $test_id = $_POST['test_id'];
  
   $questions = "SELECT * FROM $t_questions WHERE question_id  IN ( SELECT question_id FROM $b_testquestions WHERE test_id = $test_id)";
   $q = mysqli_query($dbh, $questions);
   
   $questionrows = array();
   while( ($question =  mysqli_fetch_array($q)) <> NULL){
//WORKS      logInfo(json_encode($question));
      array_push($questionrows, $question);
  }
   
   $json = array();
   foreach($questionrows as $questionbody){

       $question_id = $questionbody['question_id'];
       $question_name = $questionbody['question_name'];
       $questionToBeSent = array("question_id"=>"$question_id", "question_name"=>"$question_name");
       $query = "SELECT * FROM t_testcases WHERE testcase_question_id = '$question_id'";
       $testcases = mysqli_query($dbh,$query); 
       
       $i = 1;
       while( ($testcase = mysqli_fetch_array($testcases)) <> NULL){
       	      $testcase_input = $testcase['testcase_input'];
       	      $testcase_result = $testcase['testcase_result'];
	      $test = array("testcase_input_$i"=> $testcase_input,"testcase_result_$i"=> $testcase_result);
       	      $questionToBeSent = array_merge($questionToBeSent,$test);
	      $i++;
       }

       array_push($json, $questionToBeSent);
   }
   logInfo(json_encode($json));
   return json_encode($json);
}


function cgrade($dbh,$data){

	 $test_id = $data['test_id'];
	 $user_name = $data['user_name'];
	 $test_grade = $data['test_grade'];
	 $questions = array_slice($data,4);
   logInfo($test_grade);
   logInfo($user_name);
	 $query = "SELECT user_id FROM t_users WHERE user_name = '$user_name'";
   $users = mysqli_query($dbh,$query);
   $user = mysqli_fetch_array($users);
	 $user_id = $user['user_id'];
   logInfo($user_id);
	 foreach($questions as $question){
       
       $question = (array)$question;
	     $question_id = $question['question_id'];
       $answer_code = $question['answer_code'];
       $answer_points = $question['answer_points'];
       logInfo($question_id);
       logInfo($answer_code);
       logInfo($answer_points);

	     $query = "INSERT INTO t_answers ( answer_test_id, answer_question_id, answer_user_id, answer_code, answer_comment, answer_points) VALUES ( '$test_id', '$question_id', '$user_id', '$answer_code', '', '$answer_points')";
	     mysqli_query($dbh,$query);
      logInfo("ERROR:: " . mysqli_error($dbh));
	 }

	 $query = "UPDATE b_assignedtests SET test_grade='$test_grade', test_complete='1' WHERE (test_id='$test_id') AND (user_id='$user_id')";
   mysqli_query($dbh,$query);
   logInfo("ERROR:: " . mysqli_error($dbh));
   
	 return json_encode(array("EXAM FINISHED"));
}


function rgrades($dbh){
logInfo("RGRADES");

$query = "SELECT * FROM b_assignedtests";
$q = mysqli_query($dbh, $query);


$grades = array();
while(($grade = mysqli_fetch_array($q)) <> NULL){
    array_push($grades,$grade);
}
$json = array();
foreach($grades as $grade){
  $user_id = $grade['user_id'];
  
  $query2 = "SELECT user_name FROM t_users WHERE user_id = '$user_id'";
  $u = mysqli_query($dbh,$query2);
  logInfo(mysqli_error($dbh));
  $user = mysqli_fetch_array($u);
  $user_name = $user['user_name'];
  $test_id = $grade['test_id'];
  $query3 = "SELECT test_name FROM t_tests WHERE test_id = '$test_id'";
  $t = mysqli_query($dbh, $query3);
  logInfo(mysqli_error($dbh));
  
  while(($tests = mysqli_fetch_array($t))<>NULL){
    $test_name = $tests['test_name'];
    
    array_push($json, array("user_id"=>$user_id,"user_name"=>$user_name,"test_id"=>$test_id, "test_name"=>$test_name,"test_grade"=>$grade['test_grade']));
    logInfo("JSON: " . json_encode($json));
  }
  

}
  logInfo(json_encode($json));
  return json_encode($json);
}

function rstudentgrades($dbh){

$user_name = $_POST['user_name'];


//$user_id = 2;
$query2 = "SELECT user_id FROM t_users WHERE user_name = '$user_name'";
$u = mysqli_query($dbh,$query2);
logInfo(mysqli_error($dbh));
$user = mysqli_fetch_array($u);
$user_id = $user['user_id'];

logInfo("$user_id");

$query = "SELECT test_id FROM b_assignedtests WHERE user_id = '$user_id'";
$q = mysqli_query($dbh, $query);

$grades = array();
while(($grade = mysqli_fetch_array($q)) <> NULL){
    array_push($grades,$grade);
}

$json = array();
foreach($grades as $grade){


  $test_id = $grade['test_id'];
  $query3 = "SELECT test_name FROM t_tests WHERE test_id = '$test_id'";
  $t = mysqli_query($dbh, $query3);
  logInfo(mysqli_error($dbh));
  
  while(($tests = mysqli_fetch_array($t))<>NULL){
    $test_name = $tests['test_name'];
    if($grade['test_grade'] == NULL){
          $tgrade = "0";
    }
    else{
    $tgrade = $grade['test_grade'];
    }
    
    array_push($json, array("user_id"=>$user_id,"user_name"=>$user_name,"test_id"=>$test_id, "test_name"=>$test_name,"test_grade"=>$tgrade));
    logInfo("JSON: " . json_encode($json));
  }
  

}
  logInfo(json_encode($json));
  return json_encode($json);
}
?>