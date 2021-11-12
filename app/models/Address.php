<?php

namespace app\models;
use app\core\DbModel;

/**
 * Address
 * 
 * Documented example of how Models should look
 * They should be paired with a table in the database
 */
class Address extends DbModel {
    
    // Each column in the database should be named the same and listed as a variable here:
    // They must have some default value, or assigned in a constructor
    public $adventure_id = null;
    public $address = null;
    public $city = null;
    public $state = null;
    public $zipcode = null;
    public $country = null;
    public $latitude = null;
    public $longitude = null;

    /**
     * Table Name
     *
     * Name of table in the Mysql schema for the PDO to reference
     * The table name shouldn't be used in another model for good practice
     * @return string
     */
    public static function tableName(): string
    {
        return 'addresses';
    }

    /**
     * Primary Key
     * 
     * Unique identifier for the table, should be 'id'
     * @return string
     */
    public function primaryKey(): string
    {
        return 'id';
    }

    /**
     * Attributes
     *
     * all attributes that can be accessible/editable
     * Ignore: id, updated, created (these are managed in the database automatically)
     * @return array
     */
    public function attributes(): array
    {
        return ['adventure_id', 'address', 'city', 'state',
                'zipcode', 'country', 'latitude', 'longitude'];
    }

    public function rules(): array
    {
        return [];
    }

    public function save()
    {
        // Change / clean values here before being saved in the database

        return parent::save();
    }

    /**
     * beforeSetAttributes -- Optional
     * 
     * execute any data handling before assigning an array of attributes
     * to attributes of the Model. Will be used minimally but is available
     */
    public function beforeSetAttributes($attributes)
    {
        // Change / clean values here

        return $attributes;
    }

    /**
     * afterSetAttributes -- Optional
     * 
     * executed after setAttributes()
     * Most likely won't need but is available if needed
     */
    public function afterSetAttributes()
    {
        // Do something
    }
}