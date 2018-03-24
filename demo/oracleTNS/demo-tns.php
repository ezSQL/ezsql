<html>
<head>
<title>Demo ezSQL Oracle TNS connection</title>
</head>
<body>
<?php
    include_once dirname(__FILE__) . "../../ez_sql_loader.php";
	
    $db = new ezSQL_oracleTNS('en-yoda-1', '1521', 'ppisa.febi.bilstein.local', 'cmp', 'cmp');
    
    $db->connect();
    
  if (!$db) {
    print "Sorry! The connection to the database failed. Please try again later.";
    die();
  }
  else {
    print "Congrats! You've connected to an Oracle database!<br>";
    $current_date = $db->get_var("SELECT " . $db->sysdate() . " FROM DUAL");
    print "ezSQL demo for Oracle database run on $current_date";
	
    $db->disconnect();
	// Get list of tables from current database..
	$my_tables = $db->get_results("SELECT TABLE_NAME FROM USER_TABLES",ARRAY_N);

	// Print out last query and results..
	$db->debug();

	// Loop through each row of results..
	foreach ( $my_tables as $table )
	{
		// Get results of DESC table..
		$db->get_results("SELECT COLUMN_NAME, DATA_TYPE, DATA_LENGTH, DATA_PRECISION FROM USER_TAB_COLUMNS WHERE TABLE_NAME = '$table[0]'");

		// Print out last query and results..
		$db->debug();
	}
  }
?>