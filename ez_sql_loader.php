<?php
require_once('ezSQLcore'.DIRECTORY_SEPARATOR.'ezSQLcore.php');
function ezsql_autoloader($class) {    
    $file = strtolower($class).DIRECTORY_SEPARATOR.strtolower($class).'.php';
    if (file_exists($file)) {
        require_once($file);
    }
}
spl_autoload_register('ezsql_autoloader');
