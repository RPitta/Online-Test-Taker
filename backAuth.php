<?php
	
	/* backAuth.php */
	
	// Create Connection
	$conn = mysqli_connect('sql1.njit.edu', 'rap86', 'VaiqcQipJ', 'rap86');

	if (mysqli_connect_errno())
	{
		echo 'Failed to connect to MySQL ' . mysqli_connect_errno();
	}

	if (isset($_POST['sessionID']))
	{	
		$sid = $_POST['sessionID'];
		$logout = $_POST['logout'];

		$query = "SELECT * FROM login WHERE session_id = '$sid'";
		$result = mysqli_query($conn, $query) or die("Bad SQL: $query");
		
		
		if (mysqli_num_rows($result) > 0 && !$logout)
		{
			$row = mysqli_fetch_assoc($result);

			if ($row["user_type"] == 'student')
			{
				$jsonResponse = ["access_level" => "1", "dbresponse" => "valid session"];
			}
			else if ($row["user_type"] == 'teacher')
			{
				$jsonResponse = ["access_level" => "2", "dbresponse" => "valid session"];
			}
		}
		else if (mysqli_num_rows($result) > 0 && $logout)
		{
			// Delete session_id for that user in the table to log the user out
			$query = "UPDATE login SET session_id = null WHERE session_id = '$sid'";

			// if already NULL
			//   

			if (mysqli_query($conn, $query))
			{
				$jsonResponse = ["logout" => "1"];
			}
			else
			{
				$jsonResponse = ["logout" => "0"];
			}
		}
		else
		{	
			
			$jsonResponse = ["access_level" => "0", "dbresponse" => "invalid session", "logout" => "1"];
		}

		mysqli_free_result($result);
		mysqli_close($conn);
		echo json_encode($jsonResponse);
	}
	else
	{
		die("POST isn't set. Exiting...");
	}
