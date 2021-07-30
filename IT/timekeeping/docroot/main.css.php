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

	require_once( dirname( __FILE__ )."/./cssConstants.php" );
	header("Content-type: text/css");
?>
	div.donotdisplay {
		display: none;
		}
	
	.MainHeader {
		position: absolute; 
		top: 3px; 
		left: 1%; 
		color: <?php echo CSS_BACKGROUND ?>; 
		font-family: Verdana; 
		font-size: 32px;
		}

	.MainVersion {
		position: absolute; 
		top: 3px; 
		right: 0px; 
		color: <?php echo CSS_BACKGROUND ?>; 
		font-family: Verdana; 
		font-size: 14px;
		}
	
	.MainSeparator {
		position: absolute; 
		top: 46px; 
		left: 1%; 
		height: 1px; 
		width: 98%; 
		background: <?php echo CSS_BACKGROUND ?>;
		font-size: 3px;
		}
		
	body {
		background: <?php echo CSS_BODYCOLOR ?>;
		}

	.MenuBarVerticalSeparator {
		position: absolute; 
		/*top: 1%; 
		left: 99%; 
		height:98%;*/

		top: 1%;
		left: 98%;
		height: 5000px;
 
		width: 1px; 
		background: <?php echo CSS_BACKGROUND ?>;
		z-index: 2;
		}
		
	.MenuItem { 
		color: <?php echo CSS_BACKGROUND ?>; 
		font-family: Verdana; 
		font-size: 14px; 
		height: 20px; 
		left:4%; 
		width: 98%; 
		border-bottom: 1px solid <?php echo CSS_BACKGROUND ?>;
		}	
		
	.MenuItemActive { 
		background: #196060; 
		color: #FFFFFF; 
		font-family: Verdana; 
		font-size: 14px; 
		height: 20px; 
		left:4%; 
		width: 98% 
		}
			
	.MenuLink { 
		text-decoration: none 
		}
		
	.MenuItem:hover { 
		background: #C0F0C0; 
		cursor: pointer; 
		}

	.LangSelect {
		text-align: center; 
		background: <?php echo CSS_BACKGROUND ?>; 
		color: #FFFFFF; 
		font-family: 
		Verdana; font-size: 14px;
		}
		
	.WorkPaneTitleBar {
		background: <?php echo CSS_BACKGROUND ?>;
		width: 100%;
		color: #FFFFFF;
		font-family: verdana;
		font-size: 24px;
		border: 0px solid #000000;
		text-indent: 10px;
		}

	.WorkPaneSectionFooter {
		background: <?php echo CSS_BACKGROUND ?>;
		color: #FFFFFF;
		border: 0px solid #000000;
		}

	.WorkPaneTool {
		background: <?php echo CSS_BACKGROUND ?>;
		color: #FFFFFF;
		font-family: verdana;
		border: 0px solid #FF0000;
		}
		
	.WorkPaneSectionHeader {
		background: <?php echo CSS_BACKGROUND ?>;
		width: 100%;
		color: #FFFFFF;
		font-family: verdana;
		text-indent: 4px;
		padding-bottom: 4px;
		border: 0px solid #FF0000;
		}		

	.WorkPaneSectionBody {
		background: <?php echo CSS_SECTIONCOLOR ?>;
		width: 100%;
		color: #000000;
		font-family: verdana;
		font-size: 10px;
		}
		
		
	.WorkPaneMenuCellSelected, 
	.WorkPaneMenuCell { 
		}
		

	.WorkPaneMenuCell,
	.WorkPaneMenuCellSelected,	 
	.WorkPaneMenuCell:link, 
	.WorkPaneMenuCell:visited, 
	.WorkPaneMenuCell:hover,
	.WorkPaneMenuCell:active {
		background:#C34545;
		color: #404040; 
		text-decoration: none;
		cursor: pointer;
		font-family: verdana;
		font-size: 12px;
		text-decoration: none;
		padding-left: 30px;
		padding-right: 30px;
		height: 40px;
		border-top: 1px solid <?php echo CSS_BACKGROUND ?>;
		border-bottom: 1px solid <?php echo CSS_BACKGROUND ?>;
		}

	.WorkPaneMenuCellSelected { 
		background:#4090A0;
		color: #202020;
		}	 

	.WorkPaneMenuCell:hover {
		background:#C0F0C0;
		}


	.WorkPaneMenuSpacer {
		}

	.WorkPaneSortCell {
		background:<?php echo CSS_BACKGROUND ?>;
		color: #FFFFFF; 
		text-decoration: none;
		cursor: pointer;
		font-family: verdana;
		font-size: 10px;
		text-decoration: none;
		}
		
	.WorkPaneSortCell:hover {
		background: #E0E0D0;
		color: #404040;
		}
		
	.WorkPaneListTable {
		background: <?php echo CSS_BACKGROUND ?>;
        border-collapse: collapse;
		}
				
	.WorkPaneViewListTitle {
		background: <?php echo CSS_BACKGROUND ?>;
		width: 100%;
		color: #FFFFFF;
		font-family: verdana;
		font-size: 10px;
		border: 1px solid <?php echo CSS_BACKGROUND ?>;
		}	

	.WorkPaneListCallOut {
		background: #E0E0E0;
		font-family: verdana;
		color: #000000;
		font-size: 10px;
		width:100%
		}	

	.WorkPaneFormField {
		font-family: verdana;
		color: #000000;
		font-size: 10px;
		padding-top: 2px;
		padding-bottom: 2px;
		padding-right: 10px;
		padding-left: 10px;
		text-align: left; 
		}	

	.WorkPaneFormLabel {
		padding: 5px; 
		text-indent: 3px; 
		color: #000000;
		text-align: center; 
		}

	.WorkPaneTD,
	.WorkPaneTDHead {
		border: 1px solid <?php echo CSS_BACKGROUND ?>;
		padding: 3px;
		}
	
	.WorkPaneTDHead {
		background:  <?php echo CSS_ALT2 ?>;
        cursor: pointer;
		}

	.WorkPaneListDisabled,
	.WorkPaneListError,
	.WorkPaneListAlt1,
	.WorkPaneListAlt2,
	.WorkPaneListAlt3 {
		font-family: verdana;
		color: #000000;
		font-size: 10px;
		height: 18px;
		}
		
	.WorkPaneListError {
		background: #FF0000;
		}

	.WorkPaneListAlt1 {
		background:  <?php echo CSS_ALT1 ?>;;
		}
	
	.WorkPaneListAlt2 {
		background:  <?php echo CSS_ALT2 ?>;
		}	

	.WorkPaneListAlt3 {
		background: #80F080;
		}	

	.WorkPaneListDisabled {
		background: #808080;
		}
		
	.AuthorizeListopen,
	.AuthorizeListretracted,
	.AuthorizeListpending,
	.AuthorizeListrejected,
	.AuthorizeListauthorized,
	.AuthorizeListfinalized {
		font-family: verdana;
		color: #000000;
		font-size: 10px;
		height: 18px;
		}
		
	.AuthorizeListopen {
		background: #C7FFFF;
		}
		
	.AuthorizeListretracted {
		background: #C7C7FF;
		}
	
	.AuthorizeListpending {
		background: #FFFFC7;
		}
		
	.AuthorizeListrejected {
		background: #FFC7FF;
		}
		
	.AuthorizeListauthorized {
		background: #C7FFC7;
		}
		
	.AuthorizeListfinalized {
		background: #FFC7C7;
		}

	.RecZeroVal, 
	.RecNegVal, 
	.RecPosVal {
		font-family: verdana;
		color: #000000;
		font-size: 10px;
		}

	.RecZeroVal {
		background: #FFFFFF;
		}

	.RecNegVal {
		background: #FF8080;
		}

	.RecPosVal {
		background: #80FF80;
		}

	.LoadingTag {
		position:absolute; 
		right: 0px; 
		top: 0px; 
		background: #FF0000; 
		color: #FFFFFF;
		font-family: verdana;
		font-size: 12px;
		}

	.TopPane {
		visibility: hidden;
		}
		
	.ErrorText {
		font-family: verdana;
		color: #FF0000;
		font-size: 10px;
		font-weight: bold;
		}

	.NormalText {
		font-family: verdana;
		color: #000000;
		}

	/* for nifty corners */
