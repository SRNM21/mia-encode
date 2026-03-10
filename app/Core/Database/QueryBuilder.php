<?php

namespace App\Core\Database;

use App\Core\Facades\DB;
use App\Core\Models\Model;
use InvalidArgumentException;
use PDO;
use PDOStatement;

/**
 * @template T of Model
 */
class QueryBuilder
{
    /** @var class-string<T> */
    protected string $modelClass;

    protected string $table;
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $joins = [];
    protected array $selects = ['*'];
    protected array $groups = [];
    protected array $orders = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected bool $useCache = false;
    protected static array $cache = [];

    protected array $allowedOperators = [
        '=', '!=', '<', '>', '<=', '>=', 'LIKE',
    ];

    /**
     * @param class-string<T> $modelClass
     */
    public function __construct(string $modelClass, string $table)
    {
        $this->modelClass = $modelClass;
        $this->table = $table;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->validateColumn($column);
        $this->validateOperator($operator);

        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;

        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->validateColumn($column);

        $this->wheres[] = "{$column} IS NOT NULL";

        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->validateColumn($column);

        $this->wheres[] = "{$column} IS NULL";

        return $this;
    }

    /**
     * Get a generator to iterate over the results one by one.
     *
     * @return \Generator<T>
     */
    public function cursor(): \Generator
    {
        $sql = $this->compileSelect();
        $stmt = DB::getPDO()->prepare($sql);
        $this->bindValues($stmt);
        $stmt->execute();

        $modelClass = $this->modelClass;

        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            yield new $modelClass($row);
        }
    }

    /**
     * Get a generator to iterate over raw results one by one.
     *
     * @return \Generator<array>
     */
    public function rawCursor(): \Generator
    {
        $sql = $this->compileSelect();
        $stmt = DB::getPDO()->prepare($sql);
        $this->bindValues($stmt);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    public function getRaw($array = true): array
    {
        $sql = $this->compileSelect();

        $stmt = DB::getPDO()->prepare($sql);
        $this->bindValues($stmt);
        $stmt->execute();

        if ($array) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        }

        return [
            'sql' => $sql,
            'results' => $results
        ];
    }

    /**
     * @return T[]
     */
    public function get(): array
    {
        $raw = $this->getRaw(false);
        
        $sql = $raw['sql'];
        $cacheKey = $this->cacheKey($sql);

        if ($this->useCache && isset(self::$cache[$cacheKey]))
        {
            return self::$cache[$cacheKey];
        }

        $results = $raw['results'];

        $modelClass = $this->modelClass;

        $mapped = array_map(
            fn ($row) => new $modelClass($row),
            $results
        );
        
        if ($this->useCache) 
        {
            self::$cache[$cacheKey] = $mapped;
        }
        
        return $mapped;
    }

    /**
     * @return T|null
     */
    public function first(): ?Model
    {
        $originalLimit = $this->limit;
        $this->limit = 1;

        $sql = $this->compileSelect();
        $this->limit = $originalLimit;

        $cacheKey = $this->cacheKey($sql);

        if ($this->useCache && isset(self::$cache[$cacheKey])) 
        {
            $cached = self::$cache[$cacheKey];
            return $cached[0] ?? null;
        }

        $stmt = DB::getPDO()->prepare($sql);
        $this->bindValues($stmt);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$result) 
        {
            return null;
        }

        $modelClass = $this->modelClass;
        $model = new $modelClass($result);

        if ($this->useCache) 
        {
            self::$cache[$cacheKey] = [$model];
        }

        return $model;
    }

    /**
     * Select specific columns.
     */
    public function select(string ...$columns): self
    {
        if (!empty($columns)) 
        {
            $this->selects = $columns;
        }

        return $this;
    }

    /**
     * Add a join clause.
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->validateOperator($operator);
        $this->joins[] = [
            'type' => strtoupper($type),
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
        ];

        return $this;
    }
    
    /**
     * Add group by clause.
     */
    public function groupBy(string ...$columns): self
    {
        foreach ($columns as $column) 
        {
            $this->validateColumn($column);
            $this->groups[] = $column;
        }

        return $this;
    }

    /**
     * Add order by clause.
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->validateColumn($column);
        $dir = strtoupper($direction);

        if (!in_array($dir, ['ASC', 'DESC'], true)) 
        {
            throw new InvalidArgumentException("Invalid order direction: {$direction}");
        }

        $this->orders[] = "{$column} {$dir}";
        return $this;
    }

    /**
     * Limit / offset for pagination.
     */
    public function limit(int $limit): self
    {
        if ($limit <= 0) 
        {
            throw new InvalidArgumentException("Limit must be greater than 0.");
        }

        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        if ($offset < 0) 
        {
            throw new InvalidArgumentException("Offset must be non-negative.");
        }

        $this->offset = $offset;
        return $this;
    }

    /**
     * Enable in-memory caching for the current query.
     */
    public function cache(bool $useCache = true): self
    {
        $this->useCache = $useCache;
        return $this;
    }

    /**
     * Get results as array of plain arrays.
     */
    public function getArray(): array
    {
        return array_map(fn(Model $m) => $m->toArray(), $this->get());
    }

    /**
     * Get results as JSON string.
     */
    public function getJson(): string
    {
        return json_encode($this->getArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Paginate results with meta.
     */
    public function paginate(int $page = 1, int $perPage = 10): array
    {
        if ($page < 1) $page = 1;
        if ($perPage < 1) $perPage = 10;

        $total = $this->count();

        $this->limit($perPage)->offset(($page - 1) * $perPage);
        $data = $this->get();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Count results for current query (ignores limit/offset).
     */
    public function count(): int
    {
        $sql = $this->compileCount();
        $stmt = DB::getPDO()->prepare($sql);

        $this->bindValues($stmt);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function compileSelect(): string
    {
        $selects = implode(', ', $this->selects);
        $sql = "SELECT {$selects} FROM {$this->table}";

        foreach ($this->joins as $join) 
        {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }

        if (!empty($this->wheres)) 
        {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        
        if (!empty($this->groups)) 
        {
            $sql .= " GROUP BY " . implode(', ', $this->groups);
        }

        if (!empty($this->orders))
        {
            $sql .= " ORDER BY " . implode(', ', $this->orders);
        }

        if ($this->limit !== null) 
        {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) 
        {
            $sql .= " OFFSET {$this->offset}";
        }

        // var_dump($sql);

        return $sql;
    }

    protected function compileCount(): string
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";

        foreach ($this->joins as $join) 
        {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }

        if (!empty($this->wheres)) 
        {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        return $sql;
    }

    protected function bindValues(PDOStatement $stmt): void
    {
        foreach ($this->bindings as $index => $value) 
        {
            $stmt->bindValue($index + 1, $value);
        }
    }

    protected function validateOperator(string $operator): void
    {
        if (!in_array(strtoupper($operator), $this->allowedOperators, true)) 
        {
            throw new InvalidArgumentException("Invalid SQL operator: {$operator}");
        }
    }

    protected function validateColumn(string $column): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $column)) 
        {
            throw new InvalidArgumentException("Invalid column name: {$column}");
        }
    }

    protected function cacheKey(string $sql): string
    {
        return md5($sql . '|' . serialize($this->bindings));
    }
}
