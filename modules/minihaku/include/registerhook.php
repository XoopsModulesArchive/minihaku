<?php

// allowed requests
$allowed_requests = array(
	'uname' => '' ,
	'email' => '' ,
	'pass' => '' ,
	'vpass' => '' ,
	'url' => '' ,
	'timezone_offset' => doubleval( $xoopsConfig['default_TZ'] ) ,
	'user_viewemail' => 0 ,
	'user_mailok' => 0 ,
	'agree_disc' => 0 ,
) ;
$stop_reason_extras = array() ;

// rename config.dist.php -> config.php
if( file_exists( dirname(__FILE__).'/config.php' ) ) {
	include dirname(__FILE__).'/config.php' ;
}

include_once XOOPS_ROOT_PATH."/class/xoopslists.php";

$config_handler =& xoops_gethandler('config');
$xoopsConfigUser =& $config_handler->getConfigsByCat(XOOPS_CONF_USER);

if (empty($xoopsConfigUser['allow_register'])) {
    redirect_header('index.php', 6, _US_NOREGISTER);
    exit();
}

foreach( $allowed_requests as $key => $val ) {
	if( ! isset( $_POST[$key] ) && gettype( $val ) != 'boolean' ) continue ;
	switch( strtolower( gettype( $val ) ) ) {
		case 'double' :
			$allowed_requests[$key] = doubleval( $_POST[$key] ) ;
			break ;
		case 'integer' :
			$allowed_requests[$key] = intval( $_POST[$key] ) ;
			break ;
		case 'boolean' :
			$allowed_requests[$key] = (boolean)( @$_POST[$key] ) ;
			break ;
		case 'string' :
			$allowed_requests[$key] = get_magic_quotes_gpc() ? stripslashes( $_POST[$key] ) : $_POST[$key] ;
			break ;
	}
}

//
// REGISTER STAGE
//
if( ! empty( $_POST['do_register'] ) ) {
	// check before register (uname, email, password)
	$email4check = $allow_blank_email ? substr(md5(time()),-6).'@example.com' : $allowed_requests['email'] ;
	$allowed_requests['vpass'] = $allow_blank_vpass ? $allowed_requests['pass'] : $allowed_requests['vpass'] ;
	$stop_reason = userCheck( $allowed_requests['uname'] , $email4check , $allowed_requests['pass'] , $allowed_requests['vpass'] ) ;
}

