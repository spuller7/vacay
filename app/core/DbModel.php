<?php

//Might just be MYSQL

namespace app\core;

abstract class DbModel extends Model
{
    abstract public static function tableName(): string;
    abstract public function attributes(): array;
    abstract public function primaryKey(): string;

    /**
     * Save
     *
     * @return void
     */
    public function save()
    {
        $tableName = $this->tableName();
        $attributes = $this->attributes();

        // Make attributes lower case if database has uppercase name convention
        $lowercase_attributes = array_map('strtolower', $attributes);

        $params = array_map(fn($attr) => ":".$attr, $lowercase_attributes);

        if ($this->id)
        $statement = self::prepare("INSERT INTO $tableName (".implode(',', $attributes).") VALUES (".implode(',', $params).")");
        foreach ($lowercase_attributes as $attribute)
        {
            if ($this->{$attribute} instanceof DateTime)
            {
                $this->{$attribute} = $this->{$attribute}->format('Y-m-d H:i:s');
            }

            $statement->bindValue(":$attribute", $this->{$attribute});
        }

        $statement->execute();
        return static::fetchPDOObject($statement);
    }

    // Execute sql statement and modify object returned
    /**
     * fetchPDOObject
     *
     * @param [type] $statement
     * @return void
     */
    public static function fetchPDOObject($statement)
    {
        $statement->execute();
        $obj = $statement->fetchObject(static::class);

        if (!$obj)
        {
            return false;
        }

        // If the object's primary key isn't 'id', then assign 'id' to the primary key
        // So that it's in every data object across the application
        if ($obj->{static::primaryKey()} && static::primaryKey() !== 'id')
        {
            $obj->id = $obj->{static::primaryKey()};
        }

        return $obj;
    }

    /**
     * findOne
     *
     * @param array $conditions [email => example@email.com, firstname => Travis]
     * @return object
     */
    public static function findOne($conditions)
    {
        $tableName = static::tableName();

        // Build string for WHERE statement
        $attributes = array_keys($conditions);
        $sql = implode("AND" ,array_map(fn($attr) => "$attr = :$attr", $attributes));

        // Create SELECT statement string
        $statement = self::prepare("SELECT * FROM $tableName WHERE $sql");

        foreach ($conditions as $key => $item)
        {
            $statement->bindValue(":$key", $item);
        }

        return static::fetchPDOObject($statement);
    }

    /**
     * findOneByID
     *
     * @param int $id
     * @return object
     */
    public static function findOneByID($id)
    {
        $tableName = static::tableName();
        $statement = self::prepare("SELECT * FROM $tableName WHERE ".static::primaryKey()." = :id");
        $statement->bindValue(":id", $id);
        
        return static::fetchPDOObject($statement);
    }
    
    /**
     * Find All
     *
     * @param [type] $conditions
     * @return void
     */
    public static function findAll($params = [])
    {
        
        $tableName = static::tableName();
        $conditions = '';

        if ($params)
        {
            $attributes = array_keys($params);
            $conditions = "WHERE ".implode(" AND " ,array_map(fn($attr) => $params[$attr] == null ? "$attr IS NULL" : "$attr = :$attr", $attributes));
        }

        $statement = self::prepare("SELECT * FROM $tableName $conditions");

        foreach ($params as $key => $item)
        {
            if ($item !== null)
                $statement->bindValue(":$key", $item);
        }

        $statement->execute();
        
        return $statement->fetchAll();
    }

    public static function prepare($sql)
    {
        return Application::$app->db->pdo->prepare($sql);
    }

    public function setAttributes($array)
    {
        $attributes = $this->attributes();

        if (method_exists($this, 'beforeSetAttributes'))
        {
            $array = call_user_func_array(array($this, 'beforeSetAttributes'), func_get_args());
        }

        foreach($array as $key => $value)
        {
            if (in_array($key, $attributes))
            {
                $this->{$key} = $value;
            }
        }
        
        if (method_exists($this, 'afterSetAttributes'))
        {
            call_user_func_array(array($this, 'afterSetAttributes'), func_get_args());
        }
    }
}