.rtop,.artop{display:block}
.rtop *,.artop *{display:block;height:1px;overflow:hidden;font-size:1px}
.artop *{border-style: solid;border-width:0 1px}
.r1,.rl1,.re1,.rel1{margin-left:5px}
.r1,.rr1,.re1,.rer1{margin-right:5px}
.r2,.rl2,.re2,.rel2,.ra1,.ral1{margin-left:3px}
.r2,.rr2,.re2,.rer2,.ra1,.rar1{margin-right:3px}
.r3,.rl3,.re3,.rel3,.ra2,.ral2,.rs1,.rsl1,.res1,.resl1{margin-left:2px}
.r3,.rr3,.re3,.rer3,.ra2,.rar2,.rs1,.rsr1,.res1,.resr1{margin-right:2px}
.r4,.rl4,.rs2,.rsl2,.re4,.rel4,.ra3,.ral3,.ras1,.rasl1,.res2,.resl2{margin-left:1px}
.r4,.rr4,.rs2,.rsr2,.re4,.rer4,.ra3,.rar3,.ras1,.rasr1,.res2,.resr2{margin-right:1px}
.rx1,.rxl1{border-left-width:5px}
.rx1,.rxr1{border-right-width:5px}
.rx2,.rxl2{border-left-width:3px}
.rx2,.rxr2{border-right-width:3px}
.re2,.rel2,.ra1,.ral1,.rx3,.rxl3,.rxs1,.rxsl1{border-left-width:2px}
.re2,.rer2,.ra1,.rar1,.rx3,.rxr3,.rxs1,.rxsr1{border-right-width:2px}
.rxl1,.rxl2,.rxl3,.rxl4,.rxsl1,.rxsl2,.ral1,.ral2,.ral3,.ral4,.rasl1,.rasl2{border-right-width:0}
.rxr1,.rxr2,.rxr3,.rxr4,.rxsr1,.rxsr2,.rar1,.rar2,.rar3,.rar4,.rasr1,.rasr2{border-left-width:0}
.r4,.rl4,.rr4,.re4,.rel4,.rer4,.ra4,.rar4,.ral4,.rx4,.rxl4,.rxr4{height:2px}
.rer1,.rel1,.re1,.res1,.resl1,.resr1{border-width:1px 0 0;height:0px !important;height /**/:1px}

