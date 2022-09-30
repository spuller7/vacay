<?php

//Might just be MYSQL

namespace app\core;

abstract class DbModel extends Model
{
    abstract public static function tableName(): string;
    abstract public function attributes(): array;
    abstract public static function primaryKey(): string;
    public $id = null;

    const PARAMETER_DATA_TYPE_KEY = 'dataType';
    const PARAMETER_VALUE_KEY = 'value';

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

        // TODO update existing item
        if ($this->id)
        {

        }

        $statement = Application::$app->db->pdo->prepare("INSERT INTO $tableName (".implode(',', $attributes).") VALUES (".implode(',', $params).")");
        foreach ($lowercase_attributes as $attribute)
        {
            if ($this->{$attribute} instanceof DateTime)
            {
                $this->{$attribute} = $this->{$attribute}->format('Y-m-d H:i:s');
            }

            $statement->bindValue(":$attribute", $this->{$attribute});
        }

        static::fetchPDOObject($statement);
        return static::findOneByID(Application::$app->db->pdo->lastInsertId());
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
     * @param array $conditions ['email' => example@email.com, 'firstname' => Travis]
     * @return object
     */
    public static function findOne($conditions)
    {
        $tableName = static::tableName();

        // Build string for WHERE statement
        $attributes = array_keys($conditions);
        $sql = implode("AND" ,array_map(fn($attr) => "$attr = :$attr", $attributes));

        // Create SELECT statement string
        $statement = Application::$app->db->pdo->prepare("SELECT * FROM $tableName WHERE $sql");

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
        $statement = Application::$app->db->pdo->prepare("SELECT * FROM $tableName WHERE ".static::primaryKey()." = :id");
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

        $statement = Application::$app->db->pdo->prepare("SELECT * FROM $tableName $conditions");

        foreach ($params as $key => $item)
        {
            if ($item !== null)
                $statement->bindValue(":$key", $item);
        }

        $statement->execute();
        
        return $statement->fetchAll();
    }

    public function prepare($query, array $parameters = array()) {
        list($normalizedQuery, $normalizedParameters) = self::normalizeQueryAndParameters($query, $parameters);

        $statement = Application::$app->db->pdo->prepare($normalizedQuery);

        foreach ($normalizedParameters as $key => $value) {
            error_log(print_r($value, true));
            if (is_array($value)) {
                error_log('here');
                $statement->bindValue($key, $value[self::PARAMETER_VALUE_KEY], $value[self::PARAMETER_DATA_TYPE_KEY]);
            }
            else {
                /**
                 * Because Integers automatically get converted into strings by default, this silently fixes issues where 0 will work instead of '0' for enum field comparisons
                 * THUS, never fix this to pass PDO::PARAM_INT or whatever unless you want to see catastrophic queries failing all the time
                */
                $statement->bindValue($key, $value);
            }
        }

        return $statement;
    }

    /**
	 * Helper method make mocking out the random behavior easier
	 */
	public function getRandomNumber()
	{
		if($this->incrementingRandom === null)
			return mt_rand();
		else
			return $this->incrementingRandom++;
	}

    public $incrementingRandom = null;
	public function setIncrementingRandom($value)
	{
		$this->incrementingRandom = $value;
	}

