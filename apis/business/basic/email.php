<?php

class email_API extends hapi {

    //list all emailaddress with matched bid
    public function listing() {
        $this->bind('bid', $this->segments[0]);
        return $this->read("[e2mobile/api/business/basic/email]", 0);
    }
    
    //gets a single emailaddress by eid and bid
    public function get(){
        $this->bind('bid', $this->segments[0]);
        $this->bind('eid', $this->segments[1]);
        return $this->read("[e2mobile/api/business/basic/email/get]", 0);
    }
    
    // creates a new emailladdress
    public function add() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('email', $_POST['email']);
        $this->bind('label', $_POST['label']);
        $this->bind('main', $_POST['main']);
        $this->bind('report', $_POST['report']);
        $this->bind('eQuote', $_POST['eQuote']);
        $this->bind('massemail', $_POST['massemail']);
        $this->bind('AssignEmail', $_POST['AssignEmail']);
        
        return $this->write("email:add","[e2mobile/api/business/basic/email/add]");
    }

    // updates an emailaddress
    public function edit() {
        $this->bind('bid', $this->segments[0]);
        $this->bind('eid', $this->segments[1]);
        $this->bind('email', $_POST['email']);
        $this->bind('label', $_POST['label']);
        $this->bind('main', $_POST['main']);
        $this->bind('report', $_POST['report']);
        $this->bind('eQuote', $_POST['eQuote']);
        $this->bind('massemail', $_POST['massemail']);
        $this->bind('returned', $_POST['returned']);
        $this->bind('AssignEmail', $_POST['AssignEmail']);
        
        return $this->write("email:edit","[e2mobile/api/business/basic/email/edit]");
    }

}

?>