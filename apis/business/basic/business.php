<?php

class business_API extends hapi {

    public function basic() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/dba]", 0);
    }
}

?>