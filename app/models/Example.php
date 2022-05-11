<?php

namespace app\models;
use app\core\DbModel;

/**
 * Example
 * 
 * Documented example of how Models should look
 * They should be paired with a table in the database
 */
class Example extends DbModel {
    
    // Each column in the database should be named the same and listed as a variable here:
    // They must have some default value, or assigned in a constructor
    public string $column1 = '';
    public string $column2 = '';
    public string $column3 = '';

    /**
     * Table Name
     *
     * Name of table in the Mysql schema for the PDO to reference
     * The table name shouldn't be used in another model for good practice
     * @return string
     */
    public static function tableName(): string
    {
        return 'example';
    }

    /**
     * Primary Key
     * 
     * Unique identifier for the table, should be 'id'
     * @return string
     */
    public static function primaryKey(): string
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
        return ['column1', 'column2', 'column3'];
    }

    public function rules(): array
    {
        return [
            'column1' => [self::RULE_REQUIRED],
            'column2' => [self::RULE_REQUIRED, [self::RULE_MATCH, 'match' => 'column1']],
            'column3' => [self::RULE_EMAIL, [self::RULE_UNIQUE, 'class' => self::class]]
        ];
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