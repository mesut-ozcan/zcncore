<?php
namespace Core\Database;

use PDO;

final class QueryBuilder
{
    private PDO $pdo;
    private string $table = '';
    private array $columns = ['*'];
    private array $wheres = [];
    private array $bindings = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $orders = [];

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo) { $this->pdo = $pdo; }
        else { $this->pdo = Connection::getInstance()->pdo(); }
    }

    public static function table(string $table): self
    {
        return (new self())->from($table);
    }

    public function from(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(string ...$cols): self
    {
        if ($cols) $this->columns = $cols;
        return $this;
    }

    public function where(string $column, string $op, $value): self
    {
        $param = ':w' . count($this->bindings);
        $this->wheres[] = [$column, $op, $param];
        $this->bindings[$param] = $value;
        return $this;
    }

    public function orderBy(string $column, string $dir = 'asc'): self
    {
        $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
        $this->orders[] = [$column, $dir];
        return $this;
    }

    public function limit(int $n): self { $this->limit = max(0, $n); return $this; }
    public function offset(int $n): self { $this->offset = max(0, $n); return $this; }

    public function get(): array
    {
        $sql = $this->toSelectSql();
        $stmt = $this->pdo->prepare($sql);
        foreach ($this->bindings as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function first(): ?array
    {
        $bak = $this->limit;
        $this->limit = 1;
        $rows = $this->get();
        $this->limit = $bak;
        return $rows[0] ?? null;
    }

    public function insert(array $data): bool
    {
        if (!$data) return false;
        $cols = array_keys($data);
        $params = [];
        foreach ($cols as $c) {
            $p = ':i_' . $c;
            $params[] = $p;
            $this->bindings[$p] = $data[$c];
        }
        $sql = 'INSERT INTO '.$this->table.' ('.implode(',', $cols).') VALUES ('.implode(',', $params).')';
        $stmt = $this->pdo->prepare($sql);
        foreach ($this->bindings as $k => $v) { $stmt->bindValue($k, $v); }
        $this->bindings = [];
        return $stmt->execute();
    }

    public function update(array $data): int
    {
        if (!$data) return 0;
        $sets = [];
        foreach ($data as $c => $v) {
            $p = ':u_' . $c;
            $sets[] = $c.'='.$p;
            $this->bindings[$p] = $v;
        }
        $sql = 'UPDATE '.$this->table.' SET '.implode(',', $sets) . $this->toWhereOrderLimitSql(true);
        $stmt = $this->pdo->prepare($sql);
        foreach ($this->bindings as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        $this->bindings = [];
        return $stmt->rowCount();
    }

    public function delete(): int
    {
        $sql = 'DELETE FROM '.$this->table . $this->toWhereOrderLimitSql(true);
        $stmt = $this->pdo->prepare($sql);
        foreach ($this->bindings as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        $this->bindings = [];
        return $stmt->rowCount();
    }

    // ----------------------

    private function toSelectSql(): string
    {
        $cols = implode(',', $this->columns);
        $sql = 'SELECT '.$cols.' FROM '.$this->table;
        $sql .= $this->toWhereOrderLimitSql();
        return $sql;
    }

    private function toWhereOrderLimitSql(bool $noOrder = false): string
    {
        $sql = '';
        if ($this->wheres) {
            $parts = [];
            foreach ($this->wheres as [$col, $op, $param]) {
                $parts[] = $col.' '.$op.' '.$param;
            }
            $sql .= ' WHERE '.implode(' AND ', $parts);
        }
        if (!$noOrder && $this->orders) {
            $pieces = array_map(fn($o)=>$o[0].' '.$o[1], $this->orders);
            $sql .= ' ORDER BY ' . implode(', ', $pieces);
        }
        if ($this->limit !== null) $sql .= ' LIMIT ' . (int)$this->limit;
        if ($this->offset !== null) $sql .= ' OFFSET ' . (int)$this->offset;
        return $sql;
    }
}