    /**
     * Primary purpose of this function is to normalize parameters to allow arrays.
     * This allows for a SQL-clause (i.e. where in (:ids)) where :ids is mapped to an array of values (i.e. array(1, 2, 3)).
     * The :ids parameter within the query is transformed into ':ids_0, :ids_1, :ids2' and those parameters are mapped to the
     * respective index in the values-array.
     * The function has a secondonary purpose of allowing mixed parameter naming conventions.
     * By default, either anonymous or named can be used. This function will allow for both.
     * The former is transformed into the latter using a random number to represent the parameter name.
     *
     * @param $query string
     * @param $parameters array
     * @returns array first index is normalized query and second is normalized parameters.
     * @link PreparedStatementParserTest
     */
    public function normalizeQueryAndParameters($query, array $parameters)
    {
        $normalizedParameters = array();
        $normalizedQuery = $query;
        $randomParameterNames = array();
		
        foreach ($parameters as $key => $value)
        {
			// Create a new variable to hold the key-value since it will be modified prior to adding to the final array.
			$newKey = $key;
			
			/**
			 * Do a check to verify the key is either a number or string. Anything else is not allowed.
			 * Numbers represent anonymous placeholders while strings represent named placeholders.
			 */
			if (is_numeric($newKey))
			{
				// Generate a random number to replace the anonymous placeholder.
				do {
				    $newKey = $this->getRandomNumber();
				} while (in_array($newKey, $randomParameterNames));
				
				// Assign the generated key to the array of used auto-generated keys so we don't use it again.
				$randomParameterNames[] = $newKey;
				
				// Make the parameter key valid.
				$newKey = ":{$newKey}";
				
				// Replace the first anonymous placeholder in the query with the new key.
				$normalizedQuery = preg_replace('/\?/', $newKey, $normalizedQuery, 1);
			}
			else if (is_string($newKey))
			{
				// Prepend colon if necessary to parameter name.
				$newKey = (substr($newKey, 0, 1) === ':') ? $newKey : ":{$newKey}";
			}
			else
			{
				throw new Exception('A parameter key must be a number or string.');
			}
			
			/**
			 * If the current value is an array, then we need to also check whether the
			 * value-key is also an array. This will require a loop through those values resulting in a
			 * new normalized parameter name which is a series of concatenated parameter names appended with an index.
			 */
			if (is_array($value))
			{
			    if ((isset($value[self::PARAMETER_VALUE_KEY]) && is_array($value[self::PARAMETER_VALUE_KEY])) || (!array_key_exists(self::PARAMETER_VALUE_KEY, $value) && !array_key_exists(self::PARAMETER_DATA_TYPE_KEY, $value)))
				{
					// This list of new parameter names will replace the singular placeholder in the query.
					$normalizedParameterNames = array();
					$normalizedValues = array();
					
					// Seed an index to append to the resulting new parameter names.
					$index = 0;
					
					// Either loop through $value itself or the key of $value that contains the values we need.
					foreach ((array_key_exists(self::PARAMETER_VALUE_KEY, $value) ? $value[self::PARAMETER_VALUE_KEY] : $value) as $normalizedValue)
					{
					    $normalizedParameterName = "{$newKey}_{$index}";
					    $normalizedParameterNames[] = $normalizedParameterName;
					
					    /**
					     * Currently, recursive calls to normalize values is not supported, so we cannot
					     * allow this nested value to also be an array.
					     */
					    if (is_array($normalizedValue))
					    {
					        throw new Exception('Values within the parameter array cannot themselves be an array. '."\n".print_r($query,true).print_r($parameters,true));
					    }
					    else
					    {
					        $normalizedValues[$normalizedParameterName] = $normalizedValue;
					    }
					
					    $index++;
					}
					
					/**
					 * Add to the normalized parameters using our newly constructed normalized values.
					 * Currently, each normalized value shares the data type as the parent.
					 */
					foreach ($normalizedValues as $normalizedKey => $normalizedValue)
					{
						if (array_key_exists(self::PARAMETER_DATA_TYPE_KEY, $value))
						{
						    $normalizedParameters[$normalizedKey] = array(self::PARAMETER_VALUE_KEY => $normalizedValue, self::PARAMETER_DATA_TYPE_KEY => $value[self::PARAMETER_DATA_TYPE_KEY]);
						}
						else
						{
						    $normalizedParameters[$normalizedKey] = $normalizedValue;
						}
					}
					
					// Replace the singular placeholder with the constructed placeholder array.
					$normalizedQuery = preg_replace('/' . preg_quote($newKey) . '\b/i', implode(', ', $normalizedParameterNames), $normalizedQuery);
			    }
				else
				{
					$normalizedParameters[$newKey] = $value;
				}
			}
			else
			{
				$normalizedParameters[$newKey] = $value;
			}
		}
		
		return array($normalizedQuery, $normalizedParameters);
	}

    /**
     * Query
     * 
     */
    public static function query($query, $params)
    {
        $statement = self::prepare($query, $params);

        $result = $statement->execute();
        $results = $statement->fetchAll(Application::$app->db->pdo::FETCH_ASSOC);

        return $results;
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