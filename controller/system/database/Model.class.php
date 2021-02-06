<?php
require_once 'Join.class.php';
require_once 'Where.class.php';

class Model {
    protected $primary = 'id';
    protected $limit = null;
    protected $offset = null;

    protected $select = [];
    protected $types = [];
    protected $notNull = [];
    
    public $as = null;
    public $check = true;

    protected $command = 'SELECT';
    protected $table = null;
    protected $columns = [];
    protected $notShow = [];
    protected $where = [];
    protected $order = [];
    protected $group = [];
    protected $joins = [];

    public function __construct($name=null) {
        $this->as = $name;
    }

    /**
     * PUBLIC FUNCTIONS
     */
    
    public function findById(string $id){
        $this->where([$this->primary=>$id]);
        return $this->query();
    }

    public function find(array $options=null){
        if ( in_array('where', array_keys($options)) ) {
            $this->where($options['where']);
        }
        
        if ( in_array('limit', array_keys($options)) ) {
            $this->limit($options['limit']);
        }        
        
        if ( in_array('offset', array_keys($options)) ) {
            $this->offset($options['offset']);
        }
        
        if ( in_array('order', array_keys($options)) ) {
            $this->order($options['order']);
        }
        
        if ( in_array('group', array_keys($options)) ) {
            $this->group($options['group']);
        }

        return $this->query();
    }

    public function findOne(array $options=null){
        $this->limit(1);
        return $this->find($options);
    }

    public function offset(float $value) {
        $this->offset = $value;
        return $this;
    } 

    public function orderBy(array $value) {
        $this->order = $value;
        return $this;
    }  

    public function groupBy(array $value) {
        $this->group = $value;
        return $this;
    }      

    public function limit(float $value) {
        $this->limit = $value;
        return $this;
    }

    public function where(array $options) {
        $this->where = array_merge($this->where, $options);
        return $this;
    }

    public function select(array $options, $remove=true) {
        if ( $remove ) $this->select = [];
        $this->select = array_merge($this->select, $options);
        return $this;
    }

    public function join(string $table, array $columns, array $on, $left=null) {
        $join = new Join($table, $columns, $on, $left);
        $join->as = 'J' . count($this->joins);

        $this->joins[] = $join;
        return $this;
    }

    public function leftJoin(string $table, array $columns, array $on) {
        return $this->join($table, $columns, $on, true);
    }

    public function insert(array $values){
        $this->command = 'INSERT INTO';
        
        $values_ = [];
        array_map(function($c) use (&$values_, $values){
            $values_[$c] = in_array($c, array_keys($values)) ? $this->formatValue($c, $values[$c]) : null;
        }, $this->columns);

        $rt = $this->query($values_, 2);
        if ( empty($rt[ $this->primary ]) ) {
            $rt[ $this->primary ] = Banco::$pdo->lastInsertId();
        }

        Banco::verifyError();
        return $rt;
    }

    public function update(array $values, array $where, $usePrimary=false){
        $this->command = 'UPDATE';
        $this->where = $where;
        $this->checkWhere();

        $values = array_filter($values, function($column) use ($usePrimary){
            if ( !$usePrimary && $this->primary === $column ) {
                return false;
            }

            return in_array($column, $this->columns);
        }, ARRAY_FILTER_USE_KEY);

        if ($this->check) $this->checkPrimary();
        return $this->query($values, true);
    }

    public function delete(array $where){
        $this->command = 'DELETE';
        $this->where = $where;
        $this->checkWhere();
        
        if ($this->check) $this->checkPrimary();
        return $this->build();
    }

    public function toSql(array $values=null) {
        return $this->build($values);
    }

    public function count() {
        $this->select(["count({$this->primary}) as total"], true);
        return $this->query();
    }

    public function addCound() {
        $count = (new $this)->where($this->where)->count()[0]->total ?? 0;
        $this->select(["'{$count}' as total"]);
    }

    public function getTable() {
        $name = $this->as;
        $table = $this->table;

        if( $name ) $table .= " AS {$name}";
        return $table;
    }

    public function getColumns() {
        return $this->columns;
    }

    /**
     * PROTECTED FUNCTIONS
     */

