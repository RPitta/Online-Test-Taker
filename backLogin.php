<?php
	
	/* backLogin.php */
	
	// Create Connection
	$conn = mysqli_connect('sql1.njit.edu', 'rap86', 'VaiqcQipJ', 'rap86');

	if (mysqli_connect_errno())
	{
		echo 'Failed to connect to MySQL ' . mysqli_connect_errno();
	}

	// Login
	if (isset($_POST['user']))
	{
		$user = $_POST['user'];
		$pass = $_POST['pass'];
		$message = "";

		if (is_null($user)) $message .= "user is null\n";
		if (is_null($pass)) $message .= "pass is null\n";

		$salted = "42hjkwerf872".$pass."asdfa0987a9sdf";
		$hashed = hash('sha512', $salted);

		$query = "SELECT * FROM login WHERE username = '$user' AND password = '$hashed'";
		$result = mysqli_query($conn, $query) or die("Bad SQL: $query");

		if (mysqli_num_rows($result) > 0)
		{
			$row = mysqli_fetch_assoc($result);
			$sessionID = '#' . $user;
			$query = "UPDATE login SET session_id = '$sessionID' WHERE username = '$user'";

			if (mysqli_query($conn, $query))
			{
				if ($row["user_type"] == 'student')
				{
					$jsonResponse = ["dbresponse" => "1", "sid" => '#' . $user];
				}
				else if ($row["user_type"] == 'teacher')
				{
					$jsonResponse = ["dbresponse" => "2", "sid" => '#' . $user];
				}
			}
			else
			{
				echo 'ERROR: ' . mysqli_error($conn);
			}
		}
		else
		{	
			$jsonResponse = ["dbresponse" => "0", "sid" => null];
		}


		$jsonResponse["backDebugMessage"] = $message;
		
		// Free Result from Memory
		mysqli_free_result($result);

		mysqli_close($conn);

		echo json_encode($jsonResponse);
	}
	else
	{
		die("POST isn't set. Exiting...");
	}
