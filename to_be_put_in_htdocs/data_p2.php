<?php
/** data.php --- Connects to the database of recipes created by the associated
 *               schema.sql.  The API for selecing and saving recipes is
 *               provided below.  By default, this will return a single random
 *               recipe with each call.
 *
 * PARAMETERS 
 * ==========
 *
 * Acceptable via either GET or POST
 *
 *  @mode   --- Currently accepts the following values:
 *              'single': Returns a single random record, unless one is
 *                        specified by other parameters.  This is the default
 *                        if no mode is specified.
 *              'all':    Returns all valid records, rather than just one.
 *              'save':   Saves or inserts a recipe record.  This returns the
 *                        ID of the saved/inserted recipe record.
 *              'delete': Deletes the recipe specified by @id.
 *
 *              'link':     Creates an N:N link between specified records.  
 *              'unlink':   Removes an N:N link between specified records.
 *              'getlinks': Gets the N:N links between the specified records.
 *
 *              Note: When dealing with the link, unlink, and getlinks modes,
 *              the provided ID fields will determine what kind of link is
 *              used.  The Job_Competency table requires the @job_id and
 *              @competency_id fields be specified, and the
 *              Employee_Job_Competency table requires the @employee_id,
 *              @job_id, and @competency_id fields be specified (if @mode =
 *              link, then @score is also required).
 *
 *              For the getlinks mode, you can get the list of competencies
 *              associated with a job by passing @job_id, or the list of and
 *              employee's competency scores by passing both @employee_id and
 *              @job_id.
 *
 *  @type   --- Specify what object type to deal with. Valid types include
 *              'employee', 'job', and 'competency'.  The @type parameter also
 *              defines what parameters will be used for searching/saving
 *              (based on @mode).
 *
 *              Note that by default or when @mode = single or @mode = all,
 *              object field parameters are used to search for records.  When
 *              @mode = save, they are used to pass the data to be saved.
 *
 * 	            Object field parameters:
 *
 *                @employee_id   --- Available for @type = employee.
 *                @job_id        --- Avaialble for @type = job or @type =
 *                                   employee. For @type = employee, can only
 *                                   be used with @mode = save.
 *                @competency_id --- Avaialble for @type = competency.
 *
*               Notes on ID fields: 
*                >> When @mode = single, the ID can be used to select a specific record. 
*                >> When @mode = save, the ID can be used to specify a record
*                   to update.  If not provided, a new record will be inserted.
*                >> When @mode = delete, the ID is required, and is used to
*                   specify the record to delete.
 *
 *                @fname  --- Available for @type = employee.
 *                @lname  --- Available for @type = employee.
 *                @email  --- Available for @type = employee.
 *                @job    --- Available for @type = job.
 *                @comp   --- Available for @type = competency.
 *                @descr  --- Available for @type = competency.
 *
 *  @limit  --- The maximum number of records to return.  No effect unless
 *              @mode = all.
 *
 *  @offset --- The first index of the records to return.  No effect unless
 *              @mode = all.
 *
 *  @dbg    --- Displays the database query and parameters, and formats the
 *              JSON to use pretty HTML rendering.  This is a flag, and does
 *              not require a value.
 *
 *
 * OUTPUT
 * ======
 *
 *  For @mode = single and @mode = all, this will return an array of recipes,
 *   as JSON, or an error message if there was a problem.
 *
 *  For @mode = save, this will return the ID of the saved recipe, or an error
 *   message if there was a problem.
 *
 */


define('DEBUG', false);
#define('DEBUG', true);


////
//	Create the database connection
////
$DB_SERVER  = 'localhost';
$DB_NAME    = 'local_api_p2';
$DB_USER    = 'local_api_p2_l';
$DB_PASS    = 'a0bivlslev';
$DB_CHARSET = 'utf8mb4';

$DB_LINK = new PDO("mysql:host=$DB_SERVER;dbname=$DB_NAME;charset=$DB_CHARSET",
	$DB_USER,
	$DB_PASS,
	array(
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
		PDO::ATTR_EMULATE_PREPARES   => false,
	)
);




////
//  Import parameters regardless of source (POST, GET, PATCH, etc.)
////
parse_str(file_get_contents('php://input'), $OPTS);
if( !isset($OPTS) || empty($OPTS) || !is_array($OPTS) ){
	if( isset($_POST) && !empty($_POST) ){
		dbgmsg('OPTS = POST');
		$OPTS = $_POST;
	}else if( isset($_GET) && !empty($_GET) ){
		dbgmsg('OPTS = GET');
		$OPTS = $_GET;
	}else{
		dbgmsg('OPTS empty');
		$OPTS = array();
	}
}else{
	dbgmsg('OPTS = php://input');
}




