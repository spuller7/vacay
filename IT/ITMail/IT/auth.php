<?php

session_start();

if ( !isset($_SESSION["authenticated"]))
{

if(isset($_POST['username']) && isset($_POST['password'])){

    $adServer = "ldap://ldap.mgmt.quantumsignal.com";
	
    $ldap = ldap_connect($adServer);
    $username = $_POST['username'];
    $password = $_POST['password'];

    $ldaprdn = "uid=".$username.",ou=People,dc=ldap,dc=internal,dc=quantumsignal,dc=com";

    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);


    if ( ldap_start_tls($ldap)) 
	    $bind = @ldap_bind($ldap, $ldaprdn, $password);
	else
		$bind = false;

    $isIT = 0;
    if ($bind) {
        $filter="(&(cn=ISShare)(memberUid=".$username."))";
	$attr = array("cn");
        $result = ldap_search($ldap,"ou=group,dc=ldap,dc=internal,dc=quantumsignal,dc=com",$filter,$attr);
        $info = ldap_get_entries($ldap, $result);
	$isIT = $info["count"] > 0;
        @ldap_close($ldap);
    }
	if ( $isIT )
	{
		$_SESSION["authenticated"] = true;
		echo "<html><script>document.location='".$_SERVER['PHP_SELF']."';</script></html>";
		exit(0);
	}
else
{
	echo "No dice, chump";
	exit(0);
}

}else{
?>
    <form action="#" method="POST">
        <label for="username">Username: </label><input id="username" type="text" name="username" /> 
        <label for="password">Password: </label><input id="password" type="password" name="password" />        <input type="submit" name="submit" value="Submit" />
    </form>
<?php
exit(0);
 } 
}
?> 

