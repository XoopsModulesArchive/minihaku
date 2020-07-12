<?php

if( ! in_array( $_SERVER['SCRIPT_FILENAME'] , array( XOOPS_ROOT_PATH.'/register.php' , XOOPS_ROOT_PATH.'/userinfo.php' , XOOPS_ROOT_PATH.'/edituser.php' ) ) ) return ;

switch( strrchr( $_SERVER['SCRIPT_FILENAME'] , '/' ) ) {
	case '/register.php' :
		include dirname(__FILE__).'/registerhook.php' ;
		break ;
	case '/userinfo.php' :
		include dirname(__FILE__).'/userinfohook.php' ;
		break ;
	case '/edituser.php' :
		include dirname(__FILE__).'/edituserhook.php' ;
		break ;
}


?>