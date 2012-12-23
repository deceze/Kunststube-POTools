<?php

namespace Kunststube\POTools;


class Catalog {
    
    protected $strings = array();
    
    public function add(POString $string) {
        $id       = $string->getMsgctxt() . "\04" . $string->getMsgid();
        $category = $string->getCategoryText();
        $domain   = $string->getDomain();
        
        if (isset($this->strings[$category][$domain][$id])) {
            array_map(array($this->strings[$category][$domain][$id], 'addReference'), $string->getReferences());
        } else {
            $this->strings[$category][$domain][$id] = $string;
        }
    }
    
    public function getStrings() {
        return $this->strings;
    }
 
    public function writeToDirectory($path, POWriterFactory $writerFactory = null) {
        if (!$writerFactory) {
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'POWriterFactory.php';
            $writerFactory = new POWriterFactory;
        }
        
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!file_exists($path) || !is_dir($path)) {
            throw new \InvalidArgumentException("Cannot access path $path");
        }
        
        foreach ($this->strings as $category => $domains) {
            if (!file_exists($path . $category)) {
                mkdir($path . $category);
            }
            
            foreach ($domains as $domain => $strings) {
                $file = fopen($path . $category . DIRECTORY_SEPARATOR . "$domain.pot", 'w');
                $writer = $writerFactory->construct($file);
                
                foreach ($strings as $string) {
                    $writer->write($string);
                }
                
                fclose($file);
            }
        }
    }
    
}