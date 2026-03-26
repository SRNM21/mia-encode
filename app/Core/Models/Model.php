<?php

namespace App\Core\Models;

use App\Core\Database\QueryBuilder;
use PDO;
use Error;
use App\Core\Facades\DB;

abstract class Model
{   
    /**
     * Per-class singleton instances.
     *
     * @var array<string, static>
     */
    private static $instances = [];

    /**
     * Table name of the model.
     *
     * @var string
     */
    protected string $table = '';

    /**
     * Primary or unique key of the model.
     *
     * @var string
     */
    protected string $primary_key  = '';

    /**
     * Mass-assignable attributes.
     *
     * If empty, all columns are assignable.
     *
     * @var array
     */
    protected array $fillable = [];
    
    /**
     * Hidden attributes when converting to array.
     *
     * @var array
     */
    protected array $hidden = [];

    /**
     * Current data of the model.
     *
     * @var mixed
     */
    protected $current_data = [];

    /**
     * Initialize new model with data.
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        $this->current_data = $data;
    }

    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    public function __isset($name)
    {
        return isset($this->current_data->$name);
    }

    /**
     * Set a new table for this model.
     *
     * @param string $table new table name
     */
    public function setTable(string $table)
    {
        $this->table = $table;
    }

    /**
     * Returns the current table of this model.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;  
    }

    /**
     * Sets a primary key in this model.
     *
     * @return string
     */
    public function setPrimaryKey(string $primary_key)
    {
        $this->primary_key = $primary_key;  
    }

    /**
     * Returns the current primary key of this model.
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primary_key;  
    }

    private static function getInstance()
    {
        $class = static::class;
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static;
        }
        return self::$instances[$class];
    }

    /**
     * Returns the attribute of the current model.
     *
     * @param string $attribute
     * @return mixed
     */
    public function getAttribute(?string $attribute = null)
    {
        if (!$this->current_data) return null;

        if (!$attribute) 
        {
            return $this->toArray();
        }

        $value = $this->current_data->$attribute ?? null;

        // Apply cast if exists
        if (isset($this->casts[$attribute]))
        {
            $castType = $this->casts[$attribute];

            if (enum_exists($castType)) 
            {
                if (!is_string($value)) 
                {
                    return null;
                }

                $enum = $castType::tryFrom($value);

                if ($enum === null) 
                {
                    $enum = $castType::tryFrom(strtolower($value));
                }

                return $enum;
            }
        }

        return $value;
    }

    /**
     * Convert model to array excluding hidden attributes.
     */
    public function toArray(): array
    {
        if (!$this->current_data) return [];

        $data = (array) $this->current_data;

        if (!empty($this->hidden)) 
        {
            $data = array_diff_key($data, array_flip($this->hidden));
        }

        return $data;
    }

