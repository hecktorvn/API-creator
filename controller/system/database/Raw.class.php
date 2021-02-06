<?php
class Raw {
    private $value = '';
    function __construct($str, $comand=' AND ') {
        if ( is_array($str) ) {
            $this->value = implode($comand, $str);
        } else {
            $this->value = $str;
        }
    }

    public function build() {
        return $this->value;
    }

    public function toString() {
        return $this->build();
    }
}