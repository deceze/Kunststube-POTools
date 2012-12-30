<?php

namespace Kunststube\POTools;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'POWriter.php';


class POWriterFactory {
    
    public function construct($stream, Catalog $catalog = null) {
        return new POWriter($stream, $catalog);
    }
    
}