<?php

namespace app\models;
use app\core\DbModel;

/**
 * Feedback
 * 
 * Documented example of how Models should look
 * They should be paired with a table in the database
 */
class Feedback extends DbModel {
    
    // Each column in the database should be named the same and listed as a variable here:
    // They must have some default value, or assigned in a constructor
    public string $solicitation_date = '';
    public string $solicitation_reason = '';
    public string $solicitor_id = '';
    public string $commenter_id = '';
    public string $subject_id = '';
    public string $comment = '';
    public string $feedback_date = '';
    public string $disposition = '';
    public string $type = '';
    public string $closed_date = '';

    /**
     * Table Name
     *
     * Name of table in the Mysql schema for the PDO to reference
     * The table name shouldn't be used in another model for good practice
     * @return string
     */
    public static function tableName(): string
    {
        return 'FEEDBACK';
    }

    /**
     * Primary Key
     * 
     * Unique identifier for the table, should be 'id'
     * @return string
     */
    public function primaryKey(): string
    {
        return 'FEEDBACK_ID';
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
        return [
            'solicitation_date', 'solicitation_reason', 'solicitor_id',
            'commenter_id', 'subject_id', 'comment', 'feedback_date', 'disposition', 
            'type', 'closed_date'
        ];
    }

    public function rules(): array
    {
        return [
            'subject_id' => [self::RULE_REQUIRED],
            'comment' => [self::RULE_REQUIRED],
            'disposition' => [self::RULE_REQUIRED]
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