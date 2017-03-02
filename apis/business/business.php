<?php

class business_API extends hapi {

    public function basic() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic]", 0);
    }
    
    public function info(){
        $this->bind('bid', (int) $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/info]", 0);
    }

    public function complaints() {
        $this->bind('bid', $this->segments[3]);
        return $this->read("[e2mobile/api/business/complaints]");
    }

    public function ratings() {
        $this->bind('bid', $this->segments[3]);
    }

    public function journal() {
        $this->bind('bid', $this->segments[3]);
    }

    public function sales() {
        $this->bind('bid', $this->segments[3]);
    }

    public function forceupdate() {
        $this->bind('bid', $this->segments[3]);
    }

}

?>