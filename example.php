<?php
define('MANAGESIEVE_SERVER','myserver');
define('MANAGESIEVE_PORT','2000');
define('MANAGESIEVE_USER','myusername');
define('MANAGESIEVE_PASS','mypassword');
define('LIB_PATH',dirname(__FILE__).'/lib/');


// Just so we wont need to include everything ourselves
function __autoload($class_name) {
	$path = str_replace('_',DIRECTORY_SEPARATOR,$class_name);
	require_once LIB_PATH.$path.'.php';
}

$socket = new phpmanagesieve_Net_Socket(MANAGESIEVE_SERVER,MANAGESIEVE_PORT);
#$socket->setDebug(true);
$manageSieve = new phpmanagesieve_Mail_ManageSieve($socket);
$manageSieve->login(MANAGESIEVE_USER,MANAGESIEVE_PASS);
$filters = $manageSieve->Listscripts();
$manageSieve->getScript($filters['ACTIVE'][0]);
$manageSieve->logout();

