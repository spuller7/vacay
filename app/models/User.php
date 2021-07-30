<?php

namespace app\models;

use app\core\Application;
use app\core\LdapModel;
use app\core\Ldap;

use app\models\ReviewerAssignment;

class User extends LdapModel
{
    // Same as uidNumber in LDAP
    public $id = null;

    // Same as uid in LDAP
    public $username = '';

    public $givenName = '';
    public $sn = '';
    public $mail = '';
    public $gidNumber = null;
    public $hosts= [];

    public function primaryKey(): string
    {
        return 'username';
    }

    public function getID(): int
    {
        return $this->id;
    }

    public function rules(): array
    {
        return [];
    }

    public function findByUsername($username)
    {
        $ldap_user = $this->findOne(['uid' => $username]);

        if (!$ldap_user)
        {
            return false;
        }

        $this->username = $username;
        $this->createUserFromLdapUser($ldap_user);

        return $this;
    }

    public function createUserFromLdapUser($ldap_user)
    {
        if (isset($ldap_user["uidnumber"][0]))
            $this->id = $ldap_user["uidnumber"][0];
        else
            $this->id = 0;

        if (isset($ldap_user["uid"][0]))
            $this->username = $ldap_user["uid"][0];


        if (isset($ldap_user["givenname"][0]))
            $this->givenName = $ldap_user["givenname"][0];
        if (isset($ldap_user["sn"][0]))
            $this->sn = $ldap_user["sn"][0];
        if (isset($ldap_user["uidnumber"][0]))
            $this->uid = ($ldap_user["uidnumber"][0])|0;
        if (isset($ldap_user["mail"][0]))
            $this->mail = $ldap_user["mail"][0];
        if ( isset($ldap_user["jpegphoto"][0]))
            $this->photo = base64_encode($ldap_user["jpegphoto"][0]);
        
        if (isset($ldap_user["host"]))
        {
            for ($i=0; $i<$ldap_user["host"]["count"]; $i++)
                array_push($this->hosts,$ldap_user["host"][$i]);
        }
    }

    public function authenticate($password)
    {	
        $conn = $this->connect();
        $dn = "uid=".$this->username.",".$this->LdapPeopleOu;
        return @ldap_bind($conn, $dn, $password);
    }

    public function getDisplayName(): string
    {
        return $this->givenName;
    }

    public function getFullName(): string
    {
        return $this->givenName.' '.$this->sn;
    }

    public static function getAllUsers()
    {
        $ldap = new User;
        $ldapUsers = $ldap->findAll();

        $userList = [];
        foreach($ldapUsers as $user)
        {
            $u = new User;
            $u->createUserFromLdapUser($user);

            $userList[$u->id] = $u;
        }

        return $userList;
    }

    public static function getSubjects()
    {
        if (Application::$app->loggedInUser)
        {
            $employee_ids = ReviewerAssignment::findAllById(Application::$app->loggedInUser->getID());
            error_log(print_r($employee_ids, true));
            return array_intersect_key($employee_ids, Application::$app->users);
        }

        return null;
    }
}