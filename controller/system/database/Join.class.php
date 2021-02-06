<?php
require_once 'Model.class.php';

class Join extends Model {
    private $left = false;
    private $on = [];

    function __construct(string $table, array $columns, array $on=null, $left=null) {
        $this->columns = $columns;
        $this->table = $table;
        $this->left = $left;
        $this->on = $on;
            
        $this->where($on);
        return $this;
    }

    protected function buildJoin() {
        $where = $this->prepareWhere(true);
        $columns = array_map(function($k){
            $name = $this->as ? "{$this->as}." : '';
            return $name . $this->prepareValue( $this->on[$k], "{$k}" );
        }, array_keys($this->on));

        $columns = implode(', ', $columns);

        $name = $this->as ? " AS {$this->as}" : '';
        $query = ($this->left ? 'LEFT ' : '') . "JOIN {$this->table} {$name} ON {$columns}";
        return $query;
    }

    public function getColumns() {
        $name = $this->as ? "{$this->as}." : '';
        $columns = array_map(function($v) use ($name) { return "{$name}{$v}"; }, $this->columns);
        return $columns;
    }
}