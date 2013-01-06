<?php
    
namespace Kunststube\POTools;

require_once __DIR__ . '/POString.php';


class POStringFactory {
    
    public function construct($msgid) {
        return new POString($msgid);
    }
    
}