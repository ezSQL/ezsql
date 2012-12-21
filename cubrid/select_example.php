<?php

	// Standard ezSQL Libs
	include_once "../shared/ez_sql_core.php";
	include_once "ez_sql_cubrid.php";

	// Initialise singleton
	$db = new ezSQL_cubrid('dba','','demodb');

	$athletes = $db->get_results("SELECT code, name FROM athlete");
    
    echo "Code&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Name<br/>";
 
    foreach ( $athletes as $athlete )
    {
        // Access data using object syntax
        echo $athlete->code."&nbsp;&nbsp;&nbsp;";
        echo $athlete->name."<br />";
    }
    
    $var = $db->get_var("SELECT count(*) FROM athlete");
 
    echo "Number of athletes: ".$var;
    
    $db->debug();

?>