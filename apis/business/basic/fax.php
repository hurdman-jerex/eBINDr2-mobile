<?php

class fax_API extends hapi {

    public function get(){
        $this->bind('bid', $this->segments[0]);
        $this->bind('fid', $this->segments[1]);
        return $this->read("[e2mobile/api/business/basic/fax/get]", 0);
    }
    
    public function listing() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/fax]", 0);
    }

    public function add() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('number', $_POST['number']);
        $this->bind('main', $_POST['main']);
        $this->bind('report', $_POST['report']);
        $this->bind('label', $_POST['label']);
        $this->bind('location', $_POST['location']);
        $this->bind('disconnected', $_POST['disconnected']);
        $this->bind('massfax', $_POST['massfax']);

        return $this->write("fax:add", "[e2mobile/api/business/basic/fax/add]");
    }

    public function edit() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('fid', $this->segments[1]);
        
        $this->bind('number', $_POST['number']);
        $this->bind('main', $_POST['main']);
        $this->bind('report', $_POST['report']);
        $this->bind('label', $_POST['label']);
        $this->bind('location', $_POST['location']);
        $this->bind('disconnected', $_POST['disconnected']);
        $this->bind('massfax', $_POST['massfax']);

        return $this->write("fax:add", "[e2mobile/api/business/basic/fax/edit]");
    }

}

?>