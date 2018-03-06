<?php
require_once('shared'.DIRECTORY_SEPARATOR.'ez_sql_core.php');
function ezsql_autoloader($class) {    
	$foldername = ltrim($class, 'ezSQL_');
    $file = strtolower($foldername).DIRECTORY_SEPARATOR.'ez_sql_'.strtolower($foldername).'.php';
    if (file_exists($file)) {
        require_once($file);
    }
}
spl_autoload_register('ezsql_autoloader');
