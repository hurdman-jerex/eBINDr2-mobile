<?php

class staff_API extends hapi {
    
    public function update() {
        $this->bind('bid',$this->segments[0]);
        $this->bind('sid',$this->segments[1]);
        
        return $this->write('staff:update',"[e2mobile/api/business/basic/staffbid]");
    }
    
}

?>