<?php

namespace ReportCollection\Tests\Libs;

class ObjectIterator implements \Iterator {

    private $position = 0;

    private $array = array(
        ["Company", "Contact", "Country"],
        ["Alfreds Futterkiste", "Maria Anders", "Germany"],
        ["Centro comercial Moctezuma", "Francisco Chang", "Mexico"],
        ["Ernst Handel", "Roland Mendel", "Austria"],
        ["Island Trading", "Helen Bennett", "UK"],
        ["Laughing Bacchus Winecellars", "Yoshi Tannamuri", "Canada"],
        ["Magazzini Alimentari Riuniti", "Giovanni Rovelli", "Italy"]
    );

    public function __construct() 
    {
        $this->position = 0;
    }

    public function rewind() 
    {
        $this->position = 0;
    }

    public function current() 
    {
        return $this->array[$this->position];
    }

    public function key() 
    {
        return $this->position;
    }

    public function next() 
    {
        ++$this->position;
    }

    public function valid() 
    {
        return isset($this->array[$this->position]);
    }
}
