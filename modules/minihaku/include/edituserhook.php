<?php

// allowed requests
$allowed_requests = array(
	'uname' => $xoopsUser->getVar('uname','n') ,
	'name' => $xoopsUser->getVar('name','n') ,
	'email' => $xoopsUser->getVar('email','n') ,
	'pass' => '' ,
	'vpass' => '' ,
	'user_icq' => $xoopsUser->getVar('user_icq','n') ,
	'user_aim' => $xoopsUser->getVar('user_aim','n') ,
	'user_yim' => $xoopsUser->getVar('user_yim','n') ,
	'user_msnm' => $xoopsUser->getVar('user_msnm','n') ,
	'user_from' => $xoopsUser->getVar('user_from','n') ,
	'user_occ' => $xoopsUser->getVar('user_occ','n') ,
	'user_intrest' => $xoopsUser->getVar('user_intrest','n') ,
	'user_sig' => $xoopsUser->getVar('user_sig','n') ,
	'url' => $xoopsUser->getVar('url','n') ,
	'timezone_offset' => doubleval( $xoopsUser->getVar('timezone_offset','n') ) ,
	'user_viewemail' => (boolean)$xoopsUser->getVar('user_viewemail','n') ,
	'umode' => $xoopsUser->getVar('umode','n') ,
	'uorder' => intval( $xoopsUser->getVar('uorder','n') ) ,
	'notify_method' => intval( $xoopsUser->getVar('notify_method','n') ) ,
	'notify_mode' => intval( $xoopsUser->getVar('notify_mode','n') ) ,
	'user_mailok' => intval( $xoopsUser->getVar('user_mailok','n') ) ,
	'bio' => $xoopsUser->getVar('bio','n') ,
) ;
$stop_reason_extras = array() ;

// rename config.dist.php -> config.php
$minihaku_uid4whr = $xoopsUser->getVar('uid') ;
if( file_exists( dirname(__FILE__).'/config.php' ) ) {
	include dirname(__FILE__).'/config.php' ;
}

include_once XOOPS_ROOT_PATH."/class/xoopslists.php";
include_once XOOPS_ROOT_PATH . "/language/" . $xoopsConfig['language'] . '/notification.php';
include_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
include_once XOOPS_ROOT_PATH.'/include/comment_constants.php';
$umode_options = array('nest'=>_NESTED, 'flat'=>_FLAT, 'thread'=>_THREADED);
$uorder_options = array(XOOPS_COMMENT_OLD1ST => _OLDESTFIRST, XOOPS_COMMENT_NEW1ST => _NEWESTFIRST);
$notify_method_options = array(XOOPS_NOTIFICATION_METHOD_DISABLE=>_NOT_METHOD_DISABLE, XOOPS_NOTIFICATION_METHOD_PM=>_NOT_METHOD_PM, XOOPS_NOTIFICATION_METHOD_EMAIL=>_NOT_METHOD_EMAIL);
$notify_mode_options = array(XOOPS_NOTIFICATION_MODE_SENDALWAYS=>_NOT_MODE_SENDALWAYS, XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE=>_NOT_MODE_SENDONCE, XOOPS_NOTIFICATION_MODE_SENDONCETHENWAIT=>_NOT_MODE_SENDONCEPERLOGIN);


$op = empty( $_REQUEST['op'] ) ? 'editprofile' : $_REQUEST['op'] ;

$config_handler =& xoops_gethandler('config');
$xoopsConfigUser =& $config_handler->getConfigsByCat(XOOPS_CONF_USER);

