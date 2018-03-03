<?php
require_once('shared'.DIRECTORY_SEPARATOR.'ez_sql_core.php');
function ezsql_autoloader($class) {    
    $file = strtolower($class).DIRECTORY_SEPARATOR.'ez_sql_'.strtolower($class).'.php';
    if (file_exists($file)) {
        require_once($file);
    }
}
spl_autoload_register('ezsql_autoloader');
