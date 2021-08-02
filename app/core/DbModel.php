<?php

//Might just be MYSQL

namespace app\core;

abstract class DbModel extends Model
{
    abstract public static function tableName(): string;
    abstract public function attributes(): array;
    abstract public function primaryKey(): string;

    public function save()
    {
        $tableName = $this->tableName();
        $attributes = $this->attributes();
        $params = array_map(fn($attr) => ":$attr", $attributes);
        $statement = self::prepare("INSERT INTO $tableName (".implode(',', $attributes).") VALUES (".implode(',', $params).")");
        foreach ($attributes as $attribute)
        {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }

        $statement->execute();
        return true;
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

        $attributes = array_keys($conditions);
        $sql = implode("AND" ,array_map(fn($attr) => "$attr = :$attr", $attributes));
        $statement = self::prepare("SELECT * FROM $tableName WHERE $sql");
        foreach ($conditions as $key => $item)
        {
            $statement->bindValue(":$key", $item);
        }

        $statement->execute();
        return $statement->fetchObject(static::class);
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