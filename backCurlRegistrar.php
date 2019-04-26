<?php

/* backCurlRegistrar.php */

// Getting post data
// Initialize
$response;
$message = "";

// Data
$data = $_POST['data'];
if($data == null){ $message .= "Data error"; }
$decode = json_decode($data);
$curlid = $decode->curlid;
$message = "";
// Setup connection for DB
$conn = mysqli_connect('sql1.njit.edu', 'rap86', 'VaiqcQipJ', 'rap86');
if(mysqli_connect_errno()) {
  echo 'Failed to connect to MySQL ' . mysqli_connect_errno();
}


switch($curlid){
  // Create question curl
	case "createquestion":
	  // Get all data to add a question to the database
    $question   = $decode->questionData->question;
    $fname      = $decode->questionData->functionName;
    $difficulty = $decode->questionData->difficulty;
    $topic      = $decode->questionData->topic;
    $constraint = $decode->questionData->constraint;
    $case1      = $decode->questionData->case1;
    $rcase1     = $decode->questionData->rcase1;
    $case2      = $decode->questionData->case2;
    $rcase2     = $decode->questionData->rcase2;
    $case3      = $decode->questionData->case3;
    $rcase3     = $decode->questionData->rcase3;
    $case4      = $decode->questionData->case4;
    $rcase4     = $decode->questionData->rcase4;
    $case5      = $decode->questionData->case5;
    $rcase5     = $decode->questionData->rcase5;
    $case6      = $decode->questionData->case6;
    $rcase6     = $decode->questionData->rcase6;
    // Null Check
    if(is_null($sessionID))   { $message .= "SessionID is Not Defined \n"; }
		if(is_null($question))    { $message .= "Question is Not Defined \n"; }
		if(is_null($fname))       { $message .= "Function Name is Not Defined \n"; }
		if(is_null($difficulty))  { $message .= "Difficulty is Not Defined \n"; }
		if(is_null($topic))       { $message .= "Topic is Not Defined \n"; }
		if(is_null($constraint))  { $message .= "Constraint is Not Defined \n"; }
		if(is_null($case1))       { $message .= "Case1 is Not Defined \n"; $case1=""; }
		if(is_null($rcase1))      { $message .= "Case1 Result is Not Defined \n"; $rcase1="";}
		if(is_null($case2))       { $message .= "Case2 is Not Defined \n"; $case2=""; }
		if(is_null($rcase2))      { $message .= "Case2 Result is Not Defined \n"; $rcase2="";}
		if(is_null($case3))       { $message .= "Case3 is Not Defined \n"; $case3=""; }
		if(is_null($rcase3))      { $message .= "Case3 Result is Not Defined \n"; $rcase3="";}
		if(is_null($case4))       { $message .= "Case4 is Not Defined \n"; $case4=""; }
		if(is_null($rcase4))      { $message .= "Case4 Result is Not Defined \n"; $rcase4="";}
		if(is_null($case5))       { $message .= "Case5 is Not Defined \n"; $case5=""; }
		if(is_null($rcase5))      { $message .= "Case5 Result is Not Defined \n"; $rcase5="";}
		if(is_null($case6))       { $message .= "Case6 is Not Defined \n"; $case6=""; }
		if(is_null($rcase6))      { $message .= "Case6 Result is Not Defined \n"; $rcase6="";}
   
    $query = "INSERT INTO questionBank (question, functionname, difficulty, topic, `constraint`, case1, rcase1, case2, rcase2, case3, rcase3, case4, rcase4, case5, rcase5, case6, rcase6)"; 
    $query .= "VALUES ('$question', '$fname', '$difficulty', '$topic', '$constraint', '$case1', '$rcase1', '$case2', '$rcase2', '$case3', '$rcase3', '$case4', '$rcase4', '$case5', '$rcase5', '$case6', '$rcase6')";
 
    // Add the Question
    $jsonResponse;
    if( mysqli_query($conn, $query) ){
			$jsonResponse->dbquestionAdd = "1";
		}
		else { echo 'ERROR: ' . mysqli_error($conn); }
    
    // Clear memory
		mysqli_free_result($result);
		mysqli_close($conn);

    $jsonResponse->backdebugmessage = $message;
    $jsonResponse->dbresponse = "2";
		echo json_encode($jsonResponse);
		break;
  
  // Get questions
  case "getquestion":
    // Get all data needed for curl
    $topic      = $decode->search->topic;
		$difficulty = $decode->search->difficulty;
		$keyword    = $decode->search->keyword;
    $query = "SELECT * from questionBank WHERE ";
    $count = 0;
    // Null Check
		if(is_null($keyword))  { $message .= "Keyword is Not Defined \n"; }
		if(is_null($difficulty)) { $message .= "Difficulty is Not Defined \n"; }
		if(is_null($topic)){ $message .= "Topic is Not Defined \n"; }
   
    // Keyword Check
    if($keyword!=''){
      $query .= "question LIKE '%$keyword%' ";
      $count .=  1;
    }
    // Topic Check
    if($topic!=''){
      if($count>0){
        $query .= "AND topic LIKE '%$topic%' ";
      }
      else{
        $query .= "topic LIKE '%$topic%' ";
      }
      $count .=  1;
    }
    // Difficulty Check
    if($difficulty!=''){
      if($count>0){
        $query .= "AND difficulty LIKE '%$difficulty%' ";
      }
      else{
        $query .= "difficulty LIKE '%$difficulty%' ";
      }
      $count .=  1;
    }
    // Adding 10 Question Limit
    $query .= "LIMIT 10";
    // No Search Check - DEFAULT QUERY
    if($keyword=='' && $topic=='' && $difficulty==''){
      $query = "SELECT * FROM questionBank LIMIT 10";
    }
    
    // Return data correctly
    $questionData = [];
    $s = mysqli_query($conn, $query) or die("Bad SQL: " . mysqli_error($conn));
    while ($r = $s->fetch_assoc()) 
		{
			$questionName = $r['question'];
      $qdata["qname"] = $questionName;
  	  array_push($questionData, $qdata);
		}
   
    // Clear Memory
		mysqli_free_result($s);
		mysqli_close($conn);
   
    // Convert Data to JSON for response
    $jsonResponse->backdebugmessage = $message . " \n " . $query;
    $jsonResponse->dbresponse = "2";
    $jsonResponse->dbQuestions = $questionData;
		echo json_encode($jsonResponse);
    break;
  
  // Create test
  case "createtest":
    // Get all data needed for curl
		$sessionID  = $decode->sessionID;
		$maxScore   = $decode->testmaxscore;
		$testName   = $decode->testname;
    $errorCount = 0;
		$message = "";
    $jsonResponse;
    // Null Check
		if (is_null($sessionID)) $message .= "sessionID is not defined\n";
		if (is_null($maxScore)) $message .= "testmaxscore is not defined\n";
		if (is_null($testName)) $message .= "testname is not defined\n";

		$questions = [];
		$worths = [];
		for ($i = 0; $i < sizeof($decode->test->questions); $i++)
		{	
			array_push($questions, mysqli_real_escape_string($conn, $decode->test->questions[$i]));
			array_push($worths, $decode->test->worths[$i]);
		}
   
    // Creating data for tables
    for($i=1; $i<=3; $i++){
      $user = 'user' . $i;
      $query   = "INSERT INTO testTable (testname, status, user, maxscore, grade) VALUES ('$testName', 'posted', '$user', '$maxScore', 0)";
      // Run query1
      if( mysqli_query($conn, $query) ){
        // Do nothing - Continue
		  }
		  else { echo 'ERROR: ' . mysqli_error($conn); $errorCount .= 1; }
      
      // For each question add an entry
      foreach($questions as $key => $value){
        $worth    = $worths[$key];
        $question = $value;
        $query2   = "INSERT INTO questionTable (testname, question, worth, feedback, user, answer, earnedpoints, dcase1, dcase2, dcase3, dcase4, dcase5, dcase6)";
        $query2  .= " VALUES ('$testName', '$question', '$worth', '', '$user', '', 0, 0, 0, 0, 0, 0, 0)";
        // Run query2
        if( mysqli_query($conn, $query2) ){
          // Do nothing - Continue
		    }
		    else { echo 'ERROR: ' . mysqli_error($conn); $errorCount .= 1; } 
      }  
    }
    if($errorCount<=0){
      $jsonResponse->dbtestAdd = "1";
    }
    else{ $jsonResponse->dbtestAdd = "0"; }
    
    // Clear Memory
		mysqli_free_result($result);
		mysqli_close($conn);
   
    // Convert data to JSON for response
    $jsonResponse->backdebugmessage = $message . " \n ";
    $jsonResponse->dbresponse = "2";
		echo json_encode($jsonResponse);
    break;
  
  // Get tests
  case "gettest":
    // Setup for test structure
    $tests = [];
    // Getting test information
    $s2 = "SELECT * FROM testTable";
    ($t2 = mysqli_query($conn, $s2)) or die (mysqli_error( $conn ));
    while($r = $t2->fetch_assoc()){
      // Get row data
      $testname = $r['testname'];
      $status   = $r['status'  ];
      $user     = $r['user'    ];
      $maxscore = $r['maxscore'];
      $grade    = $r['grade'   ];
      // Use data to construct json obj
      $tests[$testname][$user]['grade']    = $grade;
      $tests[$testname][$user]['maxscore'] = $maxscore;
      $tests[$testname][$user]['status']   = $status;
      $testnames[] = $testname;
    }
    $s3 = "SELECT * FROM questionTable";
    ($t3 = mysqli_query($conn, $s3)) or die (mysqli_error( $conn ));
    while($r = $t3->fetch_assoc()){
      // Get row data
      $testname     = $r['testname'     ];
      $question     = $r['question'     ];
      $worth        = $r['worth'        ];
      $FB           = $r['feedback'     ];
      $autoFB       = $r['autofeedback' ];
      $user         = $r['user'         ];
      $answer       = $r['answer'       ];
      $earnedpoints = $r['earnedpoints' ];
      $df           = $r['dfname'       ];
      $dc           = $r['dconstraint'  ];
      $d1           = $r['dcase1'       ];
      $d2           = $r['dcase2'       ];
      $d3           = $r['dcase3'       ];
      $d4           = $r['dcase4'       ];
      $d5           = $r['dcase5'       ];
      $d6           = $r['dcase6'       ];
      $sr1          = $r['srcase1'      ];
      $sr2          = $r['srcase2'      ];
      $sr3          = $r['srcase3'      ];
      $sr4          = $r['srcase4'      ];
      $sr5          = $r['srcase5'      ];
      $sr6          = $r['srcase6'      ];
      // Use data to construct json obj
      $tests[$testname][$user]['questions'][$question]['earnedpoints']    = $earnedpoints;
      $tests[$testname][$user]['questions'][$question]['autofeedback']    = $autoFB;
      $tests[$testname][$user]['questions'][$question]['studentanswer']   = $answer;
      $tests[$testname][$user]['questions'][$question]['teacherfeedback'] = $FB;
      $tests[$testname][$user]['questions'][$question]['worth']           = $worth;
      $tests[$testname][$user]['questions'][$question]['dfname']          = $df;
      $tests[$testname][$user]['questions'][$question]['dconstraint']     = $dc;
      $tests[$testname][$user]['questions'][$question]['dcase1']          = $d1;
      $tests[$testname][$user]['questions'][$question]['dcase2']          = $d2;
      $tests[$testname][$user]['questions'][$question]['dcase3']          = $d3;
      $tests[$testname][$user]['questions'][$question]['dcase4']          = $d4;
      $tests[$testname][$user]['questions'][$question]['dcase5']          = $d5;
      $tests[$testname][$user]['questions'][$question]['dcase6']          = $d6;
      $tests[$testname][$user]['questions'][$question]['srcase1']         = $sr1;
      $tests[$testname][$user]['questions'][$question]['srcase2']         = $sr2;
      $tests[$testname][$user]['questions'][$question]['srcase3']         = $sr3;
      $tests[$testname][$user]['questions'][$question]['srcase4']         = $sr4;
      $tests[$testname][$user]['questions'][$question]['srcase5']         = $sr5;
      $tests[$testname][$user]['questions'][$question]['srcase6']         = $sr6;
      
      $s4 = "SELECT * FROM questionBank WHERE question='$question'";
      ($t4 = mysqli_query($conn, $s4)) or die (mysqli_error( $conn ));
      while($r = $t4->fetch_assoc()){
        // Get row data
        $question2    = $r['question'     ];
        $fname        = $r['functionname' ];
        $diff         = $r['difficulty'   ];
        $topic        = $r['topic'        ];
        $constraint   = $r['constraint'   ];
        $case1        = $r['case1'        ];
        $rcase1       = $r['rcase1'       ];
        $case2        = $r['case2'        ];
        $rcase2       = $r['rcase2'       ];
        $case3        = $r['case3'        ];
        $rcase3       = $r['rcase3'       ];
        $case4        = $r['case4'        ];
        $rcase4       = $r['rcase4'       ];
        $case5        = $r['case5'        ];
        $rcase5       = $r['rcase5'       ];
        $case6        = $r['case6'        ];
        $rcase6       = $r['rcase6'       ];
        // Use data to construct json obj
        $tests[$testname][$user]['questions'][$question2]['qdata']['qname']      = $question;
        $tests[$testname][$user]['questions'][$question2]['qdata']['fname']      = $fname;
        $tests[$testname][$user]['questions'][$question2]['qdata']['difficulty'] = $diff;
        $tests[$testname][$user]['questions'][$question2]['qdata']['topic']      = $topic;
        $tests[$testname][$user]['questions'][$question2]['qdata']['constraint'] = $constraint;
        $tests[$testname][$user]['questions'][$question2]['qdata']['case1']      = $case1;
        $tests[$testname][$user]['questions'][$question2]['qdata']['rcase1']     = $rcase1;
        $tests[$testname][$user]['questions'][$question2]['qdata']['case2']      = $case2;
        $tests[$testname][$user]['questions'][$question2]['qdata']['rcase2']     = $rcase2;
        $tests[$testname][$user]['questions'][$question2]['qdata']['case3']      = $case3;
        $tests[$testname][$user]['questions'][$question2]['qdata']['rcase3']     = $rcase3;
        $tests[$testname][$user]['questions'][$question2]['qdata']['case4']      = $case4;
        $tests[$testname][$user]['questions'][$question2]['qdata']['rcase4']     = $rcase4;
        $tests[$testname][$user]['questions'][$question2]['qdata']['case5']      = $case5;
        $tests[$testname][$user]['questions'][$question2]['qdata']['rcase5']     = $rcase5;
        $tests[$testname][$user]['questions'][$question2]['qdata']['case6']      = $case6;
        $tests[$testname][$user]['questions'][$question2]['qdata']['rcase6']     = $rcase6;
      }   
    }
    
    // Convert Data to json for response
    $jsonResponse->backdebugmessage = $message . " \n " . $query;
    $jsonResponse->dbresponse = "2";
    $jsonResponse->dbTests = $tests;
		echo json_encode($jsonResponse);
    break;
    
  // Mod test
  case "modtest":
    // Get data needed for curk
		$testname = $decode->testName;
		$modNum   = $decode->modNum;
		$username = $decode->userName;

		$message = "";
		if (is_null($testname)) $message .= "testName is not defined\n";
		if (is_null($modNum)) $message .= "modNum is not defined\n";
		if (is_null($username)) $message .= "userName is not defined\n";
   
    // Query
    if($modNum==0){
      // Delete All data for that test
      $query = "DELETE FROM questionTable WHERE testname='$testname' ";
      $query2 = "DELETE FROM testTable WHERE testname='$testname' ";
      mysqli_query($conn, $query) or die("Bad SQL: $query");
      mysqli_query($conn, $query2) or die("Bad SQL: $query");
    }
    elseif($modNum==1){
      $query = "UPDATE testTable SET status='posted' WHERE testname='$testname' ";
      mysqli_query($conn, $query) or die("Bad SQL: $query");
    }
    elseif($modNum==2){
      // Send test to be autograded
      $query = "UPDATE testTable SET status='pregrade' WHERE testname='$testname' ";
      mysqli_query($conn, $query) or die("Bad SQL: $query");
    }
    else{
      // Send test for grades to be released
      $query = "UPDATE testTable SET status='graded' WHERE testname='$testname' AND user='$username' ";
      mysqli_query($conn, $query) or die("Bad SQL: $query");
    }
    $jsonResponse["backDebugMsg"] = $message;

		mysqli_close($conn);
		echo json_encode($jsonResponse);
    break;
    
  // Update Feedback
  case "updatefeedback":
    $message = "";
    if (isset($decode->dbAutoUpdate))
		{
			$decode = $decode->dbAutoUpdate;
			$testname = $decode->testName;
			$username = $decode->userName;
			$grade = $decode->grade;
      
      // Update total grade for the test
      $query = "UPDATE testTable SET grade='$totalGrade' WHERE testname = '$testname' AND user='$username' ";
			mysqli_query($conn, $query) or die("Bad SQL: $query");
      
      // Update the feedback and earnedpoints
			foreach($decode->questions as $questionName=>$value){
				$autoFeedback = $value->autofeedback;
				$earnedPoints = $value->earnedpoints;
        $dcase1       = $value->dcase1;
				$dcase2       = $value->dcase2;
        $dcase3       = $value->dcase3;
				$dcase4       = $value->dcase4;
        $dcase5       = $value->dcase5;
				$dcase6       = $value->dcase6;
        $srcase1      = $value->srcase1;
				$srcase2      = $value->srcase2;
        $srcase3      = $value->srcase3;
				$srcase4      = $value->srcase4;
        $srcase5      = $value->srcase5;
				$srcase6      = $value->srcase6;
        $dfname       = $value->dfname;
				$dconstraint  = $value->dconstraint;
        
        // Null Check
        if (is_null($teacherFeedback)) { $message .= "teacherfeedback is not defined\n"; $teacherFeedback='';}
		    if (is_null($earnedPoints)) { $message .= "earnedpoints is not defined\n"; $earnedpoints=0;}
		    if (is_null($dcase1)) { $message .= "d1 is not defined\n"; $dcase1=0; }
        if (is_null($dcase2)) { $message .= "d2 is not defined\n"; $dcase2=0; }
		    if (is_null($dcase3)) { $message .= "d3 is not defined\n"; $dcase3=0; }
		    if (is_null($dcase4)) { $message .= "d4 is not defined\n"; $dcase4=0; }
        if (is_null($dcase5)) { $message .= "d5 is not defined\n"; $dcase5=0; }
		    if (is_null($dcase6)) { $message .= "d6 is not defined\n"; $dcase6=0; }
        if (is_null($srcase1)) { $message .= "sr1 is not defined\n"; $srcase1="UNDEFINED"; }
        if (is_null($srcase2)) { $message .= "sr2 is not defined\n"; $srcase2="UNDEFINED"; }
		    if (is_null($srcase3)) { $message .= "sr3 is not defined\n"; $srcase3="UNDEFINED"; }
		    if (is_null($srcase4)) { $message .= "sr4 is not defined\n"; $srcase4="UNDEFINED"; }
        if (is_null($srcase5)) { $message .= "sr5 is not defined\n"; $srcase5="UNDEFINED"; }
		    if (is_null($srcase6)) { $message .= "sr6 is not defined\n"; $srcase6="UNDEFINED"; }
		    if (is_null($dfname)) { $message .= "df is not defined\n"; $dfname=0; }
        if (is_null($dconstraint)) { $message .= "dc is not defined\n"; $dconstraint=0; }

				$query  = "UPDATE questionTable SET autofeedback='$autoFeedback', earnedpoints='$earnedPoints', dcase1='$dcase1', dcase2='$dcase2', dcase3='$dcase3', dcase4='$dcase4', dcase5='$dcase5', dcase6='$dcase6', dconstraint='$dconstraint', dfname='$dfname', srcase1='$srcase1', srcase2='$srcase2', srcase3='$srcase3', srcase4='$srcase4', srcase5='$srcase5', srcase6='$srcase6' ";
        $query .= "WHERE question='$questionName' AND testname='$testname' AND user='$username' ";
				mysqli_query($conn, $query) or die("Bad SQL: $query");
			}
		}
		else if (isset($decode->dbTeacherUpdate))
		{
			$testname = $decode->testname;
			$username = $decode->username;

      $totalGrade = 0;

			foreach($decode->dbTeacherUpdate as $questionName=>$value){
				$teacherFeedback = $value->teacherfeedback;
				$earnedPoints = $value->earnedpoints;
        $totalGrade = $totalGrade + $earnedPoints;
        $dcase1       = $value->d1;
				$dcase2       = $value->d2;
        $dcase3       = $value->d3;
				$dcase4       = $value->d4;
        $dcase5       = $value->d5;
				$dcase6       = $value->d6;
        $dfname       = $value->df;
				$dconstraint  = $value->dc;
        
        // Null Check
        if (is_null($teacherFeedback)) { $message .= "teacherfeedback is not defined\n"; $teacherFeedback='';}
		    if (is_null($earnedPoints)) { $message .= "earnedpoints is not defined\n"; $earnedpoints=0;}
		    if (is_null($dcase1)) { $message .= "d1 is not defined\n"; $dcase1=0; }
        if (is_null($dcase2)) { $message .= "d2 is not defined\n"; $dcase2=0; }
		    if (is_null($dcase3)) { $message .= "d3 is not defined\n"; $dcase3=0; }
		    if (is_null($dcase4)) { $message .= "d4 is not defined\n"; $dcase4=0; }
        if (is_null($dcase5)) { $message .= "d5 is not defined\n"; $dcase5=0; }
		    if (is_null($dcase6)) { $message .= "d6 is not defined\n"; $dcase6=0; }
		    if (is_null($dfname)) { $message .= "df is not defined\n"; $dfname=0; }
        if (is_null($dconstraint)) { $message .= "dc is not defined\n"; $dconstraint=0; }

				$query = "UPDATE questionTable SET feedback='$teacherFeedback', earnedpoints='$earnedPoints', dcase1='$dcase1', dcase2='$dcase2', dcase3='$dcase3', dcase4='$dcase4', dcase5='$dcase5', dcase6='$dcase6', dconstraint='$dconstraint', dfname='$dfname' WHERE question='$questionName' AND testname='$testname' AND user='$username' ";
				if(mysqli_query($conn, $query)){
          $jsonResponse["teacherFB"] = "1";
        }
        else{
          echo "Bad Query: " . $query;
        }
			}

      // Update total grade for the test
      $query = "UPDATE testTable SET grade='$totalGrade' WHERE testname = '$testname' AND user='$username' ";
      mysqli_query($conn, $query) or die("Bad SQL: $query");
		}
    $jsonResponse["backDebugMsg"] = $message;
    $jsonResponse["dbupdate"] = "1";

		mysqli_close($conn);
		echo json_encode($jsonResponse);
    break;
  // Add testupdate for student answer
  
  // Update student answer (update test)
  case "updatetest":
    // Get needed values
    $testname = $decode->testname;
		$username = $decode->username;

		$message = "";
     // Null Check
		if (is_null($testname)) $message .= "testname is not defined\n";
		if (is_null($username)) $message .= "username is not defined\n";
    
    
    // Setup for the query
    for ($i = 0; $i < sizeof($decode->answers); $i++)
		{	
			$question = $decode->questions[$i];
			$answer   = $decode->answers[$i];

			$query = "UPDATE questionTable SET answer='$answer' WHERE testname='$testname' AND question='$question' AND user='$username' ";
			mysqli_query($conn, $query) or die("Bad SQL: $query");
		}
   
   

		$jsonResponse["backDebugMsg"] = $message;
		mysqli_close($conn);
		echo json_encode($jsonResponse);
  
    break;
  
	// Invalid curl
	default:
		$message .= "Invalid/Unsupported Curlid";
    echo $message;
		break;
}

?>