<?php
require_once( dirname( __FILE__ )."/../authenticate.php" );
require_once( "../common.php" );
require_once( "./UserManager.php" );

checkRole( Roles::USER_MANAGER );

	//import_request_variables('g', 'um_');
	extract($_GET, EXTR_PREFIX_ALL, 'um');
	
	$user = AdminUser::retrieve( $um_Userid );
	$writable = ($um_Userid != userid()); //don't let the user edit themself
	$adminRoles = roles();
	$userRoles = $user->roles();
	
	emitStdProlog();
?><script>	

window.onload=function(){
if ( NiftyCheck() )
{ 
	Rounded("div.WorkPaneTitleBar","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>");
	RoundedTop("div.RoundHeader","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>"); 
	RoundedBottom("div.RoundFooter","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>"); 
}
<?php emitLoadingJS(); ?>
}
</script>
<?php emitLoadingHeader(); ?>
	<div class='WorkPaneTitleBar'>Role Assignment for <?php echo $user->FullName; ?></div>
	<div id='ErrorMsgArea'></div><?php
	emitMenuStart();
	emitMenuItem( '&lt;&lt;&nbsp;back','Return to subject list', 'UserView.php');
	emitMenuEnd(); ?>
	<center>
	<form action='UserRoles.php' method='post'>
	<input type=hidden name='Userid' value='<?php echo $um_Userid ?>'>
	<table class='WorkPaneListTable' cellpadding=0 cellspacing=0><?php
	ksort( $adminRoles );	
	if ( $writable )
	{
		$i = 0;
		?><tr class='WorkPaneSectionHeader'>
			<td colspan=3>
				<div class='RoundHeader'>Assignable Roles</div>
			</td>
		</tr><?php
		foreach ( array_keys($adminRoles) as $adminRole )
		{
			if ( $i % 3 == 0 )
			{
				?><tr><?php
			}
			?><td style='background: #FFFFFF; <?php
			if ( $i % 3 == 0 )
			{
				?> border-left: 1px solid <?php echo  CSS_BACKGROUND?>;<?php
			}
			else if ( $i % 3 == 2 )
			{
				?> border-right: 1px solid <?php echo CSS_BACKGROUND?>;<?php
			}
			?>' align='left'>
			<input type=checkbox name='<?php echo $adminRole ?>' <?php echo isset( $userRoles[ $adminRole ] )  ? 'CHECKED' : '' ?>><?php echo Roles::getDescription($adminRole); ?></td><?php
			if ( $i % 3 == 2 )
			{
				?></tr><?php
			}
			$i++;
		}
		if ( $i % 3 != 0 )
		{
			while ( ($i % 3) != 0)
			{
				?><td style='background: #FFFFFF;<?php
				if ( $i % 3 == 2 )
				{
					?> border-right: 1px solid <?php echo CSS_BACKGROUND ?>;<?php
				}				
				?>'/><?php
				$i++;
			}
			?></tr><?php
		}
		$otherKeys = array_diff( array_keys($userRoles), array_keys($adminRoles) );
		if ( $writable )
		{
			?><tr><td><input type=submit value='Update'> <input type=button onclick='document.location="./UserView.php";' value='Cancel'></td></tr><?php
		}
	}
	else
	{
		$otherKeys = array_keys($adminRoles);
	}
	if ( count($otherKeys) > 0 )
	{
		?><tr style='background: <?php echo CSS_BODYCOLOR;?>'><td colspan='3'>&nbsp;</td></tr>
		<tr class='WorkPaneSectionHeader'><td colspan=3><div class='RoundHeader'>Immutable Roles</div></td></tr><?php
		$i = 0;
		foreach ( $otherKeys as $otherRole )
		{
			if ( $i % 3 == 0 )
			{
				?><tr><?php
			}
			?><td style='background: #FFFFFF;<?php
			if ( $i % 3 == 0 )
			{
				?> border-left: 1px solid <?php echo CSS_BACKGROUND?>;<?php
			}
			else if ( $i % 3 == 2 )
			{
				?> border-right: 1px solid <?php echo CSS_BACKGROUND?>;<?php
			}
			?>' align='left'><img src='../images/ok_16.gif' border=0><?php echo Roles::getDescription($otherRole);?></td><?php
			if ( $i % 3 == 2 )
			{
				?></tr><?php
			}
			$i++;
		}
		if ( $i % 3 != 0 )
		{
			while ( ($i % 3) != 0)
			{
				?><td style='background: #FFFFFF;<?php
				if ( $i % 3 == 2 )
				{
					?> border-right: 1px solid <?php echo CSS_BACKGROUND ?>;<?php
				}				
				?>'/><?php
				$i++;
			}
			?></tr><?php
		}
	}
?>
<tr class='WorkPaneSectionFooter'>
	<td colspan=3>
		<div class='RoundFooter'></div>
	</td>
</tr>
</table>	
</form>
</center>
<?php emitLoadingFooter(); ?>
