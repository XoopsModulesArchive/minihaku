<?php

$modversion['name'] = 'minihaku';
$modversion['version'] = 1.09;
$modversion['description'] = 'simple subset of hakusen';
$modversion['author'] = "GIJOE";
$modversion['credits'] = "PEAK Corp.";
$modversion['license'] = "";
$modversion['official'] =  0;
$modversion['image'] = "minihaku_slogo.png";
$modversion['dirname'] = "minihaku";

$modversion['sqlfile'] = false ;

$modversion['tables'] = array() ;

$modversion['hasAdmin'] = 0;

$modversion['hasMain'] = 0;

$modversion['templates'][1] = array(
	'file' => 'minihaku_register.html' ,
	'description' => 'register form' ,
) ;
$modversion['templates'][] = array(
	'file' => 'minihaku_register_success.html' ,
	'description' => 'message after registered' ,
) ;
$modversion['templates'][] = array(
	'file' => 'minihaku_edituser.html' ,
	'description' => 'edit account' ,
) ;

$modversion['hasComments'] = 0;

?>
