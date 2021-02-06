<?php
class Req{
    /**
     * @var string
     */
    public $method = 'GET';

    /**
     * @var array
     */
    public $__data__ = [];

    /**
     * Req constructor.
     * @param string $method
     * @param array $data
     */
    public function __construct(array $data, $method)
    {
        $this->method = $method;
        $this->__data__ = $data;

        foreach($data as $var=>$value){
            $this->$var = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->__data__;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->__data__ = $data;
    }

    /**
     * @return array
     */
    public function toArray(){
        return (array) $this->__data__;
    }

    /**
     * @return object
     */
    public function toObject(){
        return (object) $this->__data__;
    }

}