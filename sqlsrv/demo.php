<?php

	/************************************************************************************
	*  ezSQL initialization for SQLSRV (Microsoft supported MSSQL Server drivers for PHP)
	*/

	// Include ezSQL core
	include_once "../shared/ez_sql_core.php";

	// Include ezSQL database specific component
	include_once "ez_sql_sqlsrv.php";

	// Initialize database object and establish a connection
	// at the same time - db_user / db_password / db_name / db_host

	 $db_host = '';
	 $db_name = ''; 
	 $db_user = '';
	 $db_password = '';

	 //example
	 //$db_host = 'servername';   or $db_host = 'servername, portnumber'
	 //$db_name = 'AdventureWorks';
	 //$db_user = 'sa';           or $db_user = ''; for Windows Authentication passthru
	 //$db_password = 'password';


	$db = new ezSQL_sqlsrv($db_user, $db_password, $db_name, $db_host);

	/*****************************************************************************
	*  ezSQL demo for MS-SQL database with Microsoft supported SQL drivers for PHP
	*/

	// Demo of getting a single variable from the db
	// (and using abstracted function sysdate)
	$current_time = $db->get_var("SELECT " . $db->sysdate() . " AS 'GetDate()'");
	print "ezSQL demo for MS-SQL database run @ $current_time";

	// Print out last query and results..
	$db->debug();

	// Get list of tables from current database..
	$my_tables = $db->get_results("select name from ".$db_name."..sysobjects where xtype = 'U'",ARRAY_N);

	// Print out last query and results..
	$db->debug();

	// Loop through each row of results..
	foreach ( $my_tables as $table )
	{
		// Get results of DESC table..
		$db->query("EXEC SP_COLUMNS '".$table[0]."'");

		// Print out last query and results..
		$db->debug();
	}

?>