////
//  Declare some utility functions
////

/*
 * Checks both POST and GET for the named key, otherwise returns the provided
 *  default (or null).
 */
function rget($key, $default=null){
	global $OPTS;
	return isset($OPTS[ $key ]) ? $OPTS[ $key ] : $default;
}


function format_json($data, $dbg=false){
	$json_str = '';

	$json_str .= "[\n";
	foreach( $data as $obj ){
		$obj_vars = get_object_vars($obj);

		$json_str .= "\t{\n";
		foreach( $obj_vars as $k => $v ){
			$v = str_replace("\n", "\n\t\t", $v);

			$json_str .= "\t\t'$k': '$v',\n";
		}
		$json_str .= "\t},\n";
	}
	$json_str .= "]\n";

	if( $dbg ){
		$json_str = htmlspecialchars($json_str);
	}
	$json_str = "<pre>$json_str</pre>";

	return $json_str;
}


/*
 * Display debugging data if the DEBUG flag is set
 */
function dbgmsg($msg, $lbl=null, $dbg=null){
	if( !DEBUG && !isset($dbg) ) return;
	echo "<p style='white-space:pre;'>";
	if( $lbl ) echo "<b>$lbl:</b>\n";
	echo print_r($msg, true) . "\n\n";
	echo "</p>";
}



////
//	Import global parameters
////
$mode = rget('mode');
$dbg  = rget('dbg');



if( DEBUG ){
	if( !empty($OPTS)  ) echo "OPTS:\n". print_r($OPTS,1);
	if( !empty($_POST) ) echo "POST:\n". print_r($_POST, 1);
	if( !empty($_GET ) ) echo "GET:\n".  print_r($_GET, 1);
	exit();
}


// Import object type
$type = rget('type');
if( (!isset($type) || empty($type)) && !in_array($mode, ['link', 'unlink', 'getlinks']) ){
	echo "Error: No type specified.";
	exit();
}


// Import object field params
#$id     = rget('id');

$employee_id   = rget('employee_id');
$job_id        = rget('job_id');
$competency_id = rget('competency_id');

$fname  = rget('fname');
$lname  = rget('lname');
$email  = rget('email');
$job    = rget('job');
$comp   = rget('comp');
$descr  = rget('descr');
$score  = rget('score');

