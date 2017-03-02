<?php

class addressgeo_API extends hapi {

    
    public function get(){
        $this->bind('bid', $this->segments[0]);
        $this->bind('aid', $this->segments[1]);
        return $this->read("[e2mobile/api/business/basic/addressgeo_get]", 0);
    }

}

?>