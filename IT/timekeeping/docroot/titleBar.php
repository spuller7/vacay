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

require_once( "./authenticate.php" );
require_once( "./common.php" );
emitStdProlog( "." );
emitStdHeader();
?>
		<div class="MainHeader">Chronoton Gatherer</div>
		<div class='MainVersion'><?php echo buildInfo() ?></div>
		<div class="MainSeparator"/>
<?php
emitStdFooter();
?>