<?php
/**
 * @ignore 
 */

	/**********************************************************************
	*  ezSQL initialisation for PDO
	*/

	// Include ezSQL core
	include_once "../ez_sql_loader.php";

	// This is how to initialse ezsql for sqlite PDO
	$db = new ezSQL_pdo('sqlite:my_database.sq3','someuser','somepassword');

?>