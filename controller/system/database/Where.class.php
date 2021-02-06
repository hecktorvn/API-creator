<?php
class Where {
    public $cond = '=';
    public $separator = ' AND ';
    public static $addEmpty = true;
    public $where = [];
    public $name = null;

    function __construct(array $where, $name=null) {
        $this->where = $where;
        $this->name = $name;
    }

    public function orWhere($where){
        if ( is_array($where) ) {
            $this->where = array_merge($this->where, $where);
            $this->separator = ' OR ';
        } else if ($where === true) {
            $this->separator = ' OR ';
        }

        $this::$addEmpty = false;
        return $this;
    }

    public function build($addWhere=false) {
        $where = array_map(function($k){
            $val = $this->where[$k];
            if ( is_a($val, 'Where') ) {
                return $val->build();
            } else if ( is_array($val) ) {
                $w = [];
                array_map(function($ok) use (&$w, $val) {
                    $v = $this->prepareValue( $val[$ok], $ok );
                    if( $v !== null ) $w[] = $v;
                }, array_keys($val));
                
                $w = implode(' OR ', $w);
                return "({$w})";
            } else {
                return $this->prepareValue( $this->where[$k], $k );
            }
        }, array_keys($this->where));

        $where = array_filter($where, function($value) { return !is_null($value) && $value !== ''; });
        $where = !empty($where) ? ($addWhere ? 'WHERE ' : '') . implode($this->separator, $where) : '';
        
        return $where;
    }

    public static function Like(array $where, $name=null) {
        $w = (new self($where, $name));
        $w->cond = 'LIKE';

        return $w;
    }    

    public static function Between($column, array $where, $name=null) {
        $value = array_map(function($v){
            return self::checkValue($v);
        }, $where);

        $val = new Raw($value);
        $w = (new self([$column=>$val], $name));
        $w->cond = 'BETWEEN';

        return $w;
    }

    protected function prepareValue($v, $c) {
        $v = $this::checkValue($v);
        if( !$this::$addEmpty && $v === '' ){
            return null;
        }

        if ( is_a($c, 'Raw') ) {
            $column = $c->toString();
        } else {
            $name = $this->name ? "{$this->name}." : '';
            $column = "{$name}`{$c}`";
        }

        return "{$column} {$this->cond} {$v}";
    }

    protected static function checkValue($v) {
        $command = ['CURRENT_DATE', 'CURRENT_TIMESTAMP'];

        if ( is_a($v, 'Raw') ) {
            return $v->build();
        } else if ( is_array($v) ){
            $v = array_map(self::checkValue, $v);
            $v = implode(' AND ', $v);
        } else if ( $v != '0' && (empty($v) || is_null($v) || in_array($v, $command)) ) {
            if (!self::$addEmpty) {
                $v = '';
            } else {
                $v = 'NULL';
            }
        } else {
            $v = !is_object($v) ? "'{$v}'" : $v->toSring();
        }

        return $v;
    }
}