if( ! empty( $_POST['do_register'] ) && empty( $stop_reason_extras ) && empty( $stop_reason ) ) {
	if( $xoopsConfigUser['reg_dispdsclmr'] && empty( $allowed_requests['agree_disc'] ) ) die( _US_UNEEDAGREE ) ;

	include XOOPS_ROOT_PATH.'/header.php';
	$member_handler =& xoops_gethandler('member');
	$newuser =& $member_handler->createUser();

	if( $allow_blank_email ) {
		$newuser->initVar('email', XOBJ_DTYPE_TXTBOX, null, false, 60);
	}

	$newuser->setVar('user_viewemail',$allowed_requests['user_viewemail'], true);
	$newuser->setVar('uname', $allowed_requests['uname'], true);
	$newuser->setVar('email', $allowed_requests['email'], true);
	$newuser->setVar('url', formatURL($allowed_requests['url']), true);
	$newuser->setVar('user_avatar','blank.gif', true);
	$actkey = substr(md5(uniqid(mt_rand(), 1)), 0, 8);
	$newuser->setVar('actkey', $actkey, true);
	$newuser->setVar('pass', md5($allowed_requests['pass']), true);
	$newuser->setVar('timezone_offset', $allowed_requests['timezone_offset'], true);
	$newuser->setVar('user_regdate', time(), true);
	$newuser->setVar('uorder',$xoopsConfig['com_order'], true);
	$newuser->setVar('umode',$xoopsConfig['com_mode'], true);
	$newuser->setVar('user_mailok',$allowed_requests['user_mailok'], true);
	if ($xoopsConfigUser['activation_type'] == 1) {
		$newuser->setVar('level', 1, true);
	}
	if (!$member_handler->insertUser($newuser)) {
		echo _US_REGISTERNG;
		include XOOPS_ROOT_PATH.'/footer.php';
		exit();
	}

	$newid = $newuser->getVar('uid') ;

	// extra fields
	if( ! empty( $extra_fields ) ) {
		$db =& Database::getInstance() ;
		foreach( array_keys( $extra_fields ) as $field ) {
			$db->query( "UPDATE ".$db->prefix("users")." SET $field='".addslashes(@$allowed_requests[$field])."' WHERE uid=".$newid ) ;
		}
	}

	// groups
	foreach( $auto_belong_groups as $group ) {
		$member_handler->addUserToGroup( intval( $group ) , $newid ) ;
	}

	// register hook
	if( file_exists( dirname(__FILE__).'/on_register_success.php' ) ) {
		include dirname(__FILE__).'/on_register_success.php' ;
	}

	$xoopsOption['template_main'] = 'minihaku_register_success.html' ;
	if ($xoopsConfigUser['activation_type'] == 1) {
		// bug ?
		//redirect_header('index.php', 4, _US_ACTLOGIN);
		//exit();
		$xoopsTpl->assign( 'message' , _US_ACTLOGIN ) ;
	} else if ($xoopsConfigUser['activation_type'] == 0) {
		$xoopsMailer =& getMailer();
		$xoopsMailer->useMail();
		$xoopsMailer->setTemplate('register.tpl');
		$xoopsMailer->assign('SITENAME', $xoopsConfig['sitename']);
		$xoopsMailer->assign('ADMINMAIL', $xoopsConfig['adminmail']);
		$xoopsMailer->assign('SITEURL', XOOPS_URL."/");
		foreach( $allowed_requests as $key => $val ) {
			$xoopsMailer->assign( 'REQUEST_'.strtoupper($key) , $val ) ;
		}
		$xoopsMailer->setToUsers(new XoopsUser($newid));
		$xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
		$xoopsMailer->setFromName($xoopsConfig['sitename']);
		$xoopsMailer->setSubject(sprintf(_US_USERKEYFOR, $allowed_requests['uname']));
		if ( !$xoopsMailer->send() ) {
			$xoopsTpl->assign( 'message' , _US_YOURREGMAILNG ) ;
		} else {
			$xoopsTpl->assign( 'message' , _US_YOURREGISTERED ) ;
		}
	} elseif ($xoopsConfigUser['activation_type'] == 2) {
		$xoopsMailer =& getMailer();
		$xoopsMailer->useMail();
		$xoopsMailer->setTemplate('adminactivate.tpl');
		$xoopsMailer->assign('USERNAME', $allowed_requests['uname']);
		$xoopsMailer->assign('USEREMAIL', $allowed_requests['email']);
		$xoopsMailer->assign('USERACTLINK', XOOPS_URL.'/user.php?op=actv&id='.$newid.'&actkey='.$actkey);
		$xoopsMailer->assign('SITENAME', $xoopsConfig['sitename']);
		$xoopsMailer->assign('ADMINMAIL', $xoopsConfig['adminmail']);
		$xoopsMailer->assign('SITEURL', XOOPS_URL."/");
		foreach( $allowed_requests as $key => $val ) {
			$xoopsMailer->assign( 'REQUEST_'.strtoupper($key) , $val ) ;
		}
		$member_handler =& xoops_gethandler('member');
		$xoopsMailer->setToGroups($member_handler->getGroup($xoopsConfigUser['activation_group']));
		$xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
		$xoopsMailer->setFromName($xoopsConfig['sitename']);
		$xoopsMailer->setSubject(sprintf(_US_USERKEYFOR, $allowed_requests['uname']));
		if ( !$xoopsMailer->send() ) {
			$xoopsTpl->assign( 'message' , _US_YOURREGMAILNG ) ;
		} else {
			$xoopsTpl->assign( 'message' , _US_YOURREGISTERED2 ) ;
		}
	}

	if ($xoopsConfigUser['new_user_notify'] == 1 && !empty($xoopsConfigUser['new_user_notify_group'])) {
		$xoopsMailer =& getMailer();
		$xoopsMailer->useMail();
		$member_handler =& xoops_gethandler('member');
		$xoopsMailer->setToGroups($member_handler->getGroup($xoopsConfigUser['new_user_notify_group']));
		$xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
		$xoopsMailer->setFromName($xoopsConfig['sitename']);
		$xoopsMailer->setSubject(sprintf(_US_NEWUSERREGAT,$xoopsConfig['sitename']));
		$xoopsMailer->setBody(sprintf(_US_HASJUSTREG, $allowed_requests['uname']));
		$xoopsMailer->send();
	}
	include XOOPS_ROOT_PATH.'/footer.php';
	exit ;

}

//
// FORM STAGE
//

include XOOPS_ROOT_PATH.'/header.php' ;
$xoopsOption['template_main'] = 'minihaku_register.html' ;
$stop_reasons = explode( '<br />' , @$stop_reason ) ;
if( empty( $stop_reasons[sizeof($stop_reasons)-1] ) ) array_pop( $stop_reasons ) ;
$xoopsTpl->assign(
	array(
		'stop_reason' => @$stop_reason , // older assign
		'stop_reasons' => array_merge( $stop_reasons , $stop_reason_extras ) ,
		'timezone_options' => XoopsLists::getTimeZoneList() ,
		'reg_disclaimer' => $xoopsConfigUser['reg_disclaimer'] , // older assign
		'xoops_config_user' => $xoopsConfigUser ,
		'xoops_config_general' => $xoopsConfig ,
	)
) ;
$xoopsTpl->assign( $allowed_requests ) ;
// extra field which has options
if( ! empty( $extra_fields ) ) {
	foreach( $extra_fields as $key => $attribs ) {
		if( ! empty( $attribs['options'] ) ) {
			$xoopsTpl->assign( $key.'_options' , $attribs['options'] ) ;
		}
	}
}
include XOOPS_ROOT_PATH.'/footer.php' ;
exit ;

?>