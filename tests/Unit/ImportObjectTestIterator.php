<?php

namespace ReportCollection\Tests\Unit;

class ImportObjectTestIterator implements Iterator {

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
        var_dump(__METHOD__);
        $this->position = 0;
    }

    public function current() 
    {
        var_dump(__METHOD__);
        return $this->array[$this->position];
    }

    public function key() 
    {
        var_dump(__METHOD__);
        return $this->position;
    }

    public function next() 
    {
        var_dump(__METHOD__);
        ++$this->position;
    }

    public function valid() 
    {
        var_dump(__METHOD__);
        return isset($this->array[$this->position]);
    }
}
