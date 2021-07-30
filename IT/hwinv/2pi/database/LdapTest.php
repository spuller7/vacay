<?php
include("LdapLib.php");

//$myuser = new LdapUser("cadams");
//echo $myuser->getUid().": ".$myuser->getFullName()."\n";
foreach(LdapUser::getAllUsers() as $user)
{
    $thisuser = new LdapUser($user);
    echo $user.": ".$thisuser->getFullName()."\n";
//      echo $user.": ".$thisuser->getMail()."\n";
}
//$myuser = new LdapUser("jenkins");
//echo $myuser->getUid().":\n";
//print_r($myuser->getHosts());

print_r(LdapGroup::getAllGroups());
//print_r(LdapGroup::getUserGroups("ddelpreto"));
print_r(LdapGroup::getGroupMembers("Jabber-QS"));
/*echo "Is jwalker in Jabber-QS?\n";
if (LdapGroup::isUserInGroup("jwalker","Jabber-QS"))
        echo "Yes\n";
else
        echo "No\n";
echo "Is jwalker in VPNgroup?\n";
if (LdapGroup::isUserInGroup("jwalker","VPNgroup"))
        echo "Yes\n";
else
        echo "No\n";*/

?>