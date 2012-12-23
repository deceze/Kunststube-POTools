<?php

namespace Kunststube\POTools;

interface POWriterInterface {
    
    public function write(POString $string);
    
    public function writeCatalog(Catalog $catalog);
    
}