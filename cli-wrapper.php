#!/usr/bin/php
<?php
/* make sure this isn't called from a web browser */
if (isset($_SERVER['REMOTE_ADDR'])) die('Permission denied.');
/* set the controller/method path */
if(isset($_SERVER['argv'][2])) {
	$_SERVER['PATH_INFO'] = $_SERVER['REQUEST_URI'] = $_SERVER['argv'][2];
} else {
	die('You must supply a second argument for the requested uri. (example sms)');
}
if(isset($_SERVER['argv'][1])) {
	$_SERVER['SERVER_NAME'] = $_SERVER['argv'][1];
} else {
	die('You must supply a first argument for the requested server name. (example technologygurusinc.com)');
}
/* right now we can only POST to special pages since the post vars are automatically lined up with the entity */
/* and we should not GET a login but the Application->_setExecutionContext sets it to GET when we use usr,pw,submit */
/* login will work without sending a submit value since it is in the GET context */
if(isset($_SERVER['argv'][3])) {
	$_SERVER['REQUEST_METHOD'] = $_SERVER['argv'][3];
} else {
	die('You must supply a third argument for the request method. (example POST)');
}
/* if we send the method, it will not use the user login in Application->_setExecutionContext() */
//$_POST['_http_method'] = $_SERVER['REQUEST_METHOD'];

for($i=4;$i<$_SERVER['argc'];$i++){
	if(strpos($_SERVER['argv'][$i],'=')) {
		$pair = explode('=',$_SERVER['argv'][$i]);
		$_POST[$pair[0]]=$pair[1];
	} else {
		echo $_SERVER['argv'][$i]."\n";
		exit('parameters must be in name value pairs in the format name=value');
	}
}
/* raise or eliminate limits we would otherwise put on http requests */
set_time_limit(0);
ini_set('memory_limit', '256M');
/* call up the framework */
include(dirname(__FILE__).'/index.php');
?>