if ($op == 'saveuser') foreach( $allowed_requests as $key => $val ) {
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


$errors = $stop_reason_extras ;

if ($op == 'saveuser') {
	/*if (!XoopsSingleTokenHandler::quickValidate('edituser')) {
		redirect_header('index.php',3,_US_NOEDITRIGHT);
		exit;
	}*/
	$uid = $xoopsUser->getVar('uid');
	$myts =& MyTextSanitizer::getInstance();
	if ($allowed_requests['pass'] != '') {
		if (strlen($allowed_requests['pass']) < $xoopsConfigUser['minpass']) {
			$errors[] = sprintf(_US_PWDTOOSHORT,$xoopsConfigUser['minpass']);
		}
		if ( empty( $allow_blank_vpass ) && $allowed_requests['pass'] != $allowed_requests['vpass'] ) {
			$errors[] = _US_PASSNOTSAME;
		}
	}
	if (count($errors) > 0) {
		$op = 'editprofile';
	} else {
		$member_handler =& xoops_gethandler('member');
		$edituser =& $member_handler->getUser($uid);

		if( ! empty( $allow_blank_email ) ) {
			$edituser->initVar('email', XOBJ_DTYPE_TXTBOX, null, false, 60);
		}

		$edituser->setVar('name', $allowed_requests['name'], true);
		if ($xoopsConfigUser['allow_chgmail'] == 1) {
			$edituser->setVar('email', $allowed_requests['email'], true);
		}
		$edituser->setVar('url', $allowed_requests['url'], true);
		$edituser->setVar('user_icq', $allowed_requests['user_icq'], true);
		$edituser->setVar('user_from', $allowed_requests['user_from'], true);
		$edituser->setVar('user_sig', xoops_substr($allowed_requests['user_sig'], 0, 255), true);
		$user_viewemail = !empty($allowed_requests['user_viewemail']) ? 1 : 0;
		$edituser->setVar('user_viewemail', $user_viewemail,true);
		$edituser->setVar('user_aim', $allowed_requests['user_aim'], true);
		$edituser->setVar('user_yim', $allowed_requests['user_yim'], true);
		$edituser->setVar('user_msnm', $allowed_requests['user_msnm'], true);
		if ($allowed_requests['pass'] != '') {
			$edituser->setVar('pass', md5($allowed_requests['pass']), true);
		}
		$attachsig = !empty($allowed_requests['attachsig']) ? 1 : 0;
		$edituser->setVar('attachsig', $attachsig, true);
		$edituser->setVar('timezone_offset', $allowed_requests['timezone_offset'], true);
		$edituser->setVar('uorder', $allowed_requests['uorder'], true);
		$edituser->setVar('umode', $allowed_requests['umode'], true);
		$edituser->setVar('notify_method', $allowed_requests['notify_method'],true);
		$edituser->setVar('notify_mode', $allowed_requests['notify_mode'], true);
		$edituser->setVar('bio', xoops_substr($allowed_requests['bio'], 0, 255), true);
		$edituser->setVar('user_occ', $allowed_requests['user_occ'], true);
		$edituser->setVar('user_intrest', $allowed_requests['user_intrest'], true);
		$edituser->setVar('user_mailok', $allowed_requests['user_mailok'],true);
		if (!empty($_POST['usercookie'])) {
			setcookie($xoopsConfig['usercookie'], $edituser->getVar('uname'), time()+ 31536000);
		} else {
			setcookie($xoopsConfig['usercookie']);
		}

		// extra fields
		if( ! empty( $extra_fields ) ) {
			$db =& Database::getInstance() ;
			foreach( array_keys( $extra_fields ) as $field ) {
				$db->query( "UPDATE ".$db->prefix("users")." SET $field='".addslashes(@$allowed_requests[$field])."' WHERE uid=".$xoopsUser->getVar("uid") ) ;
			}
		}

		if (!$member_handler->insertUser($edituser)) {
			include XOOPS_ROOT_PATH.'/header.php';
			echo $edituser->getHtmlErrors();
			include XOOPS_ROOT_PATH.'/footer.php';
		} else {
			redirect_header('userinfo.php?uid='.$uid, 2, _US_PROFUPDATED);
		}
		exit ;
	}
}

if ($op == 'editprofile') {
	include_once XOOPS_ROOT_PATH.'/header.php';
	$xoopsOption['template_main'] = 'minihaku_edituser.html' ;
	$xoopsTpl->assign(
		array(
			'error_msg' => implode( '<br />' , $errors ) , // older assign
			'stop_reasons' => $errors ,
			'timezone_options' => XoopsLists::getTimeZoneList() ,
			'umode_options' => $umode_options ,
			'uorder_options' => $uorder_options ,
			'notify_method_options' => $notify_method_options ,
			'notify_mode_options' => $notify_mode_options ,
			'yn_options' => array( 1 => _YES , 0 => _NO ) ,
			'usercookie' => intval( ! empty($_COOKIE[$xoopsConfig['usercookie']]) ) ,
			'allow_chgmail' => $xoopsConfigUser['allow_chgmail'] ,
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
	include XOOPS_ROOT_PATH.'/footer.php';
	exit ;
}


?>