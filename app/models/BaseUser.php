<?php

class BaseUser extends Model {

    private $db;
    protected static $loggedInUser = null;

    public function __construct() {
        $this->db = new Database;
    }

    public function getLoggedInUser() {
        $user = null;

        if (static::$loggedInUser)
        {
            return static::$loggedInUser;
        }
    }
}