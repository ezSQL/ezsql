<?php
function ez_autoloader($class) {    
    $base_dir = __DIR__ .DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR;
    $prefix = 'ezsql\\';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir.strtolower( str_replace('\\', DIRECTORY_SEPARATOR , $relative_class) ).'.php';
    if (file_exists($file)) {
        require_once($file);
    }
}
spl_autoload_register('ez_autoloader');

function sql_autoloader($class) {    
    $base_dir = __DIR__ .DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR;
    $prefix = 'ezsql\\Database\\';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir.strtolower( str_replace('\\', DIRECTORY_SEPARATOR , $relative_class) ).'.php';
    if (file_exists($file)) {
        require_once($file);
    }
}
spl_autoload_register('sql_autoloader');
