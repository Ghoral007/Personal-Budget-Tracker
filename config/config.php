<?php
session_start();

if($_SERVER['HTTP_HOST']=='localhost'){
define("BASE_URL","http://localhost/personal_budget_tracker/");
define("DIR_URL", $_SERVER['DOCUMENT_ROOT']."/personal_budget_tracker/");

define("SERVER_NAME", "localhost");
define("USERNAME", "root");
define("PASSWORD","");
define("DATABASE","personal_budget_tracker");
}else{
    define("BASE_URL", "https://lms.com");
    define("DIR_URL", $_SERVER['DOCUMENT_ROOT']);

    define("SERVER_NAME", "");
    define("USERNAME", "");
    define("PASSWORD","");
    define("DATABASE","");
}

?>