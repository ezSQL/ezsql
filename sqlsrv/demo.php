<?php

	/**********************************************************************
	*  ezSQL initialisation for sqlsrv
	*/

	// Include ezSQL core
	include_once "../shared/ez_sql_core.php";

	// Include ezSQL database specific component
	include_once "ez_sql_sqlsrv.php";

	// Initialise database object and establish a connection
	// at the same time - db_user / db_password / db_name / db_host

	 $db_host = '';
	 $db_name = '';
	 $db_user = '';
	 $db_password = '';

	$db = new ezSQL_sqlsrv($db_user, $db_password, $db_name, $db_host);

	/**********************************************************************
	*  ezSQL demo for sqlsrv database
	*/

	// Some nice demo code here!

?>