.popupcontentStyle {   
	background:#F9F9F9;   
    cursor: pointer;
	position: absolute;   
	visibility: hidden;   
	overflow-x: hidden; overflow-y: auto;
	border:1px solid #CCC;   
	border:1px solid #333;   
	padding:0px;
	left:10px;
	top:10px;
	width:10px;
	height:10px;
}

	.TrackerLine { 
		font-family: Courier; 
		font-size: 10px; 
		border: 0px solid #000000;
		padding: 0px 1px 0px 1px;
		}
		
	.Slotempty { 
		width:99%; height:100%;
		background: #F0F0F0;
		border:1px solid #FFFFFF;
		}			

	.Slotopen { 
		width:99%; height:100%;
		background: #FFF0FF;
		border:1px solid #FFFFFF;
		}	
	
	.PopupLink {
		font-family: Courier; 
		font-size: 10px; 
		text-decoration: none;
		}
		
	.PopupPopular {
		border-top:1px solid #909090;
		width:100%;
		background-color: #F0E0F0;
		font-family: Courier; 
		font-size: 10px; 
		}

	.PopupRemove {
		border-top:1px solid #909090;
		width:100%;
		background-color: #E0F0F0;
		font-family: Courier; 
		font-size: 10px; 
		}

	.PopupRest {
		border-top:1px solid #909090;
		width:100%;
		background-color: #F0F0E0;
		font-family: Courier; 
		font-size: 10px; 
		}
		
	.PopupSelect {
		border-top:1px solid #909090;
		width:100%;
		background-color: #C0C0C0;
		font-family: Courier; 
		font-size: 10px; 
		}	

	.MiniCalTD { 
		font-family: Courier;
		font-size: 10px; 
		border:1px solid #FFFFFF;
		padding: 0px 1px 0px 1px;
		}
	
	.MiniCalTD:hover {
		cursor: pointer;
	}
	
	.TrackerLine:hover {
		cursor: default;
	}
		
	.Daynotinperiod { 
		background: #DCDCDC;
		}
		
	.Dayempty { 
		background: lightblue;
		}	
		
	.Authorizedday {
		background: yellow;
		}
		
	.Unauthorizedday {
		background: red;
		}

	.TrackerText { 
		font-family: Courier;
		font-size: 10px; 
	}
	
	.AuditTable {
		display:inline;
		border:1px solid #000000;
	}
	
	.AuditTableHeading {
		background: blue;
		color: white;
		white-space: nowrap;
	}
	
	.AuditTableHeadingDeleted {
		background: red;
		color: white;
		white-space: nowrap;
	}
	
	.AuditTableCell {
		white-space: nowrap;
	}
	
	.AuditTR { 
		font-family: Courier;
		font-size: 10px; 
	}

	#statusfooter {
		position:absolute;
		bottom:0;
		font-family: Courier;
		font-size: 10px;
	}	
