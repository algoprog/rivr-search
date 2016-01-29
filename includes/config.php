<?php
error_reporting(E_ERROR | E_PARSE);

require_once(dirname(__FILE__).'/functions.php');

define("ENGINE_NAME",'RIVR');
define("DBHOST",'localhost');
define("DBUSER",'root');
define("DBPASS",'');
define("DBNAME",'rivr2');
define("PERPAGE",30);

ini_set('default_charset', 'utf-8');

$db = db_connect(DBHOST, DBUSER, DBPASS, DBNAME); // MySQL database

$sdb = db_connect('localhost:9306', '', '', 'rtindex'); // Sphinx Real-time index

?>