switch( $mode ){
default:
case 'random':
case 'all':
	$limit  = rget('limit');
	$offset = rget('offset');


	$where_parts = [ 1 ];
	$query_append = '';
	$params = [];


	////
	//	Based on what was imported, build the selection query
	////
	$id_name = '';
	switch( $type ){
	case 'employee':
		if( isset($employee_id) ){
			$where_parts[] = "employee_id = ?";
			$params[] = intval($employee_id);
		}

		if( isset($job_id) ){
			$where_parts[] = "job_id = ?";
			$params[] = intval($job_id);
		}

		if( isset($fname) ){
			$where_parts[] = "fname LIKE ?";
			$params[] = "%$fname%";
		}

		if( isset($lname) ){
			$where_parts[] = "lname LIKE ?";
			$params[] = "%$lname%";
		}

		if( isset($email) ){
			$where_parts[] = "email LIKE ?";
			$params[] = "%$email%";
		}

		$id = intval($employee_id);
		$id_name = 'employee_id';
		$query = "SELECT employee_id, job_id, fname, lname, email FROM Employee WHERE ";
		break;

	case 'job':
		if( isset($job_id) ){
			$where_parts[] = "job_id = ?";
			$params[] = intval($job_id);
		}

		if( isset($job) ){
			$where_parts[] = "name LIKE ?";
			$params[] = "%$job%";
		}

		$id = intval($job_id);
		$id_name = 'job_id';
		$query = "SELECT job_id, name FROM Job WHERE ";
		break;

	case 'competency':
		if( isset($competency_id) ){
			$where_parts[] = "competency_id = ?";
			$params[] = intval($competency_id);
		}

		if( isset($comp) ){
			$where_parts[] = "name LIKE ?";
			$params[] = "%$comp%";
		}

		if( isset($descr) ){
			$where_parts[] = "descr LIKE ?";
			$params[] = "%$descr%";
		}

		$id = intval($competency_id);
		$id_name = 'competency_id';
		$query = "SELECT competency_id, name, descr FROM Competency WHERE ";
		break;
	}

	if( $mode == 'all' ){
		if( isset($limit) || isset($offset) ){
			$query_append .= "LIMIT ";
			if( $limit ){
				$limit = intval($limit);

				if( isset($offset) ){
					$offset = intval($offset);

					$query_append .= "?, ?";
					$params = [ $offset, $limit ];
				}else{
					$query_append .= "?";
					$params = [ $limit ];
				}
			}else if( isset($offset) ){
				$offset = intval($offset);

				$query_append .= "?, 18446744073709551615";
				$params = [ $offset ];
			}
		}
	}else{
		$query_append .= "ORDER BY RAND() " .
			"LIMIT 1";
	}



	////
	//  Run the query
	////
	try{
		$query .= implode(' AND ', $where_parts) . " " . $query_append;
		$stmt = $DB_LINK->prepare($query);
		$success = $stmt->execute($params);

		$data = $stmt->fetchAll();
	}catch( Exception $e ){
		echo "An error occurred when querying the database.";
		if( isset($dbg) ){
			echo "<pre>" . print_r($e, true) . "</pre>";
		}
		exit();
	}

	if( isset($dbg) ){
		echo "<p><b>Query:</b> $query</p>";
		echo "<p><b>Params:</b></p>" .
			"<pre>" . print_r($params,1) . "</pre>";
		echo "<hr>";
	}


	////
	//  Format and print the data
	////
	if( isset($dbg) ){
		echo format_json($data, true);
	}else{
		$json = json_encode($data);

		if( isset($dbg) ){
			$json = htmlspecialchars($json);
			$json = str_replace('\\/', '/', $json);
		}

		echo $json;
	}
	break;

case 'save':
	$query = '';
	$params = [];

	$query2 = '';
	$params2 = [];

	switch( $type ){
	case 'employee':
		if( isset($job_id) ) $job_id = intval($job_id);
		if( isset($fname) ) $fname = trim($fname);
		if( isset($lname) ) $lname = trim($lname);
		if( isset($email) ) $email = trim($email);

		if( !isset($job_id) || !$job_id ) {
			echo "Error: Job ID for Employee cannot be blank.";
			exit();
		}

		if( !isset($fname) || !$fname ) {
			echo "Error: First name for Employee cannot be blank.";
			exit();
		}

		if( !isset($lname) || !$lname ) {
			echo "Error: First name for Employee cannot be blank.";
			exit();
		}

		$id = intval($employee_id);

		$query = "INSERT INTO Employee (employee_id, job_id, fname, lname, email) VALUES " .
			"(?, ?, ?, ?, ?) " .
			"ON DUPLICATE KEY UPDATE " .
			"job_id = IFNULL(VALUES(job_id), job_id), " .
			"fname = IFNULL(VALUES(fname), fname), " .
			"lname = IFNULL(VALUES(lname), lname), " .
			"email = IFNULL(VALUES(email), email)";
		$params[] = $id;
		$params[] = $job_id;
		$params[] = $fname;
		$params[] = $lname;
		$params[] = $email;

		$query2 = "SELECT employee_id, job_id, fname, lname, email " .
			"FROM Employee " .
			"WHERE employee_id = ?";
		if( $id ) $params2[] = $id;
		break;
	case 'job':
		if( isset($job) ) $job = trim($job);

		if( !isset($job) || !$job ) {
			echo "Error: Name for Job cannot be blank.";
			exit();
		}

		$id = intval($job_id);

		$query = "INSERT INTO Job (job_id, name) VALUES " .
			"(?, ?) " .
			"ON DUPLICATE KEY UPDATE " .
			"name = IFNULL(VALUES(name), name)";
		$params[] = $id;
		$params[] = $job;

		$query2 = "SELECT job_id, name " .
			"FROM Job " .
			"WHERE job_id = ?";
		if( $id ) $params2[] = $id;
		break;
	case 'competency':
		if( isset($comp)  ) $comp  = trim($comp);
		if( isset($descr) ) $descr = trim($descr);

		if( !isset($comp) || !$comp ) {
			echo "Error: Name for Competency cannot be blank.";
			exit();
		}

		$id = intval($competency_id);

		$query = "INSERT INTO Competency (competency_id, name, descr) VALUES " .
			"(?, ?, ?) " .
			"ON DUPLICATE KEY UPDATE " .
			"name = IFNULL(VALUES(name), name), " .
			"descr = IFNULL(VALUES(descr), descr)";
		$params[] = $id;
		$params[] = $comp;
		$params[] = $descr;

		$query2 = "SELECT competency_id, name, descr " .
			"FROM Competency " .
			"WHERE competency_id = ?";
		if( $id ) $params2[] = $id;
		break;
	}


	#dbgmsg($query, 'query', $dbg);
	#dbgmsg($params, 'params', $dbg);
	#dbgmsg($query2, 'queryj', $dbg);
	#dbgmsg($params2, 'params2', $dbg);


	try{
		$DB_LINK->beginTransaction();


		$stmt = $DB_LINK->prepare($query);
		$success = $stmt->execute($params);

		if( $success && !$id ){
			$id = $DB_LINK->lastInsertId();
			$params2[] = $id;
		}

		$stmt = $DB_LINK->prepare($query2);
		$success = $stmt->execute($params2);

		$data = $stmt->fetchAll();


		$DB_LINK->commit();
	}catch( Exception $e ){
		$DB_LINK->rollback();

		echo "An error occurred when saving the $type.";
		if( isset($dbg) ){
			echo "<pre>" . print_r($e, true) . "</pre>";
		}
		exit();
	}

	if( isset($dbg) ){
		echo format_json($data, true);
	}else{
		$json = json_encode($data);

		if( isset($dbg) ){
			$json = htmlspecialchars($json);
			$json = str_replace('\\/', '/', $json);
		}

		echo $json;
	}
	#echo $save_id;
	break;

case 'delete':
	switch( $type ){
	case 'employee':
		$id = intval($employee_id);
		if( !$id ){
			echo "Error: No Employee ID specified for deletion.";
			exit();
		}
		
		$query = "DELETE FROM Employee WHERE employee_id = ?";
		break;
	case 'job':
		$id = intval($job_id);
		if( !$id ){
			echo "Error: No Job ID specified for deletion.";
			exit();
		}

		$query = "DELETE FROM Job WHERE job_id = ?";
		break;
	case 'competency':
		$id = intval($competency_id);
		if( !$id ){
			echo "Error: No Competency ID specified for deletion.";
			exit();
		}

		$query = "DELETE FROM Competency WHERE competency_id = ?";
		break;
	}


	if( !$id ){
		echo "Error: No ID specified for deletion.";
		exit();
	}


	try{
		$stmt = $DB_LINK->prepare($query);
		$success = $stmt->execute([ $id ]);

		$n_deleted = $stmt->rowCount();
		if( !$n_deleted ){
			echo "Error: The specified ID does not exist.";
			exit();
		}

	}catch( Exception $e ){
		echo "An error occurred when deleting the recipe.";
		if( isset($dbg) ){
			echo "<pre>" . print_r($e, true) . "</pre>";
		}
		exit();
	}

	echo 0;
	break;

case 'link':
	if( !isset($job_id) || !$job_id ){
		echo "Error: A Job ID is required to create a link.";
		exit();
	}

	if( !isset($competency_id) || !$competency_id ){
		echo "Error: A Competency ID is required to create a link.";
		exit();
	}

	if( $employee_id ){
		// If employee_id was specified, we're dealing with the Employee_Job_Competency table
		try{
			if( !$score ){
				echo "Error: A score is required to create a Employee_Job_Competency link.";
				exit();
			}

			$query = "REPLACE INTO Employee_Job_Competency (employee_id, job_id, competency_id, score) VALUES " .
				"(?, ?, ?, ?)";
			$params = array(
				intval($employee_id),
				intval($job_id),
				intval($competency_id),
				intval($score)
			);

			if( isset($dbg) ){
				echo "<p><b>Query:</b> $query</p>";
				echo "<p><b>Params:</b></p>" .
					"<pre>" . print_r($params,1) . "</pre>";
				echo "<hr>";
			}

			$stmt = $DB_LINK->prepare($query);
			$success = $stmt->execute($params);
		}catch( Exception $e ){
			echo "An error occurred creating a Employee_Job_Competency link.";
			exit();
		}
	}else{
		// If no employee_id was specified, we're dealing with the Job_Competency table
		try{
			$query = "REPLACE INTO Job_Competency (job_id, competency_id) VALUES " .
				"(?, ?)";
			$params = array(
				intval($job_id),
				intval($competency_id)
			);

			if( isset($dbg) ){
				echo "<p><b>Query:</b> $query</p>";
				echo "<p><b>Params:</b></p>" .
					"<pre>" . print_r($params,1) . "</pre>";
				echo "<hr>";
			}

			$stmt = $DB_LINK->prepare($query);
			$success = $stmt->execute($params);
		}catch( Exception $e ){
			echo "An error occurred creating a Job_Competency link.";
			exit();
		}
	}
	break;

case 'unlink':
	if( !isset($job_id) || !$job_id ){
		echo "Error: A Job ID is required to delete a link.";
		exit();
	}

	if( !isset($competency_id) || !$competency_id ){
		echo "Error: A Competency ID is required to delete a link.";
		exit();
	}


	if( $employee_id ){
		// If employee_id was specified, we're dealing with the Employee_Job_Competency table
		try{
			$query = "DELETE FROM Employee_Job_Competency " .
				"WHERE employee_id = ? AND job_id = ? AND competency_id = ?";
			$params = array(
				intval($employee_id),
				intval($job_id),
				intval($competency_id)
			);

			$stmt = $DB_LINK->prepare($query);
			$success = $stmt->execute($params);
		}catch( Exception $e ){
			echo "An error occurred deleting a Employee_Job_Competency link.";
			exit();
		}
	}else{
		// If no employee_id was specified, we're dealing with the Job_Competency table
		try{
			$query = "DELETE FROM Job_Competency " .
				"WHERE job_id = ? AND competency_id = ?";
			$params = array(
				intval($job_id),
				intval($competency_id)
			);

			$stmt = $DB_LINK->prepare($query);
			$success = $stmt->execute($params);
		}catch( Exception $e ){
			echo "An error occurred deleting a Job_Competency link.";
			exit();
		}
	}
	break;

case 'getlinks':
	// If only job_id provided, get it's comps

	$z=1;
	//echo ">> ".$z++."<br>\n";

	$success = false;
	$stmt = null;
	if( $job_id ){
		if( !$employee_id ){
			try{
				$query = "SELECT c.competency_id, c.name, c.descr " .
					"FROM Competency AS c, Job_Competency AS jc " .
					"WHERE c.competency_id = jc.competency_id AND jc.job_id = ?";
				$params = array(
					intval($job_id)
				);

				if( isset($dbg) ){
					echo "<p><b>Query:</b> $query</p>";
					echo "<p><b>Params:</b></p>" .
						"<pre>" . print_r($params,1) . "</pre>";
					echo "<hr>";
				}

				$stmt = $DB_LINK->prepare($query);
				$success = $stmt->execute($params);
			}catch( Exception $e ){
				echo "An error occurred selecting Employee_Job_Competency links.";
				exit();
			}
		}else if( $employee_id ){
			try{
				$query = "SELECT c.competency_id, c.name, c.descr, ejc.score " .
					"FROM Competency AS c, Employee_Job_Competency AS ejc " .
					"WHERE c.competency_id = ejc.competency_id " .
					"AND ejc.employee_id = ? AND ejc.job_id = ?";
				$params = array(
					intval($employee_id),
					intval($job_id)
				);

				if( isset($dbg) ){
					echo "<p><b>Query:</b> $query</p>";
					echo "<p><b>Params:</b></p>" .
						"<pre>" . print_r($params,1) . "</pre>";
					echo "<hr>";
				}

				$stmt = $DB_LINK->prepare($query);
				$success = $stmt->execute($params);
			}catch( Exception $e ){
				echo "An error occurred selecting Employee_Job_Competency links.";
				exit();
			}
		}
	}else{
		echo "Error: A Job ID is required to select links.";
		exit();
	}

	/*
	if( !isset($job_id) || !$job_id ){
		echo "Error: A Job ID is required to select links.";
		exit();
	}

	if( !isset($competency_id) || !$competency_id ){
		echo "Error: A Competency ID is required to select links.";
		exit();
	}

	$success = false;
	if( $employee_id ){
		try{
			$query = "SELECT employee_id, job_id, competency_id, score FROM Employee_Job_Competency " .
				"WHERE employee_id = ? AND job_id = ? AND competency_id = ?";
			$params = array(
				intval($employee_id),
				intval($job_id),
				intval($competency_id)
			);

			$stmt = $DB_LINK->prepare($query);
			$success = $stmt->execute($params);
		}catch( Exception $e ){
			echo "An error occurred selecting Employee_Job_Competency links.";
			exit();
		}
	}else{
		try{
			$query = "SELECT job_id, competency_id FROM Job_Competency " .
				"WHERE job_id = ? AND competency_id = ?";
			$params = array(
				intval($job_id),
				intval($competency_id)
			);

			$stmt = $DB_LINK->prepare($query);
			$success = $stmt->execute($params);
		}catch( Exception $e ){
			echo "An error occurred selecting Job_Competency links.";
			exit();
		}
	}
	//*/

	if( $success && isset($stmt) && $stmt ){
		$data = $stmt->fetchAll();

		if( isset($dbg) ){
			echo format_json($data, true);
		}else{
			$json = json_encode($data);

			if( isset($dbg) ){
				$json = htmlspecialchars($json);
				$json = str_replace('\\/', '/', $json);
			}

			echo $json;
		}
	}
}


?>