    /**
     * @return QueryBuilder<static>
     */
    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::class, static::getInstance()->getTable());
    }

    /**
     * @return QueryBuilder<static>
     */
    public static function where(string $column, string $operator, mixed $value): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    /**
     * Return all model's records.
     *
     * @return array|false
     */
    public static function getAll(): array
    {
        $sql = "SELECT * 
                FROM " . static::getInstance()->getTable();

        $stm = DB::getPDO()->query($sql);
        return $stm->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Return the record based on the given ID.
     *
     * @param string $id
     * @return static|null
     */
    public static function get(string $id)
    {
        $instance = static::getInstance();

        $sql = "SELECT * 
                FROM {$instance->getTable()} 
                WHERE {$instance->getPrimaryKey()} = ?";

        $stm = DB::getPDO()->prepare($sql);
        $stm->bindValue(1, $id);
        $stm->execute();

        // Return a copy of the populated model
        return new static($stm->fetch(PDO::FETCH_OBJ));
    }
    
    /**
     * Create a new record of model.
     *
     * Supports:
     * - Sequential array (values aligned with fillable order)
     * - Associative array (key => value)
     */
    public static function create(array $data): ?static
    {
        if (empty($data)) 
        {
            throw new Error('[INSERT ERROR] No data provided.');
        }

        $instance = static::getInstance();
        $instanceFillable = $instance->fillable;

        if (empty($instanceFillable)) 
        {
            throw new Error('[INSERT ERROR] Fillable attributes must be defined.');
        }

        $invalid = array_diff(array_keys($data), $instanceFillable);

        if (!empty($invalid)) 
        {
            throw new Error('[INSERT ERROR] Invalid column(s): ' . implode(',', $invalid));
        }

        // Normalize data into associative form
        if (!is_associative($data)) 
        {
            // Sequential array -> map to fillable
            if (count($data) !== count($instanceFillable)) 
            {
                throw new Error('[INSERT ERROR] Data must match fillable count.');
            }

            $data = array_combine($instanceFillable, $data);
        } 
        else 
        {
            // Associative -> filter by fillable
            $data = $instance->filterFillable($data);

            if (empty($data)) 
            {
                throw new Error('[INSERT ERROR] No valid fillable attributes provided.');
            }
        }

        $columns = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));

        $sql = "INSERT INTO {$instance->table} (" . implode(',', $columns) . ")
                VALUES ($placeholders)";

        $stm = DB::getPDO()->prepare($sql);

        $i = 1;
        foreach ($data as $value) 
        {
            $stm->bindValue($i++, $value);
        }

        $stm->execute();

        $pdo = DB::getPDO();
        $insertId = $pdo->lastInsertId();

        if ($insertId) {
            return static::get($insertId);
        }

        $pk = $instance->getPrimaryKey();
        if ($pk !== '' && array_key_exists($pk, $data)) {
            return static::get($data[$pk]);
        }

        return new static((object) $data);
    }

    /**
     * Update record(s) by primary key OR custom where conditions.
     *
     * @throws \Error
     */
    public static function update($identifier, array $data)
    {
        $instance = static::getInstance();

        if (empty($data)) 
        {
            throw new Error('[UPDATE ERROR] No data provided for update.');
        }

        // Prevent updating primary key
        if (array_key_exists($instance->getPrimaryKey(), $data)) 
        {
            throw new Error('[UPDATE ERROR] Cannot update primary key.');
        }

        $data = $instance->filterFillable($data);

        if (empty($data)) 
        {
            throw new Error('[UPDATE ERROR] No fillable attributes provided.');
        }

        $setClause = implode(', ', array_map(
            fn($col) => "{$col} = ?",
            array_keys($data)
        ));

        $bindings = array_values($data);

        $whereClause = '';
        if (is_associative($identifier)) 
        {
            // Associative array: ['email' => 'x', 'status' => 1]
            if (empty($identifier)) 
            {
                throw new Error('[UPDATE ERROR] Where condition cannot be empty.');
            }

            $conditions = [];
            foreach ($identifier as $column => $value) 
            {
                if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) 
                {
                    throw new Error("[UPDATE ERROR] Invalid column name: {$column}");
                }

                $conditions[] = "{$column} = ?";
                $bindings[] = $value;
            }

            $whereClause = implode(' AND ', $conditions);

        } 
        else 
        {
            // Default: primary key
            $whereClause = "{$instance->getPrimaryKey()} = ?";
            $bindings[] = $identifier;
        }

        $sql = "UPDATE {$instance->getTable()} 
                SET {$setClause} 
                WHERE {$whereClause}";

        $stm = DB::getPDO()->prepare($sql);

        foreach ($bindings as $index => $value) 
        {
            $stm->bindValue($index + 1, $value);
        }

        $stm->execute();

        if ($stm->rowCount() <= 0) 
        {
            return null;
        }

        /**
         * Return updated records
         */
        if (is_associative($identifier))
        {
            $query = static::query();

            foreach ($identifier as $column => $value)
            {
                $query->where($column, '=', $value);
            }

            return $query->get();
        }

        return static::get($identifier);
    }

    /**
     * Delete a record by primary key.
     *
     * @param mixed $id
     * @return bool
     */
    public static function delete($id): bool
    {
        $instance = static::getInstance();

        $sql = "DELETE FROM {$instance->getTable()} 
                WHERE {$instance->getPrimaryKey()} = ?";

        $stm = DB::getPDO()->prepare($sql);
        $stm->bindValue(1, $id);
        $stm->execute();

        return $stm->rowCount() > 0;
    }

    /**
     * Filter attributes based on fillable.
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) 
        {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    public function __debugInfo(): array
    {
        return $this->toArray();
    }
}
