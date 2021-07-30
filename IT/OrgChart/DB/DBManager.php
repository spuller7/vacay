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

class DBManager
{
		function __construct($servername, $db, $username, $password)
		{
			$this->m_pdo = new PDO('mysql:host='.$servername.';dbname='.$db,$username,$password, array( PDO::ATTR_PERSISTENT => true ) );
			$this->m_pdo->exec('SET CHARACTER SET utf8');
		}	
		
		function pdo()
		{
			return $this->m_pdo;
		}	
		
		function exec( $sql )
		{
			return $this->m_pdo->exec( $sql );
		}		
		
		function __destruct()
		{
			$this->m_pdo = null;
		}		

}

function executeSQL( $sql, $data )
{
	$db = new DBManager();
	$pdo = $db->pdo();
	$sth = $pdo->prepare( $sql );
	$sth->execute( $data );
	checkDBError( $sth );
	return $sth;
}	

function checkDBError( $sth )
{
	if ( $sth->errorCode() != "00000" )
	{
		throw new Exception( $sth->errorCode()."\n".var_export($sth->errorInfo(),true) );
	}
}	
