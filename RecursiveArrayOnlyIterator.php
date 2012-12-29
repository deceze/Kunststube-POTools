<?php

namespace Kunststube\POTools;


/**
 * A RecursiveArrayIterator which does not descend into objects.
 */
class RecursiveArrayOnlyIterator extends \RecursiveArrayIterator {
    
    public function hasChildren() {
        return is_array($this->current());
    }
    
}