    protected function build(array $values=null) {
        $where = $this->prepareWhere();
        $joins = $this->prepareJoins();
        $command = $this->command;

        if ( $command === 'SELECT' ) {
            $select = $this->prepareSelect();
            $select = implode(', ', $select);

            $query = "{$command} {$select} FROM {$this->getTable()}";
            
            if ( !empty($joins) ) $query .= ' ' . $joins;
            if ( !empty($where) ) $query .= ' ' . $where;

            if ( !empty($this->group) ) {
                $group = implode(', ', $this->group);
                $query .= " GROUP BY {$group}";
            } 

            if ( !empty($this->group) ) {
                $group = implode(', ', $this->group);
                $query .= " GROUP BY {$group}";
            }           

            if ( !empty($this->order) ) {
                $order = implode(', ', $this->order);
                $query .= " ORDER BY {$order}";
            }
            
            if ( !empty($this->limit) ) {
                $query .= " LIMIT {$this->limit}";
            }

            if ( !empty($this->offset) ) {
                $query .= " OFFSET {$this->offset}";
            } 

        } else if ( in_array($command, ['UPDATE', 'DELETE']) ) { 

            if ( $command === 'UPDATE' ) {
                $values = array_map(function($k) use ($values){
                    return $this->prepareValue( $values[$k], $k );
                }, array_keys($values));

                $values = implode(', ', $values);
                $values = "SET {$values}";
            } else {
                $values = '';
                $command .= ' FROM';
            }

            $query = "{$command} {$this->getTable()} {$values} {$where}";
        } else {
            $columns = implode(', ', array_keys($values));
            $values_insert = [];
            
            array_map(function($c) use ($values, &$values_insert){
                $values_insert[] = $this->checkValue($values[$c], $c);
                return true;
            }, array_keys($values));

            $values = implode(', ', $values_insert);
            $query = "{$command} {$this->getTable()} ({$columns}) VALUES ({$values})";
        }

        return $query;
    }

    protected function prepareWhere() {
        return (new Where($this->where, $this->as))->build(true);
    }

    private function prepareJoins(){
        $joins = implode(' ', array_map(function($j) {
            $this->select( $j->getColumns(), false );
            return $j->buildJoin();
        }, $this->joins));

        return trim($joins);
    }

    protected function prepareValue($v, $c) {
        $v = $this->checkValue($this->formatValue($c, $v), $c);
        return "`{$c}`={$v}";
    }

    protected function prepareSelect() {
        if ( empty($this->select) ) {
            $select = array_filter($this->columns, function($value){
                return !in_array($value, $this->notShow);
            });
            
            $select = array_merge( $this->select, $select );
        } else {
            $select = $this->select;
        }

        return $select;
    }

    protected function checkValue($v, $column=null) {
        $command = ['CURRENT_DATE', 'CURRENT_TIMESTAMP'];

        if ( $v != '0' && (empty($v) || is_null($v) || in_array($v, $command)) ) {
            $v = in_array($column, $this->notNull) ? "''" : 'NULL';
        } else {
            $v = !is_object($v) ? "'{$v}'" : $v->toString();
        }

        return $v;
    }

    protected function formatValue($column, $value) {
        $fmt_dcm = new NumberFormatter( 'pt_BR', NumberFormatter::DECIMAL );
        
        if ( $this->checkValue($value) === 'NULL' ) {
            return $value;
        }

        if ( in_array($column, array_keys($this->types)) ) {
            $type = $this->types[$column];

            switch($type){
                case 'string':
                case 'varchar':
                    $value = strval($value);
                    break;
                case 'integer':
                case 'int':
                    $value = preg_replace('/[\D]+/', '', $value);
                    break;
                case 'float':
                case 'money':
                        if (!preg_match('~\A(?>[1-9][0-9]{0,2}(?:.[0-9]{3})*|0)(?:\,[0-9]+)?\z~', $value)) {
                            $value = floatval($value);
                        } else {
                            $value = $fmt_dcm->parse($value);
                        }
                    break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                        $time = $type != 'date' ? ' H:i:s' : '';

                        if ( strrpos($value, '/') > -1 ) {
                            $date = strlen($value) > 8 ? 'd/m/Y' : 'd/m/y';
                            $value = DateTime::createFromFormat($date . $time, $value)->format('Y-m-d' . $time);
                        }
                    break;
            }
        }

        if ( is_string($value) ) {
            $value = mb_strtoupper($value);
        }

        return $value;
    }

    private function checkPrimary(){ 
        if ( !in_array($this->primary, array_keys($this->where)) ) {
            throw new Exception("Favor informar a chave primaria no where!", 1);            
        }
    }

    private function checkWhere(){ 
        if ( empty($this->where) ) {
            throw new Exception("É importante informar um Where para essa operação!", 1);            
        }
    }

    private function query(array $values=null, $type=false){
        $command = $type ? ($type === 2 ? 'query_pdo' : 'exec') : 'query';
        return Banco::$command( $this->toSql($values) );
    }
}