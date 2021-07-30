<?php
//////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2008, Quantum Signal, LLC
// 
// This data and information is proprietary to, and a valuable trade 
// secret of, Quantum Signal, LLC.  It is given in confidence by Quantum 
// Signal, LLC. Its use, duplication, or disclosure is subject to the
// restrictions set forth in the License Agreement under which it has 
// been distributed.
//
//////////////////////////////////////////////////////////////////////

require_once( dirname( __FILE__ )."/../authenticate.php" );
require_once( "../common.php" );
require_once( "../utils.php" );
require_once( "./UserManager.php" );

checkRole( Roles::USER_MANAGER );

//import_request_variables('p', 'um_');
extract($_POST, EXTR_PREFIX_ALL, 'um');

$userid = $um_Userid;
$user = AdminUser::retrieve( $userid );

$writable = ($userid != userid()); 
if ( $writable )
{
	$adminRoles = roles();
	foreach( array_keys($adminRoles) as $role )
	{
		$user->setRole( $role, isset( $_POST[$role] ) );
	}
}

echo "<html><body><script>";
echo "document.location='UserView.php';";
echo "</script></body></html>";

?>
