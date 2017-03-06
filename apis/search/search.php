<?php

class search_API extends hapi {

    public function businessname() {
        $this->bind('find', $this->segments[0] ); //$this->segments[3]
        return $this->read("[e2mobile/api/search/businessname]", 0);
    }
    
    public function businessname2() {
        $this->bind('find', $this->segments[0] );
        $this->bind('shadowval find', $this->segments[0] . '%' );
        return $this->read("[e2mobile/api/search/businessname2]", 0);
    }

    public function businessphone() {
        $this->bind('find', $this->segments[0]);
        return $this->read("[e2mobile/api/search/businessphone]", 0);
    }

    public function businessbid() {
        $this->bind('find', $this->segments[0]);
        return $this->read("[e2mobile/api/search/businessbid]", 0);
    }

    public function complaintcid() {
        $this->bind('find', $this->segments[0]);
        return $this->read("[e2mobile/api/search/complaintcid]", 0);
    }
    
    public function businessemail() {
        $this->bind('find', $this->segments[0]);
        return $this->read("[e2mobile/api/search/businessemail]", 0);
    }
    
    public function webaddress() {
        $this->bind('find', $this->segments[0] );
        return $this->read("[e2mobile/api/search/webaddress]", 0);
    }
    
    public function employee() {
        $this->bind('find', $this->segments[0]);
        return $this->read("[e2mobile/api/search/employee]", 0);
    }
    
    public function consumername() {
        $this->bind('find', $this->segments[0]);
        return $this->read("[e2mobile/api/search/consumername]", 0);
    }

    public function index(){
        $this->bind('find', $this->segments[1]);
        return $this->read("[e2mobile/api/search/". $this->segments[0] ."]", 0);
    }

}

?>