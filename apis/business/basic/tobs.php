<?php

class tobs_API extends hapi {
    
    public function listing(){
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/tobs]",0);
    }
    
    public function search() {
        $this->bind('SEARCH', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/tobs/search]",0);
    }

    public function add() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('tob', $_POST['tob']);
        $this->bind('main', $_POST['main']);
        $this->bind('roster', $_POST['roster']);
        $this->bind('cguide', $_POST['cguide']);
        $this->bind('equote', $_POST['equote']);
        $this->bind('label', $_POST['label']);
        
        return $this->write("tob:add","[e2mobile/api/business/basic/tobs/add]");
    }

    public function edit() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('code', $this->segments[1]);
        $this->bind('tob', $_POST['tob']);
        $this->bind('main', $_POST['main']);
        $this->bind('roster', $_POST['roster']);
        $this->bind('cguide', $_POST['cguide']);
        $this->bind('equote', $_POST['equote']);
        $this->bind('label', $_POST['label']);
        
        return $this->write("tob:edit","[e2mobile/api/business/basic/tobs/edit]");
    }
    
    public function get(){
        $this->bind('bid', $this->segments[0]);
        $this->bind('tob', $this->segments[1]);
        return $this->read("[e2mobile/api/business/basic/tobs/get]",0);
    }

}

?>