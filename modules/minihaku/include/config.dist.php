<?php

// A sample configuration for adding fields into the users table
// Rename config.dist.php -> config.php first


// preferences
$auto_belong_groups = array( XOOPS_GROUP_USERS ) ; // default (2)
$allow_blank_email = false ;
$allow_blank_vpass = false ;

// regkey sample
// if( $_POST['regkey'] == '(key)' ) $auto_belong_groups[] = (group_number) ;


// There are a sample of adding two fields 'sex' and 'birth' into users table
/* You should issue queries like this:
alter table (prefix)_users add sex tinyint not null default 0 ;
alter table (prefix)_users add birth date not null default '1950-01-01' ;
*/

// fields definition
// if you want to add checkbox, set initval as boolean
$extra_fields = array(
	'sex' => array(
		'initval' => -1 ,
		'options' => array( 0 => 'male' , 1 => 'female' ) ,
		) ,
	'birth' => array(
		'initval' => '1950-01-01' ,
		) ,
	) ;


// initVars
if( empty( $minihaku_uid4whr ) ) {
	foreach( $extra_fields as $key => $attribs ) {
		$allowed_requests[$key] = $attribs['initval'] ;
	}
} else {
	$db =& Database::getInstance() ;
	list( $allowed_requests['sex'] , $allowed_requests['birth'] ) = $db->fetchRow( $db->query( "SELECT sex,birth FROM ".$db->prefix("users")." WHERE uid=$minihaku_uid4whr" ) ) ;
	$allowed_requests['sex'] = intval( $allowed_requests['sex'] ) ;

	// for the plugin of modifier.minihaku_userinfo.php
	$fields4html['sex'] = $extra_fields['sex']['options'][ $allowed_requests['sex'] ] ;
	$fields4html['birth'] = str_replace( '-' , '/' , $allowed_requests['birth'] ) ;
}

// request maintenances
if( isset( $_POST['sex'] ) ) {
	if( $_POST['sex'] < 0 || $_POST['sex'] > 1 ) {
		$stop_reason_extras[] = "invalid sex value" ;
	}
}
if( ! empty( $_POST['Date_Year'] ) ) {
	$_POST['birth'] = intval( $_POST['Date_Year'] ) . '-' . intval( $_POST['Date_Month'] ) . '-' . intval( $_POST['Date_Day'] ) ;
}

?>