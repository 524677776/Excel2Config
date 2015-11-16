<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/14
 * Time: 19:05
 */

ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('date.timezone','Asia/Shanghai');

function vd(){
    $args       = func_get_args();
    call_user_func_array("var_dump",$args);
    die();
}

function fw(){
    file_put_contents('log.txt', print_r(func_get_args(), 1));
}

define('ROOT_DIR',     __DIR__);
define('LOG_DIR',      ROOT_DIR.'/log/');
define('OUTPUT_DIR',   ROOT_DIR.'/output/');
define('XLS_DIR',       ROOT_DIR.'/xls/');
define('LIBRARY_DIR',  ROOT_DIR.'/library/');
define('CONFIG_FILE',  ROOT_DIR.'/config.ini');

// Add your class dir to include path
set_include_path(get_include_path().PATH_SEPARATOR.LIBRARY_DIR);
spl_autoload_extensions('.php');
spl_autoload_register();

Gen::getInst()->process();

die("Success!");