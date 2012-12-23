<?php

namespace Kunststube\POTools;


class Catalog implements \IteratorAggregate {
    
    protected $strings = array();
    
    public function add(POString $string) {
        $id = $string->getId();
        if (isset($this->strings[$id])) {
            array_map(array($this->strings[$id], 'addReference'), $string->getReferences());
        } else {
            $this->strings[$id] = $string;
        }
    }
    
    public function getIterator() {
        return new \ArrayIterator($this->strings);
    }
    
    public function getStrings() {
        return $this->strings;
    }
    
}