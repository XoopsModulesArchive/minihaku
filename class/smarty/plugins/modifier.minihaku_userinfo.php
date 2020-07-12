<?php

function smarty_modifier_minihaku_userinfo( $uid , $index = 'uname' , $uid_from_get = null )
{
	if( empty( $uid_from_get ) ) {
		$uid = intval( $uid ) ;
	} else {
		$uid = intval( $_GET[$uid_from_get] ) ;
	}

	if( $uid == 0 && $index == 'uname' ) return _GUESTS ;

	$member_handler =& xoops_gethandler('member');
	$user =& $member_handler->getUser( $uid ) ;

	if( is_object( $user ) ) {

		if( empty( $user->vars[$index] ) ) {
			if( file_exists( XOOPS_ROOT_PATH.'/modules/minihaku/include/config.php' ) ) {
				$minihaku_uid4whr = $user->getVar('uid') ;
				include XOOPS_ROOT_PATH.'/modules/minihaku/include/config.php' ;
				if( isset( $fields4html[$index] ) ) {
					return $fields4html[$index] ;
				}
			}
			return '' ;
		} else {
			return $user->getVar( $index ) ;
		}
	} else {
		return '' ;
	}
}

?>