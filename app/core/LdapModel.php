<?php

namespace app\core;

abstract class LdapModel extends Model
{

    public $LdapUri="ldap://projtrack-dev.internal.quantumsignal.com";
    public $LdapPeopleOu="ou=People,dc=ldap,dc=internal,dc=quantumsignal,dc=com";
    private $LdapGroupOu="ou=Group,dc=ldap,dc=internal,dc=quantumsignal,dc=com";

    abstract public function primaryKey(): string;
    abstract public function getDisplayName(): string;
    abstract public function getID(): int;

    /**
     * findOne
     *
     * @param array $where [email => example@email.com, firstname => Travis]
     * @return object
     */
    public function findOne($where)
    {
        $attributes = array_keys($where);
        $params = implode("," ,array_map(fn($attr) => "$attr=$where[$attr]", $attributes));
        $conn = $this->connect();
        $result = ldap_search($conn, $this->LdapPeopleOu, $params);
        $entries = ldap_get_entries($conn, $result);

        if ($entries['count'])
        {
            return $entries[0];
        }

        return false; 
    }

    public function findAll()
    {
        $conn = $this->connect();
        $result = ldap_search($conn, $this->LdapPeopleOu, '(uidNumber>=1)');
        $entries = ldap_get_entries($conn, $result);
        return $entries ?? false; 
    }

    public function connect()
    {
        $conn = ldap_connect($this->LdapUri);;
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_bind($conn) or die("Could not connect to LDAP server");
        return $conn;
    }
}